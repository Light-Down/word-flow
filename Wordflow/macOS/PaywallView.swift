import SwiftUI

// ─────────────────────────────────────────────
// MARK: - PaywallView
// Erscheint wenn Trial abgelaufen ist
// ─────────────────────────────────────────────

struct PaywallView: View {
    @ObservedObject private var license = LicenseManager.shared
    @ObservedObject private var supabase = SupabaseService.shared
    @State private var isRefreshing = false

    var body: some View {
        VStack(spacing: 0) {
            // Header
            VStack(spacing: 12) {
                Image(systemName: "mic.circle.fill")
                    .font(.system(size: 52))
                    .foregroundStyle(.primary)
                    .padding(.top, 32)

                Text("Dein Trial ist abgelaufen")
                    .font(.title2)
                    .fontWeight(.semibold)

                Text("Wie gefällt dir Wordflow?")
                    .font(.subheadline)
                    .foregroundStyle(.secondary)
            }
            .padding(.bottom, 28)

            Divider()

            // Feature-Liste
            VStack(alignment: .leading, spacing: 14) {
                FeatureRow(icon: "waveform", text: "Unbegrenzte Sprachaufnahmen")
                FeatureRow(icon: "text.bubble", text: "KI-Textkorrektur mit Schreibstilen")
                FeatureRow(icon: "clock.arrow.circlepath", text: "Vollständiger Transkript-Verlauf")
                FeatureRow(icon: "key", text: "Bring Your Own Key — volle Kontrolle")
            }
            .padding(.horizontal, 28)
            .padding(.vertical, 20)

            Divider()

            // Buttons
            VStack(spacing: 10) {
                Button(action: { license.openCheckout() }) {
                    HStack {
                        Image(systemName: "lock.open.fill")
                        Text("Wordflow freischalten")
                            .fontWeight(.semibold)
                    }
                    .frame(maxWidth: .infinity)
                    .padding(.vertical, 11)
                }
                .buttonStyle(.borderedProminent)
                .controlSize(.large)

                HStack(spacing: 16) {
                    Button("Bereits gekauft?") {
                        Task {
                            isRefreshing = true
                            await license.refreshAfterPurchase()
                            isRefreshing = false
                        }
                    }
                    .buttonStyle(.plain)
                    .foregroundStyle(.secondary)
                    .font(.footnote)

                    if isRefreshing {
                        ProgressView().controlSize(.mini)
                    }
                }
                .frame(height: 24)
            }
            .padding(.horizontal, 28)
            .padding(.vertical, 20)
        }
        .frame(width: 340)
        .background(.background)
    }
}

// ─────────────────────────────────────────────
// MARK: - LoginView
// Erscheint wenn User noch nicht eingeloggt ist
// ─────────────────────────────────────────────

struct LoginView: View {
    @ObservedObject private var supabase = SupabaseService.shared
    @State private var email: String = ""
    @State private var magicLinkSent: Bool = false

    var body: some View {
        VStack(spacing: 0) {
            VStack(spacing: 12) {
                Image(systemName: "mic.circle.fill")
                    .font(.system(size: 52))
                    .foregroundStyle(.primary)
                    .padding(.top, 32)

                Text("Willkommen bei Wordflow")
                    .font(.title2)
                    .fontWeight(.semibold)

                Text("Melde dich mit deiner E-Mail an, um zu starten.")
                    .font(.subheadline)
                    .foregroundStyle(.secondary)
                    .multilineTextAlignment(.center)
                    .padding(.horizontal, 24)
            }
            .padding(.bottom, 28)

            Divider()

            if magicLinkSent {
                // Bestätigung
                VStack(spacing: 16) {
                    Image(systemName: "envelope.badge.checkmark")
                        .font(.system(size: 36))
                        .foregroundStyle(.green)

                    Text("Link gesendet!")
                        .font(.headline)

                    Text("Schau in dein Postfach und klicke auf den Link.\nDanach öffnet sich Wordflow automatisch.")
                        .font(.subheadline)
                        .foregroundStyle(.secondary)
                        .multilineTextAlignment(.center)

                    Button("Andere E-Mail verwenden") {
                        magicLinkSent = false
                        email = ""
                    }
                    .buttonStyle(.plain)
                    .foregroundStyle(.secondary)
                    .font(.footnote)
                }
                .padding(28)
            } else {
                // E-Mail Eingabe
                VStack(spacing: 14) {
                    TextField("deine@email.com", text: $email)
                        .textFieldStyle(.roundedBorder)
                        .autocorrectionDisabled()

                    if let error = supabase.authError {
                        Text(error)
                            .font(.footnote)
                            .foregroundStyle(.red)
                    }

                    Button(action: {
                        Task {
                            await supabase.sendMagicLink(email: email)
                            if supabase.authError == nil {
                                magicLinkSent = true
                            }
                        }
                    }) {
                        HStack {
                            if supabase.isLoading {
                                ProgressView().controlSize(.small)
                            } else {
                                Image(systemName: "envelope")
                            }
                            Text("Magic Link senden")
                                .fontWeight(.semibold)
                        }
                        .frame(maxWidth: .infinity)
                        .padding(.vertical, 11)
                    }
                    .buttonStyle(.borderedProminent)
                    .controlSize(.large)
                    .disabled(email.isEmpty || supabase.isLoading)
                }
                .padding(.horizontal, 28)
                .padding(.vertical, 20)
            }
        }
        .frame(width: 340)
        .background(.background)
    }
}

// ─────────────────────────────────────────────
// MARK: - Helpers
// ─────────────────────────────────────────────

private struct FeatureRow: View {
    let icon: String
    let text: String

    var body: some View {
        HStack(spacing: 12) {
            Image(systemName: icon)
                .frame(width: 22)
                .foregroundStyle(.blue)
            Text(text)
                .font(.subheadline)
        }
    }
}
