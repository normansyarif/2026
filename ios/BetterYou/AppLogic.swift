import Foundation
import SwiftData
import SwiftUI
import UniformTypeIdentifiers

struct DayStatus: Identifiable {
    enum State {
        case complete, missed, future
    }

    let id = UUID()
    let date: Date
    let state: State
    let targetWeight: Double?
    let rollingAverageWeight: Double?
    let scheduledHabitCount: Int
    let completedHabitCount: Int
    let habitRows: [HabitStatusRow]

    var label: String {
        switch state {
        case .complete: "Finished"
        case .missed: "Incomplete"
        case .future: "Upcoming"
        }
    }
}

struct HabitStatusRow: Identifiable {
    let id = UUID()
    let name: String
    let completed: Bool
    let value: String
}

enum AppCalculations {
    static func log(for habit: Habit, on date: Date, logs: [HabitLog]) -> HabitLog? {
        logs.first { $0.habitID == habit.id && Calendar.app.isDate($0.loggedFor, inSameDayAs: date) }
    }

    static func projectedWeight(for plan: WeightLossPlan, on date: Date) -> Double {
        let daysInMonth = plan.month.daysInMonth
        guard daysInMonth > 1 else { return plan.goalWeight }
        let progress = Double(date.dayOfMonth - 1) / Double(daysInMonth - 1)
        return (plan.startingWeight + ((plan.goalWeight - plan.startingWeight) * progress)).rounded(to: 2)
    }

    static func currentMonthPlan(for date: Date, plans: [WeightLossPlan]) -> WeightLossPlan? {
        plans.first { Calendar.app.isDate($0.month, equalTo: date.startOfMonth, toGranularity: .month) }
    }

    static func status(for date: Date, habits: [Habit], habitLogs: [HabitLog], weightLogs: [WeightLog], weightPlans: [WeightLossPlan], today: Date = Date()) -> DayStatus {
        let day = date.startOfDay
        let scheduled = habits
            .filter { $0.isScheduled(for: day) }
            .sorted { lhs, rhs in
                lhs.sortOrder == rhs.sortOrder ? lhs.name < rhs.name : lhs.sortOrder < rhs.sortOrder
            }
        let plan = currentMonthPlan(for: day, plans: weightPlans)
        let targetWeight = plan.map { projectedWeight(for: $0, on: day) }

        if day > today.startOfDay {
            return DayStatus(date: day, state: .future, targetWeight: targetWeight, rollingAverageWeight: nil, scheduledHabitCount: scheduled.count, completedHabitCount: 0, habitRows: [])
        }

        let rows = scheduled.map { habit -> HabitStatusRow in
            if let log = log(for: habit, on: day, logs: habitLogs) {
                let value = "\(Formatters.amount(log.value)) / \(Formatters.amount(habit.dailyGoal)) \(habit.unit)"
                return HabitStatusRow(name: habit.name, completed: log.completed, value: value)
            }
            return HabitStatusRow(name: habit.name, completed: false, value: "No log")
        }
        let completedCount = rows.filter(\.completed).count
        let allHabitsCompleted = scheduled.isEmpty || completedCount == scheduled.count
        let weightLog = weightLogs.first { Calendar.app.isDate($0.loggedFor, inSameDayAs: day) }
        let average = weightLog?.rollingAverageWeight
        let weightOnTarget = targetWeight != nil && average != nil && average! <= targetWeight!
        let complete = allHabitsCompleted && weightOnTarget

        return DayStatus(
            date: day,
            state: complete ? .complete : .missed,
            targetWeight: targetWeight,
            rollingAverageWeight: average,
            scheduledHabitCount: scheduled.count,
            completedHabitCount: completedCount,
            habitRows: rows
        )
    }

    static func timelineCounters(settings: AppSettings?, today: Date = Date()) -> (week: Int?, days: Int?) {
        let day = today.startOfDay
        var weekCount: Int?
        var dayCount: Int?

        if let start = settings?.timelineStartDate?.startOfDay {
            let diff = Calendar.app.dateComponents([.day], from: start, to: day).day ?? 0
            weekCount = diff < 0 ? 1 : Int(floor(Double(diff) / 7.0)) + 1
        }

        if let deadline = settings?.timelineDeadlineDate?.startOfDay {
            let diff = Calendar.app.dateComponents([.day], from: day, to: deadline).day ?? 0
            dayCount = max(0, diff)
        }

        return (weekCount, dayCount)
    }

    static func overallWeightGoal(_ plans: [WeightLossPlan]) -> (start: Double, checkpoint: Double, final: Double, startMonth: String, goalMonth: String)? {
        let sorted = plans.sorted { $0.month < $1.month }
        guard let first = sorted.first, let last = sorted.last else { return nil }
        return (first.startingWeight, last.startingWeight, last.goalWeight, Formatters.month.string(from: first.month), Formatters.month.string(from: last.month))
    }
}

extension Double {
    func rounded(to places: Int) -> Double {
        let divisor = pow(10.0, Double(places))
        return (self * divisor).rounded() / divisor
    }
}

enum DataStore {
    static func upsertHabitLog(habit: Habit, date: Date, value: Double, logs: [HabitLog], context: ModelContext) {
        let completed = habit.goalReached(value)
        if let existing = AppCalculations.log(for: habit, on: date, logs: logs) {
            existing.value = value
            existing.completed = completed
            existing.updatedAt = Date()
        } else {
            context.insert(HabitLog(habitID: habit.id, loggedFor: date, value: value, completed: completed))
        }
        try? context.save()
    }

    static func upsertWeightLog(date: Date, weight: Double, logs: [WeightLog], context: ModelContext) {
        if let existing = logs.first(where: { Calendar.app.isDate($0.loggedFor, inSameDayAs: date) }) {
            existing.weight = weight
            existing.updatedAt = Date()
        } else {
            context.insert(WeightLog(loggedFor: date, weight: weight))
        }
        recomputeRollingAverages(context: context)
    }

    static func recomputeRollingAverages(context: ModelContext) {
        let logs = ((try? context.fetch(FetchDescriptor<WeightLog>())) ?? []).sorted { $0.loggedFor < $1.loggedFor }
        for log in logs {
            let start = Calendar.app.date(byAdding: .day, value: -6, to: log.loggedFor) ?? log.loggedFor
            let window = logs.filter { $0.loggedFor >= start && $0.loggedFor <= log.loggedFor }
            let average = window.reduce(0) { $0 + $1.weight } / Double(max(window.count, 1))
            log.rollingAverageWeight = average.rounded(to: 2)
        }
        try? context.save()
    }

    static func settings(in context: ModelContext) -> AppSettings {
        if let existing = try? context.fetch(FetchDescriptor<AppSettings>()).first {
            return existing
        }
        let settings = AppSettings()
        context.insert(settings)
        try? context.save()
        return settings
    }
}

struct BackupDocument: FileDocument {
    static var readableContentTypes: [UTType] { [.json] }
    var data: Data

    init(data: Data = Data()) {
        self.data = data
    }

    init(configuration: ReadConfiguration) throws {
        self.data = configuration.file.regularFileContents ?? Data()
    }

    func fileWrapper(configuration: WriteConfiguration) throws -> FileWrapper {
        FileWrapper(regularFileWithContents: data)
    }
}

struct BackupEnvelope: Codable {
    var exportedAt: Date
    var habits: [HabitRecord]
    var habitLogs: [HabitLogRecord]
    var weightPlans: [WeightPlanRecord]
    var weightLogs: [WeightLogRecord]
    var goals: [GoalRecord]
    var milestones: [MilestoneRecord]
    var settings: SettingsRecord?
}

struct HabitRecord: Codable { var id: UUID; var name: String; var days: [String]; var dailyGoal: Double; var unit: String; var sortOrder: Int; var isActive: Bool; var createdAt: Date; var updatedAt: Date }
struct HabitLogRecord: Codable { var id: UUID; var habitID: UUID; var loggedFor: Date; var value: Double; var completed: Bool; var createdAt: Date; var updatedAt: Date }
struct WeightPlanRecord: Codable { var id: UUID; var month: Date; var startingWeight: Double; var goalWeight: Double; var createdAt: Date; var updatedAt: Date }
struct WeightLogRecord: Codable { var id: UUID; var loggedFor: Date; var weight: Double; var rollingAverageWeight: Double; var createdAt: Date; var updatedAt: Date }
struct GoalRecord: Codable { var id: UUID; var name: String; var createdAt: Date; var updatedAt: Date }
struct MilestoneRecord: Codable { var id: UUID; var goalID: UUID; var name: String; var estimatedCompletionMonth: Date; var sortOrder: Int; var completed: Bool; var createdAt: Date; var updatedAt: Date }
struct SettingsRecord: Codable { var id: UUID; var timelineStartDate: Date?; var timelineDeadlineDate: Date? }

enum BackupService {
    static func exportData(context: ModelContext) throws -> Data {
        let envelope = BackupEnvelope(
            exportedAt: Date(),
            habits: try context.fetch(FetchDescriptor<Habit>()).map { HabitRecord(id: $0.id, name: $0.name, days: $0.days, dailyGoal: $0.dailyGoal, unit: $0.unit, sortOrder: $0.sortOrder, isActive: $0.isActive, createdAt: $0.createdAt, updatedAt: $0.updatedAt) },
            habitLogs: try context.fetch(FetchDescriptor<HabitLog>()).map { HabitLogRecord(id: $0.id, habitID: $0.habitID, loggedFor: $0.loggedFor, value: $0.value, completed: $0.completed, createdAt: $0.createdAt, updatedAt: $0.updatedAt) },
            weightPlans: try context.fetch(FetchDescriptor<WeightLossPlan>()).map { WeightPlanRecord(id: $0.id, month: $0.month, startingWeight: $0.startingWeight, goalWeight: $0.goalWeight, createdAt: $0.createdAt, updatedAt: $0.updatedAt) },
            weightLogs: try context.fetch(FetchDescriptor<WeightLog>()).map { WeightLogRecord(id: $0.id, loggedFor: $0.loggedFor, weight: $0.weight, rollingAverageWeight: $0.rollingAverageWeight, createdAt: $0.createdAt, updatedAt: $0.updatedAt) },
            goals: try context.fetch(FetchDescriptor<LongTermGoal>()).map { GoalRecord(id: $0.id, name: $0.name, createdAt: $0.createdAt, updatedAt: $0.updatedAt) },
            milestones: try context.fetch(FetchDescriptor<GoalMilestone>()).map { MilestoneRecord(id: $0.id, goalID: $0.goalID, name: $0.name, estimatedCompletionMonth: $0.estimatedCompletionMonth, sortOrder: $0.sortOrder, completed: $0.completed, createdAt: $0.createdAt, updatedAt: $0.updatedAt) },
            settings: try context.fetch(FetchDescriptor<AppSettings>()).first.map { SettingsRecord(id: $0.id, timelineStartDate: $0.timelineStartDate, timelineDeadlineDate: $0.timelineDeadlineDate) }
        )
        let encoder = JSONEncoder()
        encoder.outputFormatting = [.prettyPrinted, .sortedKeys]
        encoder.dateEncodingStrategy = .iso8601
        return try encoder.encode(envelope)
    }

    static func restore(data: Data, context: ModelContext) throws {
        let decoder = JSONDecoder()
        decoder.dateDecodingStrategy = .iso8601
        let envelope = try decoder.decode(BackupEnvelope.self, from: data)

        try deleteAll(Habit.self, context: context)
        try deleteAll(HabitLog.self, context: context)
        try deleteAll(WeightLossPlan.self, context: context)
        try deleteAll(WeightLog.self, context: context)
        try deleteAll(LongTermGoal.self, context: context)
        try deleteAll(GoalMilestone.self, context: context)
        try deleteAll(AppSettings.self, context: context)

        envelope.habits.forEach { context.insert(Habit(id: $0.id, name: $0.name, days: $0.days, dailyGoal: $0.dailyGoal, unit: $0.unit, sortOrder: $0.sortOrder, isActive: $0.isActive, createdAt: $0.createdAt, updatedAt: $0.updatedAt)) }
        envelope.habitLogs.forEach { context.insert(HabitLog(id: $0.id, habitID: $0.habitID, loggedFor: $0.loggedFor, value: $0.value, completed: $0.completed, createdAt: $0.createdAt, updatedAt: $0.updatedAt)) }
        envelope.weightPlans.forEach { context.insert(WeightLossPlan(id: $0.id, month: $0.month, startingWeight: $0.startingWeight, goalWeight: $0.goalWeight, createdAt: $0.createdAt, updatedAt: $0.updatedAt)) }
        envelope.weightLogs.forEach { context.insert(WeightLog(id: $0.id, loggedFor: $0.loggedFor, weight: $0.weight, rollingAverageWeight: $0.rollingAverageWeight, createdAt: $0.createdAt, updatedAt: $0.updatedAt)) }
        envelope.goals.forEach { context.insert(LongTermGoal(id: $0.id, name: $0.name, createdAt: $0.createdAt, updatedAt: $0.updatedAt)) }
        envelope.milestones.forEach { context.insert(GoalMilestone(id: $0.id, goalID: $0.goalID, name: $0.name, estimatedCompletionMonth: $0.estimatedCompletionMonth, sortOrder: $0.sortOrder, completed: $0.completed, createdAt: $0.createdAt, updatedAt: $0.updatedAt)) }
        if let settings = envelope.settings {
            context.insert(AppSettings(id: settings.id, timelineStartDate: settings.timelineStartDate, timelineDeadlineDate: settings.timelineDeadlineDate))
        }
        try context.save()
    }

    private static func deleteAll<T: PersistentModel>(_ type: T.Type, context: ModelContext) throws {
        for object in try context.fetch(FetchDescriptor<T>()) {
            context.delete(object)
        }
    }
}
