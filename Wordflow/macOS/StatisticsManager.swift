import Foundation
import SwiftUI

class StatisticsManager: ObservableObject {
    static let shared = StatisticsManager()
    
    // Persistent Stats
    @AppStorage("stats_totalRequests") var totalRequests = 0
    @AppStorage("stats_wordsSent") var totalWordsSent = 0
    @AppStorage("stats_wordsReceived") var totalWordsReceived = 0
    
    // Daily History: "yyyy-MM-dd" : Int
    @AppStorage("stats_dailyHistoryJSON") private var dailyHistoryJSON = "{}"
    
    var dailyHistory: [String: Int] {
        get {
            guard let data = dailyHistoryJSON.data(using: .utf8),
                  let dict = try? JSONDecoder().decode([String: Int].self, from: data) else {
                return [:]
            }
            return dict
        }
        set {
            if let data = try? JSONEncoder().encode(newValue),
               let json = String(data: data, encoding: .utf8) {
                dailyHistoryJSON = json
                objectWillChange.send()
            }
        }
    }
    
    func logRequest(wordsInTranscription: Int, wordsInFinalText: Int) {
        totalRequests += 1
        totalWordsSent += wordsInTranscription
        totalWordsReceived += wordsInFinalText
        
        // Update Daily
        let today = formatDate(Date())
        var history = dailyHistory
        history[today, default: 0] += 1
        dailyHistory = history
    }
    
    // MARK: - Helpers
    
    func getHistoryForLast7Days() -> [(date: String, count: Int)] {
        return getHistory(days: 7)
    }
    
    // MARK: - Time Range Logic
    
    enum TimeRange: String, CaseIterable {
        case week = "Week"
        case month = "Month"
        case year = "Year"
        case all = "All"
    }
    
    func getStats(for range: TimeRange) -> (requests: Int, words: Int, savedSeconds: Double) {
        let history = dailyHistory
        let calendar = Calendar.current
        let today = Date()
        
        var totalReq = 0
        // Note: For words/time, we only have totals stored globally, not per day in this simple version.
        // To do this strictly correctly, we would need to store daily breakdown of words too.
        // For now, we will approximate or just return totals for "All" and calc requests for others.
        // IMPROVEMENT: Let's assume average words per request to estimate for ranges.
        
        let daysToLookBack: Int
        switch range {
        case .week: daysToLookBack = 7
        case .month: daysToLookBack = 30
        case .year: daysToLookBack = 365
        case .all: return (totalRequests, totalWordsReceived, Double(totalWordsReceived) * 0.5)
        }
        
        for i in 0..<daysToLookBack {
            if let date = calendar.date(byAdding: .day, value: -i, to: today) {
                let key = formatDate(date)
                totalReq += history[key] ?? 0
            }
        }
        
        // Estimate words based on avg words/req from global stats
        let avgWords = totalRequests > 0 ? Double(totalWordsReceived) / Double(totalRequests) : 0
        let estimatedWords = Int(Double(totalReq) * avgWords)
        let estimatedSeconds = Double(estimatedWords) * 0.5
        
        return (totalReq, estimatedWords, estimatedSeconds)
    }
    
    func getChartData(for range: TimeRange) -> [(date: String, count: Int)] {
        let days: Int
        switch range {
        case .week: days = 7
        case .month: days = 30
        case .year: days = 12 // Monthly aggregation needed usually, but for simplicity let's do last 12 days or weeks?
                              // Implementing simple daily lookback for now
        case .all: days = 30 // Cap "All" chart to last 30 days for readability or implementing scaling
        }
        
        // specific logic for Year (Monthly buckets)
        if range == .year {
            return getMonthlyBuckets()
        }
        
        return getHistory(days: days)
    }
    
    private func getHistory(days: Int) -> [(date: String, count: Int)] {
        var result: [(date: String, count: Int)] = []
        let calendar = Calendar.current
        let today = Date()
        let history = dailyHistory
        
        for i in (0..<days).reversed() {
            if let date = calendar.date(byAdding: .day, value: -i, to: today) {
                let key = formatDate(date)
                let count = history[key] ?? 0
                result.append((date: key, count: count))
            }
        }
        return result
    }
    
    private func getMonthlyBuckets() -> [(date: String, count: Int)] {
        // Aggregate by Month for the last 12 months
        var result: [(date: String, count: Int)] = []
        let calendar = Calendar.current
        let today = Date()
        let history = dailyHistory
        
        for i in (0..<12).reversed() {
            if let date = calendar.date(byAdding: .month, value: -i, to: today) {
                let monthKey = formatMonth(date) // "2024-12"
                // Sum all days in this month
                var sum = 0
                for (dayKey, count) in history {
                    if dayKey.starts(with: monthKey) {
                        sum += count
                    }
                }
                result.append((date: monthKey, count: sum))
            }
        }
        return result
    }
    
    private func formatMonth(_ date: Date) -> String {
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM"
        return formatter.string(from: date)
    }
    
    private func formatDate(_ date: Date) -> String {
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM-dd"
        return formatter.string(from: date)
    }
}
