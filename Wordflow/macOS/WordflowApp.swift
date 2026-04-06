import SwiftUI
import AppKit
import UserNotifications

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
        image.size = NSSize(width: 18, height: 18)
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
        DispatchQueue.main.async {
            guard let window = view.window else { return }
            window.identifier = NSUserInterfaceItemIdentifier("wordflow.quicksetup")
            window.title = ""
            window.titleVisibility = .hidden
            window.titlebarAppearsTransparent = true
            window.titlebarSeparatorStyle = .none
            window.isMovableByWindowBackground = true
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
        DispatchQueue.main.async {
            guard let window = nsView.window else { return }
            window.identifier = NSUserInterfaceItemIdentifier("wordflow.quicksetup")
            window.title = ""
            window.titleVisibility = .hidden
            window.titlebarAppearsTransparent = true
            window.titlebarSeparatorStyle = .none
            window.isMovableByWindowBackground = true
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
class AppDelegate: NSObject, NSApplicationDelegate {
    var hotkeyManager: HotkeyManager?
    var overlayWindowController: OverlayWindowController?
    private var didScheduleAutoSetupWindow = false
    private var periodicUpdateCheckTimer: Timer?

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
            "quickSetupResumeAfterRestart": false
        ])

        // First-launch onboarding trigger (sheet is shown in SettingsView).
        handleFirstLaunchWelcome()

        // Resume quick setup after restart if requested by the wizard.
        handleQuickSetupResumeIfNeeded()
        
        // Onboarding Check: Missing API Key?
        checkForSetup()

        // Silent update checks: once on launch and then every 7 days while app stays open.
        setupAutomaticUpdateChecks()

        // Supabase: Session + Trial + Update in einem Call
        Task {
            await SupabaseService.shared.checkSession()
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
        periodicUpdateCheckTimer?.invalidate()
        periodicUpdateCheckTimer = nil
    }

    private func handleQuickSetupResumeIfNeeded() {
        let defaults = UserDefaults.standard
        guard defaults.bool(forKey: "quickSetupResumeAfterRestart") else { return }

        defaults.set(true, forKey: "showWelcomeSheetOnSettingsOpen")
        openQuickSetupWindowOnLaunch(after: 0.8)
    }

    private func handleFirstLaunchWelcome() {
        let defaults = UserDefaults.standard
        let hasSeenWelcome = defaults.bool(forKey: "hasSeenWelcomeOnboarding")

        guard !hasSeenWelcome else { return }

        defaults.set(true, forKey: "hasSeenWelcomeOnboarding")
        defaults.set(true, forKey: "showWelcomeSheetOnSettingsOpen")

        // Open onboarding directly on first launch so user does not need a menu-bar click.
        openQuickSetupWindowOnLaunch(after: 0.8)

        LogManager.shared.log("👋 First launch detected: welcome onboarding scheduled")
    }

    private func openQuickSetupWindowOnLaunch(after delay: TimeInterval = 0.8) {
        guard !didScheduleAutoSetupWindow else { return }
        didScheduleAutoSetupWindow = true

        DispatchQueue.main.asyncAfter(deadline: .now() + delay) {
            NSApp.activate(ignoringOtherApps: true)
            WordflowApp.openQuickSetupWindow()
        }
    }
    
    private func checkForSetup() {
        let apiKey = UserDefaults.standard.string(forKey: "groqAPIKey") ?? ""
        let missingAPIKey = apiKey.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty
        guard missingAPIKey else { return }

        // Missing API key should always open the quick setup directly.
        openQuickSetupWindowOnLaunch(after: 1.0)
        LogManager.shared.log("🔧 Missing API key detected: opening quick setup automatically")
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
        hotkeyManager?.start()
    }
    
    private func startRecording() {
        guard let appState = AppState.shared else { 
            print("Fehler: AppState noch nicht initialisiert")
            return 
        }
        
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
    @ObservedObject var promptManager = PromptManager.shared
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
        ScrollView {
            VStack(alignment: .leading, spacing: 10) {

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
                                    .foregroundColor(.white.opacity(0.75))
                            }
                            Spacer()
                        }
                        .padding(.horizontal, 14)
                        .padding(.vertical, 10)
                        .background(WordflowTheme.primary)
                        .clipShape(RoundedRectangle(cornerRadius: 12, style: .continuous))
                    }
                    .buttonStyle(.plain)
                }

                HStack(spacing: 10) {
                    Image(systemName: "waveform.circle.fill")
                        .font(.system(size: 16, weight: .semibold))
                        .foregroundColor(WordflowTheme.primary)
                    Text("Wordflow")
                        .font(.system(size: 22, weight: .semibold, design: .serif))
                        .foregroundColor(WordflowTheme.onSurface)
                }
                .padding(.top, 12)

                menuBarSectionTitle(appLanguage == "EN" ? "HISTORY" : "VERLAUF")

                menuBarCard {
                    VStack(spacing: 0) {
                        if appState.clipboardManager.history.isEmpty {
                            HStack(spacing: 10) {
                                Image(systemName: "doc.text.magnifyingglass")
                                    .foregroundColor(WordflowTheme.onSurfaceVariant)
                                Text(appLanguage == "EN" ? "No recordings yet" : "Noch keine Aufnahmen")
                                    .font(.system(size: 13))
                                    .foregroundColor(WordflowTheme.onSurfaceVariant)
                                Spacer(minLength: 0)
                            }
                            .padding(.horizontal, 12)
                            .padding(.vertical, 14)
                        } else {
                            ForEach(Array(appState.clipboardManager.history.prefix(3).enumerated()), id: \.element.id) { index, entry in
                                MenuBarRowButton {
                                    appState.clipboardManager.copyToClipboard(text: entry.text)
                                } label: {
                                    HStack(alignment: .top, spacing: 10) {
                                        Image(systemName: "text.quote")
                                            .font(.system(size: 12, weight: .semibold))
                                            .foregroundColor(WordflowTheme.primary)
                                            .frame(width: 18)
                                        Text(entry.shortText)
                                            .lineLimit(1)
                                            .font(.system(size: 14, weight: .medium))
                                            .foregroundColor(WordflowTheme.onSurface)
                                        Spacer(minLength: 0)
                                    }
                                    .padding(.horizontal, 12)
                                    .padding(.vertical, 10)
                                }

                                if index < 2 {
                                    Divider()
                                        .padding(.leading, 40)
                                        .opacity(0.35)
                                }
                            }
                        }
                    }
                }

                menuBarCard {
                    MenuBarRowButton {
                        WordflowApp.openHistoryWindow()
                    } label: {
                        HStack(spacing: 8) {
                            Image(systemName: "note.text")
                            Text(appLanguage == "EN" ? "Show all notes" : "Alle Notizen anzeigen")
                                .font(.system(size: 14, weight: .semibold, design: .serif))
                            Spacer()
                            Image(systemName: "chevron.right")
                                .font(.system(size: 11, weight: .semibold))
                        }
                        .foregroundColor(WordflowTheme.onSurface)
                        .padding(.horizontal, 12)
                        .padding(.vertical, 10)
                    }
                }

                menuBarSectionTitle(appLanguage == "EN" ? "QUICK CONTROLS" : "SCHNELLAKTIONEN")
                menuBarCard {
                    VStack(spacing: 0) {
                        PillToggleRow(
                            title: appLanguage == "EN" ? "Auto Paste" : "Automatisch einfügen",
                            icon: "doc.on.clipboard",
                            isOn: $autoPasteEnabled
                        )

                        Divider()
                            .padding(.leading, 40)
                            .opacity(0.35)

                        PillToggleRow(
                            title: appLanguage == "EN" ? "Sound Feedback" : "Soundfeedback",
                            icon: "speaker.wave.2",
                            isOn: $soundsEnabled
                        )
                    }
                }

                menuBarSectionTitle(appLanguage == "EN" ? "PROMPT PROFILE" : "PROMPT PROFIL")
                menuBarCard {
                    ScrollView(.horizontal, showsIndicators: false) {
                        HStack(spacing: 10) {
                            ForEach(promptManager.profiles) { profile in
                                PromptButton(profile: profile)
                            }
                        }
                        .padding(.horizontal, 12)
                        .padding(.vertical, 10)
                    }
                }

                menuBarCard {
                    VStack(spacing: 0) {
                        if #available(macOS 14.0, *) {
                            SettingsLink {
                                settingsLinkLabel
                            }
                            .buttonStyle(.plain)
                        } else {
                            Button(action: {
                                NSApp.sendAction(Selector(("showPreferencesWindow:")), to: nil, from: nil)
                            }) {
                                settingsLinkLabel
                            }
                            .buttonStyle(.plain)
                        }

                        Divider()
                            .padding(.leading, 40)
                            .opacity(0.35)

                        MenuBarRowButton(action: {
                            NSApplication.shared.terminate(nil)
                        }) {
                            Label(appLanguage == "EN" ? "Quit" : "Beenden", systemImage: "power")
                                .foregroundColor(.red.opacity(0.85))
                                .frame(maxWidth: .infinity, alignment: .leading)
                                .padding(.horizontal, 12)
                                .padding(.vertical, 10)
                        }
                        .keyboardShortcut("q")
                    }
                }
            }
            .padding(.horizontal, 14)
            .padding(.bottom, 8)
        }
        .frame(width: 480, height: 580)
        .background(WordflowTheme.background)
    }

    private var settingsLinkLabel: some View {
        HStack(spacing: 8) {
            Label(appLanguage == "EN" ? "Settings..." : "Einstellungen...", systemImage: "gear")
                .font(.system(size: 14, weight: .semibold, design: .serif))
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
            .font(.system(size: 11, weight: .semibold, design: .serif))
            .foregroundColor(WordflowTheme.onSurfaceVariant.opacity(0.9))
            .textCase(.uppercase)
            .tracking(1.6)
            .padding(.leading, 4)
            .padding(.top, 2)
    }

    @ViewBuilder
    private func menuBarCard<Content: View>(@ViewBuilder content: () -> Content) -> some View {
        content()
            .background(WordflowTheme.surface)
            .clipShape(RoundedRectangle(cornerRadius: 14, style: .continuous))
            .overlay(
                RoundedRectangle(cornerRadius: 14, style: .continuous)
                    .stroke(WordflowTheme.outline.opacity(0.55), lineWidth: 1)
            )
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
                .background(isHovered ? WordflowTheme.onSurface.opacity(0.06) : Color.clear)
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
        HStack(spacing: 10) {
            Image(systemName: icon)
                .font(.system(size: 13, weight: .semibold))
                .foregroundColor(WordflowTheme.primary)
                .frame(width: 20)

            Text(title)
                .font(.system(size: 14, weight: .semibold, design: .serif))
                .foregroundColor(WordflowTheme.onSurface)

            Spacer(minLength: 0)

            Button {
                withAnimation(.spring(response: 0.22, dampingFraction: 0.85)) {
                    isOn.toggle()
                }
            } label: {
                ZStack(alignment: isOn ? .trailing : .leading) {
                    Capsule()
                        .fill(isOn ? WordflowTheme.primary : WordflowTheme.outline.opacity(0.8))
                        .frame(width: 44, height: 24)

                    Circle()
                        .fill(Color.white)
                        .frame(width: 18, height: 18)
                        .padding(.horizontal, 3)
                        .shadow(color: .black.opacity(0.18), radius: 1.5, x: 0, y: 1)
                }
            }
            .buttonStyle(.plain)
        }
        .padding(.horizontal, 12)
        .padding(.vertical, 10)
    }
}

// MARK: - Prompt Button Component
struct PromptButton: View {
    let profile: PromptProfile
    @ObservedObject var promptManager = PromptManager.shared
    
    var isSelected: Bool {
        promptManager.selectedProfileId == profile.id
    }
    
    var body: some View {
        Button {
            promptManager.selectProfile(id: profile.id)
        } label: {
            HStack(spacing: 6) {
                Text(profile.name)
                    .font(.system(size: 12, weight: isSelected ? .semibold : .medium, design: .serif))
                    .lineLimit(1)

                if isSelected {
                    Image(systemName: "checkmark")
                        .font(.system(size: 10, weight: .bold))
                }
            }
            .foregroundColor(isSelected ? .white : WordflowTheme.onSurface)
            .padding(.horizontal, 14)
            .padding(.vertical, 8)
            .frame(minWidth: 110)
            .background(isSelected ? WordflowTheme.primary : WordflowTheme.background)
            .clipShape(Capsule())
            .overlay(
                Capsule()
                    .stroke(isSelected ? Color.clear : WordflowTheme.outline.opacity(0.55), lineWidth: 1)
            )
        }
        .buttonStyle(.plain)
    }
}
