import SwiftUI

struct HistoryView: View {
    @EnvironmentObject var appState: AppState
    @AppStorage("appLanguage") private var appLanguage = "DE"
    @State private var searchText = ""
    
    var filteredHistory: [TranscriptionEntry] {
        if searchText.isEmpty {
            return appState.clipboardManager.history
        } else {
            return appState.clipboardManager.history.filter {
                $0.text.localizedCaseInsensitiveContains(searchText)
            }
        }
    }
    
    var body: some View {
        ZStack {
            // Background
            Color(NSColor.windowBackgroundColor)
                .edgesIgnoringSafeArea(.all)
            
            VStack(spacing: 0) {
                // Header with Search
                VStack(spacing: 12) {
                    HStack {
                        Text(appLanguage == "EN" ? "My Notes" : "Meine Notizen")
                            .font(.largeTitle)
                            .fontWeight(.bold)
                        
                        Spacer()
                        
                        Button(appLanguage == "EN" ? "Delete All" : "Alle löschen") {
                            withAnimation {
                                appState.clipboardManager.clearHistory()
                                appState.refreshHistory()
                            }
                        }
                        .buttonStyle(.bordered)
                        .tint(.red)
                        .disabled(appState.clipboardManager.history.isEmpty)
                    }
                    
                    // Search Bar
                    HStack {
                        Image(systemName: "magnifyingglass")
                            .foregroundColor(.secondary)
                        TextField(appLanguage == "EN" ? "Search..." : "Suchen...", text: $searchText)
                            .textFieldStyle(.plain)
                            .font(.body)
                        
                        if !searchText.isEmpty {
                            Button {
                                searchText = ""
                            } label: {
                                Image(systemName: "xmark.circle.fill")
                                    .foregroundColor(.secondary)
                            }
                            .buttonStyle(.plain)
                        }
                    }
                    .padding(10)
                    .background(Color(NSColor.controlBackgroundColor))
                    .cornerRadius(10)
                    .overlay(
                        RoundedRectangle(cornerRadius: 10)
                            .stroke(Color.secondary.opacity(0.1), lineWidth: 1)
                    )
                }
                .padding(20)
                .background(.ultraThinMaterial)
                
                Divider()
                
                // Content
                ScrollView {
                    if filteredHistory.isEmpty {
                        VStack(spacing: 20) {
                            Image(systemName: searchText.isEmpty ? "note.text" : "magnifyingglass")
                                .font(.system(size: 60))
                                .foregroundColor(.secondary.opacity(0.3))
                                .padding(.top, 50)
                            Text(searchText.isEmpty
                                 ? (appLanguage == "EN" ? "No notes yet" : "Noch keine Notizen vorhanden")
                                 : (appLanguage == "EN" ? "No results found" : "Keine Ergebnisse gefunden"))
                                .font(.title3)
                                .foregroundColor(.secondary)
                        }
                        .frame(maxWidth: .infinity, maxHeight: .infinity)
                    } else {
                        LazyVStack(spacing: 20) {
                            ForEach(filteredHistory) { entry in
                                NoteCard(entry: entry, appLanguage: appLanguage)
                                    .transition(.opacity.combined(with: .scale(scale: 0.95)))
                            }
                        }
                        .padding(20)
                        .animation(.spring(), value: filteredHistory)
                    }
                }
            }
        }
        .frame(minWidth: 500, minHeight: 600)
    }
}

struct NoteCard: View {
    let entry: TranscriptionEntry
    let appLanguage: String
    @EnvironmentObject var appState: AppState
    @State private var isHovering = false
    @State private var hasCopied = false
    
    var body: some View {
        VStack(alignment: .leading, spacing: 0) {
            // Header with Date
            HStack {
                Image(systemName: "calendar")
                    .foregroundColor(.accentColor)
                Text(formatDate(entry.timestamp))
                    .font(.headline)
                    .foregroundColor(.primary)
                
                Spacer()
                
                Button {
                    appState.clipboardManager.copyToClipboard(text: entry.text)
                    
                    // Trigger Animation
                    withAnimation(.spring(response: 0.3, dampingFraction: 0.6)) {
                        hasCopied = true
                    }
                    
                    // Reset
                    DispatchQueue.main.asyncAfter(deadline: .now() + 1.5) {
                        withAnimation {
                            hasCopied = false
                        }
                    }
                } label: {
                    HStack(spacing: 6) {
                        Image(systemName: hasCopied ? "checkmark.circle.fill" : "doc.on.doc")
                            .foregroundColor(hasCopied ? .green : .secondary)
                            .scaleEffect(hasCopied ? 1.2 : 1.0)
                        
                        if hasCopied {
                            Text(appLanguage == "EN" ? "Copied" : "Kopiert")
                                .font(.caption)
                                .foregroundColor(.green)
                                .transition(.move(edge: .trailing).combined(with: .opacity))
                        }
                    }
                    .padding(6)
                    .background(hasCopied ? Color.green.opacity(0.1) : Color.clear)
                    .cornerRadius(8)
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
        .cornerRadius(12)
        .overlay(
            RoundedRectangle(cornerRadius: 12)
                .stroke(Color.secondary.opacity(isHovering ? 0.3 : 0.1), lineWidth: 1)
        )
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
    
    private func formatDate(_ date: Date) -> String {
        let formatter = DateFormatter()
        formatter.dateStyle = .full
        formatter.timeStyle = .short
        formatter.locale = Locale(identifier: appLanguage == "EN" ? "en_US" : "de_DE")
        return formatter.string(from: date)
    }
}
