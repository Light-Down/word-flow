import Foundation
#if os(macOS)
import AppKit
import Carbon
#else
import UIKit
#endif


// MARK: - Transcription Entry Model
struct TranscriptionEntry: Codable, Identifiable, Equatable {
    let id: UUID
    let text: String
    let timestamp: Date
    
    init(text: String) {
        self.id = UUID()
        self.text = text
        self.timestamp = Date()
    }
    
    var formattedTime: String {
        let formatter = DateFormatter()
        formatter.dateStyle = .short
        formatter.timeStyle = .short
        formatter.locale = Locale(identifier: "de_DE")
        return formatter.string(from: timestamp)
    }
    
    var shortText: String {
        let maxLength = 50
        if text.count > maxLength {
            return String(text.prefix(maxLength)) + "..."
        }
        return text
    }
}

class ClipboardManager: ObservableObject {
    private let historyKey = "transcriptionHistoryV2"
    private let logFileURL: URL
    private let maxHistoryItems = 50  // Erweitert auf 50 Einträge
    
    @Published var history: [TranscriptionEntry] = []
    
    init() {
        // Store log file in Application Support to avoid unnecessary Documents permission prompts.
        let fileManager = FileManager.default
        let appSupport = (try? fileManager.url(for: .applicationSupportDirectory, in: .userDomainMask, appropriateFor: nil, create: true))
            ?? fileManager.temporaryDirectory
        let appDir = appSupport.appendingPathComponent("WisprClone")
        if !fileManager.fileExists(atPath: appDir.path) {
            try? fileManager.createDirectory(at: appDir, withIntermediateDirectories: true)
        }
        logFileURL = appDir.appendingPathComponent("WisprClone_Log.txt")
        
        // History laden
        history = loadHistory()
    }
    
    // MARK: - Clipboard Operations
    
    func copyToClipboard(text: String) {
        #if os(macOS)
        let pasteboard = NSPasteboard.general
        pasteboard.clearContents()
        pasteboard.setString(text, forType: .string)
        #else
        UIPasteboard.general.string = text
        #endif
    }

    
    func copyAndPaste(text: String) {
        copyToClipboard(text: text)
        print("✅ Text in Zwischenablage kopiert: \(text.prefix(50))...")
        
        // Check if we have accessibility permissions - prompt if not
        #if os(macOS)
        let options = [kAXTrustedCheckOptionPrompt.takeUnretainedValue() as String: true] as CFDictionary
        let trusted = AXIsProcessTrustedWithOptions(options)
        
        if !trusted {
            print("⚠️ Accessibility-Berechtigung fehlt oder pending!")
            print("   → System-Dialog sollte erscheinen")
            print("   → Nach Genehmigung: App neu starten")
            // Versuche trotzdem zu pasten - manchmal funktioniert es
        }
        #endif

        
        // Try to paste anyway (sometimes works even without explicit permission check passing)
        DispatchQueue.main.asyncAfter(deadline: .now() + 0.3) {
            self.simulatePaste()
        }
    }
    
    // MARK: - Paste Simulation
    
    private func simulatePaste() {
        #if os(macOS)
        print("🔄 Simuliere Cmd+V...")
        
        // Create Cmd+V key event
        let source = CGEventSource(stateID: .combinedSessionState)
        
        // Key down
        guard let keyDown = CGEvent(keyboardEventSource: source, virtualKey: CGKeyCode(0x09), keyDown: true) else {
            print("❌ Konnte Key-Down Event nicht erstellen")
            return
        }
        keyDown.flags = .maskCommand
        keyDown.post(tap: .cgAnnotatedSessionEventTap)
        
        // Key up
        guard let keyUp = CGEvent(keyboardEventSource: source, virtualKey: CGKeyCode(0x09), keyDown: false) else {
            print("❌ Konnte Key-Up Event nicht erstellen")
            return
        }
        keyUp.flags = .maskCommand
        keyUp.post(tap: .cgAnnotatedSessionEventTap)
        
        print("✅ Cmd+V gesendet")
        #else
        print("ℹ️ Auto-Paste ist auf iOS technisch nicht möglich. Text ist im Clipboard.")
        #endif
    }

    
    // MARK: - History Management
    
    func addToHistory(text: String) {
        let entry = TranscriptionEntry(text: text)
        
        // Add to front
        history.insert(entry, at: 0)
        
        // Limit to max items
        if history.count > maxHistoryItems {
            history = Array(history.prefix(maxHistoryItems))
        }
        
        // Save to UserDefaults
        saveHistory()
        
        // Append to log file
        appendToLog(entry: entry)
    }
    
    // MARK: - Persistence
    
    func loadHistory() -> [TranscriptionEntry] {
        guard let data = UserDefaults.standard.data(forKey: historyKey) else {
            return []
        }
        
        do {
            return try JSONDecoder().decode([TranscriptionEntry].self, from: data)
        } catch {
            print("Failed to load history: \(error)")
            return []
        }
    }
    
    private func saveHistory() {
        do {
            let data = try JSONEncoder().encode(history)
            UserDefaults.standard.set(data, forKey: historyKey)
        } catch {
            print("Failed to save history: \(error)")
        }
    }
    
    func clearHistory() {
        history = []
        saveHistory()
    }
    
    // MARK: - Log File
    
    private func appendToLog(entry: TranscriptionEntry) {
        let logLine = "[\(entry.formattedTime)] \(entry.text)\n"
        
        do {
            if FileManager.default.fileExists(atPath: logFileURL.path) {
                // Append to existing file
                let fileHandle = try FileHandle(forWritingTo: logFileURL)
                fileHandle.seekToEndOfFile()
                if let data = logLine.data(using: .utf8) {
                    fileHandle.write(data)
                }
                fileHandle.closeFile()
            } else {
                // Create new file
                try logLine.write(to: logFileURL, atomically: true, encoding: .utf8)
            }
        } catch {
            print("Failed to write to log: \(error)")
        }
    }
    
    func getLogFileURL() -> URL {
        return logFileURL
    }
}
