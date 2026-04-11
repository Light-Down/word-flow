import AuthenticationServices
import AppKit

/// Provides the presentation anchor for ASWebAuthenticationSession (used for OAuth flows like Google).
final class AuthPresentationContext: NSObject, ASWebAuthenticationPresentationContextProviding {
    func presentationAnchor(for session: ASWebAuthenticationSession) -> ASPresentationAnchor {
        NSApp.keyWindow ?? NSWindow()
    }
}
