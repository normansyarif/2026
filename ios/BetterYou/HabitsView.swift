import SwiftData
import SwiftUI

struct HabitsView: View {
    @Environment(\.modelContext) private var modelContext
    @Query(sort: \Habit.sortOrder) private var habits: [Habit]
    @Query private var logs: [HabitLog]

    @State private var isShowingNewHabit = false

    var body: some View {
        NavigationStack {
            ZStack {
                AppBackground()
                List {
                    Section("Habits") {
                        ForEach(habits) { habit in
                            NavigationLink {
                                HabitEditorView(habit: habit)
                            } label: {
                                VStack(alignment: .leading, spacing: 4) {
                                    Text(habit.name)
                                    Text("\(Formatters.amount(habit.dailyGoal)) \(habit.unit) - \(habit.days.map { Weekday(rawValue: $0)?.shortLabel ?? $0 }.joined(separator: ", ")) - \(habit.reminderTimeLabel)")
                                        .font(.caption)
                                        .foregroundStyle(.secondary)
                                }
                            }
                        }
                        .onDelete(perform: deleteHabits)
                        .onMove(perform: moveHabits)
                    }
                }
                .scrollContentBackground(.hidden)
            }
            .navigationTitle("Habits")
            .toolbar {
                ToolbarItem(placement: .topBarLeading) {
                    EditButton()
                }
                ToolbarItem(placement: .topBarTrailing) {
                    Button {
                        isShowingNewHabit = true
                    } label: {
                        Image(systemName: "plus")
                    }
                }
            }
            .sheet(isPresented: $isShowingNewHabit) {
                NewHabitSheet { name, dailyGoal, unit, days, reminderMinutes in
                    addHabit(name: name, dailyGoal: dailyGoal, unit: unit, days: days, reminderMinutes: reminderMinutes)
                    isShowingNewHabit = false
                }
                .presentationDetents([.medium, .large])
            }
        }
    }

    private func addHabit(name: String, dailyGoal: Double, unit: String, days: [String], reminderMinutes: Int?) {
        let habit = Habit(name: name, days: days, dailyGoal: dailyGoal, unit: unit, sortOrder: (habits.map(\.sortOrder).max() ?? 0) + 1, reminderMinutes: reminderMinutes)
        modelContext.insert(habit)
        try? modelContext.save()
        HabitNotificationScheduler.sync(habit: habit)
    }

    private func deleteHabits(at offsets: IndexSet) {
        for index in offsets {
            let habit = habits[index]
            HabitNotificationScheduler.remove(habitID: habit.id)
            logs.filter { $0.habitID == habit.id }.forEach(modelContext.delete)
            modelContext.delete(habit)
        }
        try? modelContext.save()
    }

    private func moveHabits(from source: IndexSet, to destination: Int) {
        var ordered = habits
        ordered.move(fromOffsets: source, toOffset: destination)
        for (index, habit) in ordered.enumerated() {
            habit.sortOrder = index + 1
        }
        try? modelContext.save()
    }
}

struct NewHabitSheet: View {
    let onSave: (String, Double, String, [String], Int?) -> Void

    @Environment(\.dismiss) private var dismiss
    @State private var name = ""
    @State private var unit = "count"
    @State private var dailyGoal = "1"
    @State private var selectedDays = Set(Weekday.allCases.map(\.rawValue))
    @State private var hasReminder = false
    @State private var reminderTime = Habit.timeDate(from: 9 * 60)

    var body: some View {
        NavigationStack {
            Form {
                Section("New Habit") {
                    TextField("Name", text: $name)
                    TextField("Daily goal", text: $dailyGoal)
                        .keyboardType(.decimalPad)
                    TextField("Unit", text: $unit)
                }

                Section("Schedule") {
                    weekdayPicker
                    Toggle("Set time", isOn: $hasReminder)
                    if hasReminder {
                        DatePicker("Time", selection: $reminderTime, displayedComponents: [.hourAndMinute])
                    }
                }

                if let validationMessage {
                    Section {
                        Text(validationMessage)
                            .font(.caption)
                            .foregroundStyle(.red)
                    }
                }
            }
            .navigationTitle("New Habit")
            .toolbar {
                ToolbarItem(placement: .cancellationAction) {
                    Button("Cancel") { dismiss() }
                }
                ToolbarItem(placement: .confirmationAction) {
                    Button("Add", action: save)
                        .disabled(validationMessage != nil)
                }
            }
        }
    }

    private var weekdayPicker: some View {
        FlowLayout {
            ForEach(Weekday.allCases) { day in
                Toggle(day.shortLabel, isOn: Binding(
                    get: { selectedDays.contains(day.rawValue) },
                    set: { enabled in
                        if enabled {
                            selectedDays.insert(day.rawValue)
                        } else {
                            selectedDays.remove(day.rawValue)
                        }
                    }
                ))
                .toggleStyle(.button)
            }
        }
    }

    private var validationMessage: String? {
        guard !name.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty else {
            return "Habit name is required."
        }
        guard let goal = Double(dailyGoal), goal > 0 else {
            return "Daily goal must be greater than zero."
        }
        guard !selectedDays.isEmpty else {
            return "Choose at least one day."
        }
        return nil
    }

    private func save() {
        guard validationMessage == nil,
              let goal = Double(dailyGoal)
        else { return }

        let orderedDays = Weekday.allCases.map(\.rawValue).filter(selectedDays.contains)
        let reminderMinutes = hasReminder ? Habit.minutesAfterMidnight(from: reminderTime) : nil
        onSave(name.trimmingCharacters(in: .whitespacesAndNewlines), goal, unit, orderedDays, reminderMinutes)
        dismiss()
    }
}

struct HabitEditorView: View {
    @Environment(\.modelContext) private var modelContext
    @Bindable var habit: Habit
    @State private var selectedDays: Set<String> = []
    @State private var hasReminder = false
    @State private var reminderTime = Habit.timeDate(from: 9 * 60)

    var body: some View {
        Form {
            TextField("Name", text: $habit.name)
            TextField("Unit", text: $habit.unit)
            TextField("Daily goal", value: $habit.dailyGoal, format: .number)
                .keyboardType(.decimalPad)
            Toggle("Active", isOn: $habit.isActive)
            Section("Schedule") {
                FlowLayout {
                    ForEach(Weekday.allCases) { day in
                        Toggle(day.shortLabel, isOn: Binding(
                            get: { selectedDays.contains(day.rawValue) },
                            set: { enabled in
                                if enabled { selectedDays.insert(day.rawValue) } else { selectedDays.remove(day.rawValue) }
                                habit.days = Weekday.allCases.map(\.rawValue).filter(selectedDays.contains)
                                habit.updatedAt = Date()
                            }
                        ))
                        .toggleStyle(.button)
                    }
                }
                Toggle("Set time", isOn: $hasReminder)
                if hasReminder {
                    DatePicker("Time", selection: $reminderTime, displayedComponents: [.hourAndMinute])
                }
            }
        }
        .navigationTitle("Edit Habit")
        .onAppear(perform: loadReminder)
        .onDisappear(perform: saveHabit)
    }

    private func loadReminder() {
        selectedDays = Set(habit.days)
        hasReminder = habit.reminderMinutes != nil
        reminderTime = Habit.timeDate(from: habit.reminderMinutes ?? (9 * 60))
    }

    private func saveHabit() {
        habit.reminderMinutes = hasReminder ? Habit.minutesAfterMidnight(from: reminderTime) : nil
        habit.updatedAt = Date()
        try? modelContext.save()
        HabitNotificationScheduler.sync(habit: habit)
    }
}

struct FlowLayout<Content: View>: View {
    @ViewBuilder var content: Content

    var body: some View {
        ViewThatFits(in: .horizontal) {
            HStack { content }
            VStack(alignment: .leading) { content }
        }
    }
}
