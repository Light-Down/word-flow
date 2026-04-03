
import Foundation
import Combine
import SwiftUI

// MARK: - App State
class AppState: ObservableObject {
    static var shared: AppState?
    
    @Published var isRecording = false
    @Published var isProcessing = false
    @Published var transcriptionHistory: [TranscriptionEntry] = []
    @Published var lastError: LastAppError?
    
    let audioRecorder = AudioRecorder()
    let transcriptionService = TranscriptionService()
    let textCorrectionService = TextCorrectionService()
    let clipboardManager = ClipboardManager()
    private let lastErrorKey = "lastAppErrorV1"
    
    init() {
        AppState.shared = self
        
        // Observe audio recorder state
        audioRecorder.$isRecording
            .assign(to: &$isRecording)
        
        // Load history
        transcriptionHistory = clipboardManager.loadHistory()
        lastError = loadLastError()
    }
    
    func refreshHistory() {
        transcriptionHistory = clipboardManager.history
    }

    func setLastError(_ error: LastAppError) {
        lastError = error
        saveLastError(error)
    }

    func clearLastError() {
        lastError = nil
        UserDefaults.standard.removeObject(forKey: lastErrorKey)
    }
    
    /// Core logic to transcribe and optional correct text
    /// Returns the final processed text or throws error
    func processAudio(url: URL) async throws -> String {
        // Step 1: Transcribe
        let minimalText = try await transcriptionService.transcribe(audioURL: url)
        
        // Step 2: Correct (if enabled)
        // We need to access UserDefaults. Note: UserDefaults is appropriate for Shared settings if using AppGroup,
        // but for now simple standard defaults works per-app.
        if UserDefaults.standard.bool(forKey: "enableTextCorrection") {
            return try await textCorrectionService.correctText(minimalText)
        }
        
        return minimalText
    }

    private func saveLastError(_ error: LastAppError) {
        guard let data = try? JSONEncoder().encode(error) else { return }
        UserDefaults.standard.set(data, forKey: lastErrorKey)
    }

    private func loadLastError() -> LastAppError? {
        guard let data = UserDefaults.standard.data(forKey: lastErrorKey) else {
            return nil
        }
        return try? JSONDecoder().decode(LastAppError.self, from: data)
    }
}
