import SwiftData
import SwiftUI

struct CalendarView: View {
    @Query(sort: \Habit.sortOrder) private var habits: [Habit]
    @Query private var habitLogs: [HabitLog]
    @Query(sort: \WeightLossPlan.month) private var weightPlans: [WeightLossPlan]
    @Query(sort: \WeightLog.loggedFor) private var weightLogs: [WeightLog]

    @State private var month = Date().startOfMonth
    @State private var selectedStatus: DayStatus?
    private let columns = Array(repeating: GridItem(.flexible(), spacing: 8), count: 7)

    var body: some View {
        NavigationStack {
            ZStack {
                AppBackground()
                ScrollView {
                    VStack(alignment: .leading, spacing: 18) {
                        HStack {
                            Button { moveMonth(-1) } label: { Image(systemName: "chevron.left") }
                                .buttonStyle(.bordered)
                            Spacer()
                            Text(Formatters.month.string(from: month))
                                .font(.title2.bold())
                            Spacer()
                            Button { moveMonth(1) } label: { Image(systemName: "chevron.right") }
                                .buttonStyle(.bordered)
                        }
                        .glassCard()

                        LazyVGrid(columns: columns, spacing: 8) {
                            ForEach(Weekday.allCases) { day in
                                Text(day.shortLabel)
                                    .font(.caption.bold())
                                    .foregroundStyle(.secondary)
                            }
                            ForEach(0..<leadingBlankDays, id: \.self) { _ in Color.clear.frame(height: 54) }
                            ForEach(monthStatuses) { status in
                                Button {
                                    selectedStatus = status
                                } label: {
                                    Text("\(status.date.dayOfMonth)")
                                        .font(.headline)
                                        .frame(maxWidth: .infinity, minHeight: 54)
                                        .background(color(for: status.state), in: RoundedRectangle(cornerRadius: 16, style: .continuous))
                                        .overlay {
                                            if Calendar.app.isDateInToday(status.date) {
                                                RoundedRectangle(cornerRadius: 16).stroke(.tint, lineWidth: 2)
                                            }
                                        }
                                }
                                .buttonStyle(.plain)
                            }
                        }
                        .glassCard()
                    }
                    .padding()
                }
            }
            .navigationTitle("Calendar")
            .sheet(item: $selectedStatus) { status in
                DayDetailView(status: status)
                    .presentationDetents([.medium, .large])
            }
        }
    }

    private var monthStatuses: [DayStatus] {
        (1...month.daysInMonth).map { day in
            let date = Calendar.app.date(byAdding: .day, value: day - 1, to: month) ?? month
            return AppCalculations.status(for: date, habits: habits, habitLogs: habitLogs, weightLogs: weightLogs, weightPlans: weightPlans)
        }
    }

    private var leadingBlankDays: Int {
        let weekday = Calendar.app.component(.weekday, from: month)
        return (weekday + 5) % 7
    }

    private func color(for state: DayStatus.State) -> Color {
        switch state {
        case .complete: .green.opacity(0.24)
        case .missed: .red.opacity(0.20)
        case .future: .yellow.opacity(0.25)
        }
    }

    private func moveMonth(_ offset: Int) {
        month = Calendar.app.date(byAdding: .month, value: offset, to: month) ?? month
    }
}

struct DayDetailView: View {
    let status: DayStatus

    var body: some View {
        NavigationStack {
            List {
                Section("Status") {
                    LabeledContent("Day", value: Formatters.shortDate.string(from: status.date))
                    LabeledContent("Result", value: status.label)
                    LabeledContent("Habits", value: "\(status.completedHabitCount)/\(status.scheduledHabitCount)")
                    LabeledContent("Weight", value: weightSummary)
                }
                Section("Habits") {
                    if status.habitRows.isEmpty {
                        Text("No habits to show.")
                    } else {
                        ForEach(status.habitRows) { row in
                            LabeledContent(row.name, value: row.value)
                        }
                    }
                }
            }
            .navigationTitle("Day Details")
        }
    }

    private var weightSummary: String {
        guard let target = status.targetWeight else { return "No monthly target" }
        guard let average = status.rollingAverageWeight else { return "Target \(Formatters.amount(target)) kg" }
        return "\(Formatters.amount(average)) kg avg vs \(Formatters.amount(target)) kg target"
    }
}
