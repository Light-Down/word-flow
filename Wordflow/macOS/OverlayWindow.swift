import SwiftUI
import AppKit

// Custom NSWindow for floating overlay
class FloatingPanel: NSWindow {
    override var canBecomeKey: Bool { false }
    override var canBecomeMain: Bool { false }
}

class OverlayWindowController: NSWindowController, ObservableObject {
    private var appState: AppState
    var onCancel: (() -> Void)?
    var onLock: (() -> Void)?
    var onStop: (() -> Void)?
    
    // Published states for SwiftUI to observe
    @Published var isLocked: Bool = false
    @Published var isExpanded: Bool = false
    
    private var hostingView: NSHostingView<AnyView>?
    
    // Window dimensions (The invisible canvas)
    // We add +60 padding to width and +40 to height so the drop shadow doesn't get clipped into a sharp rectangle by the window bounds!
    private let expandedWidth: CGFloat = 220 
    private let collapsedWidth: CGFloat = 130 
    private let windowHeight: CGFloat = 84
    
    init(appState: AppState, onCancel: (() -> Void)? = nil, onLock: (() -> Void)? = nil, onStop: (() -> Void)? = nil) {
        self.appState = appState
        self.onCancel = onCancel
        self.onLock = onLock
        self.onStop = onStop
        
        // Window must be wide enough to accommodate expansion
        // We'll center the content in SwiftUI
        let window = FloatingPanel(
            contentRect: NSRect(x: 0, y: 0, width: expandedWidth, height: windowHeight),
            styleMask: [.borderless, .nonactivatingPanel],
            backing: .buffered,
            defer: false
        )
        
        super.init(window: window)
        
        // Setup the view
        let pillView = PillView(controller: self).environmentObject(appState)
        let hosting = NSHostingView(rootView: AnyView(pillView))
        hosting.frame = NSRect(x: 0, y: 0, width: expandedWidth, height: windowHeight)
        
        window.contentView = hosting
        hostingView = hosting
        
        // Ensure hosting view is transparent
        hosting.layer?.backgroundColor = .clear
        
        window.isOpaque = false
        window.backgroundColor = .clear
        window.level = .floating
        window.collectionBehavior = [.canJoinAllSpaces, .fullScreenAuxiliary, .stationary]
        window.hasShadow = false // Shadow is handled by SwiftUI view
        window.isMovableByWindowBackground = true
        window.hidesOnDeactivate = false
        
        centerWindow()
    }
    
    func centerWindow() {
        if let screen = NSScreen.main {
            let screenFrame = screen.visibleFrame
            let x = screenFrame.midX - expandedWidth / 2
            let y = screenFrame.minY + 40
            window?.setFrameOrigin(NSPoint(x: x, y: y))
        }
    }
    
    // Not needed explicitly anymore since we use ObservedObject in View
    func updatePillView() {
        // No-op, SwiftUI updates automatically
    }
    
    required init?(coder: NSCoder) {
        fatalError("init(coder:) has not been implemented")
    }
    
    override func showWindow(_ sender: Any?) {
        super.showWindow(sender)
        
        // Reset state
        isExpanded = false
        
        // Ensure default size
        window?.setFrame(NSRect(origin: window!.frame.origin, size: NSSize(width: expandedWidth, height: windowHeight)), display: true)
        
        window?.orderFront(nil)
        window?.alphaValue = 0
        
        NSAnimationContext.runAnimationGroup { context in
            context.duration = 0.2
            window?.animator().alphaValue = 1
        }
    }
    
    override func close() {
        NSAnimationContext.runAnimationGroup({ context in
            context.duration = 0.2
            window?.animator().alphaValue = 0
        }, completionHandler: {
            super.close()
        })
    }
}

// MARK: - Pill View (Dynamic & Minimalist)
// MARK: - Pill View (Liquid Intelligence Design)
// MARK: - Pill View (Liquid Intelligence Design 2.0)
// MARK: - Pill View (Apple Intelligence Style)
// MARK: - Pill View (Apple Intelligence Style - Refined)
struct PillView: View {
    @ObservedObject var controller: OverlayWindowController
    @EnvironmentObject var appState: AppState
    
    @State private var isHovering = false
    
    // Compute showControls based on expansion OR lock
    var showControls: Bool {
        controller.isExpanded || controller.isLocked
    }
    
    var body: some View {
        // CONTENT LAYER (Drives the size)
        HStack(spacing: 0) {
            
            // CANCEL BUTTON (Left)
            if showControls && !appState.isProcessing {
                Button {
                    withAnimation { controller.onCancel?() }
                } label: {
                    Image(systemName: "xmark")
                        .font(.system(size: 13, weight: .semibold))
                        .foregroundColor(.white.opacity(0.72))
                        .frame(width: 32, height: 44)
                        .contentShape(Rectangle())
                }
                .buttonStyle(PlainButtonStyle())
                .transition(.move(edge: .leading).combined(with: .opacity).combined(with: .scale(scale: 0.8)))
            }
            
            // CENTER CONTENT (The Core)
            WaveformContainer(
                audioRecorder: appState.audioRecorder,
                isProcessing: appState.isProcessing
            )
            .padding(.horizontal, showControls ? 2 : 4)
            .layoutPriority(1) // Ensure this keeps its size
            
            // STOP/LOCK BUTTON (Right)
            if showControls && !appState.isProcessing {
                Button {
                    withAnimation { controller.onStop?() }
                } label: {
                    Image(systemName: "stop.circle.fill")
                        .symbolRenderingMode(.hierarchical)
                        .foregroundStyle(WordflowTheme.primary.opacity(0.96))
                        .font(.system(size: 24))
                        .frame(width: 32, height: 44)
                        .contentShape(Rectangle())
                }
                .buttonStyle(PlainButtonStyle())
                .transition(.move(edge: .trailing).combined(with: .opacity).combined(with: .scale(scale: 0.8)))
            }
        }
        .padding(.horizontal, 8)
        .padding(.vertical, 4)
        .frame(height: 40) // Slimmer fixed height
        
        // BACKGROUND (Adapts to Content Size)
        .background(
            ZStack {
                // A. Real Mac Glass Material
                VisualEffectBlur(material: .popover, blendingMode: .behindWindow)
                
                // B. Liquid Glow (clipped to capsule, behind glass)
                if appState.isRecording || appState.isProcessing {
                    LiquidGlowContainer(
                        audioRecorder: appState.audioRecorder,
                        isActive: true
                    )
                    .opacity(0.8)
                }
            }
            .clipShape(Capsule())
        )
        .clipShape(Capsule()) // Double clip to ensure nothing bleeds out
        
        // BORDERS & HIGHLIGHTS
        .overlay(
            ZStack {
                // Inner Rim Light (Subtle)
                Capsule()
                    .strokeBorder(
                        LinearGradient(
                            colors: [.white.opacity(0.4), .white.opacity(0.05)],
                            startPoint: .top,
                            endPoint: .bottom
                        ),
                        lineWidth: 0.7
                    )
                
                // Locked Indicator
                if controller.isLocked {
                    Capsule()
                        .strokeBorder(WordflowTheme.primary.opacity(0.66), lineWidth: 1.2)
                }
            }
        )
        
        // INTERACTION
        .scaleEffect(isHovering ? 1.02 : 1.0)
        .animation(.spring(response: 0.4, dampingFraction: 0.6), value: showControls)
        .animation(.default, value: controller.isLocked)
        .onHover { hover in
            withAnimation(.easeInOut(duration: 0.2)) {
                isHovering = hover
            }
        }
        // Ensure the VIEW itself has a transparent frame around it
        .frame(maxWidth: .infinity, maxHeight: .infinity)
        .background(Color.clear)
    }
}

// MARK: - Waveform Container (Observes AudioRecorder directly)
// Critical for SwiftUI updates!
struct WaveformContainer: View {
    @ObservedObject var audioRecorder: AudioRecorder
    var isProcessing: Bool
    
    var body: some View {
        ZStack {
            if isProcessing {
                AppleProcessingView()
                    .transition(.scale(scale: 0.5).combined(with: .opacity))
            } else {
                // Waveform Visualization (5 Bars)
                WaveformView(audioLevel: audioRecorder.audioLevel)
                    .transition(.scale(scale: 0.5).combined(with: .opacity))
            }
        }
        .frame(width: 50, height: 44)
    }
}

// MARK: - Liquid Glow Performance Visuals
struct LiquidGlowContainer: View {
    @ObservedObject var audioRecorder: AudioRecorder
    var isActive: Bool
    
    var body: some View {
        LiquidGlowView(
            isActive: isActive,
            audioLevel: Double(audioRecorder.audioLevel)
        )
    }
}

struct LiquidGlowView: View {
    var isActive: Bool
    var audioLevel: Double
    
    var body: some View {
        let level = isActive ? max(0.1, audioLevel * 2.5) : 0.05
        
        GeometryReader { geo in
            ZStack {
                // A single, beautifully smooth core gradient 
                Circle()
                    .fill(
                        RadialGradient(
                            gradient: Gradient(colors: [
                                WordflowTheme.primary.opacity(0.7),
                                WordflowTheme.primary.opacity(0)
                            ]),
                            center: .center,
                            startRadius: 0,
                            endRadius: geo.size.width * 0.8
                        )
                    )
                    .frame(width: geo.size.width * 2, height: geo.size.width * 2)
            }
            .frame(width: geo.size.width, height: geo.size.height)
            .scaleEffect(isActive ? 1.0 + (level * 0.3) : 1.0)
            .opacity(isActive ? 0.3 + (level * 0.5) : 0.1)
        }
    }
}

// MARK: - Waveform View (5 Bars)
struct WaveformView: View {
    var audioLevel: Float
    
    // Configuration
    private let barCount = 5
    private let barWidth: CGFloat = 4
    private let spacing: CGFloat = 4
    private let minHeight: CGFloat = 4 // Height when silent (looks like a dot)
    private let maxHeight: CGFloat = 28 // Max height at full volume
    
    var body: some View {
        HStack(spacing: spacing) {
            ForEach(0..<barCount, id: \.self) { index in
                WaveformBar(
                    index: index,
                    totalBars: barCount,
                    level: Double(audioLevel),
                    minHeight: minHeight,
                    maxHeight: maxHeight,
                    width: barWidth
                )
            }
        }
    }
}

struct WaveformBar: View {
    var index: Int
    var totalBars: Int
    var level: Double
    var minHeight: CGFloat
    var maxHeight: CGFloat
    var width: CGFloat
    
    // Simulated frequency characteristics per bar
    private var frequencyConfig: (sensitivity: Double, delay: Double, dampening: Double) {
        // Index 0 = left (bass), Index 4 = right (treble)
        switch index {
        case 0: // Bass - slow, heavy, delayed
            return (sensitivity: 0.7, delay: 0.08, dampening: 0.9)
        case 1: // Low-mid
            return (sensitivity: 0.85, delay: 0.04, dampening: 0.8)
        case 2: // Mid (voice center) - most responsive
            return (sensitivity: 1.0, delay: 0.0, dampening: 0.6)
        case 3: // High-mid
            return (sensitivity: 0.9, delay: 0.02, dampening: 0.7)
        case 4: // Treble - quick, snappy
            return (sensitivity: 0.75, delay: 0.0, dampening: 0.5)
        default:
            return (sensitivity: 1.0, delay: 0.0, dampening: 0.7)
        }
    }
    var body: some View {
        let config = frequencyConfig
        
        // Add random variation to differentiate the bars slightly without 60fps timer
        let adjustedLevel = level * config.sensitivity * 0.7
        let visibleLevel = adjustedLevel > 0 ? pow(adjustedLevel, 0.7) : 0
        
        let extraHeight = CGFloat(visibleLevel) * (maxHeight - minHeight)
        let targetHeight = minHeight + extraHeight
        
        Capsule()
            .fill(Color.white.opacity(0.95))
            .frame(width: width, height: max(minHeight, min(maxHeight, targetHeight)))
    }
}

// MARK: - Processing View (Shimmering)
struct AppleProcessingView: View {
    @State private var isAnimating = false
    
    var body: some View {
        HStack(spacing: 5) {
            ForEach(0..<3) { i in
                Circle()
                    .fill(
                        LinearGradient(
                            colors: [.white, .white.opacity(0.5)],
                            startPoint: .topLeading,
                            endPoint: .bottomTrailing
                        )
                    )
                    .frame(width: 6, height: 6)
                    .scaleEffect(isAnimating ? 1.0 : 0.8)
                    .opacity(isAnimating ? 1.0 : 0.5)
                    .animation(
                        Animation.easeInOut(duration: 0.6)
                            .repeatForever()
                            .delay(Double(i) * 0.2),
                        value: isAnimating
                    )
            }
        }
        .onAppear {
            isAnimating = true
        }
    }
}

// Helper for Visual Effect Blur
struct VisualEffectBlur: NSViewRepresentable {
    var material: NSVisualEffectView.Material
    var blendingMode: NSVisualEffectView.BlendingMode
    
    func makeNSView(context: Context) -> NSVisualEffectView {
        let visualEffectView = NSVisualEffectView()
        visualEffectView.material = material
        visualEffectView.blendingMode = blendingMode
        visualEffectView.state = .active
        return visualEffectView
    }
    
    func updateNSView(_ visualEffectView: NSVisualEffectView, context: Context) {
        visualEffectView.material = material
        visualEffectView.blendingMode = blendingMode
    }
}

