import SwiftUI
import AppKit
import UserNotifications
import Combine

@main
struct WordflowApp: App {
    @StateObject private var appState = AppState()
    @NSApplicationDelegateAdaptor(AppDelegate.self) var appDelegate
    
    var body: some Scene {
        // Primary entry - menu bar
        MenuBarExtra {
            MenuBarView()
                .environmentObject(appState)
        } label: {
            // Dynamic Custom Icon
            if let image = templateMenuBarImage(named: appState.isRecording ? "MenuBar-Recording" : "MenuBar-Normal") {
                Image(nsImage: image)
                    .renderingMode(.template)
            } else {
                // Fallback
                Image(systemName: appState.isRecording ? "waveform.circle.fill" : "mic.circle")
                    .symbolRenderingMode(.hierarchical)
            }
        }
        .menuBarExtraStyle(.window)
        
        Settings {
            SettingsView()
                .environmentObject(appState)
        }
        .windowResizability(.contentMinSize)
        
        WindowGroup("Notizen Verlauf", id: "history") {
            HistoryView()
                .environmentObject(appState)
                .frame(minWidth: 500, maxWidth: .infinity, minHeight: 600, maxHeight: .infinity)
        }
        .handlesExternalEvents(matching: Set(arrayLiteral: "history"))
        .windowResizability(.contentMinSize)

        WindowGroup("Quick Setup", id: "quicksetup") {
            QuickSetupWindowRoot()
                .frame(minWidth: 720, minHeight: 760)
        }
        .handlesExternalEvents(matching: Set(arrayLiteral: "quicksetup"))
        .windowResizability(.contentMinSize)
        .windowStyle(.hiddenTitleBar)
    }

    private func templateMenuBarImage(named name: String) -> NSImage? {
        guard let loaded = NSImage(named: name),
              let image = loaded.copy() as? NSImage else {
            return nil
        }
        image.isTemplate = true
        return image
    }
    
    // Static helper to open history window
    static func openHistoryWindow() {
        if let url = URL(string: "wordflow://history") {
            NSWorkspace.shared.open(url)
            // Ensure app is active to show window
            NSApp.activate(ignoringOtherApps: true)
        }
    }

    static func openQuickSetupWindow() {
        if let url = URL(string: "wordflow://quicksetup") {
            NSWorkspace.shared.open(url)
            NSApp.activate(ignoringOtherApps: true)
        }
    }

    static func closeQuickSetupWindow() {
        if let window = NSApp.windows.first(where: { $0.identifier?.rawValue == "wordflow.quicksetup" }) {
            window.close()
        }
    }
}

struct QuickSetupWindowRoot: View {
    var body: some View {
        WelcomeOnboardingSheet()
            .ignoresSafeArea(.container, edges: .top)
            .background(QuickSetupWindowChromeConfigurator())
    }
}

private struct QuickSetupWindowChromeConfigurator: NSViewRepresentable {
    func makeNSView(context: Context) -> NSView {
        let view = NSView()
        Task { @MainActor in
            guard let window = view.window else { return }
            window.identifier = NSUserInterfaceItemIdentifier("wordflow.quicksetup")
            window.title = ""
            window.titleVisibility = .hidden
            window.titlebarAppearsTransparent = true
            window.titlebarSeparatorStyle = .none
            window.isMovableByWindowBackground = false
            window.toolbar = nil
            window.styleMask.insert(.fullSizeContentView)
            window.isOpaque = true
            window.backgroundColor = NSColor.windowBackgroundColor

            // Hide traffic-light controls for this onboarding window.
            [NSWindow.ButtonType.closeButton, .miniaturizeButton, .zoomButton].forEach { buttonType in
                if let button = window.standardWindowButton(buttonType) {
                    button.isHidden = true
                    button.isEnabled = false
                }
            }
        }
        return view
    }

    func updateNSView(_ nsView: NSView, context: Context) {
        Task { @MainActor in
            guard let window = nsView.window else { return }
            window.identifier = NSUserInterfaceItemIdentifier("wordflow.quicksetup")
            window.title = ""
            window.titleVisibility = .hidden
            window.titlebarAppearsTransparent = true
            window.titlebarSeparatorStyle = .none
            window.isMovableByWindowBackground = false
            window.toolbar = nil
            window.styleMask.insert(.fullSizeContentView)
            window.isOpaque = true
            window.backgroundColor = NSColor.windowBackgroundColor

            // Keep controls hidden on view updates as well.
            [NSWindow.ButtonType.closeButton, .miniaturizeButton, .zoomButton].forEach { buttonType in
                if let button = window.standardWindowButton(buttonType) {
                    button.isHidden = true
                    button.isEnabled = false
                }
            }
        }
    }
}

// MARK: - App Delegate
@MainActor
class AppDelegate: NSObject, NSApplicationDelegate {
    var hotkeyManager: HotkeyManager?
    var overlayWindowController: OverlayWindowController?
    private var didScheduleAutoSetupWindow = false
    private var didLaunchReplacementOnTerminate = false
    private var restartRequestedOnTerminate = false
    private var periodicUpdateCheckTimer: Timer?
    private var authStateCancellable: AnyCancellable?

    private let lastAutoUpdateCheckKey = "lastAutoUpdateCheckAt"
    private let autoUpdateChecksEnabledKey = "autoUpdateChecksEnabled"
    private let periodicUpdateInterval: TimeInterval = 7 * 24 * 60 * 60
    private let periodicHeartbeatInterval: TimeInterval = 12 * 60 * 60
    
    func applicationDidFinishLaunching(_ notification: Notification) {
        // Hide from Dock
        NSApp.setActivationPolicy(.accessory)
        
        // Setup global hotkey
        setupHotkey()
        
        // Register Defaults
        UserDefaults.standard.register(defaults: [
            "enableTextCorrection": true,
            "appLanguage": "EN",
            "soundsEnabled": true,
            "autoPasteEnabled": true,
            "autoUpdateChecksEnabled": true,
            "hasSeenWelcomeOnboarding": false,
            "hasCompletedInitialLanguageChoice": false,
            "showWelcomeSheetOnSettingsOpen": false,
            "quickSetupResumeAfterRestart": false,
            "quickSetupLaunchStep": ""
        ])

        // Resume path has highest priority and should not race with other auto-open paths.
        let resumedQuickSetup = handleQuickSetupResumeIfNeeded()

        observeAuthStateForSetup()

        if !resumedQuickSetup {
            // Mark first-launch onboarding so it can be shown after a successful login.
            handleFirstLaunchWelcome()
        }

        // Silent update checks: once on launch and then every 7 days while app stays open.
        setupAutomaticUpdateChecks()

        // Supabase: Session + Trial + Update in einem Call
        Task {
            await SupabaseService.shared.checkSession()
            if !resumedQuickSetup {
                openQuickSetupIfNeededAfterLogin(trigger: "startup-session-check")
            }
            LicenseManager.shared.checkAndShowPaywallIfNeeded()
        }
    }

    // Deep Link Handler: wordflow://activate#access_token=...
    func application(_ application: NSApplication, open urls: [URL]) {
        guard let url = urls.first, url.scheme == "wordflow" else { return }
        LogManager.shared.log("🔗 Deep Link empfangen: \(url)")
        Task {
            await SupabaseService.shared.handleDeepLink(url: url)
            LicenseManager.shared.checkAndShowPaywallIfNeeded()
        }
    }

    func applicationWillTerminate(_ notification: Notification) {
        requestReplacementRelaunchIfNeeded()
        periodicUpdateCheckTimer?.invalidate()
        periodicUpdateCheckTimer = nil
    }

    private func handleQuickSetupResumeIfNeeded() -> Bool {
        let defaults = UserDefaults.standard
        let resumeFlag = defaults.bool(forKey: "quickSetupResumeAfterRestart")
        let launchStep = defaults.string(forKey: "quickSetupLaunchStep") ?? ""
        guard resumeFlag || !launchStep.isEmpty else { return false }

        let resolvedLaunchStep = launchStep.isEmpty ? "hotkey" : launchStep
        LogManager.shared.log("🔁 Resume after restart detected: scheduling quick setup window at step '\(resolvedLaunchStep)'")
        defaults.set(false, forKey: "quickSetupResumeAfterRestart")
        defaults.set(resolvedLaunchStep, forKey: "quickSetupLaunchStep")
        defaults.set(false, forKey: "showWelcomeSheetOnSettingsOpen")
        openQuickSetupWindowOnLaunch(after: 0.8)
        return true
    }

    func requestReplacementRelaunchIfNeeded() {
        let defaults = UserDefaults.standard
        let hasLaunchStep = !(defaults.string(forKey: "quickSetupLaunchStep") ?? "").isEmpty
        guard restartRequestedOnTerminate || defaults.bool(forKey: "quickSetupResumeAfterRestart") || hasLaunchStep else { return }
        guard !didLaunchReplacementOnTerminate else { return }
        didLaunchReplacementOnTerminate = true

        let bundlePath = Bundle.main.bundlePath
        let script = "sleep 0.65 && /usr/bin/open '\(bundlePath)'"
        let process = Process()
        process.executableURL = URL(fileURLWithPath: "/bin/sh")
        process.arguments = ["-c", script]

        do {
            try process.run()
            LogManager.shared.log("🚀 Relaunch spawned from applicationWillTerminate")
        } catch {
            LogManager.shared.log("❌ Failed to spawn relaunch on terminate: \(error)")
        }
    }

    func requestQuickSetupRelaunchOnTerminate() {
        restartRequestedOnTerminate = true
        LogManager.shared.log("🧭 Restart requested: relaunch will be spawned on terminate")
    }

    private func handleFirstLaunchWelcome() {
        let defaults = UserDefaults.standard
        let hasSeenWelcome = defaults.bool(forKey: "hasSeenWelcomeOnboarding")

        guard !hasSeenWelcome else { return }

        defaults.set(true, forKey: "hasSeenWelcomeOnboarding")
        defaults.set(true, forKey: "showWelcomeSheetOnSettingsOpen")

        LogManager.shared.log("👋 First launch detected: onboarding flagged and will open after login")
    }

    private func openQuickSetupWindowOnLaunch(after delay: TimeInterval = 0.8) {
        guard !didScheduleAutoSetupWindow else { return }
        didScheduleAutoSetupWindow = true

        Task { @MainActor in
            try? await Task.sleep(for: .seconds(delay))
            NSApp.activate(ignoringOtherApps: true)
            WordflowApp.openQuickSetupWindow()
            LogManager.shared.log("🪟 Quick setup auto-open requested")

            for attempt in 1...8 {
                try? await Task.sleep(for: .seconds(0.35))

                if isQuickSetupWindowVisible() {
                    LogManager.shared.log("✅ Quick setup window is visible after attempt \(attempt)")
                    return
                }

                if attempt == 4 {
                    LogManager.shared.log("🪟 Quick setup still not visible, issuing one retry open")
                    NSApp.activate(ignoringOtherApps: true)
                    WordflowApp.openQuickSetupWindow()
                }
            }

            LogManager.shared.log("⚠️ Quick setup window was not visible after visibility checks")
        }
    }

    @MainActor
    private func isQuickSetupWindowVisible() -> Bool {
        NSApp.windows.contains { window in
            window.identifier?.rawValue == "wordflow.quicksetup" && window.isVisible
        }
    }
    
    private func observeAuthStateForSetup() {
        authStateCancellable = SupabaseService.shared.$isLoggedIn
            .removeDuplicates()
            .sink { [weak self] isLoggedIn in
                guard isLoggedIn else { return }
                self?.openQuickSetupIfNeededAfterLogin(trigger: "auth-state-change")
            }
    }

    private func hasMissingAPIKey() -> Bool {
        let apiKey = UserDefaults.standard.string(forKey: "groqAPIKey") ?? ""
        return apiKey.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty
    }

    private func openQuickSetupIfNeededAfterLogin(trigger: String) {
        guard SupabaseService.shared.isLoggedIn else { return }

        let defaults = UserDefaults.standard
        let shouldShowWelcome = defaults.bool(forKey: "showWelcomeSheetOnSettingsOpen")
        let missingAPIKey = hasMissingAPIKey()
        guard shouldShowWelcome || missingAPIKey else { return }

        openQuickSetupWindowOnLaunch(after: 0.5)
        LogManager.shared.log("🔧 Opening quick setup after login (\(trigger)); missingAPIKey=\(missingAPIKey), firstLaunchFlag=\(shouldShowWelcome)")
    }

    private func setupAutomaticUpdateChecks() {
        let defaults = UserDefaults.standard
        guard defaults.bool(forKey: autoUpdateChecksEnabledKey) else { return }

        periodicUpdateCheckTimer?.invalidate()
        periodicUpdateCheckTimer = Timer.scheduledTimer(
            timeInterval: periodicHeartbeatInterval,
            target: self,
            selector: #selector(handlePeriodicUpdateTimer),
            userInfo: nil,
            repeats: true
        )

        if let timer = periodicUpdateCheckTimer {
            RunLoop.main.add(timer, forMode: .common)
        }

        // Always do a quick silent check when the app launches.
        performAutomaticUpdateCheckIfDue(force: true, trigger: "launch")
    }

    @objc private func handlePeriodicUpdateTimer() {
        performAutomaticUpdateCheckIfDue(force: false, trigger: "periodic")
    }

    private func performAutomaticUpdateCheckIfDue(force: Bool, trigger: String) {
        let defaults = UserDefaults.standard
        guard defaults.bool(forKey: autoUpdateChecksEnabledKey) else { return }

        let now = Date()
        let lastCheck = defaults.object(forKey: lastAutoUpdateCheckKey) as? Date ?? .distantPast

        if !force && now.timeIntervalSince(lastCheck) < periodicUpdateInterval {
            return
        }

        defaults.set(now, forKey: lastAutoUpdateCheckKey)
        UpdateChecker.shared.checkForUpdates(userInitiated: false)
        LogManager.shared.log("🔄 Automatic update check triggered (\(trigger))")
    }
    
    private func setupHotkey() {
        hotkeyManager = HotkeyManager(
            onHotkeyChange: { [weak self] isPressed in
                DispatchQueue.main.async {
                    if isPressed {
                        self?.startRecording()
                    } else {
                        // Check if locked - if so, don't stop
                        if self?.overlayWindowController?.isLocked == true {
                            print("🔒 Fn losgelassen aber gelockt - Aufnahme läuft weiter")
                            return
                        }
                        self?.stopRecording()
                    }
                }
            },
            onLockChange: { [weak self] isLocked in
                DispatchQueue.main.async {
                    // Sync lock state from HotkeyManager to UI and play sound
                    self?.overlayWindowController?.isLocked = isLocked
                    
                    if isLocked {
                        SoundManager.shared.playLock()
                        print("🔒 Lock aktiviert (Hotkey)")
                    } else {
                        SoundManager.shared.playUnlock()
                        print("🔓 Lock deaktiviert (Hotkey)")
                    }
                }
            },
            onExpandChange: { [weak self] isExpanded in
                DispatchQueue.main.async {
                    self?.overlayWindowController?.isExpanded = isExpanded
                }
            },
            onCancel: { [weak self] in
                DispatchQueue.main.async {
                    self?.cancelRecording()
                }
            }
        )
        hotkeyManager?.onProfileChange = { mode in
            DispatchQueue.main.async {
                let pm = PromptManager.shared
                switch mode {
                case .smartCasual:
                    pm.selectProfile(id: PromptManager.smartCasualId)
                    AppState.shared?.activeProfileColor = WordflowTheme.profileSmartCasual
                case .email:
                    pm.selectProfile(id: PromptManager.emailId)
                    AppState.shared?.activeProfileColor = WordflowTheme.profileEmail
                case .tech:
                    pm.selectProfile(id: PromptManager.techId)
                    AppState.shared?.activeProfileColor = WordflowTheme.profileTech
                }
            }
        }
        hotkeyManager?.start()
    }
    
    private func startRecording() {
        guard LicenseManager.shared.canUseApp else {
            let appLanguage = UserDefaults.standard.string(forKey: "appLanguage") ?? "EN"
            if SupabaseService.shared.isLoggedIn {
                LicenseManager.shared.checkAndShowPaywallIfNeeded()
                let body = appLanguage.uppercased() == "EN"
                    ? "Your trial has expired. Please unlock Wordflow to continue."
                    : "Deine Trial ist abgelaufen. Bitte entsperre Wordflow, um fortzufahren."
                sendNotification(title: "Wordflow", body: body)
            } else {
                let body = appLanguage.uppercased() == "EN"
                    ? "Please log in from the menu bar before using Wordflow."
                    : "Bitte melde dich zuerst ueber die Menueleiste an, bevor du Wordflow nutzt."
                sendNotification(title: "Wordflow", body: body)
                NSApp.activate(ignoringOtherApps: true)
            }
            return
        }

        guard let appState = AppState.shared else { 
            print("Fehler: AppState noch nicht initialisiert")
            return 
        }
        
        // Profil auf Smart Casual zurücksetzen + Farbe zurücksetzen
        PromptManager.shared.selectProfile(id: PromptManager.smartCasualId)
        AppState.shared?.activeProfileColor = WordflowTheme.profileSmartCasual

        // Play start sound & haptics
        SoundManager.shared.playStartRecording()
        SoundManager.shared.playHaptic(type: .start)
        
        // Create window controller with callbacks
        overlayWindowController = OverlayWindowController(
            appState: appState,
            onCancel: { [weak self] in
                self?.cancelRecording()
            },
            onLock: { [weak self] in
                self?.toggleLock()
            },
            onStop: { [weak self] in
                self?.stopRecording()
            }
        )
        
        overlayWindowController?.showWindow(nil)
        appState.audioRecorder.startRecording()
    }
    
    private func toggleLock() {
        // Sync lock state with HotkeyManager
        if let isLocked = overlayWindowController?.isLocked {
            hotkeyManager?.isLocked = isLocked
            
            // Play appropriate sound
            if isLocked {
                SoundManager.shared.playLock()
                print("🔒 Lock aktiviert - Fn loslassen möglich")
            } else {
                SoundManager.shared.playUnlock()
                print("🔓 Lock deaktiviert")
            }
        }
    }
    
    private func cancelRecording() {
        guard let appState = AppState.shared else { return }
        
        // Play cancel sound & haptics
        SoundManager.shared.playCancel()
        SoundManager.shared.playHaptic(type: .error)
        
        // Reset all states
        appState.activeProfileColor = WordflowTheme.profileSmartCasual
        PromptManager.shared.selectProfile(id: PromptManager.smartCasualId)
        appState.isProcessing = false
        appState.audioRecorder.stopRecording()
        
        // Close window
        overlayWindowController?.close()
        overlayWindowController = nil
        hotkeyManager?.stopRecording()
        
        print("🚫 Aufnahme abgebrochen")
    }
    
    private func stopRecording() {
        guard let appState = AppState.shared else { return }
        
        // Stop recording first to get duration
        appState.audioRecorder.stopRecording()
        
        // Check if recording was long enough
        if !appState.audioRecorder.isLastRecordingValid {
            print("⚠️ Aufnahme zu kurz (< \(AudioRecorder.minimumDuration)s) - ignoriert")
            SoundManager.shared.playCancel()
            overlayWindowController?.close()
            overlayWindowController = nil
            return
        }
        
        // Play stop sound & haptics
        SoundManager.shared.playStopRecording()
        SoundManager.shared.playHaptic(type: .stop)
        
        // Don't close window yet - enter processing state
        appState.isProcessing = true
        
        // Transcribe audio
        Task {
            await transcribeAndPaste(appState: appState)
        }
    }
    
    private func transcribeAndPaste(appState: AppState) async {
        guard let audioURL = appState.audioRecorder.audioFileURL else {
            // Guard against missing file - cleanup immediately
            await MainActor.run {
                appState.isProcessing = false
                self.overlayWindowController?.close()
                self.overlayWindowController = nil
            }
            return
        }

        // Step 1: Transcribe audio
        var text: String
        do {
            text = try await appState.transcriptionService.transcribe(audioURL: audioURL)
            print("📝 Transkription: \(text.prefix(50))...")
        } catch {
            await handlePipelineError(error, stage: .transcription, appState: appState)
            return
        }

        // Step 2: Correct text with Llama (if enabled)
        if UserDefaults.standard.bool(forKey: "enableTextCorrection") {
            do {
                print("🔧 Korrigiere mit Llama...")
                text = try await appState.textCorrectionService.correctText(text)
                print("✅ Korrigiert: \(text.prefix(50))...")
            } catch {
                await handlePipelineError(error, stage: .correction, appState: appState)
                return
            }
        }

        // Capture for MainActor
        let finalText = text

        await MainActor.run {
            if finalText.isEmpty {
                print("⚠️ Leerer Text (oder Halluzination gefiltert) - Nichts tun.")
                SoundManager.shared.playError() // Or explicit "No Speech" sound

                // cleanup even on empty text
                appState.isProcessing = false
                self.overlayWindowController?.close()
                self.overlayWindowController = nil

                return
            }

            // Play success sound
            SoundManager.shared.playSuccess()

            // Reset last error after a successful full pipeline run.
            appState.clearLastError()

            appState.clipboardManager.copyAndPaste(text: finalText)
            appState.clipboardManager.addToHistory(text: finalText)
            appState.refreshHistory()

            // Track Statistics
            // Note: We don't have the "original" raw text length easily available unless we store it before correction.
            // For simplicity, we use finalText count for both if correction was off, or just track final.
            // Let's assume input length approx output length for now.
            StatisticsManager.shared.logRequest(
                wordsInTranscription: finalText.split(separator: " ").count,
                wordsInFinalText: finalText.split(separator: " ").count
            )

            // Cleanup
            appState.isProcessing = false
            self.overlayWindowController?.close()
            self.overlayWindowController = nil
        }
    }

    private func handlePipelineError(_ error: Error, stage: AppErrorStage, appState: AppState) async {
        let mappedError = AppErrorMapper.map(error: error, stage: stage)
        let appLanguage = UserDefaults.standard.string(forKey: "appLanguage") ?? "EN"
        let notificationTitle = appLanguage.uppercased() == "EN" ? "Wordflow Error" : "Wordflow Fehler"
        let technicalPreview = String(mappedError.technicalMessage.prefix(200))

        LogManager.shared.log("❌ Pipeline error: stage=\(stage.rawValue), category=\(mappedError.category.rawValue), code=\(mappedError.code ?? "-")")
        LogManager.shared.log("❌ Technical detail: \(technicalPreview)")

        await MainActor.run {
            appState.setLastError(mappedError)
            SoundManager.shared.playError()
            appState.isProcessing = false
            self.overlayWindowController?.close()
            self.overlayWindowController = nil

            // Show mapped user-facing notification.
            self.sendNotification(title: notificationTitle, body: mappedError.userMessage(for: appLanguage))
        }
    }
    
    // MARK: - Notification Helper
    private func sendNotification(title: String, body: String) {
        let content = UNMutableNotificationContent()
        content.title = title
        content.body = body
        content.sound = .default
        
        let request = UNNotificationRequest(identifier: UUID().uuidString, content: content, trigger: nil)
        UNUserNotificationCenter.current().add(request)
    }
}

// AppState moved to Shared/AppState.swift

// MARK: - Menu Bar View (Premium Design)
struct MenuBarView: View {
    @EnvironmentObject var appState: AppState
    @AppStorage("appLanguage") private var appLanguage = "EN"
    @AppStorage("autoPasteEnabled") private var autoPasteEnabled = true
    @AppStorage("soundsEnabled") private var soundsEnabled = true
    @ObservedObject private var supabase = SupabaseService.shared
    @ObservedObject private var updateChecker = UpdateChecker.shared

    var body: some View {
        // ── Not logged in → show login screen ──────────────────────
        if !supabase.isLoggedIn {
            LoginView()
                .frame(width: 340)
        } else {
            mainContent
        }
    }

    private var mainContent: some View {
        VStack(alignment: .leading, spacing: 14) {

            // ── Update banner ───────────────────────────────────
            if supabase.hasUpdateAvailable,
               let info = supabase.latestVersionInfo {
                Button {
                    if let url = URL(string: info.url) {
                        NSWorkspace.shared.open(url)
                    }
                } label: {
                    HStack(spacing: 8) {
                        Image(systemName: "arrow.down.circle.fill")
                            .foregroundColor(.white)
                        VStack(alignment: .leading, spacing: 1) {
                            Text(appLanguage == "EN" ? "Update available — v\(info.version)" : "Update verfügbar — v\(info.version)")
                                .font(.system(size: 12, weight: .semibold))
                                .foregroundColor(.white)
                            Text(appLanguage == "EN" ? "Tap to download" : "Tippen zum Herunterladen")
                                .font(.system(size: 11))
                                .foregroundColor(.white.opacity(0.8))
                        }
                        Spacer()
                    }
                    .padding(.horizontal, 14)
                    .padding(.vertical, 10)
                    .background(
                        RoundedRectangle(cornerRadius: 10, style: .continuous)
                            .fill(WordflowTheme.primary)
                    )
                }
                .buttonStyle(.plain)
            }

            // ── Header ───────────────────────────────────
            HStack(spacing: 10) {
                Image("AppLogo")
                    .resizable()
                    .scaledToFit()
                    .frame(height: 14)
                Text("Wordflow")
                    .font(.system(size: 20, weight: .bold))
                    .foregroundColor(WordflowTheme.onSurface)
            }
            .padding(.top, 8)
            .padding(.horizontal, 4)

            // ── App Settings ───────────────────────────────────
            menuBarCard {
                VStack(spacing: 0) {
                        if #available(macOS 14.0, *) {
                            SettingsLink {
                                MenuBarRowHighlight { settingsLinkLabel }
                            }
                            .buttonStyle(.plain)
                        } else {
                            Button(action: {
                                NSApp.sendAction(Selector(("showPreferencesWindow:")), to: nil, from: nil)
                            }) {
                                MenuBarRowHighlight { settingsLinkLabel }
                            }
                            .buttonStyle(.plain)
                        }

                        Divider()
                            .padding(.leading, 40)
                        .opacity(0.5)

                    MenuBarRowButton(action: {
                        NSApplication.shared.terminate(nil)
                    }) {
                        Label(appLanguage == "EN" ? "Quit" : "Beenden", systemImage: "power")
                            .font(.system(size: 13, weight: .medium))
                            .foregroundColor(.red.opacity(0.9))
                            .frame(maxWidth: .infinity, alignment: .leading)
                            .padding(.horizontal, 12)
                            .padding(.vertical, 10)
                    }
                    .keyboardShortcut("q")
                }
            }
        }
        .padding(.horizontal, 16)
        .padding(.vertical, 16)
        .frame(width: 360)
        .background(.regularMaterial)
    }

    private var settingsLinkLabel: some View {
        HStack(spacing: 8) {
            Label(appLanguage == "EN" ? "Settings..." : "Einstellungen...", systemImage: "gear")
                .font(.system(size: 13, weight: .medium))
            Spacer()
            Image(systemName: "chevron.right")
                .font(.system(size: 11, weight: .semibold))
        }
        .frame(maxWidth: .infinity, alignment: .leading)
        .foregroundColor(WordflowTheme.onSurface)
        .padding(.horizontal, 12)
        .padding(.vertical, 10)
    }

    private func menuBarSectionTitle(_ title: String) -> some View {
        Text(title)
            .font(.caption.weight(.semibold))
            .foregroundColor(WordflowTheme.onSurfaceVariant.opacity(0.9))
            .padding(.leading, 4)
    }

    @ViewBuilder
    private func menuBarCard<Content: View>(@ViewBuilder content: () -> Content) -> some View {
        content()
            .background(Color.primary.opacity(0.04))
            .clipShape(RoundedRectangle(cornerRadius: 10, style: .continuous))
    }
}

/// A wrapper view that adds a hover highlight for custom buttons or links.
struct MenuBarRowHighlight<Content: View>: View {
    @ViewBuilder let content: Content
    @State private var isHovered = false

    var body: some View {
        content
            .contentShape(Rectangle())
            .background(
                RoundedRectangle(cornerRadius: 6, style: .continuous)
                    .fill(isHovered ? Color.primary.opacity(0.08) : Color.clear)
                    .padding(.horizontal, 4)
                    .padding(.vertical, 2)
            )
            .onHover { hovering in
                withAnimation(.easeInOut(duration: 0.15)) {
                    isHovered = hovering
                }
            }
    }
}

/// A button with hover highlight for menu bar rows.
struct MenuBarRowButton<Label: View>: View {
    let action: () -> Void
    @ViewBuilder let label: Label
    @State private var isHovered = false

    var body: some View {
        Button(action: action) {
            label
                .contentShape(Rectangle())
                .background(
                    RoundedRectangle(cornerRadius: 6, style: .continuous)
                        .fill(isHovered ? Color.primary.opacity(0.08) : Color.clear)
                        .padding(.horizontal, 4)
                        .padding(.vertical, 2)
                )
        }
        .buttonStyle(.plain)
        .onHover { hovering in
            withAnimation(.easeInOut(duration: 0.15)) {
                isHovered = hovering
            }
        }
    }
}

struct PillToggleRow: View {
    let title: String
    let icon: String
    @Binding var isOn: Bool

    var body: some View {
        HStack(spacing: 12) {
            Image(systemName: icon)
                .font(.system(size: 13, weight: .medium))
                .foregroundColor(WordflowTheme.primary)
                .frame(width: 16)

            Toggle(isOn: $isOn) {
                Text(title)
                    .font(.system(size: 13, weight: .medium))
                    .foregroundColor(WordflowTheme.onSurface)
            }
            .toggleStyle(.switch)
            .tint(WordflowTheme.primary)
        }
        .padding(.horizontal, 12)
        .padding(.vertical, 10)
    }
}

