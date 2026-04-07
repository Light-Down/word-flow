import SwiftUI

// MARK: - LoginView
// Shown when the user is not yet logged in.
// Supports Magic Link, Email/Password and Google OAuth.

enum LoginMode {
    case magicLink, emailPassword
}

struct LoginView: View {
    @State private var mode: LoginMode = .magicLink

    var body: some View {
        VStack(spacing: 0) {
            header

            Divider()

            Picker("", selection: $mode) {
                Text("Magic Link").tag(LoginMode.magicLink)
                Text("Passwort").tag(LoginMode.emailPassword)
            }
            .pickerStyle(.segmented)
            .padding(.horizontal, 28)
            .padding(.top, 20)
            .onChange(of: mode) { _, _ in
                SupabaseService.shared.authError = nil
            }

            switch mode {
            case .magicLink:
                MagicLinkForm()
            case .emailPassword:
                EmailPasswordForm()
            }

            orDivider

            GoogleSignInButton()
                .padding(.horizontal, 28)
                .padding(.bottom, 24)
        }
        .frame(width: 340)
        .background(.background)
    }

    private var header: some View {
        VStack(spacing: 12) {
            Image(systemName: "mic.circle.fill")
                .font(.system(size: 52))
                .foregroundStyle(.primary)
                .padding(.top, 32)

            Text("Willkommen bei Wordflow")
                .font(.title2)
                .bold()

            Text("Melde dich an, um loszulegen")
                .font(.subheadline)
                .foregroundStyle(.secondary)
        }
        .padding(.bottom, 28)
    }

    private var orDivider: some View {
        HStack {
            Rectangle().frame(height: 1).foregroundStyle(.separator)
            Text("oder").font(.footnote).foregroundStyle(.secondary)
            Rectangle().frame(height: 1).foregroundStyle(.separator)
        }
        .padding(.horizontal, 28)
        .padding(.vertical, 8)
    }
}

// MARK: - MagicLinkForm

struct MagicLinkForm: View {
    @ObservedObject private var supabase = SupabaseService.shared
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
            TextField("E-Mail-Adresse", text: $email)
                .textFieldStyle(.roundedBorder)
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
                    Text("Magic Link senden")
                }
                .frame(maxWidth: .infinity)
            }
            .buttonStyle(.borderedProminent)
            .controlSize(.large)
            .disabled(email.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty || supabase.isLoading)
        }
        .padding(.horizontal, 28)
        .padding(.vertical, 24)
    }

    private var sentConfirmation: some View {
        VStack(spacing: 16) {
            Image(systemName: "envelope.badge.checkmark")
                .font(.system(size: 40))
                .foregroundStyle(.green)

            Text("Link gesendet!")
                .font(.headline)

            Text("Wir haben dir einen Magic Link an **\(email)** gesendet. Öffne die E-Mail und tippe auf den Link, um dich anzumelden.")
                .font(.subheadline)
                .foregroundStyle(.secondary)
                .multilineTextAlignment(.center)

            Button("Andere E-Mail verwenden") {
                didSend = false
                email = ""
            }
            .buttonStyle(.plain)
            .foregroundStyle(.secondary)
            .font(.footnote)
        }
        .padding(.horizontal, 28)
        .padding(.vertical, 24)
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
    @State private var email = ""
    @State private var password = ""
    @State private var isSignUp = false

    var body: some View {
        VStack(spacing: 14) {
            TextField("E-Mail-Adresse", text: $email)
                .textFieldStyle(.roundedBorder)
                .textContentType(.emailAddress)
                .autocorrectionDisabled()

            SecureField("Passwort", text: $password)
                .textFieldStyle(.roundedBorder)
                .textContentType(isSignUp ? .newPassword : .password)

            if let error = supabase.authError {
                Text(error)
                    .font(.footnote)
                    .foregroundStyle(.red)
                    .multilineTextAlignment(.center)
            }

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
                    Text(isSignUp ? "Konto erstellen" : "Anmelden")
                }
                .frame(maxWidth: .infinity)
            }
            .buttonStyle(.borderedProminent)
            .controlSize(.large)
            .disabled(email.isEmpty || password.count < 6 || supabase.isLoading)

            Button(isSignUp ? "Bereits ein Konto? Anmelden" : "Neu hier? Konto erstellen") {
                isSignUp.toggle()
                supabase.authError = nil
            }
            .buttonStyle(.plain)
            .foregroundStyle(.secondary)
            .font(.footnote)
        }
        .padding(.horizontal, 28)
        .padding(.vertical, 20)
    }
}

// MARK: - GoogleSignInButton

struct GoogleSignInButton: View {
    @ObservedObject private var supabase = SupabaseService.shared

    var body: some View {
        Button {
            Task { await supabase.signInWithGoogle() }
        } label: {
            HStack {
                Image(systemName: "globe")
                Text("Mit Google anmelden")
            }
            .frame(maxWidth: .infinity)
        }
        .buttonStyle(.bordered)
        .controlSize(.large)
        .disabled(supabase.isLoading)
    }
}
