//
//  TranscriptionService.swift
//  Wordflow
//
//  Handles speech-to-text transcription using Groq's Whisper API.
//  Supports both whisper-large-v3 and whisper-large-v3-turbo models.
//

import Foundation

/// Service for speech-to-text transcription via Groq Whisper API.
class TranscriptionService {
    
    // MARK: - Configuration
    private let groqEndpoint = "https://api.groq.com/openai/v1/audio/transcriptions"
    
    // MARK: - Public Methods
    
    /// Transcribes audio file to text using Groq Whisper API.
    /// - Parameter audioURL: URL to the audio file (M4A format)
    /// - Returns: Transcribed text
    /// - Throws: TranscriptionError if API call fails
    func transcribe(audioURL: URL) async throws -> String {
        return try await transcribeGroq(audioURL: audioURL)
    }
    
    // MARK: - Private Implementation
    
    private func transcribeGroq(audioURL: URL) async throws -> String {
        guard let apiKey = UserDefaults.standard.string(forKey: "groqAPIKey"), !apiKey.isEmpty else {
            throw TranscriptionError.missingAPIKey
        }
        
        let provider = UserDefaults.standard.string(forKey: "transcriptionProvider") ?? "Groq"
        let model = (provider == "GroqTurbo") ? "whisper-large-v3-turbo" : "whisper-large-v3"
        let rawLanguage = UserDefaults.standard.string(forKey: "appLanguage") ?? "DE"
        let normalizedLanguage = rawLanguage.uppercased()
        let whisperLanguage: String? = {
            switch normalizedLanguage {
            case "EN": return "en"
            case "DE": return "de"
            default: return nil
            }
        }()
        
        let audioData = try Data(contentsOf: audioURL)
        let boundary = UUID().uuidString
        
        var request = URLRequest(url: URL(string: groqEndpoint)!)
        request.httpMethod = "POST"
        request.setValue("Bearer \(apiKey)", forHTTPHeaderField: "Authorization")
        request.setValue("multipart/form-data; boundary=\(boundary)", forHTTPHeaderField: "Content-Type")
        
        var body = Data()
        
        // Add file
        body.append("--\(boundary)\r\n".data(using: .utf8)!)
        body.append("Content-Disposition: form-data; name=\"file\"; filename=\"recording.m4a\"\r\n".data(using: .utf8)!)
        body.append("Content-Type: audio/m4a\r\n\r\n".data(using: .utf8)!)
        body.append(audioData)
        body.append("\r\n".data(using: .utf8)!)
        
        // Add model
        body.append("--\(boundary)\r\n".data(using: .utf8)!)
        body.append("Content-Disposition: form-data; name=\"model\"\r\n\r\n".data(using: .utf8)!)
        body.append("\(model)\r\n".data(using: .utf8)!)
        
        // Force Whisper language to match app language to avoid wrong-language transcriptions.
        if let whisperLanguage {
            body.append("--\(boundary)\r\n".data(using: .utf8)!)
            body.append("Content-Disposition: form-data; name=\"language\"\r\n\r\n".data(using: .utf8)!)
            body.append("\(whisperLanguage)\r\n".data(using: .utf8)!)
        }
        
        body.append("--\(boundary)--\r\n".data(using: .utf8)!)
        request.httpBody = body
        LogManager.shared.log("🎙️ Transcription request: provider=\(provider), model=\(model), appLanguage raw=\(rawLanguage), normalized=\(normalizedLanguage), whisperLanguage=\(whisperLanguage ?? "auto")")
        
        let (data, response) = try await URLSession.shared.data(for: request)
        
        guard let httpResponse = response as? HTTPURLResponse, httpResponse.statusCode == 200 else {
            let errorMessage = String(data: data, encoding: .utf8) ?? "Unknown error"
            throw TranscriptionError.apiError(statusCode: (response as? HTTPURLResponse)?.statusCode ?? 500, message: errorMessage)
        }
        
        let json = try JSONDecoder().decode(TranscriptionResponse.self, from: data)
        return json.text.trimmingCharacters(in: .whitespacesAndNewlines)
    }
}

// MARK: - Response Model
struct TranscriptionResponse: Codable {
    let text: String
}

// MARK: - Errors
enum TranscriptionError: Error, LocalizedError {
    case missingAPIKey
    case invalidResponse
    case apiError(statusCode: Int, message: String)
    
    var errorDescription: String? {
        switch self {
        case .missingAPIKey:
            return "Groq API Key fehlt. Bitte in den Einstellungen eingeben."
        case .invalidResponse:
            return "Ungültige Antwort vom Server."
        case .apiError(let statusCode, let message):
            return "API Fehler (\(statusCode)): \(message)"
        }
    }
}
