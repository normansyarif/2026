import Foundation
import SwiftData

enum SeedData {
    static func installIfNeeded(in context: ModelContext, existingHabitCount: Int) {
        guard existingHabitCount == 0 else { return }

        let pianoGoalID = UUID(uuidString: "11111111-1111-1111-1111-111111111111")!
        let habitIDs = [
            1: UUID(uuidString: "00000000-0000-0000-0000-000000000001")!,
            2: UUID(uuidString: "00000000-0000-0000-0000-000000000002")!,
            5: UUID(uuidString: "00000000-0000-0000-0000-000000000005")!,
            6: UUID(uuidString: "00000000-0000-0000-0000-000000000006")!,
            7: UUID(uuidString: "00000000-0000-0000-0000-000000000007")!,
            8: UUID(uuidString: "00000000-0000-0000-0000-000000000008")!,
            9: UUID(uuidString: "00000000-0000-0000-0000-000000000009")!
        ]
        let allDays = Weekday.allCases.map(\.rawValue)

        [
            Habit(id: habitIDs[7]!, name: "Clean room", days: ["sunday"], dailyGoal: 1, unit: "count", sortOrder: 1),
            Habit(id: habitIDs[8]!, name: "Do laundry", days: ["sunday"], dailyGoal: 1, unit: "count", sortOrder: 2),
            Habit(id: habitIDs[9]!, name: "Work", days: ["monday", "tuesday", "wednesday", "thursday", "friday"], dailyGoal: 1, unit: "ticket", sortOrder: 3),
            Habit(id: habitIDs[1]!, name: "Practice piano", days: allDays, dailyGoal: 1, unit: "count", sortOrder: 4),
            Habit(id: habitIDs[2]!, name: "Walk", days: allDays, dailyGoal: 1, unit: "count", sortOrder: 5),
            Habit(id: habitIDs[5]!, name: "Wash face", days: allDays, dailyGoal: 2, unit: "counts", sortOrder: 6),
            Habit(id: habitIDs[6]!, name: "Eat below 1500 cals", days: allDays, dailyGoal: 1, unit: "count", sortOrder: 7)
        ].forEach(context.insert)

        [
            ("2026-04-01", 105.60, 101.50), ("2026-05-01", 101.50, 98.20),
            ("2026-06-01", 98.20, 95.00), ("2026-07-01", 95.00, 92.00),
            ("2026-08-01", 92.00, 89.20), ("2026-09-01", 89.20, 86.50),
            ("2026-10-01", 86.50, 83.90), ("2026-11-01", 83.90, 81.50),
            ("2026-12-01", 81.50, 79.20), ("2027-01-01", 79.20, 77.00),
            ("2027-02-01", 77.00, 75.00)
        ].forEach { context.insert(WeightLossPlan(month: date($0.0), startingWeight: $0.1, goalWeight: $0.2)) }

        [
            ("2026-04-01", 105.60, 105.60), ("2026-04-02", 105.10, 105.35),
            ("2026-04-03", 104.40, 105.03), ("2026-04-04", 103.70, 104.70),
            ("2026-04-13", 104.60, 104.60)
        ].forEach { context.insert(WeightLog(loggedFor: date($0.0), weight: $0.1, rollingAverageWeight: $0.2)) }

        context.insert(LongTermGoal(id: pianoGoalID, name: "Be A Piano Player"))
        [
            ("Finish level 1", "2026-04-01", 18), ("Finish level 2", "2026-04-01", 17),
            ("Finish level 3", "2026-04-01", 16), ("Finish level 4", "2026-05-01", 15),
            ("Finish read music intro (vid. 15)", "2026-06-01", 14),
            ("Finish read music intro (vid. 31)", "2026-07-01", 13),
            ("Finish level 5.2", "2026-08-01", 12), ("Finish level 5.3", "2026-08-01", 11),
            ("Finish level 6.1", "2026-09-01", 10), ("Finish level 6.2", "2026-09-01", 9),
            ("Finish level 6.3", "2026-10-01", 8), ("Finish level 6.4", "2026-10-01", 7),
            ("Finish level 7.1", "2026-11-01", 6), ("Finish level 7.2", "2026-11-01", 5),
            ("Finish level 7.3", "2026-12-01", 4), ("Finish level 7.4", "2026-12-01", 3),
            ("Play Bach Minuet III in G (lv. 8.1.3)", "2027-01-01", 2),
            ("Play Handel Impertinence (lv. 8.1.5)", "2027-02-01", 1)
        ].forEach { context.insert(GoalMilestone(goalID: pianoGoalID, name: $0.0, estimatedCompletionMonth: date($0.1), sortOrder: $0.2)) }

        context.insert(AppSettings(timelineStartDate: date("2026-04-01"), timelineDeadlineDate: date("2027-02-28")))
        try? context.save()
    }

    private static func date(_ string: String) -> Date {
        Formatters.isoDate.date(from: string) ?? Date()
    }
}
