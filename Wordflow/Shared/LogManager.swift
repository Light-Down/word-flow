//
//  LogManager.swift
//  Wordflow
//
//  Centralized logging system that writes to both console and file.
//  Log file location: ~/Library/Application Support/WisprClone/WisprClone.log
//

import Foundation

/// Centralized logging manager that writes to console and persistent file.
class LogManager {
    static let shared = LogManager()
    
    private let logFileName = "WisprClone.log"
    private var logFileURL: URL?
    
    private init() {
        setupLogFile()
    }
    
    private func setupLogFile() {
        do {
            let fileManager = FileManager.default
            let appSupport = try fileManager.url(for: .applicationSupportDirectory, in: .userDomainMask, appropriateFor: nil, create: true)
            let appDir = appSupport.appendingPathComponent("WisprClone")
            
            if !fileManager.fileExists(atPath: appDir.path) {
                try fileManager.createDirectory(at: appDir, withIntermediateDirectories: true)
            }
            
            logFileURL = appDir.appendingPathComponent(logFileName)
            
            // Create file if not exists
            if let url = logFileURL, !fileManager.fileExists(atPath: url.path) {
                fileManager.createFile(atPath: url.path, contents: nil)
            }
            
        } catch {
            print("❌ Failed to setup log file: \(error)")
        }
    }
    
    /// Logs a message to console and file with timestamp.
    /// - Parameter message: The message to log
    func log(_ message: String) {
        let timestamp = DateFormatter.localizedString(from: Date(), dateStyle: .short, timeStyle: .medium)
        let logMessage = "[\(timestamp)] \(message)\n"
        
        print(logMessage.trimmingCharacters(in: .whitespacesAndNewlines))
        
        guard let fileURL = logFileURL else { return }
        
        do {
            let handle = try FileHandle(forWritingTo: fileURL)
            handle.seekToEndOfFile()
            if let data = logMessage.data(using: .utf8) {
                handle.write(data)
            }
            try handle.close()  // Modern API instead of deprecated closeFile()
        } catch {
            // Re-try creating file if handle fails (e.g. if deleted)
            try? logMessage.write(to: fileURL, atomically: true, encoding: .utf8)
        }
    }
    
    func getLogFileURL() -> URL? {
        return logFileURL
    }
    
    func clearLogs() {
        guard let fileURL = logFileURL else { return }
        try? "".write(to: fileURL, atomically: true, encoding: .utf8)
        log("Logs cleared.")
    }
}
