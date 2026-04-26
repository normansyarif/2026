import SwiftData
import SwiftUI

struct MoreView: View {
    var body: some View {
        NavigationStack {
            ZStack {
                AppBackground()
                List {
                    NavigationLink {
                        HabitsView()
                    } label: {
                        Label("Habits", systemImage: "checklist")
                    }

                    NavigationLink {
                        WeightGoalsView()
                    } label: {
                        Label("Weight Goal", systemImage: "target")
                    }

                    NavigationLink {
                        SettingsView()
                    } label: {
                        Label("Setting", systemImage: "gearshape")
                    }
                }
                .scrollContentBackground(.hidden)
            }
            .navigationTitle("More")
        }
    }
}

struct SettingsView: View {
    @Environment(\.modelContext) private var modelContext
    @Query private var settingsQuery: [AppSettings]
    @Query private var habits: [Habit]

    @State private var startDate = Date()
    @State private var deadlineDate = Date()
    @State private var hasStartDate = false
    @State private var hasDeadlineDate = false
    @State private var exportDocument = BackupDocument()
    @State private var isExporting = false
    @State private var isImporting = false
    @State private var restoreMessage: String?

    var body: some View {
        NavigationStack {
            ZStack {
                AppBackground()
                List {
                    Section("Current Setup") {
                        LabeledContent("Timezone", value: TimeZone.current.identifier)
                        LabeledContent("Total habits", value: "\(habits.count)")
                    }

                    Section("Timeline") {
                        Toggle("Starting date", isOn: $hasStartDate)
                        if hasStartDate {
                            DatePicker("Start", selection: $startDate, displayedComponents: [.date])
                        }
                        Toggle("Deadline", isOn: $hasDeadlineDate)
                        if hasDeadlineDate {
                            DatePicker("Deadline", selection: $deadlineDate, displayedComponents: [.date])
                        }
                        Button("Save Timeline", action: saveTimeline)
                    }

                    Section("Backup and Restore") {
                        Button {
                            do {
                                exportDocument = BackupDocument(data: try BackupService.exportData(context: modelContext))
                                isExporting = true
                            } catch {
                                restoreMessage = error.localizedDescription
                            }
                        } label: {
                            Label("Export Backup", systemImage: "square.and.arrow.up")
                        }

                        Button {
                            isImporting = true
                        } label: {
                            Label("Restore Backup", systemImage: "square.and.arrow.down")
                        }

                        if let restoreMessage {
                            Text(restoreMessage)
                                .font(.caption)
                                .foregroundStyle(.secondary)
                        }
                    }
                }
                .scrollContentBackground(.hidden)
            }
            .navigationTitle("Setting")
            .onAppear(perform: loadSettings)
            .fileExporter(isPresented: $isExporting, document: exportDocument, contentType: .json, defaultFilename: "better-you-backup") { result in
                if case let .failure(error) = result {
                    restoreMessage = error.localizedDescription
                }
            }
            .fileImporter(isPresented: $isImporting, allowedContentTypes: [.json]) { result in
                do {
                    let url = try result.get()
                    guard url.startAccessingSecurityScopedResource() else { return }
                    defer { url.stopAccessingSecurityScopedResource() }
                    try BackupService.restore(data: Data(contentsOf: url), context: modelContext)
                    restoreMessage = "Backup restored."
                    loadSettings()
                } catch {
                    restoreMessage = error.localizedDescription
                }
            }
        }
    }

    private func loadSettings() {
        let settings = settingsQuery.first ?? DataStore.settings(in: modelContext)
        hasStartDate = settings.timelineStartDate != nil
        hasDeadlineDate = settings.timelineDeadlineDate != nil
        startDate = settings.timelineStartDate ?? Date()
        deadlineDate = settings.timelineDeadlineDate ?? Date()
    }

    private func saveTimeline() {
        let settings = settingsQuery.first ?? DataStore.settings(in: modelContext)
        settings.timelineStartDate = hasStartDate ? startDate.startOfDay : nil
        settings.timelineDeadlineDate = hasDeadlineDate ? deadlineDate.startOfDay : nil
        try? modelContext.save()
    }
}
