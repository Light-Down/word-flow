import Foundation

enum AppErrorStage: String, Codable {
    case transcription
    case correction
    case unknown
}

enum AppErrorCategory: String, Codable {
    case keyMissing
    case keyInvalid
    case networkOffline
    case timeout
    case rateLimit
    case serviceUnavailable
    case apiError
    case unknown
}

struct LastAppError: Codable, Equatable {
    let stage: AppErrorStage
    let category: AppErrorCategory
    let code: String?
    let userMessageDE: String
    let userMessageEN: String
    let technicalMessage: String
    let timestamp: Date

    func userMessage(for appLanguage: String) -> String {
        return appLanguage.uppercased() == "EN" ? userMessageEN : userMessageDE
    }
}

enum AppErrorMapper {
    static func map(error: Error, stage: AppErrorStage) -> LastAppError {
        if let urlError = error as? URLError {
            return map(urlError: urlError, stage: stage)
        }

        if let transcriptionError = error as? TranscriptionError {
            return map(transcriptionError: transcriptionError, stage: stage)
        }

        if let correctionError = error as? CorrectionError {
            return map(correctionError: correctionError, stage: stage)
        }

        return LastAppError(
            stage: stage,
            category: .unknown,
            code: nil,
            userMessageDE: "Unbekannter Fehler. Bitte erneut versuchen.",
            userMessageEN: "Unknown error. Please try again.",
            technicalMessage: error.localizedDescription,
            timestamp: Date()
        )
    }

    private static func map(urlError: URLError, stage: AppErrorStage) -> LastAppError {
        switch urlError.code {
        case .notConnectedToInternet, .networkConnectionLost, .cannotFindHost, .cannotConnectToHost, .dnsLookupFailed:
            return LastAppError(
                stage: stage,
                category: .networkOffline,
                code: "URL_\(urlError.code.rawValue)",
                userMessageDE: "Netzwerkfehler. Bitte Internetverbindung prüfen.",
                userMessageEN: "Network error. Please check your internet connection.",
                technicalMessage: urlError.localizedDescription,
                timestamp: Date()
            )
        case .timedOut:
            return LastAppError(
                stage: stage,
                category: .timeout,
                code: "URL_\(urlError.code.rawValue)",
                userMessageDE: "Zeitüberschreitung. Bitte erneut versuchen.",
                userMessageEN: "Request timed out. Please try again.",
                technicalMessage: urlError.localizedDescription,
                timestamp: Date()
            )
        default:
            return LastAppError(
                stage: stage,
                category: .unknown,
                code: "URL_\(urlError.code.rawValue)",
                userMessageDE: "Netzwerkproblem. Bitte erneut versuchen.",
                userMessageEN: "Network issue. Please try again.",
                technicalMessage: urlError.localizedDescription,
                timestamp: Date()
            )
        }
    }

    private static func map(transcriptionError: TranscriptionError, stage: AppErrorStage) -> LastAppError {
        switch transcriptionError {
        case .missingAPIKey:
            return LastAppError(
                stage: stage,
                category: .keyMissing,
                code: nil,
                userMessageDE: "Groq API Key fehlt. Bitte in den Einstellungen hinterlegen.",
                userMessageEN: "Groq API key is missing. Please add it in Settings.",
                technicalMessage: transcriptionError.localizedDescription,
                timestamp: Date()
            )
        case .invalidResponse:
            return LastAppError(
                stage: stage,
                category: .apiError,
                code: "INVALID_RESPONSE",
                userMessageDE: "Ungültige Serverantwort.",
                userMessageEN: "Invalid server response.",
                technicalMessage: transcriptionError.localizedDescription,
                timestamp: Date()
            )
        case .apiError(let statusCode, let message):
            return mapHTTP(statusCode: statusCode, message: message, stage: stage)
        }
    }

    private static func map(correctionError: CorrectionError, stage: AppErrorStage) -> LastAppError {
        switch correctionError {
        case .missingAPIKey:
            return LastAppError(
                stage: stage,
                category: .keyMissing,
                code: nil,
                userMessageDE: "Groq API Key fehlt. Bitte in den Einstellungen hinterlegen.",
                userMessageEN: "Groq API key is missing. Please add it in Settings.",
                technicalMessage: correctionError.localizedDescription,
                timestamp: Date()
            )
        case .invalidResponse:
            return LastAppError(
                stage: stage,
                category: .apiError,
                code: "INVALID_RESPONSE",
                userMessageDE: "Ungültige Serverantwort.",
                userMessageEN: "Invalid server response.",
                technicalMessage: correctionError.localizedDescription,
                timestamp: Date()
            )
        case .apiError(let statusCode, let message):
            return mapHTTP(statusCode: statusCode, message: message, stage: stage)
        }
    }

    private static func mapHTTP(statusCode: Int, message: String, stage: AppErrorStage) -> LastAppError {
        let category: AppErrorCategory
        let userMessageDE: String
        let userMessageEN: String

        switch statusCode {
        case 401, 403:
            category = .keyInvalid
            userMessageDE = "API Key ungültig oder ohne Berechtigung."
            userMessageEN = "API key is invalid or lacks permissions."
        case 429:
            category = .rateLimit
            userMessageDE = "Rate Limit erreicht. Bitte kurz warten und erneut versuchen."
            userMessageEN = "Rate limit reached. Please wait and try again."
        case 500 ... 599:
            category = .serviceUnavailable
            userMessageDE = "Server derzeit nicht verfügbar. Bitte später erneut versuchen."
            userMessageEN = "Service is currently unavailable. Please try again later."
        default:
            category = .apiError
            userMessageDE = "API-Fehler (\(statusCode)). Bitte erneut versuchen."
            userMessageEN = "API error (\(statusCode)). Please try again."
        }

        return LastAppError(
            stage: stage,
            category: category,
            code: "HTTP_\(statusCode)",
            userMessageDE: userMessageDE,
            userMessageEN: userMessageEN,
            technicalMessage: message,
            timestamp: Date()
        )
    }
}