import SwiftUI
import Charts
import AVFoundation
import Darwin
import AppKit
import Carbon

struct SettingsWindowChromeConfigurator: NSViewRepresentable {
    func makeNSView(context: Context) -> NSView {
        let view = NSView()
        DispatchQueue.main.async {
            guard let window = view.window else { return }
            window.title = ""
            window.titleVisibility = .hidden
            window.titlebarAppearsTransparent = true
            window.titlebarSeparatorStyle = .none
            window.isMovableByWindowBackground = true
            window.toolbar = nil
            window.styleMask.insert(.fullSizeContentView)
        }
        return view
    }

    func updateNSView(_ nsView: NSView, context: Context) {
        DispatchQueue.main.async {
            guard let window = nsView.window else { return }
            window.title = ""
            window.titleVisibility = .hidden
            window.titlebarAppearsTransparent = true
            window.titlebarSeparatorStyle = .none
            window.isMovableByWindowBackground = true
            window.toolbar = nil
            window.styleMask.insert(.fullSizeContentView)
        }
    }
}

struct SettingsView: View {
    @AppStorage("appLanguage") private var appLanguage = "EN"
    @AppStorage("showWelcomeSheetOnSettingsOpen") private var showWelcomeSheetOnSettingsOpen = false
    @State private var selectedTab: SettingsTab = .general
    enum SettingsTab: String, Identifiable, Hashable {
        case general, profile, system, statistics, account
        var id: String { rawValue }
        func title(language: String) -> String {
            switch self {
            case .general: return language == "EN" ? "General" : "Allgemeines"
            case .profile: return language == "EN" ? "Prompts" : "Prompt-Profile"
            case .system: return "System"
            case .statistics: return language == "EN" ? "Statistics" : "Statistiken"
            case .account: return language == "EN" ? "Account" : "Account"
            }
        }
        var icon: String {
            switch self {
            case .general: return "gearshape"
            case .profile: return "text.badge.plus"
            case .system: return "cpu"
            case .statistics: return "chart.bar.xaxis"
            case .account: return "person.crop.circle"
            }
        }
    }
    var body: some View {
        TabView(selection: $selectedTab) { GeneralSettingsView(onOpenWelcome: { showWelcomeSheetOnSettingsOpen = true }) .tabItem { Label(SettingsTab.general.title(language: appLanguage), systemImage: SettingsTab.general.icon) } .tag(SettingsTab.general)
            ProfileSettingsView() .tabItem { Label(SettingsTab.profile.title(language: appLanguage), systemImage: SettingsTab.profile.icon) } .tag(SettingsTab.profile)
            SystemSettingsView() .tabItem { Label(SettingsTab.system.title(language: appLanguage), systemImage: SettingsTab.system.icon) } .tag(SettingsTab.system)
            StatisticsView() .tabItem { Label(SettingsTab.statistics.title(language: appLanguage), systemImage: SettingsTab.statistics.icon) } .tag(SettingsTab.statistics)
            AccountSettingsView() .tabItem { Label(SettingsTab.account.title(language: appLanguage), systemImage: SettingsTab.account.icon) } .tag(SettingsTab.account)
        }
        .frame(width: 700, height: 500)
        .fixedSize()
        .background(.ultraThinMaterial)
        .sheet(isPresented: $showWelcomeSheetOnSettingsOpen) { WelcomeOnboardingSheet() }
    }
}
// MARK: - Design System Components

struct SettingsCard<Content: View>: View {
    let title: String?
    @ViewBuilder let content: Content
    
    var body: some View {
        VStack(alignment: .leading, spacing: 12) {
            if let title {
                Text(title)
                    .font(.system(size: 11, weight: .semibold, design: .serif))
                    .foregroundColor(WordflowTheme.onSurfaceVariant.opacity(0.9))
                    .textCase(.uppercase)
                    .tracking(1.6)
                    .padding(.leading, 4)
            }
            
            VStack(spacing: 0) {
                content
            }
            .background(WordflowTheme.surface)
            .clipShape(RoundedRectangle(cornerRadius: 14))
            .overlay(
                RoundedRectangle(cornerRadius: 14)
                    .stroke(WordflowTheme.outline.opacity(0.65), lineWidth: 1)
            )
        }
    }
}

struct SettingsRow<Content: View>: View {
    let title: String
    let icon: String?
    let showDivider: Bool
    @ViewBuilder let rightContent: Content
    
    init(title: String, icon: String? = nil, showDivider: Bool = true, @ViewBuilder rightContent: () -> Content) {
        self.title = title
        self.icon = icon
        self.showDivider = showDivider
        self.rightContent = rightContent()
    }
    
    var body: some View {
        VStack(spacing: 0) {
            HStack(spacing: 12) {
                if let icon {
                    Image(systemName: icon)
                        .font(.system(size: 16))
                        .foregroundColor(WordflowTheme.onSurfaceVariant)
                        .frame(width: 20)
                }
                Text(title)
                    .font(.system(size: 14))
                    .foregroundColor(WordflowTheme.onSurface)
                Spacer()
                rightContent
            }
            .padding(.horizontal, 16)
            .padding(.vertical, 12)
            
            if showDivider {
                Divider()
                    .padding(.leading, icon == nil ? 16 : 48)
                    .opacity(0.35)
            }
        }
    }
}

// MARK: - 1. General Settings
struct GeneralSettingsView: View {
    @AppStorage("appLanguage") private var appLanguage = "EN"
    @AppStorage("autoPasteEnabled") private var autoPasteEnabled = true
    @AppStorage("soundsEnabled") private var soundsEnabled = true
    @State private var currentHotkey = HotkeyManager.loadConfig()
    @State private var isRecordingHotkey = false
    let onOpenWelcome: () -> Void
    var body: some View {
        Form {
            Section { Button { onOpenWelcome() } label: { HStack(spacing: 20) { Image(systemName: "play.circle.fill").font(.system(size: 40)).foregroundStyle(WordflowTheme.primary).shadow(color: WordflowTheme.primary.opacity(0.4), radius: 8, x: 0, y: 4)
                        VStack(alignment: .leading, spacing: 4) { Text(appLanguage == "EN" ? "New here? Start Setup Guide" : "Neu hier? Starte den Setup-Guide").font(.system(size: 18, weight: .bold)).foregroundStyle(WordflowTheme.onSurface); Text(appLanguage == "EN" ? "Step-by-step walkthrough of features" : "Schritt-für-Schritt Anleitung der Funktionen").font(.system(size: 13, weight: .medium)).foregroundStyle(WordflowTheme.onSurfaceVariant) }
                        Spacer()
                        Image(systemName: "chevron.right").font(.system(size: 14, weight: .semibold)).foregroundStyle(WordflowTheme.onSurfaceVariant.opacity(0.8))
                    }.padding(.vertical, 12).contentShape(Rectangle()) }.buttonStyle(.plain) }
            Section { HStack { Text(appLanguage == "EN" ? "Language:" : "Sprache:"); Spacer(); Picker("", selection: $appLanguage) { Text("Deutsch").tag("DE"); Text("English").tag("EN") }.labelsHidden().pickerStyle(.menu).frame(width: 160) } }
            Section(appLanguage == "EN" ? "Automation" : "Automatisierung") { Toggle(appLanguage == "EN" ? "Auto-Paste Text" : "Text automatisch einfügen", isOn: $autoPasteEnabled).toggleStyle(.switch); Toggle(appLanguage == "EN" ? "Play Sound Effects" : "Soundeffekte abspielen", isOn: $soundsEnabled).toggleStyle(.switch) }
            Section {
                VStack(alignment: .leading, spacing: 12) {
                    HStack(spacing: 12) {
                        Button { withAnimation(.spring(response: 0.3, dampingFraction: 0.8)) { isRecordingHotkey.toggle() } } label: {
                            HStack(spacing: 8) { Image(systemName: isRecordingHotkey ? "record.circle" : "keyboard").foregroundStyle(isRecordingHotkey ? .red : .primary).symbolEffect(.pulse, options: .repeating, isActive: isRecordingHotkey)
                                Text(isRecordingHotkey ? (appLanguage == "EN" ? "Press keys..." : "Taste drücken...") : currentHotkey.displayString).monospaced().font(.system(size: 13, weight: .semibold))
                            }.padding(.horizontal, 16).padding(.vertical, 8).background(isRecordingHotkey ? Color.red.opacity(0.1) : WordflowTheme.primary.opacity(0.1)).foregroundColor(isRecordingHotkey ? .red : WordflowTheme.primary).clipShape(.rect(cornerRadius: 8, style: .continuous))
                        }.buttonStyle(.plain)
                        HStack(spacing: 8) { Text(appLanguage == "EN" ? "or" : "oder").font(.system(size: 13)).foregroundStyle(.secondary).padding(.horizontal, 4)
                            presetButton(label: "Fn", modifiers: [], includeFn: true)
                            presetButton(label: "⇧ + ⌥", modifiers: [.shift, .option])
                            presetButton(label: "⇧ + ⌃", modifiers: [.shift, .control])
                            presetButton(label: "⇧ + ⌘", modifiers: [.shift, .command])
                        }
                        if isRecordingHotkey { HotkeyRecorderView(isRecording: $isRecordingHotkey, onHotkeyRecorded: { config in var adjustedConfig = config; adjustedConfig.useModifierOnly = false; currentHotkey = adjustedConfig; HotkeyManager.saveConfig(adjustedConfig); withAnimation(.spring(response: 0.3, dampingFraction: 0.8)) { isRecordingHotkey = false } }).frame(width: 0, height: 0) }
                    }
                }.padding(.vertical, 4)
            } header: { Text(appLanguage == "EN" ? "Activation Shortcut" : "Aktivierungs-Shortcut") } footer: { Text(appLanguage == "EN" ? "Use this shortcut to start recording." : "Nutze diesen Shortcut, um die Aufnahme zu starten.").font(.footnote).foregroundColor(.secondary) }
            Section(appLanguage == "EN" ? "History" : "Verlauf") {
                Button(appLanguage == "EN" ? "Open Transcription History" : "Transkriptions-Verlauf öffnen") {
                    WordflowApp.openHistoryWindow()
                }
            }
        }.formStyle(.grouped).padding(16)
    }
    @ViewBuilder private func presetButton(label: String, modifiers: NSEvent.ModifierFlags, includeFn: Bool = false) -> some View { let isMatch = currentHotkey.useModifierOnly && currentHotkey.useFnKey == includeFn && NSEvent.ModifierFlags(rawValue: currentHotkey.modifiers).contains(modifiers.intersection([.shift, .option, .control, .command])) && NSEvent.ModifierFlags(rawValue: currentHotkey.modifiers).isSubset(of: modifiers.union([.function])); Button { let config = HotkeyConfig(modifiers: modifiers.rawValue, keyCode: 0, useFnKey: includeFn, useModifierOnly: true); currentHotkey = config; HotkeyManager.saveConfig(config) } label: { Text(label).font(.system(size: 13, weight: isMatch ? .bold : .medium)).foregroundColor(isMatch ? WordflowTheme.primary : .secondary).padding(.horizontal, 10).padding(.vertical, 6).background(isMatch ? WordflowTheme.primary.opacity(0.15) : Color.primary.opacity(0.04)).clipShape(RoundedRectangle(cornerRadius: 6, style: .continuous)).overlay(RoundedRectangle(cornerRadius: 6, style: .continuous).stroke(isMatch ? WordflowTheme.primary.opacity(0.3) : Color.clear, lineWidth: 1)) }.buttonStyle(.plain) }
}
struct WelcomeOnboardingSheet: View {
    @Environment(\.dismiss) var dismiss
    @AppStorage("appLanguage") private var appLanguage = "EN"
    @AppStorage("groqAPIKey") private var apiKey = ""
    @AppStorage("hasCompletedInitialLanguageChoice") private var hasCompletedInitialLanguageChoice = false
    @AppStorage("showWelcomeSheetOnSettingsOpen") private var showWelcomeSheetOnSettingsOpen = false
    @AppStorage("quickSetupResumeAfterRestart") private var quickSetupResumeAfterRestart = false
    @AppStorage("quickSetupLaunchStep") private var quickSetupLaunchStep = ""

    @State private var currentStep: QuickSetupStep = .language
    @State private var apiValidationState: APIValidationState = .idle
    @State private var apiValidationMessage = ""
    @State private var hasAccessibilityPermission = AXIsProcessTrusted()
    @State private var microphoneStatus = AVCaptureDevice.authorizationStatus(for: .audio)
    @State private var isFinishingForManualRestart = false
    @State private var restartErrorMessage = ""
    @State private var currentHotkeyConfig = HotkeyManager.loadConfig()
    @State private var isRecordingNewHotkey = false
    @State private var testFieldText = ""
    @State private var hasTestedHotkey = false
    @State private var lockModeTestFieldText = ""
    @State private var hasTestedLockModeHotkey = false
    @State private var selectedLanguage = "EN"
    @State private var hasSelectedLanguageInStep = false
    @State private var hasInitializedStepFlow = false
    @State private var navigationDirection: NavigationDirection = .forward

    private enum NavigationDirection {
        case forward, backward
    }

    private enum WizardAccent {
        static let color = Color.orange
        static let softFill = Color.orange.opacity(0.08)
        static let border = Color.orange.opacity(0.28)
        static let arrowFill = Color.orange.opacity(0.15)
        static let strongBorder = Color.orange.opacity(0.45)
    }

    var body: some View {
        VStack(spacing: 0) {
            wizardHeader

            ScrollView {
                Group {
                    switch currentStep {
                    case .language:
                        languageStep
                    case .welcome:
                        welcomeStep
                    case .apiKey:
                        apiKeyStep
                    case .permissions:
                        permissionsStep
                    case .restart:
                        restartStep
                    case .hotkey:
                        hotkeyStep
                    case .lockMode:
                        lockModeStep
                    case .done:
                        doneStep
                    }
                }
                .frame(maxWidth: .infinity, alignment: .topLeading)
                .padding(24)
                .transition(.asymmetric(
                    insertion: .move(edge: navigationDirection == .forward ? .trailing : .leading).combined(with: .opacity),
                    removal: .move(edge: navigationDirection == .forward ? .leading : .trailing).combined(with: .opacity)
                ))
                .id(currentStep)
            }

            Divider()

            HStack {
                if currentStep == .language {
                    EmptyView()
                } else if currentStep == .welcome {
                    Button {
                        closeQuickSetupWindow()
                    } label: {
                        Text(appLanguage == "EN" ? "Skip" : "Ueberspringen")
                            .underline()
                    }
                    .buttonStyle(.plain)
                    .foregroundColor(.secondary)
                } else {
                    Button {
                        goBack()
                    } label: {
                        HStack(spacing: 4) {
                            Image(systemName: "chevron.left")
                                .font(.caption.weight(.semibold))
                            Text(appLanguage == "EN" ? "Back" : "Zurueck")
                        }
                    }
                    .buttonStyle(.plain)
                    .foregroundColor(.secondary)
                }

                Spacer()

                if currentStep == .hotkey && !hasTestedHotkey {
                    Text(appLanguage == "EN" ? "Test hotkey to continue" : "Teste Hotkey zum Fortfahren")
                        .font(.system(size: 13, weight: .semibold))
                        .foregroundColor(WizardAccent.color)
                        .padding(.horizontal, 14)
                        .padding(.vertical, 8)
                        .background(WizardAccent.softFill)
                        .clipShape(RoundedRectangle(cornerRadius: 9))
                        .overlay(
                            RoundedRectangle(cornerRadius: 9)
                                .stroke(WizardAccent.strongBorder, lineWidth: 1)
                        )
                } else {
                    Button(nextButtonTitle) {
                        goForward()
                    }
                    .buttonStyle(.borderedProminent)
                    .tint(WordflowTheme.primary)
                    .controlSize(.large)
                    .disabled(isNextDisabled)
                }
            }
            .padding(.horizontal, 24)
            .padding(.vertical, 14)
        }
        .background(WordflowTheme.background)
        .frame(minWidth: 720, minHeight: 760)
        .onAppear {
            guard !hasInitializedStepFlow else { return }
            hasInitializedStepFlow = true

            refreshPermissionState()
            selectedLanguage = (appLanguage == "DE" || appLanguage == "EN") ? appLanguage : "EN"

            LogManager.shared.log("🧭 Quick setup onAppear: launchStep='\(quickSetupLaunchStep)', resumeFlag=\(quickSetupResumeAfterRestart)")

            if quickSetupLaunchStep == "hotkey" {
                currentStep = .hotkey
                quickSetupLaunchStep = ""
                quickSetupResumeAfterRestart = false
                showWelcomeSheetOnSettingsOpen = false
                hasCompletedInitialLanguageChoice = true
                hasSelectedLanguageInStep = true
            } else if quickSetupResumeAfterRestart {
                // Backward-compatibility fallback for stale flags from older builds.
                currentStep = .hotkey
                quickSetupResumeAfterRestart = false
                showWelcomeSheetOnSettingsOpen = false
                hasCompletedInitialLanguageChoice = true
                hasSelectedLanguageInStep = true
            } else if hasCompletedInitialLanguageChoice {
                currentStep = .welcome
                hasSelectedLanguageInStep = true
            } else {
                currentStep = .language
            }
        }
    }

    // MARK: - Header

    private var wizardHeader: some View {
        VStack(alignment: .leading, spacing: 14) {
            HStack(alignment: .center) {
                Image(systemName: "waveform.circle")
                    .font(.system(size: 26))
                    .foregroundColor(.primary)
                Text("Wordflow")
                    .font(.system(size: 24, weight: .medium, design: .serif))
                    .foregroundColor(.primary)
                Spacer()
                Button {
                    closeQuickSetupWindow()
                } label: {
                    Image(systemName: "xmark.circle.fill")
                        .font(.title3)
                        .foregroundColor(.secondary.opacity(0.8))
                }
                .buttonStyle(.borderless)
            }

            Text(appLanguage == "EN" ? "Quick Setup" : "Quick Setup")
                .font(.system(size: 18, weight: .regular, design: .serif))
                .foregroundColor(.secondary)

            stepIndicator
        }
        .padding(.horizontal, 24)
        .padding(.top, 24)
        .padding(.bottom, 16)
        .background(WordflowTheme.background)
    }

    private var stepIndicator: some View {
        HStack(spacing: 0) {
            ForEach(QuickSetupStep.allCases, id: \.rawValue) { step in
                Circle()
                    .fill(stepDotColor(for: step))
                    .frame(width: 8, height: 8)
                    .overlay(
                        Circle()
                            .stroke(WordflowTheme.primary.opacity(0.15), lineWidth: step == currentStep ? 1 : 0)
                            .frame(width: 14, height: 14)
                    )

                if step.rawValue < QuickSetupStep.allCases.count - 1 {
                    Rectangle()
                        .fill(step.rawValue < currentStep.rawValue ? WordflowTheme.primary.opacity(0.6) : WordflowTheme.primary.opacity(0.1))
                        .frame(height: 1)
                        .frame(maxWidth: .infinity)
                }
            }
        }
        .padding(.top, 8)
    }

    private func stepDotColor(for step: QuickSetupStep) -> Color {
        if step.rawValue < currentStep.rawValue {
            return WordflowTheme.primary.opacity(0.8)
        } else if step == currentStep {
            return WordflowTheme.primary
        } else {
            return WordflowTheme.primary.opacity(0.2)
        }
    }

    // MARK: - Language Step

    private var languageStep: some View {
        VStack(alignment: .leading, spacing: 16) {
            Text("Choose your language")
                .font(.title2)
                .fontWeight(.bold)

            Text("Select your preferred language. The setup and app UI switch immediately.")
                .foregroundColor(.secondary)

            VStack(spacing: 12) {
                languageOptionCard(
                    code: "EN",
                    title: "English",
                    subtitle: "Use Wordflow in English"
                )

                languageOptionCard(
                    code: "DE",
                    title: "Deutsch",
                    subtitle: "Wordflow auf Deutsch nutzen"
                )
            }

            Text("You can change this later in Settings.")
                .font(.caption)
                .foregroundColor(.secondary)
        }
    }

    private func languageOptionCard(code: String, title: String, subtitle: String) -> some View {
        Button {
            selectedLanguage = code
            appLanguage = code
            hasSelectedLanguageInStep = true
        } label: {
            HStack(spacing: 14) {
                VStack(alignment: .leading, spacing: 2) {
                    Text(title)
                        .font(.system(size: 15, weight: .semibold, design: .serif))
                        .foregroundColor(.primary)
                    Text(subtitle)
                        .font(.system(size: 13))
                        .foregroundColor(.secondary)
                }

                Spacer()

                Image(systemName: selectedLanguage == code ? "checkmark.circle.fill" : "circle")
                    .font(.system(size: 18))
                    .foregroundColor(selectedLanguage == code ? .primary : .secondary.opacity(0.6))
            }
            .padding(16)
            .frame(maxWidth: .infinity, alignment: .leading)
            .background(WordflowTheme.surface)
            .clipShape(RoundedRectangle(cornerRadius: 12))
            .overlay(
                RoundedRectangle(cornerRadius: 12)
                    .stroke(selectedLanguage == code ? WordflowTheme.primary.opacity(0.4) : WordflowTheme.primary.opacity(0.12), lineWidth: 1)
            )
        }
        .buttonStyle(.plain)
    }

    // MARK: - Welcome Step

    private var welcomeStep: some View {
        VStack(alignment: .leading, spacing: 16) {
            Text(appLanguage == "EN" ? "Welcome, and thanks for buying the app." : "Willkommen und danke fuer deinen Kauf.")
                .font(.title2)
                .fontWeight(.bold)

            Text(appLanguage == "EN" ? "Ready in under 2 minutes." : "In unter 2 Minuten startklar.")
                .foregroundColor(.secondary)

            // Video Card
            Link(destination: URL(string: "https://word-flow.store/setup")!) {
                HStack(spacing: 16) {
                    Image(systemName: "play.rectangle")
                        .font(.system(size: 24))
                        .foregroundColor(WizardAccent.color)
                    
                    VStack(alignment: .leading, spacing: 2) {
                        Text(appLanguage == "EN" ? "Watch the Setup Video" : "Setup-Video ansehen")
                            .font(.system(size: 15, weight: .semibold, design: .serif))
                            .foregroundColor(.primary)
                        Text(appLanguage == "EN" ? "Full walkthrough in under 2 minutes" : "Die komplette Anleitung in unter 2 Minuten")
                            .font(.system(size: 13))
                            .foregroundColor(.secondary)
                    }
                    Spacer()
                    Image(systemName: "arrow.up.right")
                        .font(.system(size: 12, weight: .bold))
                        .foregroundColor(WizardAccent.color)
                        .frame(width: 24, height: 24)
                        .background(WizardAccent.arrowFill)
                        .clipShape(Circle())
                }
                .padding(16)
                .frame(maxWidth: .infinity, alignment: .leading)
                .background(WizardAccent.softFill)
                .clipShape(RoundedRectangle(cornerRadius: 12))
                .overlay(RoundedRectangle(cornerRadius: 12).stroke(WizardAccent.border, lineWidth: 1))
            }
            .buttonStyle(.plain)

            // Steps overview
            VStack(alignment: .leading, spacing: 10) {
                Text(appLanguage == "EN" ? "What you'll set up" : "Was du jetzt einrichtest")
                    .font(.headline)

                onboardingStep(number: "01", title: appLanguage == "EN" ? "Connect your API key" : "API Key verbinden", detail: appLanguage == "EN" ? "Paste your Groq API key and validate it instantly." : "Fuege deinen Groq API Key ein und pruefe ihn sofort.")
                onboardingStep(number: "02", title: appLanguage == "EN" ? "Grant permissions" : "Berechtigungen geben", detail: appLanguage == "EN" ? "Enable Accessibility and Microphone access." : "Aktiviere Bedienungshilfen und Mikrofonzugriff.")
                onboardingStep(number: "03", title: appLanguage == "EN" ? "Test your hotkey" : "Hotkey testen", detail: appLanguage == "EN" ? "Try recording and see your text appear live." : "Teste die Aufnahme und sieh deinen Text live erscheinen.")
            }
            .padding(16)
            .frame(maxWidth: .infinity, alignment: .leading)
            .background(WordflowTheme.surface)
            .clipShape(RoundedRectangle(cornerRadius: 12))
            .overlay(RoundedRectangle(cornerRadius: 12).stroke(WordflowTheme.primary.opacity(0.12), lineWidth: 1))

            Link(appLanguage == "EN" ? "Open Groq Console" : "Groq Console oeffnen", destination: URL(string: "https://console.groq.com")!)
                .font(.caption)
                .foregroundColor(.secondary)
        }
    }

    // MARK: - API Key Step

    private var apiKeyStep: some View {
        VStack(alignment: .leading, spacing: 16) {
            Text(appLanguage == "EN" ? "Step 2: API Key" : "Schritt 2: API Key")
                .font(.title2)
                .fontWeight(.bold)

            Text(appLanguage == "EN"
                 ? "Paste your Groq API key. We'll run a quick check before you continue."
                 : "Fuege deinen Groq API Key ein. Wir machen einen kurzen Check, bevor du weitergehst.")
                .foregroundColor(.secondary)

            VStack(spacing: 16) {
                // Info Card: Get an API Key
                Link(destination: URL(string: "https://console.groq.com/keys")!) {
                    HStack(spacing: 16) {
                        Image(systemName: "key.fill")
                            .font(.system(size: 20))
                            .foregroundColor(WizardAccent.color)
                        
                        VStack(alignment: .leading, spacing: 2) {
                            Text(appLanguage == "EN" ? "Get a free API Key" : "Kostenlosen API Key holen")
                                .font(.system(size: 15, weight: .semibold, design: .serif))
                                .foregroundColor(.primary)
                            Text(appLanguage == "EN" ? "Wordflow uses Groq's blazing fast inference." : "Wordflow nutzt die extrem schnelle Groq API.")
                                .font(.system(size: 13))
                                .foregroundColor(.secondary)
                        }
                        Spacer()
                        Image(systemName: "arrow.up.right")
                            .font(.system(size: 12, weight: .bold))
                            .foregroundColor(WizardAccent.color)
                            .frame(width: 24, height: 24)
                            .background(WizardAccent.arrowFill)
                            .clipShape(Circle())
                    }
                    .padding(16)
                    .frame(maxWidth: .infinity, alignment: .leading)
                    .background(WizardAccent.softFill)
                    .clipShape(RoundedRectangle(cornerRadius: 12))
                    .overlay(RoundedRectangle(cornerRadius: 12).stroke(WizardAccent.border, lineWidth: 1))
                }
                .buttonStyle(.plain)

                // Input Card
                VStack(alignment: .leading, spacing: 12) {
                    SecureField("Groq API Key", text: $apiKey)
                        .textFieldStyle(.roundedBorder)
                        .padding(6)
                        .background(WizardAccent.softFill)
                        .clipShape(RoundedRectangle(cornerRadius: 8))
                        .overlay(
                            RoundedRectangle(cornerRadius: 8)
                                .stroke(WizardAccent.border, lineWidth: 1)
                        )

                    HStack {
                        Link(appLanguage == "EN" ? "Setup guide & help" : "Hilfe bei der Einrichtung", destination: URL(string: "https://word-flow.store/setup")!)
                            .font(.caption)
                            .foregroundColor(.secondary)
                            
                        Spacer()
                        
                        Button(appLanguage == "EN" ? "Validate key" : "Key pruefen") {
                            Task {
                                await validateAPIKey()
                            }
                        }
                        .buttonStyle(.borderedProminent)
                        .tint(WordflowTheme.primary)
                        .controlSize(.small)
                        .disabled(apiKey.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty || apiValidationState == .checking)
                    }

                    validationStatusView
                }
                .padding(16)
                .frame(maxWidth: .infinity, alignment: .leading)
                .background(WordflowTheme.surface)
                .clipShape(RoundedRectangle(cornerRadius: 12))
                .overlay(RoundedRectangle(cornerRadius: 12).stroke(WordflowTheme.primary.opacity(0.12), lineWidth: 1))
            }
        }
    }

    @ViewBuilder
    private var validationStatusView: some View {
        switch apiValidationState {
        case .idle:
            Text(appLanguage == "EN" ? "Status: not checked yet" : "Status: noch nicht geprueft")
                .font(.caption)
                .foregroundColor(.secondary)
        case .checking:
            HStack(spacing: 8) {
                ProgressView()
                    .controlSize(.small)
                Text(appLanguage == "EN" ? "Checking key..." : "Pruefe Key...")
                    .font(.caption)
            }
        case .success:
            Label(appLanguage == "EN" ? "Key is valid" : "Key ist gueltig", systemImage: "checkmark.circle.fill")
                .font(.caption)
                .foregroundColor(WizardAccent.color)
        case .failure:
            VStack(alignment: .leading, spacing: 4) {
                Label(appLanguage == "EN" ? "Validation failed" : "Pruefung fehlgeschlagen", systemImage: "exclamationmark.triangle.fill")
                    .font(.caption)
                    .foregroundColor(.red)
                Text(apiValidationMessage)
                    .font(.caption)
                    .foregroundColor(.secondary)
            }
        }
    }

    // MARK: - Permissions Step

    private var permissionsStep: some View {
        VStack(alignment: .leading, spacing: 16) {
            Text(appLanguage == "EN" ? "Step 3: Permissions" : "Schritt 3: Berechtigungen")
                .font(.title2)
                .fontWeight(.bold)

            Text(appLanguage == "EN"
                 ? "Wordflow needs two permissions to work. Please grant them in order."
                 : "Wordflow benoetigt zwei Berechtigungen. Bitte erteile sie der Reihe nach.")
                .foregroundColor(.secondary)

            VStack(alignment: .leading, spacing: 12) {
                // 1. Accessibility
                HStack {
                    ZStack {
                        Circle()
                            .fill(hasAccessibilityPermission ? WordflowTheme.primary.opacity(0.18) : WordflowTheme.primary.opacity(0.06))
                            .frame(width: 32, height: 32)
                        Text("1")
                            .font(.system(size: 13, weight: .bold, design: .serif))
                            .foregroundColor(hasAccessibilityPermission ? WordflowTheme.primary : .primary)
                    }
                    VStack(alignment: .leading, spacing: 2) {
                        Text(appLanguage == "EN" ? "Accessibility" : "Bedienungshilfen")
                            .font(.system(size: 15, weight: .semibold, design: .serif))
                        Text(hasAccessibilityPermission
                             ? (appLanguage == "EN" ? "Granted" : "Erteilt")
                             : (appLanguage == "EN" ? "Required to paste text" : "Erforderlich zum Einfuegen von Text"))
                            .font(.caption)
                            .foregroundColor(hasAccessibilityPermission ? WordflowTheme.primary : .secondary)
                    }
                    Spacer()
                    if hasAccessibilityPermission {
                        Image(systemName: "checkmark.circle.fill")
                            .foregroundColor(WordflowTheme.primary)
                            .font(.title3)
                    } else {
                        Button(appLanguage == "EN" ? "Open Settings" : "Einstellungen oeffnen") {
                            openAccessibilitySettings()
                        }
                        .buttonStyle(.borderedProminent)
                        .tint(WordflowTheme.primary)
                        .controlSize(.small)
                    }
                }
                .padding(12)
                .background(WordflowTheme.surface)
                .clipShape(RoundedRectangle(cornerRadius: 10))
                .overlay(RoundedRectangle(cornerRadius: 10).stroke(WordflowTheme.primary.opacity(0.12), lineWidth: 1))

                // 2. Refresh status
                HStack {
                    ZStack {
                        Circle()
                            .fill(WordflowTheme.primary.opacity(0.06))
                            .frame(width: 32, height: 32)
                        Text("2")
                            .font(.system(size: 13, weight: .bold, design: .serif))
                            .foregroundColor(WordflowTheme.primary)
                    }

                    VStack(alignment: .leading, spacing: 2) {
                        Text(appLanguage == "EN" ? "Refresh status" : "Status aktualisieren")
                            .font(.system(size: 15, weight: .semibold, design: .serif))
                        Text(appLanguage == "EN"
                             ? "If permissions were granted but still unchecked, tap once to re-check."
                             : "Wenn Berechtigungen erteilt wurden, aber noch nicht abgehakt sind, bitte einmal neu prüfen.")
                            .font(.caption)
                            .foregroundColor(.secondary)
                    }

                    Spacer()

                    Button(appLanguage == "EN" ? "Refresh" : "Aktualisieren") {
                        refreshPermissionState()
                    }
                    .buttonStyle(.borderedProminent)
                    .tint(WordflowTheme.primary)
                    .controlSize(.small)
                }
                .padding(12)
                .background(WordflowTheme.surface)
                .clipShape(RoundedRectangle(cornerRadius: 10))
                .overlay(RoundedRectangle(cornerRadius: 10).stroke(WordflowTheme.primary.opacity(0.12), lineWidth: 1))

                // 3. Microphone
                VStack(alignment: .leading, spacing: 0) {
                    HStack {
                        ZStack {
                            Circle()
                                .fill(microphoneStatus == .authorized ? WordflowTheme.primary.opacity(0.18) : WordflowTheme.primary.opacity(0.06))
                                .frame(width: 32, height: 32)
                            Text("3")
                                .font(.system(size: 13, weight: .bold, design: .serif))
                                .foregroundColor(microphoneStatus == .authorized ? WordflowTheme.primary : .primary)
                        }
                        VStack(alignment: .leading, spacing: 2) {
                            Text(appLanguage == "EN" ? "Microphone" : "Mikrofon")
                                .font(.system(size: 15, weight: .semibold, design: .serif))
                            Text(microphoneStatus == .authorized
                                 ? (appLanguage == "EN" ? "Granted" : "Erteilt")
                                 : (appLanguage == "EN" ? "Required for voice recording" : "Erforderlich fuer Sprachaufnahme"))
                                .font(.caption)
                                .foregroundColor(microphoneStatus == .authorized ? WordflowTheme.primary : .secondary)
                        }
                        Spacer()
                        if microphoneStatus == .authorized {
                            Image(systemName: "checkmark.circle.fill")
                                .foregroundColor(WordflowTheme.primary)
                                .font(.title3)
                        } else {
                            Button(appLanguage == "EN" ? "Request Access" : "Zugriff anfragen") {
                                requestMicrophonePermission()
                            }
                            .buttonStyle(.borderedProminent)
                            .tint(WordflowTheme.primary)
                            .controlSize(.small)
                            .disabled(!hasAccessibilityPermission)
                        }
                    }
                    .padding(12)

                    if !hasAccessibilityPermission {
                        Text(appLanguage == "EN" ? "Grant Accessibility first" : "Bitte zuerst Bedienungshilfen erteilen")
                            .font(.caption2)
                            .foregroundColor(.orange)
                            .padding(.horizontal, 12)
                            .padding(.bottom, 8)
                    }
                }
                .background(WordflowTheme.surface)
                .clipShape(RoundedRectangle(cornerRadius: 10))
                .overlay(RoundedRectangle(cornerRadius: 10).stroke(WordflowTheme.primary.opacity(0.12), lineWidth: 1))
                .opacity(hasAccessibilityPermission ? 1.0 : 0.5)
                .animation(.easeInOut(duration: 0.3), value: hasAccessibilityPermission)
            }
        }
    }

    // MARK: - Restart Step

    private var restartStep: some View {
        VStack(alignment: .leading, spacing: 16) {
            Text(appLanguage == "EN" ? "Step 4: Restart" : "Schritt 4: Neustart")
                .font(.title2)
                .fontWeight(.bold)

            Text(appLanguage == "EN"
                 ? "Wordflow needs to restart once so permissions take full effect."
                 : "Wordflow muss einmal neu starten, damit die Berechtigungen vollstaendig greifen.")
                .foregroundColor(.secondary)

            infoCard(
                icon: "arrow.clockwise.circle.fill",
                title: appLanguage == "EN" ? "Automatic restart" : "Automatischer Neustart",
                body: appLanguage == "EN"
                    ? "Click Restart and Wordflow will close and reopen automatically. Setup continues at the hotkey test."
                    : "Klicke auf Neu starten und Wordflow schliesst sich und oeffnet sich automatisch wieder. Das Setup geht beim Hotkey-Test weiter."
            )

            if isFinishingForManualRestart {
                HStack(spacing: 8) {
                    ProgressView()
                        .controlSize(.small)
                    Text(appLanguage == "EN" ? "Restarting..." : "Neustart laeuft...")
                        .font(.caption)
                        .foregroundColor(.secondary)
                }
            }

            if !restartErrorMessage.isEmpty {
                Text(restartErrorMessage)
                    .font(.caption)
                    .foregroundColor(.red)
            }
        }
    }

    // MARK: - Hotkey Step

    private var hotkeyStep: some View {
        VStack(alignment: .leading, spacing: 16) {
            Text(appLanguage == "EN" ? "Step 5: Hotkey" : "Schritt 5: Hotkey")
                .font(.title2)
                .fontWeight(.bold)

            Text(appLanguage == "EN"
                 ? "Hold the hotkey to record, release to transcribe and paste."
                 : "Halte den Hotkey zum Aufnehmen, lass los zum Transkribieren und Einfuegen.")
                .foregroundColor(.secondary)

            // Hotkey Configuration Container
            VStack(spacing: 24) {
                if !isRecordingNewHotkey {
                    // Display Current Hotkey
                    VStack(spacing: 0) {
                        // Top part: Visual Display
                        VStack(spacing: 16) {
                            if currentHotkeyConfig.useFnKey {
                                fnKeyVisual
                            } else {
                                customKeyVisual
                            }
                        }
                        .padding(.vertical, 28)
                        .padding(.horizontal, 16)
                        .frame(maxWidth: .infinity)
                        .background(WordflowTheme.primary.opacity(0.02))
                        
                        Divider()
                        
                        // Bottom part: Action Block
                        HStack {
                            VStack(alignment: .leading, spacing: 4) {
                                Text(appLanguage == "EN" ? "Want a different shortcut?" : "Anderen Shortcut wählen?")
                                    .font(.system(size: 14, weight: .semibold, design: .serif))
                                Text(appLanguage == "EN" ? "Customize this to any key combination." : "Lege eine eigene Tastenkombination fest.")
                                    .font(.system(size: 12))
                                    .foregroundColor(.secondary)
                            }
                            Spacer()
                            
                            Button {
                                withAnimation(.spring(response: 0.3, dampingFraction: 0.8)) {
                                    isRecordingNewHotkey = true
                                }
                            } label: {
                                HStack(spacing: 6) {
                                    Image(systemName: "keyboard")
                                    Text(appLanguage == "EN" ? "Change Hotkey" : "Hotkey ändern")
                                        .fontWeight(.medium)
                                }
                                .padding(.horizontal, 8)
                                .padding(.vertical, 4)
                            }
                            .buttonStyle(.borderedProminent)
                            .tint(WordflowTheme.primary)
                        }
                        .padding(20)
                    }
                    .background(WordflowTheme.surface)
                    .clipShape(RoundedRectangle(cornerRadius: 16))
                    .overlay(RoundedRectangle(cornerRadius: 16).stroke(WordflowTheme.primary.opacity(0.12), lineWidth: 1))
                } else {
                    // Recording State with Presets
                    VStack(spacing: 18) {
                        Text(appLanguage == "EN" ? "Set a new shortcut" : "Neuen Shortcut festlegen")
                            .font(.system(size: 16, weight: .semibold, design: .serif))
                            .foregroundColor(WizardAccent.color)

                        Text(appLanguage == "EN"
                             ? "Pick a modifier combo below, or press a modifier (⌘, ⌃, ⌥, ⇧) + key to set a custom shortcut."
                             : "Wähle eine Modifier-Kombi unten, oder drücke Modifier (⌘, ⌃, ⌥, ⇧) + Taste für eigenen Shortcut.")
                            .font(.system(size: 12))
                            .foregroundColor(.secondary)
                            .multilineTextAlignment(.center)
                            .padding(.horizontal, 12)

                        // Listening indicator
                        HStack(spacing: 8) {
                            Circle()
                                .fill(WizardAccent.color)
                                .frame(width: 6, height: 6)
                            Text(appLanguage == "EN" ? "Listening for keys..." : "Warte auf Eingabe...")
                                .monospaced()
                                .font(.system(size: 12))
                                .foregroundColor(.secondary)
                        }
                        .padding(.vertical, 8)
                        .background(HotkeyRecorderView(isRecording: $isRecordingNewHotkey, onHotkeyRecorded: { config in
                            var adjustedConfig = config
                            adjustedConfig.useModifierOnly = false
                            currentHotkeyConfig = adjustedConfig
                            HotkeyManager.saveConfig(adjustedConfig)
                            withAnimation(.spring(response: 0.3, dampingFraction: 0.8)) {
                                isRecordingNewHotkey = false
                            }
                        }))

                        Divider()
                            .padding(.horizontal, 40)
                            .opacity(0.35)

                        // Quick-Select Presets
                        VStack(spacing: 10) {
                            Text(appLanguage == "EN" ? "Or pick a preset:" : "Oder wähle einen Vorschlag:")
                                .font(.system(size: 12, weight: .medium))
                                .foregroundColor(.secondary)

                            HStack(spacing: 10) {
                                wizardPresetButton(
                                    label: "🌐 Fn",
                                    isActive: currentHotkeyConfig.useFnKey
                                ) {
                                    applyWizardPreset(HotkeyConfig.defaultConfig)
                                }

                                wizardPresetButton(
                                    label: "⇧ ⌥",
                                    isActive: currentHotkeyConfig.useModifierOnly
                                        && NSEvent.ModifierFlags(rawValue: currentHotkeyConfig.modifiers).contains(.shift)
                                        && NSEvent.ModifierFlags(rawValue: currentHotkeyConfig.modifiers).contains(.option)
                                ) {
                                    applyWizardPreset(HotkeyConfig(
                                        modifiers: NSEvent.ModifierFlags([.shift, .option]).rawValue,
                                        keyCode: 0, useFnKey: false, useModifierOnly: true
                                    ))
                                }

                                wizardPresetButton(
                                    label: "⇧ ⌃",
                                    isActive: currentHotkeyConfig.useModifierOnly
                                        && NSEvent.ModifierFlags(rawValue: currentHotkeyConfig.modifiers).contains(.shift)
                                        && NSEvent.ModifierFlags(rawValue: currentHotkeyConfig.modifiers).contains(.control)
                                ) {
                                    applyWizardPreset(HotkeyConfig(
                                        modifiers: NSEvent.ModifierFlags([.shift, .control]).rawValue,
                                        keyCode: 0, useFnKey: false, useModifierOnly: true
                                    ))
                                }

                                wizardPresetButton(
                                    label: "⇧ ⌘",
                                    isActive: currentHotkeyConfig.useModifierOnly
                                        && NSEvent.ModifierFlags(rawValue: currentHotkeyConfig.modifiers).contains(.shift)
                                        && NSEvent.ModifierFlags(rawValue: currentHotkeyConfig.modifiers).contains(.command)
                                ) {
                                    applyWizardPreset(HotkeyConfig(
                                        modifiers: NSEvent.ModifierFlags([.shift, .command]).rawValue,
                                        keyCode: 0, useFnKey: false, useModifierOnly: true
                                    ))
                                }
                            }
                        }

                        Button(appLanguage == "EN" ? "Cancel" : "Abbrechen") {
                            withAnimation(.spring(response: 0.3, dampingFraction: 0.8)) {
                                isRecordingNewHotkey = false
                            }
                        }
                        .buttonStyle(.borderless)
                        .foregroundColor(.secondary)
                        .font(.caption)
                        .padding(.top, 2)
                    }
                    .padding(24)
                    .frame(maxWidth: .infinity)
                    .background(WordflowTheme.surface)
                    .clipShape(RoundedRectangle(cornerRadius: 16))
                    .overlay(RoundedRectangle(cornerRadius: 16).stroke(WizardAccent.border, lineWidth: 1))
                }
            }

            // Live test area
            hotkeyLiveTestCard(
                text: $testFieldText,
                hasTested: $hasTestedHotkey,
                title: appLanguage == "EN" ? "Test Your Hotkey" : "Teste deinen Hotkey",
                hint: appLanguage == "EN"
                    ? "Click the text field below, then hold your hotkey and speak. Release to see your text appear."
                    : "Klicke ins Textfeld, halte deinen Hotkey und sprich. Lass los, um deinen Text zu sehen.",
                successMessage: appLanguage == "EN" ? "Hotkey works! You can continue." : "Hotkey funktioniert! Du kannst weitermachen."
            )
        }
    }

    private var customKeyVisual: some View {
        VStack(spacing: 12) {
            if currentHotkeyConfig.useModifierOnly {
                Text(appLanguage == "EN" ? "Modifier Combo Active" : "Modifier-Kombi aktiv")
                    .font(.system(size: 14, weight: .semibold, design: .serif))
                    .foregroundColor(.secondary)

                HStack(spacing: 8) {
                    let flags = NSEvent.ModifierFlags(rawValue: currentHotkeyConfig.modifiers)
                    let modList = wizardModifierKeycaps(for: flags)
                    ForEach(Array(modList.enumerated()), id: \.offset) { index, item in
                        if index > 0 {
                            Text("+")
                                .font(.system(size: 16, weight: .medium))
                                .foregroundColor(.secondary)
                        }
                        keycap(symbol: item.0, label: item.1, highlighted: true, width: 72, height: 48)
                    }
                }
            } else {
                Text(appLanguage == "EN" ? "Custom Shortcut Active" : "Eigener Hotkey aktiv")
                    .font(.system(size: 14, weight: .semibold, design: .serif))
                    .foregroundColor(.secondary)

                keycap(symbol: nil, label: currentHotkeyConfig.displayString, highlighted: true, width: nil, height: 46)
            }
        }
    }

    private func wizardModifierKeycaps(for flags: NSEvent.ModifierFlags) -> [(String, String)] {
        var result: [(String, String)] = []
        if flags.contains(.shift) { result.append(("⇧", "shift")) }
        if flags.contains(.control) { result.append(("⌃", "control")) }
        if flags.contains(.option) { result.append(("⌥", "option")) }
        if flags.contains(.command) { result.append(("⌘", "command")) }
        return result
    }

    private func wizardPresetButton(label: String, isActive: Bool, action: @escaping () -> Void) -> some View {
        Button(action: action) {
            Text(label)
                .font(.system(size: 12, weight: .medium, design: .rounded))
                .padding(.horizontal, 14)
                .padding(.vertical, 8)
                .background(isActive ? WizardAccent.softFill : WordflowTheme.background)
                .foregroundColor(isActive ? WizardAccent.color : .primary)
                .clipShape(Capsule())
                .overlay(
                    Capsule()
                        .stroke(isActive ? WizardAccent.strongBorder : WordflowTheme.outline.opacity(0.3), lineWidth: 1)
                )
        }
        .buttonStyle(.plain)
    }

    private func applyWizardPreset(_ config: HotkeyConfig) {
        currentHotkeyConfig = config
        HotkeyManager.saveConfig(config)
        withAnimation(.spring(response: 0.3, dampingFraction: 0.8)) {
            isRecordingNewHotkey = false
        }
    }


    private var fnKeyVisual: some View {
        VStack(spacing: 16) {
            Text(appLanguage == "EN" ? "Default Hotkey Active (Globe/Fn)" : "Standard-Hotkey aktiv (Globe/Fn)")
                .font(.system(size: 14, weight: .semibold, design: .serif))
                .foregroundColor(.secondary)

            VStack(alignment: .leading, spacing: 6) {
                HStack(spacing: 6) {
                    keycap(symbol: "⇧", label: "shift", width: 96, height: 34)
                    keycap(symbol: "< >", label: "", width: 44, height: 34)
                }

                HStack(spacing: 6) {
                    keycap(symbol: nil, systemImage: "globe", label: "fn", highlighted: true, width: 52, height: 46)
                    keycap(symbol: "⌃", label: "control", width: 74, height: 46)
                    keycap(symbol: "⌥", label: "option", width: 78, height: 46)
                    keycap(symbol: "⌘", label: "command", width: 86, height: 46)
                }
            }
        }
    }

    private func keycap(symbol: String?, systemImage: String? = nil, label: String, highlighted: Bool = false, width: CGFloat? = 68, height: CGFloat = 42) -> some View {
        VStack(spacing: 2) {
            if let systemImage {
                Image(systemName: systemImage)
                    .font(.system(size: 14, weight: .medium))
            } else if let symbol, !symbol.isEmpty {
                Text(symbol)
                    .font(.system(size: 14, weight: .medium, design: .rounded))
            }
            if !label.isEmpty {
                Text(label)
                    .font(.system(size: 11, weight: .semibold, design: .rounded))
                    .lineLimit(1)
            }
        }
        .padding(.horizontal, width == nil ? 24 : 0)
        .frame(width: width)
        .frame(minWidth: width == nil ? 68 : nil)
        .frame(height: height)
        .background(highlighted ? WordflowTheme.primary : WordflowTheme.background)
        .foregroundColor(highlighted ? WordflowTheme.background : .primary)
        .cornerRadius(7)
        .overlay(
            RoundedRectangle(cornerRadius: 7)
                .stroke(highlighted ? Color.clear : WordflowTheme.primary.opacity(0.12), lineWidth: 1)
        )
    }

    // MARK: - Lock Mode Step

    private var lockModeStep: some View {
        VStack(alignment: .leading, spacing: 16) {
            Text(appLanguage == "EN" ? "Step 6: Lock Mode" : "Schritt 6: Lock-Modus")
                .font(.title2)
                .fontWeight(.bold)

            Text(appLanguage == "EN"
                 ? "You don't always have to hold the key. Lock recording to speak hands-free."
                 : "Du musst die Taste nicht immer halten. Sperre die Aufnahme, um freihaendig zu sprechen.")
                .foregroundColor(.secondary)

            // Key combo visual
            VStack(alignment: .leading, spacing: 14) {
                Text(appLanguage == "EN" ? "Hands-free Speech" : "Handfreie Eingabe")
                    .font(.system(size: 16, weight: .semibold, design: .serif))
                    .foregroundColor(.primary)
                
                Text(appLanguage == "EN" 
                     ? "Hold your hotkey and tap Space to lock the microphone. Speak freely without holding any keys. Tap your hotkey again to finish." 
                     : "Halte deinen Hotkey und tippe die Leertaste, um die Aufnahme zu sperren. Sprich vollkommen freihaendig. Druecke deinen Hotkey danach einfach nochmal, um zu beenden.")
                    .font(.system(size: 13))
                    .foregroundColor(.secondary)
                    .lineSpacing(4)
                
                HStack(spacing: 12) {
                    keycap(
                        symbol: nil,
                        systemImage: nil,
                        label: appLanguage == "EN" ? "Hotkey" : "Hotkey",
                        highlighted: true,
                        width: 96,
                        height: 44
                    )
                    
                    Text("+")
                        .font(.system(size: 18, weight: .medium))
                        .foregroundColor(.secondary)
                        
                    keycap(symbol: nil, label: "space", highlighted: true, width: 84, height: 44)
                    
                    Image(systemName: "arrow.right")
                        .font(.system(size: 14, weight: .semibold))
                        .foregroundColor(.secondary)
                        .padding(.horizontal, 4)
                        
                    Image(systemName: "lock.fill")
                        .font(.system(size: 18))
                        .foregroundColor(.primary)

                    Text(appLanguage == "EN" ? "press again" : "erneut drücken")
                        .font(.system(size: 11, weight: .semibold))
                        .foregroundColor(.secondary)
                        .padding(.horizontal, 8)
                        .padding(.vertical, 5)
                        .background(WordflowTheme.surface)
                        .clipShape(Capsule())

                    keycap(
                        symbol: nil,
                        systemImage: nil,
                        label: appLanguage == "EN" ? "Hotkey" : "Hotkey",
                        highlighted: true,
                        width: 96,
                        height: 44
                    )

                    Image(systemName: "arrow.right")
                        .font(.system(size: 14, weight: .semibold))
                        .foregroundColor(.secondary)

                    Text(appLanguage == "EN" ? "Transcribe & Insert" : "Transkribieren & Einfügen")
                        .font(.system(size: 12, weight: .semibold))
                        .foregroundColor(.secondary)
                        .padding(.horizontal, 10)
                        .padding(.vertical, 7)
                        .background(WordflowTheme.surface)
                        .clipShape(Capsule())
                        .overlay(
                            Capsule()
                                .stroke(WordflowTheme.primary.opacity(0.18), lineWidth: 1)
                        )
                }
                .padding(.top, 8)
            }
            .padding(20)
            .frame(maxWidth: .infinity, alignment: .leading)
            .background(WordflowTheme.primary.opacity(0.02))
            .clipShape(RoundedRectangle(cornerRadius: 16))
            .overlay(RoundedRectangle(cornerRadius: 16).stroke(WordflowTheme.primary.opacity(0.12), lineWidth: 1))

            hotkeyLiveTestCard(
                text: $lockModeTestFieldText,
                hasTested: $hasTestedLockModeHotkey,
                title: appLanguage == "EN" ? "Test Lock Mode" : "Teste den Lock-Modus",
                hint: appLanguage == "EN"
                    ? "Try the same flow again: hold your hotkey, tap Space to lock, and dictate into this field."
                    : "Teste den Ablauf direkt hier: Halte deinen Hotkey, tippe Leertaste zum Sperren und diktiere in dieses Feld.",
                successMessage: appLanguage == "EN" ? "Lock mode input received." : "Lock-Modus Eingabe erkannt."
            )
        }
    }

    private func hotkeyLiveTestCard(
        text: Binding<String>,
        hasTested: Binding<Bool>,
        title: String,
        hint: String,
        successMessage: String
    ) -> some View {
        VStack(alignment: .leading, spacing: 10) {
            HStack(spacing: 12) {
                Image(systemName: "mic.fill")
                    .font(.system(size: 16))
                    .foregroundColor(.primary)
                Text(title)
                    .font(.system(size: 15, weight: .semibold, design: .serif))
            }

            Text(hint)
                .font(.caption)
                .foregroundColor(.secondary)

            ZStack(alignment: .topLeading) {
                if text.wrappedValue.isEmpty {
                    Text(appLanguage == "EN" ? "Your dictated text will appear here..." : "Dein diktierter Text erscheint hier...")
                        .font(.body)
                        .foregroundColor(.secondary.opacity(0.5))
                        .padding(.horizontal, 4)
                        .padding(.vertical, 0)
                        .allowsHitTesting(false)
                }

                TextEditor(text: text)
                    .font(.body)
                    .foregroundColor(text.wrappedValue.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty ? .primary : WizardAccent.color)
                    .scrollContentBackground(.hidden)
                    .background(Color.clear)
                    .padding(0)
                    .frame(minHeight: 80, maxHeight: 120)
            }
            .padding(8)
            .background(Color(NSColor.textBackgroundColor))
            .clipShape(RoundedRectangle(cornerRadius: 8))
            .overlay(RoundedRectangle(cornerRadius: 8).stroke(Color.secondary.opacity(0.2), lineWidth: 1))
            .onChange(of: text.wrappedValue) { newValue in
                if !newValue.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty {
                    hasTested.wrappedValue = true
                }
            }

            if hasTested.wrappedValue {
                Label(successMessage, systemImage: "checkmark.circle.fill")
                    .font(.caption)
                    .foregroundColor(WizardAccent.color)
            }
        }
        .padding(14)
        .frame(maxWidth: .infinity, alignment: .leading)
        .background(WordflowTheme.surface)
        .clipShape(RoundedRectangle(cornerRadius: 12))
        .overlay(RoundedRectangle(cornerRadius: 12).stroke(WordflowTheme.primary.opacity(0.12), lineWidth: 1))
    }

    private func lockModeInstruction(number: String, text: String) -> some View {
        EmptyView() // unused
    }

    // MARK: - Done Step

    private var doneStep: some View {
        VStack(alignment: .leading, spacing: 16) {
            Text(appLanguage == "EN" ? "You're all set!" : "Alles ist eingerichtet!")
                .font(.title2)
                .fontWeight(.bold)

            infoCard(
                icon: "checkmark.seal.fill",
                title: appLanguage == "EN" ? "Quick Setup complete" : "Quick Setup abgeschlossen",
                body: appLanguage == "EN"
                    ? "API key configured, permissions granted, hotkey tested, and lock mode explained. You're ready to dictate in any app!"
                    : "API Key eingerichtet, Berechtigungen erteilt, Hotkey getestet und Lock-Modus erklaert. Du kannst jetzt in jeder App diktieren!"
            )

            VStack(alignment: .leading, spacing: 10) {
                Text(appLanguage == "EN" ? "Available Profiles" : "Verfuegbare Profile")
                    .font(.system(size: 13, weight: .semibold, design: .serif))
                    .foregroundColor(.secondary)

                VStack(spacing: 0) {
                    doneProfileRow(
                        title: "Smart Casual",
                        body: appLanguage == "EN"
                            ? "Keeps your natural vibe, removes stutters, and fixes punctuation. Great for everyday messages."
                            : "Behaelt deinen natuerlichen Vibe bei, entfernt Stotterer und korrigiert die Zeichensetzung. Perfekt fuer alltaegliche Nachrichten.",
                        showDivider: true
                    )

                    doneProfileRow(
                        title: "Smart Business",
                        body: appLanguage == "EN"
                            ? "Turns spoken thoughts into clear, logical text suitable for business contexts."
                            : "Verwandelt gesprochene Gedanken in klaren, logischen Text. Ideal fuer das Business-Umfeld.",
                        showDivider: true
                    )

                    doneProfileRow(
                        title: "Professional",
                        body: appLanguage == "EN"
                            ? "Produces highly polished, formal text perfect for professional emails and documents."
                            : "Erzeugt sehr formellen, feingeschliffenen Text. Perfekt fuer professionelle E-Mails und Dokumente.",
                        showDivider: false
                    )
                }
                .background(WordflowTheme.surface)
                .clipShape(RoundedRectangle(cornerRadius: 12))
                .overlay(RoundedRectangle(cornerRadius: 12).stroke(WordflowTheme.primary.opacity(0.12), lineWidth: 1))
            }

            VStack(alignment: .leading, spacing: 10) {
                Text(appLanguage == "EN" ? "Use Cases To Start With" : "Direkt starten mit")
                    .font(.system(size: 13, weight: .semibold, design: .serif))
                    .foregroundColor(.secondary)

                VStack(spacing: 0) {
                    doneUseCaseRow(
                        icon: "note.text",
                        title: appLanguage == "EN" ? "Notes" : "Notizen",
                        body: appLanguage == "EN"
                            ? "Capture thoughts as clean bullet points or quick summaries while you speak."
                            : "Sprich frei und erhalte sofort saubere Stichpunkte oder kurze Zusammenfassungen.",
                        showDivider: true
                    )

                    doneUseCaseRow(
                        icon: "envelope",
                        title: appLanguage == "EN" ? "Emails" : "E-Mails",
                        body: appLanguage == "EN"
                            ? "Dictate rough ideas and let Wordflow turn them into clear, professional messages."
                            : "Diktiere grobe Gedanken und lass Wordflow daraus klare, professionelle Nachrichten machen.",
                        showDivider: true
                    )

                    doneUseCaseRow(
                        icon: "sparkles",
                        title: appLanguage == "EN" ? "Prompting" : "Prompting",
                        body: appLanguage == "EN"
                            ? "Speak prompts naturally and use your profile style to produce ready-to-use AI instructions."
                            : "Sprich Prompts natürlich ein und erhalte mit deinem Profil direkt nutzbare KI-Anweisungen.",
                        showDivider: false
                    )
                }
                .background(WordflowTheme.surface)
                .clipShape(RoundedRectangle(cornerRadius: 12))
                .overlay(RoundedRectangle(cornerRadius: 12).stroke(WordflowTheme.primary.opacity(0.12), lineWidth: 1))
            }

            Text(appLanguage == "EN"
                 ? "You can reopen this wizard anytime in Settings > General."
                 : "Du kannst diesen Wizard jederzeit unter Einstellungen > Allgemein erneut oeffnen.")
                .font(.caption)
                .foregroundColor(.secondary)
        }
    }

    // MARK: - Shared Components

    private func infoCard(icon: String, title: String, body: String) -> some View {
        VStack(alignment: .leading, spacing: 8) {
            HStack(spacing: 12) {
                Image(systemName: icon)
                    .font(.system(size: 16))
                    .foregroundColor(.primary.opacity(0.8))
                
                Text(title)
                    .font(.system(size: 14, weight: .semibold, design: .serif))
                    .foregroundColor(.primary)
            }
            Text(body)
                .font(.subheadline)
                .foregroundColor(.secondary)
        }
        .padding(16)
        .frame(maxWidth: .infinity, alignment: .leading)
        .background(WordflowTheme.surface)
        .clipShape(RoundedRectangle(cornerRadius: 12))
        .overlay(RoundedRectangle(cornerRadius: 12).stroke(WordflowTheme.primary.opacity(0.12), lineWidth: 1))
    }

    private func doneUseCaseRow(icon: String, title: String, body: String, showDivider: Bool) -> some View {
        VStack(spacing: 0) {
            HStack(alignment: .top, spacing: 12) {
                Image(systemName: icon)
                    .font(.system(size: 15, weight: .semibold))
                    .foregroundColor(WordflowTheme.primary)
                    .frame(width: 24)
                    .padding(.top, 1)

                VStack(alignment: .leading, spacing: 3) {
                    Text(title)
                        .font(.system(size: 14, weight: .semibold, design: .serif))
                        .foregroundColor(.primary)
                    Text(body)
                        .font(.system(size: 13))
                        .foregroundColor(.secondary)
                        .lineLimit(2)
                        .fixedSize(horizontal: false, vertical: true)
                }

                Spacer(minLength: 0)
            }
            .padding(.horizontal, 14)
            .padding(.vertical, 12)
            .frame(maxWidth: .infinity, alignment: .leading)
            .frame(minHeight: 74)

            if showDivider {
                Divider()
                    .padding(.leading, 50)
                    .opacity(0.45)
            }
        }
    }

    private func doneProfileRow(title: String, body: String, showDivider: Bool) -> some View {
        VStack(spacing: 0) {
            HStack(alignment: .top, spacing: 12) {
                Text(title)
                    .font(.system(size: 12, weight: .semibold, design: .serif))
                    .foregroundColor(WordflowTheme.primary)
                    .padding(.horizontal, 10)
                    .padding(.vertical, 6)
                    .background(WordflowTheme.primary.opacity(0.08))
                    .clipShape(Capsule())

                Text(body)
                    .font(.system(size: 13))
                    .foregroundColor(.secondary)
                    .lineLimit(3)
                    .fixedSize(horizontal: false, vertical: true)

                Spacer(minLength: 0)
            }
            .padding(.horizontal, 14)
            .padding(.vertical, 12)
            .frame(maxWidth: .infinity, alignment: .leading)

            if showDivider {
                Divider()
                    .padding(.leading, 14)
                    .opacity(0.45)
            }
        }
    }

    private func onboardingStep(number: String, title: String, detail: String) -> some View {
        HStack(alignment: .top, spacing: 12) {
            Text(number)
                .font(.system(size: 11, weight: .semibold, design: .serif).monospacedDigit())
                .foregroundColor(.primary)
                .padding(.horizontal, 8)
                .padding(.vertical, 4)
                .background(WordflowTheme.primary.opacity(0.06))
                .clipShape(RoundedRectangle(cornerRadius: 6))

            VStack(alignment: .leading, spacing: 2) {
                Text(title)
                    .font(.system(size: 13, weight: .semibold, design: .serif))
                    .foregroundColor(.primary)
                Text(detail)
                    .font(.system(size: 12))
                    .foregroundColor(.secondary)
                    .lineSpacing(2)
            }
        }
    }

    private func labelPill(text: String) -> some View {
        Text(text)
            .font(.caption.weight(.semibold))
            .padding(.horizontal, 10)
            .padding(.vertical, 5)
            .background(Color.white.opacity(0.2))
            .clipShape(Capsule())
    }

    private var stepLabel: String {
        appLanguage == "EN" ? "Step \(currentStep.index + 1)/\(QuickSetupStep.allCases.count)" : "Schritt \(currentStep.index + 1)/\(QuickSetupStep.allCases.count)"
    }

    // MARK: - Navigation

    private var nextButtonTitle: String {
        switch currentStep {
        case .language:
            return appLanguage == "EN" ? "Continue" : "Weiter"
        case .restart:
            return appLanguage == "EN" ? "Restart" : "Neu starten"
        case .done:
            return appLanguage == "EN" ? "Finish" : "Fertig"
        default:
            return appLanguage == "EN" ? "Next" : "Weiter"
        }
    }

    private var isNextDisabled: Bool {
        switch currentStep {
        case .language:
            return !hasCompletedInitialLanguageChoice && !hasSelectedLanguageInStep
        case .apiKey:
            return apiValidationState != .success
        case .permissions:
            return !hasAccessibilityPermission
        case .restart:
            return isFinishingForManualRestart
        case .hotkey:
            return !hasTestedHotkey
        default:
            return false
        }
    }

    private func goForward() {
        if currentStep == .language {
            hasCompletedInitialLanguageChoice = true
        }

        if currentStep == .restart {
            restartErrorMessage = ""
            autoRestartApp()
            return
        }

        if currentStep == .done {
            closeQuickSetupWindow()
            return
        }

        if let next = QuickSetupStep(rawValue: currentStep.rawValue + 1) {
            if next == .lockMode {
                lockModeTestFieldText = ""
                hasTestedLockModeHotkey = false
            }
            navigationDirection = .forward
            withAnimation(.easeInOut(duration: 0.25)) {
                currentStep = next
            }
        }
    }

    private func goBack() {
        if let previous = QuickSetupStep(rawValue: currentStep.rawValue - 1) {
            navigationDirection = .backward
            withAnimation(.easeInOut(duration: 0.25)) {
                currentStep = previous
            }
        }
    }

    private func closeQuickSetupWindow() {
        // Always clear reopen flags first so closing the wizard cannot re-trigger itself.
        showWelcomeSheetOnSettingsOpen = false
        quickSetupResumeAfterRestart = false
        quickSetupLaunchStep = ""

        if let window = NSApp.windows.first(where: { $0.identifier?.rawValue == "wordflow.quicksetup" }) {
            window.close()
            return
        }

        if let keyWindow = NSApp.keyWindow {
            keyWindow.close()
            return
        }

        dismiss()
    }

    // MARK: - API Validation

    private func validateAPIKey() async {
        let trimmed = apiKey.trimmingCharacters(in: .whitespacesAndNewlines)
        guard !trimmed.isEmpty else {
            apiValidationState = .failure
            apiValidationMessage = appLanguage == "EN" ? "Please enter an API key first." : "Bitte zuerst einen API Key eingeben."
            return
        }

        apiValidationState = .checking
        apiValidationMessage = ""

        guard let url = URL(string: "https://api.groq.com/openai/v1/models") else {
            apiValidationState = .failure
            apiValidationMessage = appLanguage == "EN" ? "Invalid validation URL." : "Ungueltige Validierungs-URL."
            return
        }

        var request = URLRequest(url: url)
        request.httpMethod = "GET"
        request.timeoutInterval = 15
        request.setValue("Bearer \(trimmed)", forHTTPHeaderField: "Authorization")

        do {
            let (_, response) = try await URLSession.shared.data(for: request)
            let statusCode = (response as? HTTPURLResponse)?.statusCode ?? 0

            if statusCode == 200 {
                apiValidationState = .success
            } else {
                apiValidationState = .failure
                switch statusCode {
                case 401, 403:
                    apiValidationMessage = appLanguage == "EN" ? "Invalid key or missing permissions." : "Ungueltiger Key oder fehlende Rechte."
                case 429:
                    apiValidationMessage = appLanguage == "EN" ? "Rate limit reached. Try again shortly." : "Rate-Limit erreicht. Bitte kurz warten."
                default:
                    apiValidationMessage = appLanguage == "EN" ? "Validation failed (HTTP \(statusCode))." : "Pruefung fehlgeschlagen (HTTP \(statusCode))."
                }
            }
        } catch {
            apiValidationState = .failure
            apiValidationMessage = appLanguage == "EN" ? "Network error while validating key." : "Netzwerkfehler bei der Key-Pruefung."
        }
    }

    // MARK: - Enums

    private enum QuickSetupStep: Int, CaseIterable {
        case language = 0
        case welcome = 1
        case apiKey = 2
        case permissions = 3
        case restart = 4
        case hotkey = 5
        case lockMode = 6
        case done = 7

        var index: Int { rawValue }
    }

    private enum APIValidationState {
        case idle
        case checking
        case success
        case failure
    }

    // MARK: - Permissions

    private func refreshPermissionState() {
        hasAccessibilityPermission = AXIsProcessTrusted()
        microphoneStatus = AVCaptureDevice.authorizationStatus(for: .audio)
    }

    private func openAccessibilitySettings() {
        if let url = URL(string: "x-apple.systempreferences:com.apple.preference.security?Privacy_Accessibility") {
            NSWorkspace.shared.open(url)
        }
        Task { @MainActor in
            try? await Task.sleep(for: .seconds(0.8))
            refreshPermissionState()
        }
    }

    private func requestMicrophonePermission() {
        AVCaptureDevice.requestAccess(for: .audio) { _ in
            Task { @MainActor in
                refreshPermissionState()
            }
        }
    }

    // MARK: - Auto Restart

    private func autoRestartApp() {
        guard !isFinishingForManualRestart else { return }
        isFinishingForManualRestart = true

        quickSetupLaunchStep = "hotkey"
        quickSetupResumeAfterRestart = true
        showWelcomeSheetOnSettingsOpen = false
        UserDefaults.standard.synchronize()
        (NSApp.delegate as? AppDelegate)?.requestQuickSetupRelaunchOnTerminate()

        LogManager.shared.log("Auto-restart: flags persisted, requesting app termination")

        Task { @MainActor in
            try? await Task.sleep(for: .seconds(0.35))
            NSApplication.shared.terminate(nil)
        }

        // If normal terminate gets stuck, enforce restart without leaving spinner forever.
        Task {
            try? await Task.sleep(for: .seconds(1.8))

            if !NSRunningApplication.current.isTerminated {
                LogManager.shared.log("Auto-restart timeout: escalating to forced exit")
                (NSApp.delegate as? AppDelegate)?.requestReplacementRelaunchIfNeeded()
                exit(0)
            }
        }
    }
}


struct APIGuideSheet: View {
    @Environment(\.dismiss) var dismiss
    let appLanguage: String
    
    var body: some View {
        VStack(spacing: 0) {
            // Header
            VStack(alignment: .leading, spacing: 14) {
                HStack(alignment: .center) {
                    Image(systemName: "key.fill")
                        .font(.system(size: 20))
                        .foregroundColor(.primary)
                    Text(appLanguage == "EN" ? "Setup Guide" : "Einrichtung")
                        .font(.system(size: 20, weight: .medium, design: .serif))
                        .foregroundColor(.primary)
                    Spacer()
                    Button(action: { dismiss() }) {
                        Image(systemName: "xmark.circle.fill")
                            .font(.title2)
                            .foregroundColor(.secondary.opacity(0.8))
                    }
                    .buttonStyle(.borderless)
                }
            }
            .padding(.horizontal, 24)
            .padding(.top, 24)
            .padding(.bottom, 16)
            .background(WordflowTheme.background)
            
            Divider()
            
            ScrollView {
                VStack(alignment: .leading, spacing: 24) {
                    // Intro
                    Text(appLanguage == "EN" ? "How to get your free API Key:" : "So erhältst du deinen kostenlosen API Key:")
                        .font(.system(size: 15, weight: .semibold, design: .serif))
                        .padding(.top, 8)
                    
                    // Steps
                    VStack(alignment: .leading, spacing: 16) {
                        GuideStepView(number: 1, title: "Account erstellen", description: "Gehe auf console.groq.com und melde dich an (z.B. mit Google).")
                        GuideStepView(number: 2, title: "API Key generieren", description: "Klicke im Menü auf 'API Keys' und dann auf 'Create API Key'. Gib dem Key einen Namen (z.B. 'Wordflow').")
                        GuideStepView(number: 3, title: "Key kopieren", description: "Der Key wird dir nur ein einziges Mal angezeigt. Kopiere ihn und füge ihn hier in Wordflow ein.")
                    }
                    
                    Divider()
                        .padding(.vertical, 8)
                    
                    // Video Placeholder
                    Link(destination: URL(string: "https://word-flow.store/setup")!) {
                        HStack(spacing: 16) {
                            Image(systemName: "play.rectangle")
                                .font(.system(size: 24))
                                .foregroundColor(.primary)
                            
                            VStack(alignment: .leading, spacing: 2) {
                                Text(appLanguage == "EN" ? "Video for setup" : "Video zum Setup")
                                    .font(.system(size: 15, weight: .semibold, design: .serif))
                                    .foregroundColor(.primary)
                                Text(appLanguage == "EN" ? "Open setup video" : "Setup-Video öffnen")
                                    .font(.system(size: 13))
                                    .foregroundColor(.secondary)
                            }
                            Spacer()
                            Image(systemName: "arrow.up.right")
                                .font(.caption)
                                .foregroundColor(.secondary)
                        }
                        .padding(16)
                        .frame(maxWidth: .infinity, alignment: .leading)
                        .background(WordflowTheme.surface)
                        .clipShape(RoundedRectangle(cornerRadius: 12))
                        .overlay(RoundedRectangle(cornerRadius: 12).stroke(WordflowTheme.primary.opacity(0.12), lineWidth: 1))
                    }
                    .buttonStyle(.plain)
                    
                }
                .padding(24)
            }
            .background(WordflowTheme.background)
            
            Divider()
            
            // Footer
            HStack {
                Spacer()
                Button("Verstanden") {
                    dismiss()
                }
                .buttonStyle(.borderedProminent)
                .tint(WordflowTheme.primary)
                .controlSize(.large)
            }
            .padding(16)
            .background(WordflowTheme.background)
        }
        .frame(width: 500, height: 600)
    }
}

struct GuideStepView: View {
    let number: Int
    let title: String
    let description: String
    
    var body: some View {
        HStack(alignment: .top, spacing: 16) {
            Text("\(number)")
                .font(.system(size: 12, weight: .semibold, design: .serif).monospacedDigit())
                .foregroundColor(.primary)
                .padding(.horizontal, 9)
                .padding(.vertical, 5)
                .background(WordflowTheme.primary.opacity(0.06))
                .clipShape(RoundedRectangle(cornerRadius: 6))
            
            VStack(alignment: .leading, spacing: 2) {
                Text(title)
                    .font(.system(size: 14, weight: .semibold, design: .serif))
                Text(description)
                    .font(.system(size: 13))
                    .foregroundColor(.secondary)
                    .fixedSize(horizontal: false, vertical: true)
                    .lineSpacing(2)
            }
        }
    }
}

// MARK: - 3. Profile Settings
struct ProfileSettingsView: View {
    @AppStorage("appLanguage") private var appLanguage = "EN"
    @ObservedObject var promptManager = PromptManager.shared
    var body: some View {
        Form {
            Section { VStack(spacing: 12) { ForEach(promptManager.profiles) { profile in ProfileCardView(profile: profile, isSelected: promptManager.selectedProfileId == profile.id, appLanguage: appLanguage) { withAnimation(.spring(response: 0.3, dampingFraction: 0.8)) { promptManager.selectProfile(id: profile.id) } } } }.padding(.vertical, 4) } header: { Text(appLanguage == "EN" ? "AI Text Refinement" : "KI-Textveredelung") } footer: { Text(appLanguage == "EN" ? "Choose how the AI should rewrite your dictations. More profiles coming soon." : "Wähle, wie die KI deine Diktate umschreiben soll. Weitere Profile folgen in Kürze.").font(.footnote).foregroundStyle(.secondary).padding(.top, 4) }
        }.formStyle(.grouped).padding(16)
    }
}
struct ProfileCardView: View {
    let profile: PromptProfile; let isSelected: Bool; let appLanguage: String; let action: () -> Void
    var body: some View {
        Button(action: action) { HStack(alignment: .top, spacing: 16) { Image(systemName: isSelected ? "checkmark.circle.fill" : "circle").font(.system(size: 20)).foregroundStyle(isSelected ? WordflowTheme.primary : .secondary.opacity(0.3)).padding(.top, 2)
                VStack(alignment: .leading, spacing: 4) { Text(profile.name).font(.system(size: 15, weight: .semibold)).foregroundStyle(isSelected ? Color.primary : Color.primary.opacity(0.8)); Text(description(for: profile.name, lang: appLanguage)).font(.system(size: 13)).foregroundStyle(.secondary).fixedSize(horizontal: false, vertical: true) }
                Spacer() }.padding(16).background(isSelected ? WordflowTheme.primary.opacity(0.08) : Color.primary.opacity(0.02)).overlay(RoundedRectangle(cornerRadius: 12, style: .continuous).strokeBorder(isSelected ? WordflowTheme.primary.opacity(0.5) : Color.primary.opacity(0.05), lineWidth: isSelected ? 1.5 : 1)).clipShape(RoundedRectangle(cornerRadius: 12, style: .continuous)).contentShape(Rectangle()) }.buttonStyle(.plain)
    }
    private func description(for name: String, lang: String) -> String {
        switch name { case "Smart Casual": return lang == "EN" ? "Keeps your natural vibe, removes stutters, and fixes punctuation. Great for everyday messages." : "Behält deinen natürlichen Vibe bei, entfernt Stotterer und korrigiert die Zeichensetzung. Perfekt für alltägliche Nachrichten."; case "Smart Business": return lang == "EN" ? "Turns spoken thoughts into clear, logical text suitable for business contexts." : "Verwandelt gesprochene Gedanken in klaren, logischen Text. Ideal für das Business-Umfeld."; case "Professional": return lang == "EN" ? "Produces highly polished, formal text perfect for professional emails and documents." : "Erzeugt sehr formellen, feingeschliffenen Text. Perfekt für professionelle E-Mails und Dokumente."; case "Prompt Engineer": return lang == "EN" ? "Turns your raw brain dump into a precise, effective prompt for AI chat tools like Claude or ChatGPT." : "Verwandelt deinen rohen Gedanken-Dump in einen präzisen, effektiven Prompt für KI-Chat-Tools wie Claude oder ChatGPT."; default: return "" }
    }
}
// MARK: - 4. System Settings
struct SystemSettingsView: View {
    @AppStorage("appLanguage") private var appLanguage = "EN"
    @AppStorage("transcriptionModel") private var transcriptionModel = "whisper-large-v3-turbo"
    @AppStorage("textCorrectionModel") private var textCorrectionModel = "llama-4-scout"
    @AppStorage("logLevel") private var logLevel = "info"
    @AppStorage("groqAPIKey") private var apiKey = ""
    @EnvironmentObject var appState: AppState
    @ObservedObject var updateChecker = UpdateChecker.shared
    @State private var hasAccessibility = AXIsProcessTrusted()
    @State private var showingAPIKey = false
    @State private var showingGuide = false
    var body: some View {
        Form {
            Section { LabeledContent(appLanguage == "EN" ? "Groq API Key:" : "Groq API Key:") { HStack(spacing: 8) { if showingAPIKey { TextField("API Key", text: $apiKey).textFieldStyle(.roundedBorder) } else { SecureField("API Key", text: $apiKey).textFieldStyle(.roundedBorder) }
                        Button { showingAPIKey.toggle() } label: { Image(systemName: showingAPIKey ? "eye.slash" : "eye") }.buttonStyle(.borderless).foregroundStyle(.secondary)
                    }.frame(width: 320) }
                Link(destination: URL(string: "https://console.groq.com")!) { Text(appLanguage == "EN" ? "Get free API Key →" : "Kostenlosen API Key holen →").font(.system(size: 12)).foregroundStyle(WordflowTheme.primary) }.padding(.leading, 2)
            } header: { Text(appLanguage == "EN" ? "API Configuration" : "API Konfiguration") } footer: { Text(appLanguage == "EN" ? "We securely store your API keys locally on your Mac." : "API Keys werden nur lokal auf deinem Mac gespeichert.").font(.footnote).foregroundStyle(.secondary) }
            
            Section { HStack { Text(appLanguage == "EN" ? "Transcription" : "Transkription"); Spacer(); Picker("", selection: $transcriptionModel) { Text("Whisper Large V3 Turbo").tag("whisper-large-v3-turbo"); Text("Whisper Large V3").tag("whisper-large-v3") }.labelsHidden().pickerStyle(.menu).frame(width: 240) }
                HStack { Text(appLanguage == "EN" ? "Text Correction" : "Textkorrektur"); Spacer(); Picker("", selection: $textCorrectionModel) { Text("Llama 3.1 (8B)").tag("llama-3.1-8b-instant"); Text("Llama 3.3 (70B)").tag("llama-3.3-70b-versatile"); Text("Llama 3.2 (3B)").tag("llama-3.2-3b-preview"); Text("Llama 4 Scout").tag("llama-4-scout"); Text("Mixtral (8x7b)").tag("mixtral-8x7b-32768") }.labelsHidden().pickerStyle(.menu).frame(width: 240) }
            } header: { Text(appLanguage == "EN" ? "AI Models" : "KI-Modelle") } footer: { Text(appLanguage == "EN" ? "Choose models that balance speed and accuracy." : "Wähle Modelle, Geschwindigkeit und Genauigkeit im Gleichgewicht halten.").font(.footnote).foregroundStyle(.secondary) }
            
            Section(appLanguage == "EN" ? "Permissions" : "Berechtigungen") { HStack { Text(appLanguage == "EN" ? "Accessibility Access" : "Bedienungshilfen-Zugriff"); Spacer()
                    if hasAccessibility { HStack { Image(systemName: "checkmark.circle.fill").foregroundStyle(.green); Text(appLanguage == "EN" ? "Granted" : "Erteilt").foregroundStyle(.secondary) }
                    } else { Button(appLanguage == "EN" ? "Grant Permission" : "Erlauben") { let url = URL(string: "x-apple.systempreferences:com.apple.preference.security?Privacy_Accessibility")!; NSWorkspace.shared.open(url) }.buttonStyle(.borderedProminent).tint(WordflowTheme.primary) } }
                if !hasAccessibility { Text(appLanguage == "EN" ? "Required for auto-pasting text." : "Benötigt für das automatische Einfügen.").font(.footnote).foregroundStyle(.orange) } }
            
            if let lastError = appState.lastError {
                Section(appLanguage == "EN" ? "Last Error" : "Letzter Fehler") {
                    VStack(alignment: .leading, spacing: 8) {
                        HStack(spacing: 8) {
                            Image(systemName: categoryIcon(lastError.category))
                                .foregroundStyle(categoryColor(lastError.category))
                            Text(lastError.userMessage(for: appLanguage))
                                .font(.system(size: 13, weight: .medium))
                                .foregroundStyle(categoryColor(lastError.category))
                        }
                        HStack {
                            Text(appLanguage == "EN" ? "Stage:" : "Stufe:")
                                .foregroundStyle(.secondary)
                                .font(.footnote)
                            Text(lastError.stage.rawValue.capitalized)
                                .font(.footnote)
                            if let code = lastError.code {
                                Text("(\(code))")
                                    .font(.footnote)
                                    .foregroundStyle(.secondary)
                            }
                            Spacer()
                            Text(lastError.timestamp, style: .relative)
                                .font(.footnote)
                                .foregroundStyle(.secondary)
                        }
                        Text(lastError.technicalMessage.prefix(300))
                            .font(.system(size: 11, design: .monospaced))
                            .foregroundStyle(.secondary)
                            .lineLimit(4)
                    }
                    .padding(.vertical, 4)
                    Button(appLanguage == "EN" ? "Clear Error" : "Fehler löschen", role: .destructive) {
                        appState.clearLastError()
                    }
                }
            }

            Section(appLanguage == "EN" ? "Logging & Diagnostics" : "Logging & Diagnose") {
                Button(appLanguage == "EN" ? "Reveal Log File" : "Log-Datei anzeigen") { if let url = LogManager.shared.getLogFileURL() { NSWorkspace.shared.open(url) } }
                Button(appLanguage == "EN" ? "Clear History" : "Verlauf komplett löschen", role: .destructive) { appState.clipboardManager.clearHistory(); appState.refreshHistory() }
                Button(appLanguage == "EN" ? "Check for Updates" : "Nach Updates suchen") { Task { await updateChecker.checkForUpdates(userInitiated: true) } }
                HStack { Text(appLanguage == "EN" ? "Version" : "Version"); Spacer(); Text({ let version = Bundle.main.infoDictionary?["CFBundleShortVersionString"] as? String ?? "1.0"; let build = Bundle.main.infoDictionary?["CFBundleVersion"] as? String ?? "1"; return "\(version) (Build \(build))" }()).foregroundStyle(.secondary) }
            }
        }.formStyle(.grouped).padding(16).sheet(isPresented: $showingGuide) { APIGuideSheet(appLanguage: appLanguage) }.onAppear { hasAccessibility = AXIsProcessTrusted() }
    }

    private func categoryIcon(_ category: AppErrorCategory) -> String {
        switch category {
        case .networkOffline: return "wifi.slash"
        case .timeout: return "clock.badge.exclamationmark"
        case .rateLimit: return "speedometer"
        case .keyMissing: return "key.slash"
        case .keyInvalid: return "key.slash"
        case .serviceUnavailable: return "server.rack"
        case .apiError: return "exclamationmark.triangle"
        case .unknown: return "questionmark.circle"
        }
    }

    private func categoryColor(_ category: AppErrorCategory) -> Color {
        switch category {
        case .networkOffline, .timeout: return .orange
        case .rateLimit: return .yellow
        case .keyMissing, .keyInvalid: return .red
        case .serviceUnavailable: return .orange
        case .apiError, .unknown: return .red
        }
    }
}
// MARK: - Hotkey Recorder Components
// MARK: - 5. Statistics View
struct StatisticsView: View {
    @AppStorage("appLanguage") private var appLanguage = "EN"
    @ObservedObject var manager = StatisticsManager.shared
    @State private var timeRange: StatisticsManager.TimeRange = .week
    
    var body: some View {
        Form {
            Section(appLanguage == "EN" ? "Time Range" : "Zeitraum") {
                HStack { Text(appLanguage == "EN" ? "Period:" : "Zeitraum:")
                    Spacer()
                    Picker("", selection: $timeRange) {
                        Text(appLanguage == "EN" ? "1 Week" : "1 Woche").tag(StatisticsManager.TimeRange.week)
                        Text(appLanguage == "EN" ? "1 Month" : "1 Monat").tag(StatisticsManager.TimeRange.month)
                        Text(appLanguage == "EN" ? "1 Year" : "1 Jahr").tag(StatisticsManager.TimeRange.year)
                        Text(appLanguage == "EN" ? "All Time" : "Gesamt").tag(StatisticsManager.TimeRange.all)
                    }.labelsHidden().pickerStyle(.menu).frame(width: 140)
                }
                .pickerStyle(.menu)
                .frame(width: 260)
            }
            
            // KPI Grid inside form sections
            let stats = manager.getStats(for: timeRange)
            
            HStack(spacing: 12) {
                StatCard(
                    title: appLanguage == "EN" ? "Requests" : "Anfragen",
                    value: "\(stats.requests)",
                    icon: "waveform"
                )
                
                StatCard(
                    title: appLanguage == "EN" ? "Words" : "Wörter",
                    value: "\(stats.words)",
                    icon: "text.quote"
                )
                
                StatCard(
                    title: appLanguage == "EN" ? "Saved Time" : "Zeit gespart",
                    value: formatTime(stats.savedSeconds),
                    icon: "stopwatch"
                )
            }
            .padding(.vertical, 8)
            
            // Activity Chart
            Section(appLanguage == "EN" ? "Activity" : "Aktivität") {
                if #available(macOS 13.0, *) {
                    Chart {
                        ForEach(manager.getChartData(for: timeRange), id: \.date) { item in
                            BarMark(
                                x: .value("Date", dateLabel(for: item.date)),
                                y: .value("Requests", item.count)
                            )
                            .foregroundStyle(WordflowTheme.primary.gradient)
                            .clipShape(.rect(cornerRadius: 3, style: .continuous))
                        }
                    }
                    .chartXAxis {
                        AxisMarks { _ in
                            AxisValueLabel()
                                .foregroundStyle(Color.secondary)
                        }
                    }
                    .chartYAxis {
                        AxisMarks(position: .leading)
                    }
                    .frame(height: 200)
                    .padding(16)
                } else {
                    Text("Charts require macOS 13+")
                        .padding(16)
                }
            }
        }
        .formStyle(.grouped)
        .padding(16)
    }
    
    // Helpers
    func formatTime(_ seconds: Double) -> String {
        if seconds < 60 {
            return "\(Int(seconds))s"
        } else if seconds < 3600 {
            return String(format: "%.1f min", seconds / 60)
        } else {
            return String(format: "%.1f h", seconds / 3600)
        }
    }
    
    func dateLabel(for dateString: String) -> String {
        if dateString.count == 7 {
            let formatter = DateFormatter()
            formatter.dateFormat = "yyyy-MM"
            if let date = formatter.date(from: dateString) {
                formatter.dateFormat = "MMM"
                return formatter.string(from: date)
            }
            return dateString
        }
        
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM-dd"
        guard let date = formatter.date(from: dateString) else { return "?" }
        
        if timeRange == .week {
            formatter.dateFormat = "E"
        } else {
            formatter.dateFormat = "d.M"
        }
        return formatter.string(from: date)
    }
}

struct StatCard: View {
    let title: String
    let value: String
    let icon: String
    var body: some View {
        HStack(spacing: 12) {
            Image(systemName: icon)
                .font(.system(size: 20))
                .foregroundColor(.primary)
                .frame(width: 36, height: 36)
                .background(Color.primary.opacity(0.04))
                .clipShape(Circle())
            
            VStack(alignment: .leading, spacing: 2) {
                Text(value)
                    .font(.system(size: 20, weight: .semibold, design: .serif))
                    .foregroundColor(.primary)
                Text(title)
                    .font(.system(size: 13))
                    .foregroundColor(.secondary)
            }
            Spacer()
        }
        .padding(16)
        .background(Color(nsColor: .controlBackgroundColor))
        .clipShape(RoundedRectangle(cornerRadius: 10, style: .continuous))
        .overlay(RoundedRectangle(cornerRadius: 10, style: .continuous).stroke(Color.primary.opacity(0.08), lineWidth: 1))
    }
}

// MARK: - Account Settings
struct AccountSettingsView: View {
    @AppStorage("appLanguage") private var appLanguage = "EN"
    @EnvironmentObject var appState: AppState
    
    var body: some View {
        Form {
            Section(appLanguage == "EN" ? "Account Details" : "Konto & Zugriff") {
                VStack(alignment: .leading, spacing: 12) {
                    Text(appLanguage == "EN" ? "You are currently logged into Wordflow." : "Du bist in deinen Wordflow-Account eingeloggt.")
                        .font(.system(size: 14))
                        
                    Button(role: .destructive) {
                        SupabaseService.shared.logout()
                    } label: {
                        HStack(spacing: 8) {
                            Image(systemName: "rectangle.portrait.and.arrow.right")
                            Text(appLanguage == "EN" ? "Log Out" : "Abmelden")
                        }
                    }
                    .buttonStyle(.borderedProminent)
                    .tint(.red)
                }
            }
        }
        .formStyle(.grouped)
        .padding(16)
    }
}
// MARK: - Hotkey Recorder Components
struct HotkeyRecorderView: NSViewRepresentable {
    @Binding var isRecording: Bool
    var onHotkeyRecorded: (HotkeyConfig) -> Void
    
    func makeNSView(context: Context) -> HotkeyRecorderNSView {
        let view = HotkeyRecorderNSView()
        view.onHotkeyRecorded = onHotkeyRecorded
        return view
    }
    
    func updateNSView(_ nsView: HotkeyRecorderNSView, context: Context) {
        nsView.isRecordingHotkey = isRecording
        if isRecording {
            nsView.window?.makeFirstResponder(nsView)
        }
    }
}

class HotkeyRecorderNSView: NSView {
    var isRecordingHotkey = false
    var onHotkeyRecorded: ((HotkeyConfig) -> Void)?
    
    override var acceptsFirstResponder: Bool { true }
    
    override func keyDown(with event: NSEvent) {
        guard isRecordingHotkey else {
            super.keyDown(with: event)
            return
        }
        
        let modifiers = event.modifierFlags.intersection(.deviceIndependentFlagsMask)
        if modifiers.isEmpty { return }
        
        let modifierOnlyKeyCodes: Set<UInt16> = [54, 55, 56, 57, 58, 59, 60, 61, 62, 63]
        if modifierOnlyKeyCodes.contains(event.keyCode) { return }
        
        let config = HotkeyConfig(
            modifiers: modifiers.rawValue,
            keyCode: event.keyCode,
            useFnKey: false
        )
        onHotkeyRecorded?(config)
    }
}
