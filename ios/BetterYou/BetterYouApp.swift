import SwiftData
import SwiftUI

@main
struct BetterYouApp: App {
    var body: some Scene {
        WindowGroup {
            RootView()
        }
        .modelContainer(for: [
            Habit.self,
            HabitLog.self,
            WeightLossPlan.self,
            WeightLog.self,
            LongTermGoal.self,
            GoalMilestone.self,
            AppSettings.self
        ])
    }
}

struct RootView: View {
    @Environment(\.scenePhase) private var scenePhase
    @Environment(\.modelContext) private var modelContext
    @Query private var habits: [Habit]
    @Query(sort: \WeightLog.loggedFor) private var weightLogs: [WeightLog]
    @StateObject private var healthKit = HealthKitWeightReader()
    @State private var didSeed = false
    @State private var isCheckingLaunchWeight = false
    @State private var pendingHealthWeight: Double?
    @State private var isShowingHealthWeightPrompt = false

    var body: some View {
        ContentView()
            .task {
                guard !didSeed else { return }
                didSeed = true
                SeedData.installIfNeeded(in: modelContext, existingHabitCount: habits.count)
                checkLaunchWeight()
            }
            .onChange(of: scenePhase) { _, phase in
                if phase == .active {
                    checkLaunchWeight()
                }
            }
            .alert(healthWeightPromptTitle, isPresented: $isShowingHealthWeightPrompt) {
                Button("No", role: .cancel) {
                    pendingHealthWeight = nil
                }
                Button("Yes") {
                    logPendingHealthWeight()
                }
            }
    }

    private var healthWeightPromptTitle: String {
        guard let pendingHealthWeight else { return "Log weight from Health?" }
        return "Log \(Formatters.amount(pendingHealthWeight)) kg?"
    }

    private func checkLaunchWeight() {
        let today = Date().startOfDay
        guard !hasWeightLog(for: today),
              !isCheckingLaunchWeight,
              !isShowingHealthWeightPrompt
        else { return }

        isCheckingLaunchWeight = true
        healthKit.refresh { weight in
            isCheckingLaunchWeight = false
            guard let weight,
                  !hasWeightLog(for: today)
            else { return }

            pendingHealthWeight = weight
            isShowingHealthWeightPrompt = true
        }
    }

    private func hasWeightLog(for date: Date) -> Bool {
        weightLogs.contains { Calendar.app.isDate($0.loggedFor, inSameDayAs: date) }
    }

    private func logPendingHealthWeight() {
        guard let pendingHealthWeight else { return }
        DataStore.upsertWeightLog(date: Date().startOfDay, weight: pendingHealthWeight, logs: weightLogs, context: modelContext)
        self.pendingHealthWeight = nil
    }
}
