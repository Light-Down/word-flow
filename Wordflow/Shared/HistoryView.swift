import SwiftUI

struct HistoryView: View {
    @EnvironmentObject var appState: AppState
    @AppStorage("appLanguage") private var appLanguage = "EN"
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

