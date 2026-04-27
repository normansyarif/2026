import Foundation
import SwiftData

enum Weekday: String, CaseIterable, Identifiable, Codable {
    case monday, tuesday, wednesday, thursday, friday, saturday, sunday

    var id: String { rawValue }
    var shortLabel: String { String(label.prefix(3)) }
    var label: String {
        rawValue.prefix(1).uppercased() + rawValue.dropFirst()
    }
}

extension Calendar {
    static var app: Calendar {
        var calendar = Calendar(identifier: .gregorian)
        calendar.firstWeekday = 2
        return calendar
    }
}

extension Date {
    var startOfDay: Date { Calendar.app.startOfDay(for: self) }
    var startOfMonth: Date {
        Calendar.app.date(from: Calendar.app.dateComponents([.year, .month], from: self)) ?? startOfDay
    }
    var endOfMonth: Date {
        Calendar.app.date(byAdding: DateComponents(month: 1, day: -1), to: startOfMonth) ?? startOfDay
    }
    var weekdayKey: String {
        let index = Calendar.app.component(.weekday, from: self)
        let mapped = (index + 5) % 7
        return Weekday.allCases[mapped].rawValue
    }
    var daysInMonth: Int {
        Calendar.app.range(of: .day, in: .month, for: self)?.count ?? 30
    }
    var dayOfMonth: Int { Calendar.app.component(.day, from: self) }
}

enum Formatters {
    static let month: DateFormatter = {
        let formatter = DateFormatter()
        formatter.dateFormat = "MMMM yyyy"
        return formatter
    }()

    static let shortDate: DateFormatter = {
        let formatter = DateFormatter()
        formatter.dateFormat = "MMM d, yyyy"
        return formatter
    }()

    static let shortTime: DateFormatter = {
        let formatter = DateFormatter()
        formatter.timeStyle = .short
        formatter.dateStyle = .none
        return formatter
    }()

    static let isoDate: DateFormatter = {
        let formatter = DateFormatter()
        formatter.calendar = Calendar(identifier: .gregorian)
        formatter.locale = Locale(identifier: "en_US_POSIX")
        formatter.dateFormat = "yyyy-MM-dd"
        return formatter
    }()

    static func amount(_ value: Double) -> String {
        let text = String(format: "%.2f", value)
        return text.replacingOccurrences(of: #"\.?0+$"#, with: "", options: .regularExpression)
    }
}

@Model
final class Habit {
    var id: UUID
    var name: String
    var daysRaw: String
    var dailyGoal: Double
    var unit: String
    var sortOrder: Int
    var isActive: Bool
    var reminderMinutes: Int?
    var createdAt: Date
    var updatedAt: Date

    init(
        id: UUID = UUID(),
        name: String,
        days: [String],
        dailyGoal: Double,
        unit: String,
        sortOrder: Int,
        isActive: Bool = true,
        reminderMinutes: Int? = nil,
        createdAt: Date = Date(),
        updatedAt: Date = Date()
    ) {
        self.id = id
        self.name = name
        self.daysRaw = days.joined(separator: ",")
        self.dailyGoal = dailyGoal
        self.unit = unit
        self.sortOrder = sortOrder
        self.isActive = isActive
        self.reminderMinutes = reminderMinutes
        self.createdAt = createdAt
        self.updatedAt = updatedAt
    }

    var days: [String] {
        get { daysRaw.split(separator: ",").map(String.init) }
        set { daysRaw = newValue.joined(separator: ",") }
    }

    func isScheduled(for date: Date) -> Bool {
        isActive && days.contains(date.weekdayKey)
    }

    func goalReached(_ value: Double) -> Bool {
        value >= dailyGoal
    }

    var reminderTimeLabel: String {
        guard let reminderMinutes else { return "All day" }
        return Formatters.shortTime.string(from: Self.timeDate(from: reminderMinutes))
    }

    static func minutesAfterMidnight(from date: Date) -> Int {
        let components = Calendar.app.dateComponents([.hour, .minute], from: date)
        return ((components.hour ?? 0) * 60) + (components.minute ?? 0)
    }

    static func timeDate(from minutesAfterMidnight: Int) -> Date {
        let clamped = min(max(minutesAfterMidnight, 0), 1439)
        return Calendar.app.date(
            from: DateComponents(
                year: 2001,
                month: 1,
                day: 1,
                hour: clamped / 60,
                minute: clamped % 60
            )
        ) ?? Date()
    }
}

@Model
final class HabitLog {
    var id: UUID
    var habitID: UUID
    var loggedFor: Date
    var value: Double
    var completed: Bool
    var createdAt: Date
    var updatedAt: Date

    init(id: UUID = UUID(), habitID: UUID, loggedFor: Date, value: Double, completed: Bool, createdAt: Date = Date(), updatedAt: Date = Date()) {
        self.id = id
        self.habitID = habitID
        self.loggedFor = loggedFor.startOfDay
        self.value = value
        self.completed = completed
        self.createdAt = createdAt
        self.updatedAt = updatedAt
    }
}

@Model
final class WeightLossPlan {
    var id: UUID
    var month: Date
    var startingWeight: Double
    var goalWeight: Double
    var createdAt: Date
    var updatedAt: Date

    init(id: UUID = UUID(), month: Date, startingWeight: Double, goalWeight: Double, createdAt: Date = Date(), updatedAt: Date = Date()) {
        self.id = id
        self.month = month.startOfMonth
        self.startingWeight = startingWeight
        self.goalWeight = goalWeight
        self.createdAt = createdAt
        self.updatedAt = updatedAt
    }
}

@Model
final class WeightLog {
    var id: UUID
    var loggedFor: Date
    var weight: Double
    var rollingAverageWeight: Double
    var createdAt: Date
    var updatedAt: Date

    init(id: UUID = UUID(), loggedFor: Date, weight: Double, rollingAverageWeight: Double = 0, createdAt: Date = Date(), updatedAt: Date = Date()) {
        self.id = id
        self.loggedFor = loggedFor.startOfDay
        self.weight = weight
        self.rollingAverageWeight = rollingAverageWeight
        self.createdAt = createdAt
        self.updatedAt = updatedAt
    }
}

@Model
final class LongTermGoal {
    var id: UUID
    var name: String
    var createdAt: Date
    var updatedAt: Date

    init(id: UUID = UUID(), name: String, createdAt: Date = Date(), updatedAt: Date = Date()) {
        self.id = id
        self.name = name
        self.createdAt = createdAt
        self.updatedAt = updatedAt
    }
}

@Model
final class GoalMilestone {
    var id: UUID
    var goalID: UUID
    var name: String
    var estimatedCompletionMonth: Date
    var sortOrder: Int
    var completed: Bool
    var createdAt: Date
    var updatedAt: Date

    init(id: UUID = UUID(), goalID: UUID, name: String, estimatedCompletionMonth: Date, sortOrder: Int, completed: Bool = false, createdAt: Date = Date(), updatedAt: Date = Date()) {
        self.id = id
        self.goalID = goalID
        self.name = name
        self.estimatedCompletionMonth = estimatedCompletionMonth.startOfMonth
        self.sortOrder = sortOrder
        self.completed = completed
        self.createdAt = createdAt
        self.updatedAt = updatedAt
    }
}

@Model
final class AppSettings {
    var id: UUID
    var timelineStartDate: Date?
    var timelineDeadlineDate: Date?

    init(id: UUID = UUID(), timelineStartDate: Date? = nil, timelineDeadlineDate: Date? = nil) {
        self.id = id
        self.timelineStartDate = timelineStartDate?.startOfDay
        self.timelineDeadlineDate = timelineDeadlineDate?.startOfDay
    }
}
