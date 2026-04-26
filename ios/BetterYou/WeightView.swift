import SwiftData
import SwiftUI

struct WeightView: View {
    @Environment(\.scenePhase) private var scenePhase
    @Environment(\.modelContext) private var modelContext
    @Query(sort: \WeightLossPlan.month) private var plans: [WeightLossPlan]
    @Query(sort: \WeightLog.loggedFor) private var logs: [WeightLog]

    @StateObject private var healthKit = HealthKitWeightReader()
    @State private var isShowingManualLog = false
    @State private var chartMonth = Date().startOfMonth
    private let today = Date().startOfDay

    var body: some View {
        NavigationStack {
            ZStack {
                AppBackground()
                ScrollView {
                    VStack(alignment: .leading, spacing: 18) {
                        weightCard
                    }
                    .padding()
                }
            }
            .navigationTitle("Weight")
            .onAppear(perform: refreshHealthWeight)
            .onChange(of: scenePhase) { _, phase in
                if phase == .active {
                    refreshHealthWeight()
                }
            }
            .sheet(isPresented: $isShowingManualLog) {
                ManualWeightLogSheet(initialWeight: todaysWeightLog?.weight) { value in
                    saveWeight(value)
                    isShowingManualLog = false
                }
                .presentationDetents([.height(240), .medium])
            }
        }
    }

    private var weightCard: some View {
        let todayPlan = AppCalculations.currentMonthPlan(for: today, plans: plans)
        let selectedPlan = AppCalculations.currentMonthPlan(for: chartMonth, plans: plans)
        let latestLog = logs.sorted { $0.loggedFor > $1.loggedFor }.first
        let todaysLog = todaysWeightLog
        let dailyTarget = todayPlan.map { AppCalculations.projectedWeight(for: $0, on: today) }

        return VStack(alignment: .leading, spacing: 14) {
            Label("Weight", systemImage: "scalemass")
                .font(.headline)

            HStack(spacing: 12) {
                MetricTile(
                    title: "Current Weight",
                    value: latestLog.map { "\(Formatters.amount($0.rollingAverageWeight)) kg" } ?? "-",
                    systemImage: "waveform.path.ecg"
                )
                MetricTile(
                    title: "Daily Target",
                    value: dailyTarget.map { "\(Formatters.amount($0)) kg" } ?? "-",
                    systemImage: "scope"
                )
            }

            WeightStatusTile(
                currentWeight: latestLog?.rollingAverageWeight,
                dailyTarget: dailyTarget
            )

            HStack(spacing: 10) {
                Button {
                    isShowingManualLog = true
                } label: {
                    Label("Manual Log", systemImage: "square.and.pencil")
                        .frame(maxWidth: .infinity)
                }
                .buttonStyle(.borderedProminent)

                Button(action: logHealthWeight) {
                    Label(healthKit.buttonTitle, systemImage: "heart.fill")
                        .frame(maxWidth: .infinity)
                }
                .buttonStyle(.borderedProminent)
                .tint(.red)
                .foregroundStyle(.white)
                .disabled(healthKit.latestWeight == nil)
            }

            if todaysLog != nil {
                Text("Today has already been logged. New entries will update today's weight.")
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }

            HStack {
                Button { moveChartMonth(-1) } label: { Image(systemName: "chevron.left") }
                    .buttonStyle(.bordered)
                Spacer()
                Text(Formatters.month.string(from: chartMonth))
                    .font(.headline)
                Spacer()
                Button { moveChartMonth(1) } label: { Image(systemName: "chevron.right") }
                    .buttonStyle(.bordered)
            }

            if let selectedPlan {
                WeightTrendChart(plan: selectedPlan, logs: logs, today: chartMonth)
                    .frame(height: 270)
            } else {
                Text("Set a weight goal for \(Formatters.month.string(from: chartMonth)) in More to see the target trend.")
                    .font(.subheadline)
                    .foregroundStyle(.secondary)
            }

            NavigationLink {
                WeightLogListView(month: chartMonth)
            } label: {
                Label("Weight Log", systemImage: "list.bullet.rectangle")
                    .font(.headline)
                    .frame(maxWidth: .infinity)
            }
            .buttonStyle(.bordered)
        }
        .glassCard()
    }

    private func refreshHealthWeight() {
        healthKit.refresh()
    }

    private func logHealthWeight() {
        guard let value = healthKit.latestWeight else { return }
        saveWeight(value)
    }

    private func saveWeight(_ value: Double) {
        DataStore.upsertWeightLog(date: today, weight: value, logs: logs, context: modelContext)
    }

    private var todaysWeightLog: WeightLog? {
        logs.first { Calendar.app.isDate($0.loggedFor, inSameDayAs: today) }
    }

    private func moveChartMonth(_ offset: Int) {
        chartMonth = (Calendar.app.date(byAdding: .month, value: offset, to: chartMonth) ?? chartMonth).startOfMonth
    }
}

struct ManualWeightLogSheet: View {
    let onSave: (Double) -> Void

    @Environment(\.dismiss) private var dismiss
    @State private var weight: String

    init(initialWeight: Double?, onSave: @escaping (Double) -> Void) {
        self.onSave = onSave
        _weight = State(initialValue: initialWeight.map(Formatters.amount) ?? "")
    }

    var body: some View {
        NavigationStack {
            Form {
                Section {
                    TextField("Weight", text: $weight)
                        .keyboardType(.decimalPad)
                }

                if let validationMessage {
                    Section {
                        Text(validationMessage)
                            .font(.caption)
                            .foregroundStyle(.red)
                    }
                }
            }
            .navigationTitle("Manual Log")
            .toolbar {
                ToolbarItem(placement: .cancellationAction) {
                    Button("Cancel") { dismiss() }
                }
                ToolbarItem(placement: .confirmationAction) {
                    Button("Save", action: save)
                        .disabled(validationMessage != nil)
                }
            }
        }
    }

    private var validationMessage: String? {
        guard !weight.isEmpty else { return nil }
        guard let value = Double(weight), value > 0 else {
            return "Weight must be greater than zero."
        }
        return nil
    }

    private func save() {
        guard validationMessage == nil,
              let value = Double(weight)
        else { return }

        onSave(value)
        dismiss()
    }
}

struct WeightLogListView: View {
    @Environment(\.modelContext) private var modelContext
    @Query(sort: \WeightLog.loggedFor, order: .reverse) private var logs: [WeightLog]
    let month: Date

    var body: some View {
        ZStack {
            AppBackground()
            List {
                Section(Formatters.month.string(from: month)) {
                    if filteredLogs.isEmpty {
                        Text("No weight logs for this month.")
                            .foregroundStyle(.secondary)
                    } else {
                        ForEach(filteredLogs) { log in
                            WeightLogRow(log: log)
                                .swipeActions(edge: .trailing) {
                                    Button(role: .destructive) {
                                        delete(log)
                                    } label: {
                                        Label("Delete", systemImage: "trash")
                                    }
                                }
                        }
                        .onDelete(perform: delete)
                    }
                }
            }
            .scrollContentBackground(.hidden)
        }
        .navigationTitle("Weight Log")
    }

    private var filteredLogs: [WeightLog] {
        logs.filter { Calendar.app.isDate($0.loggedFor, equalTo: month, toGranularity: .month) }
    }

    private func delete(_ log: WeightLog) {
        modelContext.delete(log)
        DataStore.recomputeRollingAverages(context: modelContext)
    }

    private func delete(at offsets: IndexSet) {
        offsets.map { filteredLogs[$0] }.forEach(modelContext.delete)
        DataStore.recomputeRollingAverages(context: modelContext)
    }
}

struct WeightLogRow: View {
    let log: WeightLog

    var body: some View {
        VStack(alignment: .leading, spacing: 10) {
            Text(Formatters.shortDate.string(from: log.loggedFor))
                .font(.headline)

            HStack(spacing: 12) {
                LabeledContent("Raw", value: "\(Formatters.amount(log.weight)) kg")
                LabeledContent("Current Weight", value: "\(Formatters.amount(log.rollingAverageWeight)) kg")
            }
            .font(.subheadline)
        }
        .padding(.vertical, 4)
    }
}
struct WeightGoalsView: View {
    @Environment(\.modelContext) private var modelContext
    @Query(sort: \WeightLossPlan.month) private var plans: [WeightLossPlan]

    @State private var isAddingGoal = false
    @State private var editingPlan: WeightLossPlan?

    var body: some View {
        NavigationStack {
            ZStack {
                AppBackground()
                ScrollView {
                    VStack(alignment: .leading, spacing: 18) {
                        if let overall = AppCalculations.overallWeightGoal(plans) {
                            HStack(spacing: 12) {
                                MetricTile(title: "Overall Start", value: "\(Formatters.amount(overall.start)) kg", systemImage: "figure")
                                MetricTile(title: "Final Target", value: "\(Formatters.amount(overall.final)) kg", systemImage: "target")
                            }
                        }

                        ForEach(plans) { plan in
                            WeightGoalRow(
                                plan: plan,
                                onEdit: { editingPlan = plan },
                                onDelete: { delete(plan) }
                            )
                                .onTapGesture {
                                    editingPlan = plan
                                }
                                .swipeActions(edge: .trailing) {
                                    Button(role: .destructive) {
                                        delete(plan)
                                    } label: {
                                        Label("Delete", systemImage: "trash")
                                    }

                                    Button {
                                        editingPlan = plan
                                    } label: {
                                        Label("Edit", systemImage: "pencil")
                                    }
                                    .tint(.blue)
                                }
                        }
                    }
                    .padding()
                }
            }
            .navigationTitle("Weight Goal")
            .toolbar {
                ToolbarItem(placement: .topBarTrailing) {
                    Button {
                        isAddingGoal = true
                    } label: {
                        Image(systemName: "plus")
                    }
                }
            }
            .sheet(isPresented: $isAddingGoal) {
                WeightGoalEditor(
                    title: "Set a new weight goal",
                    existingMonths: plans.map(\.month)
                ) { month, startingWeight, goalWeight in
                    modelContext.insert(WeightLossPlan(month: month, startingWeight: startingWeight, goalWeight: goalWeight))
                    try? modelContext.save()
                    isAddingGoal = false
                }
                .presentationDetents([.medium])
            }
            .sheet(item: $editingPlan) { plan in
                WeightGoalEditor(
                    title: "Edit weight goal",
                    month: plan.month,
                    startingWeight: plan.startingWeight,
                    goalWeight: plan.goalWeight,
                    existingMonths: plans.filter { $0.id != plan.id }.map(\.month)
                ) { month, startingWeight, goalWeight in
                    plan.month = month.startOfMonth
                    plan.startingWeight = startingWeight
                    plan.goalWeight = goalWeight
                    plan.updatedAt = Date()
                    try? modelContext.save()
                    editingPlan = nil
                }
                .presentationDetents([.medium])
            }
        }
    }

    private func delete(_ plan: WeightLossPlan) {
        modelContext.delete(plan)
        try? modelContext.save()
    }
}

struct WeightGoalRow: View {
    let plan: WeightLossPlan
    let onEdit: () -> Void
    let onDelete: () -> Void

    var body: some View {
        VStack(alignment: .leading, spacing: 12) {
            HStack(alignment: .firstTextBaseline) {
                Text(Formatters.month.string(from: plan.month))
                    .font(.headline)
                Spacer()
                Text("\(Formatters.amount(plan.startingWeight - plan.goalWeight)) kg planned")
                    .font(.caption)
                    .foregroundStyle(.secondary)
                Menu {
                    Button(action: onEdit) {
                        Label("Edit", systemImage: "pencil")
                    }
                    Button(role: .destructive, action: onDelete) {
                        Label("Delete", systemImage: "trash")
                    }
                } label: {
                    Image(systemName: "ellipsis.circle")
                        .font(.title3)
                        .foregroundStyle(.secondary)
                        .frame(width: 28, height: 28)
                }
            }

            HStack(spacing: 10) {
                CompactWeightMetric(title: "Start", value: plan.startingWeight, systemImage: "arrow.up.left")
                CompactWeightMetric(title: "Goal", value: plan.goalWeight, systemImage: "arrow.down.right")
            }
        }
        .contentShape(RoundedRectangle(cornerRadius: 22, style: .continuous))
        .glassCard(radius: 22)
    }
}

struct CompactWeightMetric: View {
    let title: String
    let value: Double
    let systemImage: String

    var body: some View {
        HStack(spacing: 10) {
            Image(systemName: systemImage)
                .foregroundStyle(.tint)
            VStack(alignment: .leading, spacing: 2) {
                Text("\(Formatters.amount(value)) kg")
                    .font(.headline)
                Text(title)
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }
            Spacer(minLength: 0)
        }
        .padding(12)
        .frame(maxWidth: .infinity)
        .background(.thinMaterial, in: RoundedRectangle(cornerRadius: 16, style: .continuous))
    }
}

struct WeightGoalEditor: View {
    let title: String
    let existingMonths: [Date]
    let onSave: (Date, Double, Double) -> Void

    @Environment(\.dismiss) private var dismiss
    @State private var month: Date
    @State private var startingWeight: String
    @State private var goalWeight: String

    init(
        title: String,
        month: Date = Date().startOfMonth,
        startingWeight: Double? = nil,
        goalWeight: Double? = nil,
        existingMonths: [Date],
        onSave: @escaping (Date, Double, Double) -> Void
    ) {
        self.title = title
        self.existingMonths = existingMonths
        self.onSave = onSave
        _month = State(initialValue: month.startOfMonth)
        _startingWeight = State(initialValue: startingWeight.map(Formatters.amount) ?? "")
        _goalWeight = State(initialValue: goalWeight.map(Formatters.amount) ?? "")
    }

    var body: some View {
        NavigationStack {
            Form {
                Section {
                    DatePicker("Month", selection: $month, displayedComponents: [.date])
                    TextField("Starting weight", text: $startingWeight)
                        .keyboardType(.decimalPad)
                    TextField("Goal weight", text: $goalWeight)
                        .keyboardType(.decimalPad)
                }

                if let validationMessage {
                    Section {
                        Text(validationMessage)
                            .font(.caption)
                            .foregroundStyle(.red)
                    }
                }
            }
            .navigationTitle(title)
            .toolbar {
                ToolbarItem(placement: .cancellationAction) {
                    Button("Cancel") { dismiss() }
                }
                ToolbarItem(placement: .confirmationAction) {
                    Button("Save", action: save)
                        .disabled(validationMessage != nil)
                }
            }
        }
    }

    private var validationMessage: String? {
        guard let start = Double(startingWeight), start > 0 else {
            return startingWeight.isEmpty ? nil : "Starting weight must be greater than zero."
        }
        guard let goal = Double(goalWeight), goal > 0 else {
            return goalWeight.isEmpty ? nil : "Goal weight must be greater than zero."
        }
        guard goal < start else {
            return "Goal weight must be below starting weight."
        }
        guard !existingMonths.contains(where: { Calendar.app.isDate($0, equalTo: month, toGranularity: .month) }) else {
            return "That month already has a weight goal."
        }
        return nil
    }

    private func save() {
        guard validationMessage == nil,
              let start = Double(startingWeight),
              let goal = Double(goalWeight)
        else { return }

        onSave(month.startOfMonth, start, goal)
    }
}
