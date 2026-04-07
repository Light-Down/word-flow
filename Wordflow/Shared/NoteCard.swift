import SwiftUI

struct NoteCard: View {
    let entry: TranscriptionEntry
    let appLanguage: String
    @Environment(AppState.self) var appState
    @State private var isHovering = false
    @State private var hasCopied = false

    var body: some View {
        VStack(alignment: .leading, spacing: 0) {
            // Header with Date
            HStack {
                Image(systemName: "calendar")
                    .foregroundStyle(.tint)
                Text(entry.timestamp, format: .dateTime.weekday(.wide).day().month().year().hour().minute())
                    .font(.headline)
                    .foregroundStyle(.primary)

                Spacer()

                Button {
                    appState.clipboardManager.copyToClipboard(text: entry.text)
                    withAnimation(.spring(response: 0.3, dampingFraction: 0.6)) {
                        hasCopied = true
                    }
                    Task {
                        try? await Task.sleep(for: .milliseconds(1500))
                        hasCopied = false
                    }
                } label: {
                    HStack(spacing: 6) {
                        Image(systemName: hasCopied ? "checkmark.circle.fill" : "doc.on.doc")
                            .foregroundStyle(hasCopied ? .green : .secondary)
                            .scaleEffect(hasCopied ? 1.2 : 1.0)

                        if hasCopied {
                            Text(appLanguage == "EN" ? "Copied" : "Kopiert")
                                .font(.caption)
                                .foregroundStyle(.green)
                                .transition(.move(edge: .trailing).combined(with: .opacity))
                        }
                    }
                    .padding(6)
                    .background(hasCopied ? Color.green.opacity(0.1) : Color.clear)
                    .clipShape(.rect(cornerRadius: 8))
                }
                .buttonStyle(.plain)
                .help(appLanguage == "EN" ? "Copy" : "Kopieren")
            }
            .padding(.horizontal, 16)
            .padding(.vertical, 12)
            .background(Color.secondary.opacity(0.05))

            Divider()

            // Text Content
            Text(entry.text)
                .font(.body)
                .lineSpacing(4)
                .padding(16)
                .frame(maxWidth: .infinity, alignment: .leading)
                .textSelection(.enabled)
        }
        .background(Color(NSColor.controlBackgroundColor))
        .clipShape(.rect(cornerRadius: 12))
        .overlay {
            RoundedRectangle(cornerRadius: 12)
                .stroke(Color.secondary.opacity(isHovering ? 0.3 : 0.1), lineWidth: 1)
        }
        .shadow(
            color: Color.black.opacity(isHovering ? 0.15 : 0.05),
            radius: isHovering ? 15 : 10,
            x: 0,
            y: isHovering ? 6 : 4
        )
        .scaleEffect(isHovering ? 1.005 : 1.0)
        .animation(.spring(response: 0.3, dampingFraction: 0.7), value: isHovering)
        .onHover { hover in
            isHovering = hover
        }
    }
}
