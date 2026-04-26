import SwiftData
import SwiftUI

struct GoalsView: View {
    @Environment(\.modelContext) private var modelContext
    @Query(sort: \LongTermGoal.createdAt, order: .reverse) private var goals: [LongTermGoal]
    @Query(sort: \GoalMilestone.sortOrder) private var milestones: [GoalMilestone]
    @State private var isShowingNewGoal = false

    var body: some View {
        NavigationStack {
            ZStack {
                AppBackground()
                List {
                    Section("Goals") {
                        ForEach(goals) { goal in
                            NavigationLink {
                                GoalDetailView(goal: goal)
                            } label: {
                                VStack(alignment: .leading, spacing: 4) {
                                    Text(goal.name)
                                    Text("\(milestones.filter { $0.goalID == goal.id }.count) milestones")
                                        .font(.caption)
                                        .foregroundStyle(.secondary)
                                }
                            }
                        }
                        .onDelete(perform: deleteGoals)
                    }
                }
                .scrollContentBackground(.hidden)
            }
            .navigationTitle("Goals")
            .toolbar {
                ToolbarItem(placement: .topBarLeading) {
                    EditButton()
                }
                ToolbarItem(placement: .topBarTrailing) {
                    Button {
                        isShowingNewGoal = true
                    } label: {
                        Image(systemName: "plus")
                    }
                }
            }
            .sheet(isPresented: $isShowingNewGoal) {
                NewGoalSheet { name in
                    addGoal(name: name)
                    isShowingNewGoal = false
                }
                .presentationDetents([.height(220), .medium])
            }
        }
    }

    private func addGoal(name: String) {
        modelContext.insert(LongTermGoal(name: name))
        try? modelContext.save()
    }

    private func deleteGoals(at offsets: IndexSet) {
        for index in offsets {
            let goal = goals[index]
            milestones
                .filter { $0.goalID == goal.id }
                .forEach(modelContext.delete)
            modelContext.delete(goal)
        }
        try? modelContext.save()
    }
}

struct NewGoalSheet: View {
    let onSave: (String) -> Void

    @Environment(\.dismiss) private var dismiss
    @State private var goalName = ""

    var body: some View {
        NavigationStack {
            Form {
                Section("New Goal") {
                    TextField("Goal name", text: $goalName)
                }
            }
            .navigationTitle("New Goal")
            .toolbar {
                ToolbarItem(placement: .cancellationAction) {
                    Button("Cancel") { dismiss() }
                }
                ToolbarItem(placement: .confirmationAction) {
                    Button("Add", action: save)
                        .disabled(trimmedName.isEmpty)
                }
            }
        }
    }

    private var trimmedName: String {
        goalName.trimmingCharacters(in: .whitespacesAndNewlines)
    }

    private func save() {
        guard !trimmedName.isEmpty else { return }
        onSave(trimmedName)
        dismiss()
    }
}

struct GoalDetailView: View {
    @Environment(\.modelContext) private var modelContext
    @Bindable var goal: LongTermGoal
    @Query(sort: \GoalMilestone.sortOrder) private var allMilestones: [GoalMilestone]

    @State private var milestoneName = ""
    @State private var month = Date().startOfMonth

    private var milestones: [GoalMilestone] {
        allMilestones.filter { $0.goalID == goal.id }.sorted { $0.sortOrder < $1.sortOrder }
    }

    var body: some View {
        List {
            Section("Goal") {
                TextField("Name", text: $goal.name)
            }
            Section("New Milestone") {
                TextField("Milestone", text: $milestoneName)
                DatePicker("Month", selection: $month, displayedComponents: [.date])
                Button("Add Milestone", action: addMilestone)
            }
            Section("Milestones") {
                ForEach(milestones) { milestone in
                    VStack(alignment: .leading, spacing: 8) {
                        TextField("Milestone", text: Binding(
                            get: { milestone.name },
                            set: { milestone.name = $0; milestone.updatedAt = Date() }
                        ))
                        DatePicker("Month", selection: Binding(
                            get: { milestone.estimatedCompletionMonth },
                            set: { milestone.estimatedCompletionMonth = $0.startOfMonth; milestone.updatedAt = Date() }
                        ), displayedComponents: [.date])
                        Toggle("Completed", isOn: Binding(
                            get: { milestone.completed },
                            set: { milestone.completed = $0; milestone.updatedAt = Date() }
                        ))
                    }
                }
                .onDelete(perform: deleteMilestones)
                .onMove(perform: move)
            }
        }
        .navigationTitle(goal.name)
        .toolbar { EditButton() }
        .onDisappear { try? modelContext.save() }
    }

    private func addMilestone() {
        let trimmed = milestoneName.trimmingCharacters(in: .whitespacesAndNewlines)
        guard !trimmed.isEmpty else { return }
        modelContext.insert(GoalMilestone(goalID: goal.id, name: trimmed, estimatedCompletionMonth: month, sortOrder: (milestones.map(\.sortOrder).min() ?? 1) - 1))
        milestoneName = ""
        try? modelContext.save()
    }

    private func move(from source: IndexSet, to destination: Int) {
        var ordered = milestones
        ordered.move(fromOffsets: source, toOffset: destination)
        for (index, milestone) in ordered.enumerated() {
            milestone.sortOrder = index + 1
        }
        try? modelContext.save()
    }

    private func deleteMilestones(at offsets: IndexSet) {
        for index in offsets {
            modelContext.delete(milestones[index])
        }
        try? modelContext.save()
    }
}
