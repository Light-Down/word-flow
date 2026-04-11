import Foundation
import AuthenticationServices

// ─────────────────────────────────────────────
// MARK: - Response Models
// ─────────────────────────────────────────────

struct SessionCheckResponse: Codable {
    let valid: Bool
    let model: String?          // "trial" | "byok" | "free_tier" | "credits"
    let trialExpired: Bool?
    let trialDaysLeft: Int?
    let creditsBalance: Double?
    let latestVersion: String?
    let downloadUrl: String?
    let releaseNotes: String?
    let minRequired: String?
    let reason: String?

    enum CodingKeys: String, CodingKey {
        case valid, model, reason
        case trialExpired     = "trial_expired"
        case trialDaysLeft    = "trial_days_left"
        case creditsBalance   = "credits_balance"
        case latestVersion    = "latest_version"
        case downloadUrl      = "download_url"
        case releaseNotes     = "release_notes"
        case minRequired      = "min_required"
    }
}

struct SupabaseSession: Codable {
    let accessToken: String
    let refreshToken: String
    let expiresAt: TimeInterval

    enum CodingKeys: String, CodingKey {
        case accessToken  = "access_token"
        case refreshToken = "refresh_token"
        case expiresAt    = "expires_at"
    }

    var isExpired: Bool {
        Date().timeIntervalSince1970 > expiresAt - 60
    }
}

// ─────────────────────────────────────────────
// MARK: - SupabaseService
// ─────────────────────────────────────────────

@MainActor
class SupabaseService: ObservableObject {
    static let shared = SupabaseService()

    // MARK: Config
    private let projectURL = "https://amieachokpogaspaplxr.supabase.co"
    private let anonKey    = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFtaWVhY2hva3BvZ2FzcGFwbHhyIiwicm9sZSI6ImFub24iLCJpYXQiOjE3Njk2MjkyNjgsImV4cCI6MjA4NTIwNTI2OH0.h7-dkHNAcJjwoGgnBQxUH8fIcNpzDT1q9nyEeWlDNq8"

    // MARK: State
    @Published var isLoggedIn: Bool = false
    @Published var sessionCheck: SessionCheckResponse? = nil
    @Published var isLoading: Bool = false
    @Published var authError: String? = nil

    private let sessionKey = "wordflow_supabase_session"
    private let authContext = AuthPresentationContext()
    private var webAuthSession: ASWebAuthenticationSession?

    private init() {
        // Gespeicherte Session laden beim Start
        if let saved = loadStoredSession(), !saved.isExpired {
            self.isLoggedIn = true
            LogManager.shared.log("✅ Supabase: Gespeicherte Session gefunden")
        }
    }

    // ─────────────────────────────────────────────
    // MARK: - Magic Link senden
    // ─────────────────────────────────────────────

    func sendMagicLink(email: String) async {
        isLoading = true
        authError = nil

        let url = URL(string: "\(projectURL)/auth/v1/magiclink")!
        var req = URLRequest(url: url)
        req.httpMethod = "POST"
        req.setValue("application/json", forHTTPHeaderField: "Content-Type")
        req.setValue(anonKey, forHTTPHeaderField: "apikey")

        let body = ["email": email, "redirect_to": "wordflow://activate"]
        req.httpBody = try? JSONEncoder().encode(body)

        do {
            let (_, response) = try await URLSession.shared.data(for: req)
            if let http = response as? HTTPURLResponse, http.statusCode == 200 {
                LogManager.shared.log("📧 Magic Link gesendet an \(email)")
            } else {
                authError = "E-Mail konnte nicht gesendet werden. Bitte versuche es erneut."
            }
        } catch {
            authError = "Keine Verbindung. Bitte Internet prüfen."
        }
        isLoading = false
    }

    // ─────────────────────────────────────────────
    // MARK: - OAuth (Google / Apple)
    // ─────────────────────────────────────────────

    func signInWithGoogle() {
        guard let url = URL(string: "\(projectURL)/auth/v1/authorize?provider=google&redirect_to=wordflow://activate") else { return }
        NSWorkspace.shared.open(url)
        LogManager.shared.log("🌐 Google OAuth: Browser geöffnet")
    }

    func signInWithApple() {
        guard let url = URL(string: "\(projectURL)/auth/v1/authorize?provider=apple&redirect_to=wordflow://activate") else { return }
        NSWorkspace.shared.open(url)
        LogManager.shared.log("🍎 Apple OAuth: Browser geöffnet")
    }

    // ─────────────────────────────────────────────
    // MARK: - Deep Link verarbeiten (wordflow://activate)
    // ─────────────────────────────────────────────

    func handleDeepLink(url: URL) async {
        guard url.scheme == "wordflow" else { return }

        // Supabase Magic Link liefert Token im Fragment (#access_token=...&refresh_token=...)
        let fragment = url.fragment ?? ""
        var params: [String: String] = [:]
        for part in fragment.split(separator: "&") {
            let kv = part.split(separator: "=", maxSplits: 1)
            if kv.count == 2 { params[String(kv[0])] = String(kv[1]) }
        }

        guard let accessToken  = params["access_token"],
              let refreshToken = params["refresh_token"] else {
            LogManager.shared.log("⚠️ Deep Link: Kein Token gefunden")
            return
        }

        // Token-Ablauf berechnen (Standard: 3600 Sekunden)
        let expiresIn = Double(params["expires_in"] ?? "3600") ?? 3600
        let session = SupabaseSession(
            accessToken:  accessToken,
            refreshToken: refreshToken,
            expiresAt:    Date().timeIntervalSince1970 + expiresIn
        )

        saveSession(session)
        isLoggedIn = true
        LogManager.shared.log("✅ Supabase: Magic Link Login erfolgreich")

        // Sofort Session checken
        await checkSession()
    }

    // ─────────────────────────────────────────────
    // MARK: - Session Check (beim App-Start)
    // ─────────────────────────────────────────────

    func checkSession() async {
        guard let session = loadStoredSession() else {
            isLoggedIn = false
            return
        }

        // Session abgelaufen oder abweisend? → Refresh versuchen
        let tokenToUse: String
        if session.isExpired {
            guard let refreshed = await refreshSession(token: session.refreshToken) else {
                isLoggedIn = false
                LogManager.shared.log("❌ Token abgelaufen, Refresh fehlgeschlagen → ausgeloggt")
                return
            }
            saveSession(refreshed)
            tokenToUse = refreshed.accessToken
        } else {
            tokenToUse = session.accessToken
        }

        let url = URL(string: "\(projectURL)/functions/v1/check-session")!
        var req = URLRequest(url: url)
        req.httpMethod = "POST"
        req.setValue("Bearer \(tokenToUse)", forHTTPHeaderField: "Authorization")
        req.setValue(anonKey, forHTTPHeaderField: "apikey")
        req.setValue("application/json", forHTTPHeaderField: "Content-Type")

        do {
            let (data, response) = try await URLSession.shared.data(for: req)
            let statusCode = (response as? HTTPURLResponse)?.statusCode ?? 0

            // Bei 401: Token trotz nicht-abgelaufener isExpired-Prüfung ungültig → Refresh
            if statusCode == 401 {
                LogManager.shared.log("🔄 check-session 401 → versuche Token-Refresh")
                guard let freshSession = loadStoredSession(),
                      let refreshed = await refreshSession(token: freshSession.refreshToken) else {
                    isLoggedIn = false
                    LogManager.shared.log("❌ Refresh nach 401 fehlgeschlagen → ausgeloggt")
                    return
                }
                saveSession(refreshed)
                // Retry mit neuem Token
                var retryReq = URLRequest(url: url)
                retryReq.httpMethod = "POST"
                retryReq.setValue("Bearer \(refreshed.accessToken)", forHTTPHeaderField: "Authorization")
                retryReq.setValue(anonKey, forHTTPHeaderField: "apikey")
                retryReq.setValue("application/json", forHTTPHeaderField: "Content-Type")
                let (retryData, _) = try await URLSession.shared.data(for: retryReq)
                let result = try JSONDecoder().decode(SessionCheckResponse.self, from: retryData)
                sessionCheck = result
                isLoggedIn = result.valid
                LogManager.shared.log("🔍 Session Check (retry): model=\(result.model ?? "-"), trial_days=\(result.trialDaysLeft ?? 0)")
                return
            }

            let result = try JSONDecoder().decode(SessionCheckResponse.self, from: data)
            sessionCheck = result
            isLoggedIn = result.valid
            LogManager.shared.log("🔍 Session Check: model=\(result.model ?? "-"), trial_days=\(result.trialDaysLeft ?? 0)")
        } catch {
            // Offline Grace Period: Session bleibt gültig
            LogManager.shared.log("⚠️ Session Check fehlgeschlagen (offline?): \(error.localizedDescription)")
        }
    }

    // ─────────────────────────────────────────────
    // MARK: - Logout
    // ─────────────────────────────────────────────

    func logout() {
        UserDefaults.standard.removeObject(forKey: sessionKey)
        isLoggedIn = false
        sessionCheck = nil
        LogManager.shared.log("👋 Supabase: Ausgeloggt")
    }

    // ─────────────────────────────────────────────
    // MARK: - Computed Helpers
    // ─────────────────────────────────────────────

    var isTrialExpired: Bool {
        guard let check = sessionCheck else { return false }
        return check.trialExpired == true
    }

    var trialDaysLeft: Int {
        sessionCheck?.trialDaysLeft ?? 0
    }

    var isByokUnlocked: Bool {
        sessionCheck?.model == "byok"
    }

    var hasUpdateAvailable: Bool {
        guard let latest = sessionCheck?.latestVersion else { return false }
        let current = Bundle.main.infoDictionary?["CFBundleShortVersionString"] as? String ?? "0"
        return isNewer(remote: latest, current: current)
    }

    var latestVersionInfo: (version: String, url: String, notes: String?)? {
        guard let v = sessionCheck?.latestVersion,
              let u = sessionCheck?.downloadUrl else { return nil }
        return (v, u, sessionCheck?.releaseNotes)
    }

    // ─────────────────────────────────────────────
    // MARK: - Private Helpers
    // ─────────────────────────────────────────────

    private func refreshSession(token: String) async -> SupabaseSession? {
        let url = URL(string: "\(projectURL)/auth/v1/token?grant_type=refresh_token")!
        var req = URLRequest(url: url)
        req.httpMethod = "POST"
        req.setValue("application/json", forHTTPHeaderField: "Content-Type")
        req.setValue(anonKey, forHTTPHeaderField: "apikey")
        req.httpBody = try? JSONSerialization.data(withJSONObject: ["refresh_token": token])

        guard let (data, response) = try? await URLSession.shared.data(for: req) else { return nil }

        let statusCode = (response as? HTTPURLResponse)?.statusCode ?? 0
        guard (200...299).contains(statusCode) else {
            LogManager.shared.log("❌ Token-Refresh fehlgeschlagen (HTTP \(statusCode))")
            return nil
        }

        // Supabase liefert expires_in (relativ) ODER expires_at (absolut)
        // Wir parsen beides manuell
        guard let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
              let accessToken  = json["access_token"]  as? String,
              let refreshToken = json["refresh_token"] as? String
        else {
            LogManager.shared.log("❌ Token-Refresh: Ungültiges JSON")
            return nil
        }

        let expiresAt: TimeInterval
        if let ea = json["expires_at"] as? TimeInterval {
            expiresAt = ea
        } else if let ei = json["expires_in"] as? TimeInterval {
            expiresAt = Date().timeIntervalSince1970 + ei
        } else {
            expiresAt = Date().timeIntervalSince1970 + 3600
        }

        LogManager.shared.log("🔄 Token-Refresh erfolgreich")
        return SupabaseSession(accessToken: accessToken, refreshToken: refreshToken, expiresAt: expiresAt)
    }

    private func saveSession(_ session: SupabaseSession) {
        if let data = try? JSONEncoder().encode(session) {
            UserDefaults.standard.set(data, forKey: sessionKey)
        }
    }

    private func loadStoredSession() -> SupabaseSession? {
        guard let data = UserDefaults.standard.data(forKey: sessionKey),
              let session = try? JSONDecoder().decode(SupabaseSession.self, from: data)
        else { return nil }
        return session
    }

    // ─────────────────────────────────────────────
    // MARK: - E-Mail + Passwort Login
    // ─────────────────────────────────────────────

    func signInWithEmail(email: String, password: String) async {
        isLoading = true
        authError = nil

        let url = URL(string: "\(projectURL)/auth/v1/token?grant_type=password")!
        var req = URLRequest(url: url)
        req.httpMethod = "POST"
        req.setValue("application/json", forHTTPHeaderField: "Content-Type")
        req.setValue(anonKey, forHTTPHeaderField: "apikey")
        req.httpBody = try? JSONEncoder().encode(["email": email, "password": password])

        do {
            let (data, response) = try await URLSession.shared.data(for: req)
            let statusCode = (response as? HTTPURLResponse)?.statusCode ?? 0

            if statusCode == 400 {
                authError = "E-Mail oder Passwort falsch."
                isLoading = false
                return
            }

            guard let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
                  let accessToken  = json["access_token"]  as? String,
                  let refreshToken = json["refresh_token"] as? String
            else {
                authError = "Anmeldung fehlgeschlagen."
                isLoading = false
                return
            }

            let expiresAt = Date.now.timeIntervalSince1970 + (json["expires_in"] as? TimeInterval ?? 3600)
            saveSession(SupabaseSession(accessToken: accessToken, refreshToken: refreshToken, expiresAt: expiresAt))
            isLoggedIn = true
            LogManager.shared.log("✅ E-Mail Login erfolgreich")
            await checkSession()
        } catch {
            authError = "Keine Verbindung. Bitte Internet prüfen."
        }
        isLoading = false
    }

    // ─────────────────────────────────────────────
    // MARK: - E-Mail Registrierung
    // ─────────────────────────────────────────────

    func signUpWithEmail(email: String, password: String) async {
        isLoading = true
        authError = nil

        let url = URL(string: "\(projectURL)/auth/v1/signup")!
        var req = URLRequest(url: url)
        req.httpMethod = "POST"
        req.setValue("application/json", forHTTPHeaderField: "Content-Type")
        req.setValue(anonKey, forHTTPHeaderField: "apikey")
        req.httpBody = try? JSONEncoder().encode(["email": email, "password": password])

        do {
            let (data, response) = try await URLSession.shared.data(for: req)
            let statusCode = (response as? HTTPURLResponse)?.statusCode ?? 0

            if statusCode == 422 {
                authError = "Diese E-Mail-Adresse ist bereits registriert."
                isLoading = false
                return
            }

            if let json = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
               let accessToken  = json["access_token"]  as? String,
               let refreshToken = json["refresh_token"] as? String {
                // E-Mail-Bestätigung deaktiviert → sofort eingeloggt
                let expiresAt = Date.now.timeIntervalSince1970 + (json["expires_in"] as? TimeInterval ?? 3600)
                saveSession(SupabaseSession(accessToken: accessToken, refreshToken: refreshToken, expiresAt: expiresAt))
                isLoggedIn = true
                await checkSession()
            } else {
                // E-Mail-Bestätigung aktiv → User muss zuerst bestätigen
                authError = "✉️ Bitte bestätige deine E-Mail-Adresse. Dann kannst du dich anmelden."
            }
            LogManager.shared.log("📝 Registrierung abgeschlossen für \(email)")
        } catch {
            authError = "Keine Verbindung. Bitte Internet prüfen."
        }
        isLoading = false
    }

    // ─────────────────────────────────────────────
    // MARK: - Google Sign-In (OAuth via Browser)
    // ─────────────────────────────────────────────

    func signInWithGoogle() async {
        isLoading = true
        authError = nil

        let authURLString = "\(projectURL)/auth/v1/authorize?provider=google&redirect_to=wordflow://activate"
        guard let authURL = URL(string: authURLString) else {
            isLoading = false
            return
        }

        do {
            let callbackURL = try await withCheckedThrowingContinuation { (continuation: CheckedContinuation<URL, Error>) in
                let session = ASWebAuthenticationSession(
                    url: authURL,
                    callbackURLScheme: "wordflow"
                ) { url, error in
                    if let error {
                        continuation.resume(throwing: error)
                    } else if let url {
                        continuation.resume(returning: url)
                    }
                }
                session.prefersEphemeralWebBrowserSession = false
                session.presentationContextProvider = authContext
                self.webAuthSession = session
                session.start()
            }
            webAuthSession = nil
            await handleDeepLink(url: callbackURL)
        } catch {
            authError = "Google Sign-In fehlgeschlagen."
            LogManager.shared.log("❌ Google Sign-In: \(error.localizedDescription)")
        }
        isLoading = false
    }

    private func isNewer(remote: String, current: String) -> Bool {
        let r = remote.split(separator: ".").compactMap { Int($0) }
        let c = current.split(separator: ".").compactMap { Int($0) }
        for i in 0..<max(r.count, c.count) {
            let rv = i < r.count ? r[i] : 0
            let cv = i < c.count ? c[i] : 0
            if rv != cv { return rv > cv }
        }
        return false
    }
}
