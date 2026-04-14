import SwiftUI
import AppKit

private extension Color {
    static func adaptive(light: NSColor, dark: NSColor) -> Color {
        Color(nsColor: NSColor(name: nil) { appearance in
            switch appearance.bestMatch(from: [.darkAqua, .aqua]) {
            case .darkAqua:
                return dark
            default:
                return light
            }
        })
    }
}

enum WordflowTheme {
    static let primary = Color.adaptive(
        light: NSColor(calibratedRed: 210.0 / 255.0, green: 105.0 / 255.0, blue: 30.0 / 255.0, alpha: 1),
        dark: NSColor(calibratedRed: 229.0 / 255.0, green: 138.0 / 255.0, blue: 69.0 / 255.0, alpha: 1)
    )

    static let background = Color.adaptive(
        light: NSColor(calibratedRed: 253.0 / 255.0, green: 251.0 / 255.0, blue: 247.0 / 255.0, alpha: 1),
        dark: NSColor(calibratedRed: 18.0 / 255.0, green: 20.0 / 255.0, blue: 26.0 / 255.0, alpha: 1)
    )

    static let surface = Color.adaptive(
        light: NSColor(calibratedRed: 247.0 / 255.0, green: 243.0 / 255.0, blue: 238.0 / 255.0, alpha: 1),
        dark: NSColor(calibratedRed: 25.0 / 255.0, green: 29.0 / 255.0, blue: 38.0 / 255.0, alpha: 1)
    )

    static let surfaceHigh = Color.adaptive(
        light: NSColor(calibratedRed: 235.0 / 255.0, green: 229.0 / 255.0, blue: 219.0 / 255.0, alpha: 1),
        dark: NSColor(calibratedRed: 34.0 / 255.0, green: 39.0 / 255.0, blue: 50.0 / 255.0, alpha: 1)
    )

    static let onSurface = Color.adaptive(
        light: NSColor(calibratedRed: 26.0 / 255.0, green: 26.0 / 255.0, blue: 26.0 / 255.0, alpha: 1),
        dark: NSColor(calibratedRed: 240.0 / 255.0, green: 238.0 / 255.0, blue: 232.0 / 255.0, alpha: 1)
    )

    static let onSurfaceVariant = Color.adaptive(
        light: NSColor(calibratedRed: 74.0 / 255.0, green: 74.0 / 255.0, blue: 74.0 / 255.0, alpha: 1),
        dark: NSColor(calibratedRed: 186.0 / 255.0, green: 182.0 / 255.0, blue: 174.0 / 255.0, alpha: 1)
    )

    static let outline = Color.adaptive(
        light: NSColor(calibratedRed: 209.0 / 255.0, green: 205.0 / 255.0, blue: 199.0 / 255.0, alpha: 1),
        dark: NSColor(calibratedRed: 66.0 / 255.0, green: 72.0 / 255.0, blue: 88.0 / 255.0, alpha: 1)
    )

    static let recordingBase = Color.adaptive(
        light: NSColor(calibratedRed: 26.0 / 255.0, green: 30.0 / 255.0, blue: 44.0 / 255.0, alpha: 1),
        dark: NSColor(calibratedRed: 17.0 / 255.0, green: 21.0 / 255.0, blue: 31.0 / 255.0, alpha: 1)
    )

    static let editorialShadow = Color.adaptive(
        light: NSColor(calibratedWhite: 0, alpha: 0.08),
        dark: NSColor(calibratedWhite: 0, alpha: 0.45)
    )

    static let ambientShadow = Color.adaptive(
        light: NSColor(calibratedWhite: 0, alpha: 0.05),
        dark: NSColor(calibratedWhite: 0, alpha: 0.28)
    )

    // Profil-Farben für Pill-Glow
    static let profileSmartCasual = Color.white
    static let profileEmail = Color(red: 59.0 / 255.0, green: 130.0 / 255.0, blue: 246.0 / 255.0)  // Blau
    static let profileTech = Color(red: 139.0 / 255.0, green: 92.0 / 255.0, blue: 246.0 / 255.0)   // Lila
}

extension View {
    func wordflowEditorialShadow() -> some View {
        shadow(color: WordflowTheme.editorialShadow, radius: 26, x: 0, y: 10)
    }

    func wordflowAmbientShadow() -> some View {
        shadow(color: WordflowTheme.ambientShadow, radius: 14, x: 0, y: 6)
    }
}
