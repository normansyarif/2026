import Charts
import SwiftData
import SwiftUI

struct TodayView: View {
    @Environment(\.modelContext) private var modelContext
    @Query(sort: \Habit.sortOrder) private var habits: [Habit]
    @Query private var habitLogs: [HabitLog]
    @Query private var settings: [AppSettings]
    @Query(sort: \LongTermGoal.createdAt, order: .reverse) private var goals: [LongTermGoal]
    @Query(sort: \GoalMilestone.sortOrder) private var milestones: [GoalMilestone]
    @Query(sort: \WeightLossPlan.month) private var weightPlans: [WeightLossPlan]
    @Query(sort: \WeightLog.loggedFor) private var weightLogs: [WeightLog]

    @State private var habitValues: [UUID: String] = [:]
    @State private var valueHabit: Habit?
    @State private var quickActionHabit: Habit?
    private let today = Date().startOfDay

    var scheduledHabits: [Habit] {
        habits.filter { $0.isScheduled(for: today) }
    }

    var body: some View {
        NavigationStack {
            ZStack {
                AppBackground()
                ScrollView {
                    VStack(alignment: .leading, spacing: 18) {
                        header
                        weightSummarySection
                        weightProgressSection
                        habitSection
                        milestoneSection
                    }
                    .padding()
                }
            }
            .navigationTitle("Today")
            .toolbarTitleDisplayMode(.large)
            .onAppear(perform: primeInputs)
            .sheet(item: $valueHabit) { habit in
                HabitValueSheet(
                    habit: habit,
                    initialValue: AppCalculations.log(for: habit, on: today, logs: habitLogs)?.value ?? 0
                ) { value in
                    DataStore.upsertHabitLog(habit: habit, date: today, value: value, logs: habitLogs, context: modelContext)
                    habitValues[habit.id] = Formatters.amount(value)
                    valueHabit = nil
                }
                .presentationDetents([.height(280), .medium])
            }
            .alert(quickActionTitle, isPresented: quickActionPresented) {
                if let habit = quickActionHabit, isCompleted(habit) {
                    Button("Undo", role: .destructive) {
                        DataStore.upsertHabitLog(habit: habit, date: today, value: 0, logs: habitLogs, context: modelContext)
                        habitValues[habit.id] = "0"
                        quickActionHabit = nil
                    }
                    Button("Cancel", role: .cancel) {
                        quickActionHabit = nil
                    }
                } else if let habit = quickActionHabit {
                    Button("Yes") {
                        DataStore.upsertHabitLog(habit: habit, date: today, value: habit.dailyGoal, logs: habitLogs, context: modelContext)
                        habitValues[habit.id] = Formatters.amount(habit.dailyGoal)
                        quickActionHabit = nil
                    }
                    Button("No", role: .cancel) {
                        quickActionHabit = nil
                    }
                }
            } message: {
                Text(quickActionMessage)
            }
        }
    }

    private var weightSummarySection: some View {
        let latestLog = weightLogs.sorted { $0.loggedFor > $1.loggedFor }.first
        let todayPlan = AppCalculations.currentMonthPlan(for: today, plans: weightPlans)
        let dailyTarget = todayPlan.map { AppCalculations.projectedWeight(for: $0, on: today) }

        return VStack(alignment: .leading, spacing: 12) {
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
        }
    }

    @ViewBuilder
    private var weightProgressSection: some View {
        let latestLog = weightLogs
            .filter { $0.loggedFor <= today }
            .sorted { $0.loggedFor > $1.loggedFor }
            .first
        let currentMonthLog = weightLogs
            .filter { Calendar.app.isDate($0.loggedFor, equalTo: today, toGranularity: .month) }
            .sorted { $0.loggedFor > $1.loggedFor }
            .first

        if let overall = AppCalculations.overallWeightGoal(weightPlans),
           let latestLog {
            WeightProgressCard(
                title: "Overall Weight Progress",
                start: overall.start,
                current: latestLog.rollingAverageWeight,
                goal: overall.final
            )
        }

        if let currentPlan = AppCalculations.currentMonthPlan(for: today, plans: weightPlans),
           let currentMonthLog {
            WeightProgressCard(
                title: "Monthly Weight Progress",
                start: currentPlan.startingWeight,
                current: currentMonthLog.rollingAverageWeight,
                goal: currentPlan.goalWeight
            )
        }
    }

    private var header: some View {
        let counters = AppCalculations.timelineCounters(settings: settings.first, today: today)
        return VStack(alignment: .leading, spacing: 16) {
            Text(today, format: .dateTime.weekday(.wide).month(.wide).day())
                .font(.title2.bold())
            HStack(spacing: 12) {
                MetricTile(title: "Week", value: counters.week.map(String.init) ?? "-", systemImage: "calendar.badge.clock")
                MetricTile(title: "Days Left", value: counters.days.map(String.init) ?? "-", systemImage: "hourglass")
            }
        }
    }

    private var habitSection: some View {
        VStack(alignment: .leading, spacing: 12) {
            Label("Scheduled Habits", systemImage: "checkmark.circle")
                .font(.headline)

            if scheduledHabits.isEmpty {
                Text("No habits are scheduled today.")
                    .foregroundStyle(.secondary)
                    .glassCard()
            } else {
                ForEach(scheduledHabits) { habit in
                    let log = AppCalculations.log(for: habit, on: today, logs: habitLogs)
                    Button {
                        openHabitLogger(for: habit)
                    } label: {
                        HabitSummaryCard(habit: habit, log: log)
                    }
                    .buttonStyle(.plain)
                }
            }
        }
    }

    private var milestoneSection: some View {
        let goalsByID = Dictionary(uniqueKeysWithValues: goals.map { ($0.id, $0.name) })
        let current = milestones
            .filter { Calendar.app.isDate($0.estimatedCompletionMonth, equalTo: today.startOfMonth, toGranularity: .month) }
            .sorted { $0.sortOrder < $1.sortOrder }

        return VStack(alignment: .leading, spacing: 12) {
            Label("This Month's Milestones", systemImage: "flag")
                .font(.headline)
            if current.isEmpty {
                Text("No milestones are due this month.")
                    .foregroundStyle(.secondary)
                    .glassCard()
            } else {
                ForEach(current) { milestone in
                    Toggle(isOn: Binding(
                        get: { milestone.completed },
                        set: { milestone.completed = $0; milestone.updatedAt = Date(); try? modelContext.save() }
                    )) {
                        VStack(alignment: .leading, spacing: 4) {
                            Text(milestone.name)
                            Text(goalsByID[milestone.goalID] ?? "Goal")
                                .font(.caption)
                                .foregroundStyle(.secondary)
                        }
                    }
                    .glassCard()
                }
            }
        }
    }

    private func primeInputs() {
        for habit in scheduledHabits {
            if habitValues[habit.id] == nil {
                habitValues[habit.id] = AppCalculations.log(for: habit, on: today, logs: habitLogs).map { Formatters.amount($0.value) } ?? ""
            }
        }
    }

    private var quickActionPresented: Binding<Bool> {
        Binding(
            get: { quickActionHabit != nil },
            set: { isPresented in
                if !isPresented {
                    quickActionHabit = nil
                }
            }
        )
    }

    private var quickActionTitle: String {
        guard let habit = quickActionHabit else { return "" }
        return isCompleted(habit) ? "Undo \(habit.name)?" : "Have you done \(habit.name)?"
    }

    private var quickActionMessage: String {
        guard let habit = quickActionHabit else { return "" }
        return isCompleted(habit)
            ? "This will mark the habit incomplete for today."
            : "Choose Yes to mark it complete for today."
    }

    private func openHabitLogger(for habit: Habit) {
        if habit.dailyGoal == 1 {
            quickActionHabit = habit
        } else {
            valueHabit = habit
        }
    }

    private func isCompleted(_ habit: Habit) -> Bool {
        AppCalculations.log(for: habit, on: today, logs: habitLogs)?.completed == true
    }
}

struct WeightProgressCard: View {
    let title: String
    let start: Double
    let current: Double
    let goal: Double

    var body: some View {
        VStack(alignment: .leading, spacing: 12) {
            Text(title)
                .font(.headline.weight(.semibold))

            HStack(alignment: .firstTextBaseline) {
                Text(percentText)
                    .foregroundStyle(.secondary)
                    .frame(maxWidth: .infinity, alignment: .leading)

                Spacer()

                Text(statusText)
                    .foregroundStyle(.secondary)
                    .frame(maxWidth: .infinity, alignment: .trailing)
            }
            .font(.subheadline.weight(.semibold))
            .lineLimit(1)
            .minimumScaleFactor(0.75)

            ProgressView(value: progress)
                .tint(.green)
        }
        .glassCard()
    }

    private var progress: Double {
        let totalChange = goal - start
        guard abs(totalChange) > 0.001 else { return 1 }

        let rawProgress = (current - start) / totalChange
        return min(max(rawProgress, 0), 1)
    }

    private var percentText: String {
        String(format: "%.2f%%", progress * 100)
    }

    private var statusText: String {
        if progress >= 1 {
            return "Goal range reached."
        }

        return "\(Formatters.amount(abs(current - goal))) kg to go"
    }
}

struct HabitSummaryCard: View {
    let habit: Habit
    let log: HabitLog?

    var body: some View {
        VStack(alignment: .leading, spacing: 14) {
            HStack(alignment: .top, spacing: 12) {
                VStack(alignment: .leading, spacing: 6) {
                    Text(habit.name)
                        .font(.headline)
                    Text(counterText)
                        .font(.subheadline.weight(.medium))
                        .foregroundStyle(.secondary)
                }
                Spacer()
                VStack(alignment: .trailing, spacing: 6) {
                    Image(systemName: isCompleted ? "checkmark.seal.fill" : "circle.dashed")
                        .font(.title2)
                        .foregroundStyle(isCompleted ? .green : .secondary)
                    Text(isCompleted ? "Completed" : "Incomplete")
                        .font(.caption)
                        .foregroundStyle(isCompleted ? .green : .secondary)
                }
            }
            ProgressView(value: progress)
        }
        .contentShape(RoundedRectangle(cornerRadius: 24, style: .continuous))
        .glassCard()
    }

    private var isCompleted: Bool {
        log?.completed == true
    }

    private var progress: Double {
        min((log?.value ?? 0) / max(habit.dailyGoal, 0.01), 1)
    }

    private var counterText: String {
        "\(Formatters.amount(log?.value ?? 0)) / \(Formatters.amount(habit.dailyGoal)) \(habit.unit)"
    }
}

struct HabitValueSheet: View {
    let habit: Habit
    let initialValue: Double
    let onSave: (Double) -> Void

    @Environment(\.dismiss) private var dismiss
    @State private var value: String

    init(habit: Habit, initialValue: Double, onSave: @escaping (Double) -> Void) {
        self.habit = habit
        self.initialValue = initialValue
        self.onSave = onSave
        _value = State(initialValue: initialValue > 0 ? Formatters.amount(initialValue) : "")
    }

    var body: some View {
        NavigationStack {
            Form {
                Section {
                    LabeledContent("Goal", value: "\(Formatters.amount(habit.dailyGoal)) \(habit.unit)")
                    TextField("Value", text: $value)
                        .keyboardType(.decimalPad)
                }
            }
            .navigationTitle(habit.name)
            .toolbar {
                ToolbarItem(placement: .cancellationAction) {
                    Button("Cancel") { dismiss() }
                }
                ToolbarItem(placement: .confirmationAction) {
                    Button("Save") {
                        guard let numericValue = Double(value), numericValue >= 0 else { return }
                        onSave(numericValue)
                    }
                }
            }
        }
    }
}

struct WeightTrendChart: View {
    let plan: WeightLossPlan
    let logs: [WeightLog]
    let today: Date

    var body: some View {
        Chart {
            ForEach(linePoints) { point in
                LineMark(
                    x: .value("Day", point.day),
                    y: .value("Weight", point.weight),
                    series: .value("Series", point.series.rawValue)
                )
                .foregroundStyle(point.series.color)
                .lineStyle(point.series.strokeStyle)
            }

            ForEach(actualPoints, id: \.day) { point in
                PointMark(
                    x: .value("Day", point.day),
                    y: .value("Weight", point.weight)
                )
                .foregroundStyle(.green)
            }
        }
        .chartXScale(domain: 1...today.daysInMonth)
        .chartYScale(domain: yDomain)
        .chartXAxis {
            AxisMarks(values: xAxisValues) { value in
                AxisGridLine()
                AxisTick()
                AxisValueLabel()
            }
        }
        .chartYAxis {
            AxisMarks(position: .leading, values: yAxisValues) { value in
                AxisGridLine()
                AxisTick()
                AxisValueLabel {
                    if let weight = value.as(Double.self) {
                        Text("\(Formatters.amount(weight)) kg")
                    }
                }
            }
        }
        .chartXAxisLabel("Day")
        .chartYAxisLabel("Weight")
    }

    private var projectedPoints: [(day: Int, weight: Double)] {
        (1...today.daysInMonth).map { day in
            let date = Calendar.app.date(byAdding: .day, value: day - 1, to: today.startOfMonth) ?? today
            return (day, AppCalculations.projectedWeight(for: plan, on: date))
        }
    }

    private var actualPoints: [(day: Int, weight: Double)] {
        logs
            .filter { Calendar.app.isDate($0.loggedFor, equalTo: today, toGranularity: .month) }
            .sorted { $0.loggedFor < $1.loggedFor }
            .map { ($0.loggedFor.dayOfMonth, $0.rollingAverageWeight) }
    }

    private var linePoints: [WeightTrendLinePoint] {
        projectedPoints.map { WeightTrendLinePoint(day: $0.day, weight: $0.weight, series: .projected) }
            + actualPoints.map { WeightTrendLinePoint(day: $0.day, weight: $0.weight, series: .rollingAverage) }
    }

    private var yDomain: ClosedRange<Double> {
        let allWeights = projectedPoints.map(\.weight) + actualPoints.map(\.weight) + [plan.startingWeight, plan.goalWeight]
        guard let minWeight = allWeights.min(), let maxWeight = allWeights.max() else {
            return 0...1
        }

        let range = max(0.1, maxWeight - minWeight)
        let padding = max(0.5, range * 0.12)
        let minValue = floor((minWeight - padding) * 10) / 10
        var maxValue = ceil((maxWeight + padding) * 10) / 10

        if maxValue <= minValue {
            maxValue = minValue + 1
        }

        return minValue...maxValue
    }

    private var yAxisValues: [Double] {
        let lower = yDomain.lowerBound
        let upper = yDomain.upperBound
        let step = max(0.5, ((upper - lower) / 5).rounded(to: 1))
        var values: [Double] = []
        var value = lower

        while value <= upper + 0.001 {
            values.append(value.rounded(to: 1))
            value += step
        }

        return values
    }

    private var xAxisValues: [Int] {
        stride(from: 1, through: today.daysInMonth, by: 5).map { $0 }
    }
}

private struct WeightTrendLinePoint: Identifiable {
    let day: Int
    let weight: Double
    let series: WeightTrendSeries

    var id: String {
        "\(series.rawValue)-\(day)"
    }
}

private enum WeightTrendSeries: String {
    case projected = "Projected"
    case rollingAverage = "Rolling Average"

    var color: Color {
        switch self {
        case .projected:
            .secondary
        case .rollingAverage:
            .green
        }
    }

    var strokeStyle: StrokeStyle {
        switch self {
        case .projected:
            StrokeStyle(lineWidth: 2.5, dash: [6, 5])
        case .rollingAverage:
            StrokeStyle(lineWidth: 2.5)
        }
    }
}
