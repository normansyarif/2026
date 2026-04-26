import SwiftUI

struct ContentView: View {
    var body: some View {
        TabView {
            TodayView()
                .tabItem { Label("Today", systemImage: "sun.max") }
            CalendarView()
                .tabItem { Label("Calendar", systemImage: "calendar") }
            WeightView()
                .tabItem { Label("Weight", systemImage: "chart.line.uptrend.xyaxis") }
            GoalsView()
                .tabItem { Label("Goals", systemImage: "flag.checkered") }
            MoreView()
                .tabItem { Label("More", systemImage: "ellipsis") }
        }
    }
}
