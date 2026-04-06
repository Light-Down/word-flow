import Foundation
import AppKit

struct UpdateInfo: Codable {
    let version: String
    let downloadURL: String?
    let updateURL: String?
    let releaseNotes: String?
}

enum UpdateCheckError: LocalizedError {
    case invalidServerResponse
    case invalidStatusCode(Int)
    case emptyResponse
    case invalidManifestFormat
    case unsupportedContentType(String)
    case noReachableUpdateEndpoint

    var errorDescription: String? {
        switch self {
        case .invalidServerResponse:
            return "Ungültige Serverantwort erhalten."
        case .invalidStatusCode(let code):
            return "Server antwortete mit Status \(code)."
        case .emptyResponse:
            return "Leere Antwort vom Update-Server."
        case .invalidManifestFormat:
            return "Update-Manifest hat ein ungültiges Format."
        case .unsupportedContentType(let type):
            return "Unerwarteter Inhaltstyp vom Server: \(type)."
        case .noReachableUpdateEndpoint:
            return "Kein Update-Endpunkt erreichbar."
        }
    }
}

class UpdateChecker: ObservableObject {
    static let shared = UpdateChecker()

    private let defaultUpdateDestination = URL(string: "https://word-flow.store/update")!
    
    // Primary endpoint plus fallbacks under the same domain.
    private let updateManifestURLs: [URL] = [
        URL(string: "https://word-flow.store/update/version.json")!,
        URL(string: "https://word-flow.store/update.json")!,
        URL(string: "https://word-flow.store/update/version.php")!,
        URL(string: "https://word-flow.store/version.php")!
    ]
    
    @Published var isChecking = false
    
    private init() {}
    
    func checkForUpdates(userInitiated: Bool) {
        guard !isChecking else { return }
        isChecking = true
        
        Task {
            do {
                let updateInfo = try await fetchLatestUpdateInfo()
                
                // 2. Compare Versions
                let currentVersion = Bundle.main.infoDictionary?["CFBundleShortVersionString"] as? String ?? "1.0"
                
                if isNewer(remote: updateInfo.version, current: currentVersion) {
                    // Update Available
                    await MainActor.run {
                        self.showUpdateAlert(info: updateInfo)
                        self.isChecking = false
                    }
                } else {
                    // Up to date
                    if userInitiated {
                        await MainActor.run {
                            self.showUpToDateAlert()
                            self.isChecking = false
                        }
                    } else {
                        await MainActor.run { self.isChecking = false }
                    }
                }
            } catch {
                print("Update check failed: \(error)")
                if userInitiated {
                    await MainActor.run {
                        self.showErrorAlert(error: error)
                        self.isChecking = false
                    }
                } else {
                    await MainActor.run { self.isChecking = false }
                }
            }
        }
    }

    private func fetchLatestUpdateInfo() async throws -> UpdateInfo {
        var lastError: Error?

        for manifestURL in updateManifestURLs {
            do {
                return try await fetchUpdateInfo(from: manifestURL)
            } catch {
                lastError = error
                continue
            }
        }

        throw lastError ?? UpdateCheckError.noReachableUpdateEndpoint
    }

    private func fetchUpdateInfo(from manifestURL: URL) async throws -> UpdateInfo {
        var request = URLRequest(url: manifestURL)
        request.timeoutInterval = 8
        request.cachePolicy = .reloadIgnoringLocalCacheData
        request.setValue("application/json", forHTTPHeaderField: "Accept")

        let (data, response) = try await URLSession.shared.data(for: request)

        guard let httpResponse = response as? HTTPURLResponse else {
            throw UpdateCheckError.invalidServerResponse
        }

        guard (200...299).contains(httpResponse.statusCode) else {
            throw UpdateCheckError.invalidStatusCode(httpResponse.statusCode)
        }

        if let contentType = httpResponse.value(forHTTPHeaderField: "Content-Type")?.lowercased(),
           !contentType.contains("application/json") && !contentType.contains("text/json") {
            throw UpdateCheckError.unsupportedContentType(contentType)
        }

        guard !data.isEmpty else {
            throw UpdateCheckError.emptyResponse
        }

        let decoder = JSONDecoder()
        decoder.keyDecodingStrategy = .convertFromSnakeCase

        guard let info = try? decoder.decode(UpdateInfo.self, from: data) else {
            throw UpdateCheckError.invalidManifestFormat
        }

        guard !info.version.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty else {
            throw UpdateCheckError.invalidManifestFormat
        }

        return info
    }
    
    // Very basic semantic version comparison
    private func isNewer(remote: String, current: String) -> Bool {
        return remote.compare(current, options: .numeric) == .orderedDescending
    }
    
    // MARK: - Alerts
    
    private func showUpdateAlert(info: UpdateInfo) {
        let appLanguage = UserDefaults.standard.string(forKey: "appLanguage")?.uppercased() ?? "EN"
        let isDE = appLanguage == "DE"

        let alert = NSAlert()
        applyCurrentAppIcon(to: alert)

        alert.messageText = isDE
            ? "Wordflow \(info.version) ist verfügbar"
            : "Wordflow \(info.version) is available"

        let notes = info.releaseNotes ?? ""
        let deBase = "Das Update ist kostenlos \u{2014} dein Kauf gilt fuer alle zukuenftigen Versionen.\n\nKlicke auf \"Jetzt herunterladen\", um das DMG zu laden. Ersetze danach einfach die App in deinem Programme-Ordner."
        let enBase = "This update is free \u{2014} your purchase covers all future versions.\n\nClick \"Download now\" to get the DMG. Then replace the app in your Applications folder."
        let deText = notes.isEmpty ? deBase : "\(deBase)\n\n\(notes)"
        let enText = notes.isEmpty ? enBase : "\(enBase)\n\n\(notes)"
        alert.informativeText = isDE ? deText : enText

        alert.alertStyle = .informational
        alert.addButton(withTitle: isDE ? "Jetzt herunterladen" : "Download now")
        alert.addButton(withTitle: isDE ? "Später" : "Later")

        let response = alert.runModal()
        if response == .alertFirstButtonReturn {
            NSWorkspace.shared.open(preferredUpdateURL(for: info))
        }
    }

    private func preferredUpdateURL(for info: UpdateInfo) -> URL {
        // Prefer direct download URL (DMG) — simpler for the user
        if let directDownload = normalizedURL(from: info.downloadURL) {
            return directDownload
        }
        // Fall back to update page (word-flow.store/update)
        if let updatePage = normalizedURL(from: info.updateURL) {
            return updatePage
        }
        return defaultUpdateDestination
    }

    private func normalizedURL(from value: String?) -> URL? {
        guard let raw = value?.trimmingCharacters(in: .whitespacesAndNewlines),
              !raw.isEmpty,
              let url = URL(string: raw),
              ["https"].contains(url.scheme?.lowercased() ?? "") else {
            return nil
        }

        return url
    }
    
    private func showUpToDateAlert() {
        let alert = NSAlert()
        applyCurrentAppIcon(to: alert)
        alert.messageText = "Du bist auf dem neuesten Stand"
        alert.informativeText = "Wordflow Version \(Bundle.main.infoDictionary?["CFBundleShortVersionString"] as? String ?? "1.0") ist die aktuelle Version."
        alert.alertStyle = .informational
        alert.addButton(withTitle: "Okay")
        alert.runModal()
    }
    
    private func showErrorAlert(error: Error) {
        let alert = NSAlert()
        applyCurrentAppIcon(to: alert)
        alert.messageText = "Update-Prüfung fehlgeschlagen"
        alert.informativeText = "Konnte nicht nach Updates suchen.\n\(error.localizedDescription)"
        alert.alertStyle = .warning
        alert.addButton(withTitle: "Okay")
        alert.runModal()
    }

    private func applyCurrentAppIcon(to alert: NSAlert) {
        if let iconURL = Bundle.main.url(forResource: "AppIcon", withExtension: "icns"),
           let icon = NSImage(contentsOf: iconURL) {
            alert.icon = icon
            return
        }

        alert.icon = NSApplication.shared.applicationIconImage
    }
}
