import SwiftUI

// MARK: - LoginView
// Shown when the user is not yet logged in.
// Supports Magic Link, Email/Password and Google OAuth.

enum LoginMode {
    case magicLink, emailPassword
}

struct LoginView: View {
    @AppStorage("appLanguage") private var appLanguage = "EN"
    @State private var mode: LoginMode = .magicLink

    var body: some View {
        VStack(spacing: 0) {
            header

            Picker("", selection: $mode) {
                Text("Magic Link").tag(LoginMode.magicLink)
                Text(appLanguage == "EN" ? "Password" : "Passwort").tag(LoginMode.emailPassword)
            }
            .pickerStyle(.segmented)
            .labelsHidden()
            .controlSize(.large)
            .padding(.horizontal, 28)
            .padding(.bottom, 16)
            .onChange(of: mode) { _, _ in
                SupabaseService.shared.authError = nil
            }

            VStack(spacing: 0) {
                switch mode {
                case .magicLink:
                    MagicLinkForm()
                        .transition(.asymmetric(insertion: .opacity.combined(with: .scale(scale: 0.98)), removal: .opacity))
                case .emailPassword:
                    EmailPasswordForm()
                        .transition(.asymmetric(insertion: .opacity.combined(with: .scale(scale: 0.98)), removal: .opacity))
                }
            }
            .animation(.spring(response: 0.35, dampingFraction: 0.8), value: mode)

            orDivider
            
            GoogleSignInButton()
                .padding(.horizontal, 28)
                .padding(.bottom, 8)
        }
        .padding(.vertical, 24)
        .frame(maxWidth: .infinity)
        .background(.ultraThinMaterial)
        .background(WordflowTheme.background.opacity(0.4))
        // No clipShape, no overlay, no outer padding to avoid black bars
        .tint(WordflowTheme.primary)
    }

    private var header: some View {
        VStack(spacing: 8) {
            Image("AppLogo")
                .resizable()
                .scaledToFit()
                .frame(height: 36)
                .padding(.top, 16)
                .padding(.bottom, 12)

            Text(appLanguage == "EN" ? "Welcome" : "Willkommen")
                .font(.system(size: 24, weight: .bold, design: .default))
                .foregroundStyle(WordflowTheme.onSurface)

            Text(appLanguage == "EN" ? "Sign in to get started" : "Melde dich an, um loszulegen")
                .font(.body)
                .foregroundStyle(WordflowTheme.onSurfaceVariant)
        }
        .padding(.bottom, 20)
    }

    private var orDivider: some View {
        HStack(spacing: 16) {
            Rectangle().frame(height: 1).foregroundStyle(WordflowTheme.outline.opacity(0.3))
            Text(appLanguage == "EN" ? "or" : "oder")
                .font(.system(size: 13, weight: .medium))
                .foregroundStyle(WordflowTheme.onSurfaceVariant.opacity(0.8))
            Rectangle().frame(height: 1).foregroundStyle(WordflowTheme.outline.opacity(0.3))
        }
        .padding(.horizontal, 28)
        .padding(.vertical, 16)
    }
}

// MARK: - MagicLinkForm

struct MagicLinkForm: View {
    @ObservedObject private var supabase = SupabaseService.shared
    @AppStorage("appLanguage") private var appLanguage = "EN"
    @State private var email = ""
    @State private var didSend = false

    var body: some View {
        if didSend {
            sentConfirmation
        } else {
            loginForm
        }
    }

    private var loginForm: some View {
        VStack(spacing: 16) {
            TextField(appLanguage == "EN" ? "Email Address" : "E-Mail-Adresse", text: $email)
                .textFieldStyle(.roundedBorder)
                .controlSize(.large)
                .textContentType(.emailAddress)
                .autocorrectionDisabled()
                .onSubmit { sendLink() }

            if let error = supabase.authError {
                Text(error)
                    .font(.footnote)
                    .foregroundStyle(.red)
                    .multilineTextAlignment(.center)
            }

            Button {
                sendLink()
            } label: {
                HStack {
                    if supabase.isLoading { ProgressView().controlSize(.small) }
                    Text(appLanguage == "EN" ? "Send Magic Link" : "Magic Link senden")
                        .font(.system(size: 14, weight: .semibold))
                }
                .frame(maxWidth: .infinity, alignment: .center)
            }
            .buttonStyle(.borderedProminent)
            .controlSize(.large)
            .disabled(email.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty || supabase.isLoading)
        }
        .padding(.horizontal, 28)
        .padding(.vertical, 8)
    }

    private var sentConfirmation: some View {
        VStack(spacing: 16) {
            Image(systemName: "envelope.badge.checkmark")
                .font(.system(size: 40))
                .foregroundStyle(.green)

            Text(appLanguage == "EN" ? "Link sent!" : "Link gesendet!")
                .font(.headline)

            let desc = appLanguage == "EN" 
                ? "We've sent a magic link to **\(email)**. Open the email and tap the link to sign in."
                : "Wir haben dir einen Magic Link an **\(email)** gesendet. Öffne die E-Mail und tippe auf den Link, um dich anzumelden."
            Text(.init(desc))
                .font(.subheadline)
                .foregroundStyle(.secondary)
                .multilineTextAlignment(.center)

            Button(appLanguage == "EN" ? "Use different email" : "Andere E-Mail verwenden") {
                withAnimation {
                    didSend = false
                    email = ""
                }
            }
            .buttonStyle(.bordered)
            .controlSize(.regular)
            .font(.footnote)
        }
        .padding(.horizontal, 28)
        .padding(.vertical, 8)
    }

    private func sendLink() {
        let trimmed = email.trimmingCharacters(in: .whitespacesAndNewlines)
        guard !trimmed.isEmpty else { return }
        Task {
            await supabase.sendMagicLink(email: trimmed)
            if supabase.authError == nil {
                didSend = true
            }
        }
    }
}

// MARK: - EmailPasswordForm

struct EmailPasswordForm: View {
    @ObservedObject private var supabase = SupabaseService.shared
    @AppStorage("appLanguage") private var appLanguage = "EN"
    @State private var email = ""
    @State private var password = ""
    @State private var isSignUp = false

    var body: some View {
        VStack(spacing: 16) {
            TextField(appLanguage == "EN" ? "Email Address" : "E-Mail-Adresse", text: $email)
                .textFieldStyle(.roundedBorder)
                .controlSize(.large)
                .textContentType(.emailAddress)
                .autocorrectionDisabled()

            SecureField(appLanguage == "EN" ? "Password" : "Passwort", text: $password)
                .textFieldStyle(.roundedBorder)
                .controlSize(.large)
                .textContentType(isSignUp ? .newPassword : .password)

            if let error = supabase.authError {
                Text(error)
                    .font(.footnote)
                    .foregroundStyle(.red)
                    .multilineTextAlignment(.center)
            }

            VStack(spacing: 12) {
                Button {
                    Task {
                        if isSignUp {
                            await supabase.signUpWithEmail(email: email, password: password)
                        } else {
                            await supabase.signInWithEmail(email: email, password: password)
                        }
                    }
                } label: {
                    HStack {
                        if supabase.isLoading { ProgressView().controlSize(.small) }
                        Text(isSignUp ? (appLanguage == "EN" ? "Create Account" : "Konto erstellen") : (appLanguage == "EN" ? "Sign In" : "Anmelden"))
                            .font(.system(size: 14, weight: .semibold))
                    }
                    .frame(maxWidth: .infinity, alignment: .center)
                }
                .buttonStyle(.borderedProminent)
                .controlSize(.large)
                .disabled(email.isEmpty || password.count < 6 || supabase.isLoading)

                Button(action: {
                    withAnimation {
                        isSignUp.toggle()
                        supabase.authError = nil
                    }
                }) {
                    Text(isSignUp 
                        ? (appLanguage == "EN" ? "Already have an account? Sign in" : "Bereits ein Konto? Anmelden")
                        : (appLanguage == "EN" ? "New here? Create account" : "Neu hier? Konto erstellen"))
                        .font(.system(size: 12, weight: .medium))
                        .frame(maxWidth: .infinity, alignment: .center)
                }
                .buttonStyle(.bordered)
                .controlSize(.regular)
            }
        }
        .padding(.horizontal, 28)
        .padding(.vertical, 8)
    }
}

// MARK: - GoogleSignInButton

struct GoogleSignInButton: View {
    @ObservedObject private var supabase = SupabaseService.shared
    @AppStorage("appLanguage") private var appLanguage = "EN"

    var body: some View {
        Button {
            Task { await supabase.signInWithGoogle() }
        } label: {
            HStack(spacing: 10) {
                Image(systemName: "globe")
                Text(appLanguage == "EN" ? "Continue with Google" : "Mit Google anmelden")
                    .font(.system(size: 14, weight: .medium))
            }
            .frame(maxWidth: .infinity, alignment: .center)
        }
        .buttonStyle(.bordered)
        .controlSize(.large)
        .disabled(supabase.isLoading)
    }
}
