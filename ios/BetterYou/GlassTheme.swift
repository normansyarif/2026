import SwiftUI

struct GlassCard: ViewModifier {
    @Environment(\.colorScheme) private var colorScheme
    var radius: CGFloat = 24

    func body(content: Content) -> some View {
        content
            .padding(16)
            .background(.regularMaterial, in: RoundedRectangle(cornerRadius: radius, style: .continuous))
            .overlay {
                RoundedRectangle(cornerRadius: radius, style: .continuous)
                    .stroke(colorScheme == .dark ? .white.opacity(0.16) : .white.opacity(0.55), lineWidth: 1)
            }
            .shadow(color: .black.opacity(colorScheme == .dark ? 0.28 : 0.08), radius: 18, y: 8)
    }
}

extension View {
    func glassCard(radius: CGFloat = 24) -> some View {
        modifier(GlassCard(radius: radius))
    }
}

struct AppBackground: View {
    @Environment(\.colorScheme) private var colorScheme

    var body: some View {
        LinearGradient(
            colors: colorScheme == .dark ? darkColors : lightColors,
            startPoint: .topLeading,
            endPoint: .bottomTrailing
        )
        .ignoresSafeArea()
    }

    private var lightColors: [Color] {
        [
            Color(.systemBackground),
            Color(.secondarySystemBackground),
            Color(red: 0.90, green: 0.97, blue: 0.95)
        ]
    }

    private var darkColors: [Color] {
        [
            Color(.systemBackground),
            Color(red: 0.08, green: 0.11, blue: 0.14),
            Color(red: 0.05, green: 0.12, blue: 0.10)
        ]
    }
}

struct MetricTile: View {
    let title: String
    let value: String
    let systemImage: String

    var body: some View {
        VStack(alignment: .leading, spacing: 10) {
            Image(systemName: systemImage)
                .font(.title3)
                .foregroundStyle(.tint)
            Text(value)
                .font(.title2.bold())
            Text(title)
                .font(.caption)
                .foregroundStyle(.secondary)
        }
        .frame(maxWidth: .infinity, alignment: .leading)
        .glassCard(radius: 18)
    }
}

struct WeightStatusTile: View {
    let currentWeight: Double?
    let dailyTarget: Double?

    private var status: (title: String, detail: String, systemImage: String, tint: Color)? {
        guard let currentWeight, let dailyTarget else { return nil }

        let difference = currentWeight - dailyTarget
        if difference <= 0 {
            let detail = abs(difference) < 0.005
                ? "At daily target"
                : "\(Formatters.amount(abs(difference))) kg below daily target"

            return (
                "On target",
                detail,
                "checkmark.seal.fill",
                .green
            )
        }

        return (
            "Above target",
            "\(Formatters.amount(difference)) kg over daily target",
            "exclamationmark.triangle.fill",
            .red
        )
    }

    var body: some View {
        HStack(spacing: 12) {
            Image(systemName: status?.systemImage ?? "questionmark.circle")
                .font(.title3)
                .foregroundStyle(status?.tint ?? .secondary)

            VStack(alignment: .leading, spacing: 4) {
                Text(status?.title ?? "-")
                    .font(.headline.bold())
                Text(status?.detail ?? "Status unavailable")
                    .font(.caption)
                    .foregroundStyle(.secondary)
            }

            Spacer()
        }
        .frame(maxWidth: .infinity, alignment: .leading)
        .glassCard(radius: 18)
    }
}
