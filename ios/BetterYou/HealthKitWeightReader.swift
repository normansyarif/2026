import Foundation
import HealthKit
import Combine

@MainActor
final class HealthKitWeightReader: ObservableObject {
    @Published private(set) var latestWeight: Double?
    @Published private(set) var isAvailable = HKHealthStore.isHealthDataAvailable()

    private let healthStore = HKHealthStore()

    var buttonTitle: String {
        guard let latestWeight else { return "No Health Weight" }
        return "Log \(Formatters.amount(latestWeight)) kg"
    }

    func refresh(completion: (@MainActor @Sendable (Double?) -> Void)? = nil) {
        guard isAvailable,
              let bodyMassType = HKObjectType.quantityType(forIdentifier: .bodyMass)
        else {
            latestWeight = nil
            completion?(nil)
            return
        }

        healthStore.requestAuthorization(toShare: [], read: [bodyMassType]) { [weak self] success, _ in
            guard success else {
                Task { @MainActor in
                    self?.latestWeight = nil
                    completion?(nil)
                }
                return
            }

            Task { @MainActor in
                self?.fetchTodayBodyMass(from: bodyMassType, completion: completion)
            }
        }
    }

    private func fetchTodayBodyMass(from bodyMassType: HKQuantityType, completion: (@MainActor @Sendable (Double?) -> Void)? = nil) {
        let startOfToday = Calendar.app.startOfDay(for: Date())
        let endOfToday = Calendar.app.date(byAdding: .day, value: 1, to: startOfToday) ?? Date()
        let predicate = HKQuery.predicateForSamples(
            withStart: startOfToday,
            end: endOfToday,
            options: [.strictStartDate]
        )
        let sortDescriptor = NSSortDescriptor(key: HKSampleSortIdentifierEndDate, ascending: false)
        let query = HKSampleQuery(
            sampleType: bodyMassType,
            predicate: predicate,
            limit: 1,
            sortDescriptors: [sortDescriptor]
        ) { [weak self] _, samples, _ in
            let weight = (samples?.first as? HKQuantitySample)?
                .quantity
                .doubleValue(for: .gramUnit(with: .kilo))

            Task { @MainActor in
                self?.latestWeight = weight
                completion?(weight)
            }
        }

        healthStore.execute(query)
    }
}
