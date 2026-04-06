import Foundation
import SwiftUI

// ─────────────────────────────────────────────
// MARK: - LicenseManager
// Verwaltet Trial-Zustand, Paywall und BYOK-Status
// ─────────────────────────────────────────────

@MainActor
class LicenseManager: ObservableObject {
    static let shared = LicenseManager()

    @Published var showPaywall: Bool = false

    private let supabase = SupabaseService.shared

    // LemonSqueezy / Gumroad Checkout URL (wenn BYOK-Unlock verfügbar)
    private let checkoutURL = "https://markolenberg.gumroad.com/l/qfvqc"

    private init() {}

    // ─────────────────────────────────────────────
    // MARK: - Status prüfen
    // ─────────────────────────────────────────────

    /// Gibt zurück ob der User die App nutzen darf
    var canUseApp: Bool {
        guard supabase.isLoggedIn else { return false }

        // BYOK immer erlaubt
        if supabase.isByokUnlocked { return true }

        // Trial noch aktiv
        if !supabase.isTrialExpired { return true }

        return false
    }

    /// Gibt zurück ob BYOK-Funktionen verfügbar sind
    var byokEnabled: Bool {
        supabase.isByokUnlocked || !supabase.isTrialExpired
    }

    /// Trial-Tage die noch übrig sind
    var trialDaysLeft: Int {
        supabase.trialDaysLeft
    }

    // ─────────────────────────────────────────────
    // MARK: - Paywall
    // ─────────────────────────────────────────────

    /// Prüft ob Paywall angezeigt werden soll
    func checkAndShowPaywallIfNeeded() {
        guard supabase.isLoggedIn else { return }

        if supabase.isTrialExpired && !supabase.isByokUnlocked {
            showPaywall = true
            LogManager.shared.log("🔒 Trial abgelaufen – Paywall wird angezeigt")
        }
    }

    /// Öffnet LemonSqueezy / Gumroad Checkout im Browser
    func openCheckout() {
        guard let url = URL(string: checkoutURL) else { return }
        NSWorkspace.shared.open(url)
        LogManager.shared.log("💳 Checkout geöffnet")
    }

    /// Aktualisiert Lizenz-Status nach Kauf (aufgerufen wenn App wieder in den Vordergrund kommt)
    func refreshAfterPurchase() async {
        await supabase.checkSession()
        if supabase.isByokUnlocked {
            showPaywall = false
            LogManager.shared.log("✅ BYOK freigeschaltet")
        }
    }
}
