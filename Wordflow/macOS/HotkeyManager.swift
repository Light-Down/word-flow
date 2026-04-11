import Foundation
import Carbon
import AppKit
import CoreGraphics

// MARK: - Hotkey Configuration
struct HotkeyConfig: Codable, Equatable {
    var modifiers: UInt  // NSEvent.ModifierFlags raw value
    var keyCode: UInt16
    var useFnKey: Bool  // Special flag for Fn key mode
    var useModifierOnly: Bool  // Modifier-only combo mode (e.g. ⇧+⌥)
    
    // Custom decoding for backward compatibility (useModifierOnly may be missing)
    init(modifiers: UInt, keyCode: UInt16, useFnKey: Bool, useModifierOnly: Bool = false) {
        self.modifiers = modifiers
        self.keyCode = keyCode
        self.useFnKey = useFnKey
        self.useModifierOnly = useModifierOnly
    }
    
    init(from decoder: Decoder) throws {
        let container = try decoder.container(keyedBy: CodingKeys.self)
        modifiers = try container.decode(UInt.self, forKey: .modifiers)
        keyCode = try container.decode(UInt16.self, forKey: .keyCode)
        useFnKey = try container.decode(Bool.self, forKey: .useFnKey)
        useModifierOnly = try container.decodeIfPresent(Bool.self, forKey: .useModifierOnly) ?? false
    }
    
    static let defaultConfig = HotkeyConfig(
        modifiers: 0,
        keyCode: 63,  // Fn key
        useFnKey: true,
        useModifierOnly: false
    )
    
    /// True if this config uses flagsChanged-based detection (Fn or modifier-only)
    var isFlagsBased: Bool {
        return useFnKey || useModifierOnly
    }
    
    var displayString: String {
        if useFnKey {
            return "Fn (Globe)"
        }
        
        var parts: [String] = []
        let flags = NSEvent.ModifierFlags(rawValue: modifiers)
        
        if flags.contains(.control) { parts.append("⌃") }
        if flags.contains(.option) { parts.append("⌥") }
        if flags.contains(.shift) { parts.append("⇧") }
        if flags.contains(.command) { parts.append("⌘") }
        if flags.contains(.function) { parts.append("Fn") }
        
        // For modifier-only mode, don't append a key name
        if useModifierOnly {
            return parts.joined(separator: " + ")
        }
        
        // Key name for modifier+key mode
        let keyName = keyCodeToString(keyCode)
        parts.append(keyName)
        
        return parts.joined(separator: " + ")
    }
    
    /// Descriptive name for the modifier combo (used in UI)
    var modifierDescription: String {
        var parts: [String] = []
        let flags = NSEvent.ModifierFlags(rawValue: modifiers)
        if flags.contains(.shift) { parts.append("Shift") }
        if flags.contains(.control) { parts.append("Control") }
        if flags.contains(.option) { parts.append("Option") }
        if flags.contains(.command) { parts.append("Command") }
        return parts.joined(separator: " + ")
    }
    
    private func keyCodeToString(_ keyCode: UInt16) -> String {
        switch keyCode {
        case 49: return "Leertaste"
        case 50: return "<"
        case 36: return "Return"
        case 48: return "Tab"
        case 51: return "Delete"
        case 53: return "Escape"
        case 63: return "Fn"
        case 123: return "←"
        case 124: return "→"
        case 125: return "↓"
        case 126: return "↑"
        default:
            if let char = keyCodeToCharacter(keyCode) {
                return char.uppercased()
            }
            return "Key \(keyCode)"
        }
    }
    
    private func keyCodeToCharacter(_ keyCode: UInt16) -> String? {
        let source = TISCopyCurrentKeyboardInputSource().takeRetainedValue()
        guard let layoutData = TISGetInputSourceProperty(source, kTISPropertyUnicodeKeyLayoutData) else {
            return nil
        }
        
        let dataRef = unsafeBitCast(layoutData, to: CFData.self)
        let keyboardLayout = unsafeBitCast(CFDataGetBytePtr(dataRef), to: UnsafePointer<UCKeyboardLayout>.self)
        
        var deadKeyState: UInt32 = 0
        var chars = [UniChar](repeating: 0, count: 4)
        var length: Int = 0
        
        let error = UCKeyTranslate(
            keyboardLayout,
            keyCode,
            UInt16(kUCKeyActionDown),
            0,
            UInt32(LMGetKbdType()),
            UInt32(kUCKeyTranslateNoDeadKeysBit),
            &deadKeyState,
            4,
            &length,
            &chars
        )
        
        if error == noErr && length > 0 {
            return String(utf16CodeUnits: chars, count: length)
        }
        return nil
    }
}

class HotkeyManager {
    static let configDidChangeNotification = Notification.Name("HotkeyManager.configDidChange")

    private var globalKeyMonitor: Any?
    private var localKeyMonitor: Any?
    private var globalFlagMonitor: Any?
    private var localFlagMonitor: Any?
    private var configObserver: NSObjectProtocol?
    private var eventTap: CFMachPort?
    
    private let onHotkeyChange: (Bool) -> Void
    private let onLockChange: ((Bool) -> Void)?
    private let onExpandChange: ((Bool) -> Void)?
    private let onCancel: (() -> Void)?
    private var currentModifiers: NSEvent.ModifierFlags = []
    private var isRecording = false
    var isLocked = false
    private var config: HotkeyConfig
    var fnKeyPressed = false
    var modifierComboActive = false  // Tracks if modifier-only combo is held
    
    init(onHotkeyChange: @escaping (Bool) -> Void, onLockChange: ((Bool) -> Void)? = nil, onExpandChange: ((Bool) -> Void)? = nil, onCancel: (() -> Void)? = nil) {
        self.onHotkeyChange = onHotkeyChange
        self.onLockChange = onLockChange
        self.onExpandChange = onExpandChange
        self.onCancel = onCancel
        self.config = HotkeyManager.loadConfig()
        
        // Auto-reload config when it changes from Settings
        configObserver = NotificationCenter.default.addObserver(
            forName: HotkeyManager.configDidChangeNotification,
            object: nil,
            queue: .main
        ) { [weak self] _ in
            guard let self = self else { return }
            let newConfig = HotkeyManager.loadConfig()
            print("🎹 Hotkey config updated: \(newConfig.displayString)")
            if !self.isRecording {
                self.config = newConfig
                self.fnKeyPressed = false
                self.modifierComboActive = false
                self.currentModifiers = []
            }
        }
    }
    
    static func loadConfig() -> HotkeyConfig {
        if let data = UserDefaults.standard.data(forKey: "hotkeyConfig"),
           let config = try? JSONDecoder().decode(HotkeyConfig.self, from: data) {
            return config
        }
        return HotkeyConfig.defaultConfig
    }
    
    static func saveConfig(_ config: HotkeyConfig) {
        if let data = try? JSONEncoder().encode(config) {
            UserDefaults.standard.set(data, forKey: "hotkeyConfig")
        }
        NotificationCenter.default.post(name: HotkeyManager.configDidChangeNotification, object: nil)
    }
    
    func updateConfig(_ config: HotkeyConfig) {
        self.config = config
        HotkeyManager.saveConfig(config)
    }
    
    func start() {
        print("🎹 HotkeyManager gestartet: \(config.displayString) (mode: \(config.useFnKey ? "Fn" : config.useModifierOnly ? "ModifierOnly" : "Key+Mod"))")

        // Monitor for modifier key changes (Fn, modifier-only combos, and modifier release)
        globalFlagMonitor = NSEvent.addGlobalMonitorForEvents(matching: .flagsChanged) { [weak self] event in
            self?.handleFlagsChanged(event)
        }

        localFlagMonitor = NSEvent.addLocalMonitorForEvents(matching: .flagsChanged) { [weak self] event in
            self?.handleFlagsChanged(event)
            return event
        }

        // Monitor for key events (cancel via Escape/Cmd+.)
        globalKeyMonitor = NSEvent.addGlobalMonitorForEvents(matching: [.keyDown, .keyUp]) { [weak self] event in
            self?.handleKeyEvent(event)
        }

        localKeyMonitor = NSEvent.addLocalMonitorForEvents(matching: [.keyDown, .keyUp]) { [weak self] event in
            self?.handleKeyEvent(event)
            return event
        }

        // CGEventTap: intercept and suppress Space keydown when used as lock trigger
        startEventTap()
    }

    func stop() {
        [globalFlagMonitor, localFlagMonitor, globalKeyMonitor, localKeyMonitor].forEach { monitor in
            if let m = monitor { NSEvent.removeMonitor(m) }
        }
        globalFlagMonitor = nil
        localFlagMonitor = nil
        globalKeyMonitor = nil
        localKeyMonitor = nil
        stopEventTap()
    }

    private func startEventTap() {
        guard AXIsProcessTrusted() else {
            print("⚠️ Kein Accessibility-Zugriff – Space-Unterdrückung nicht aktiv")
            return
        }

        let selfPtr = Unmanaged.passRetained(self).toOpaque()

        let tap = CGEvent.tapCreate(
            tap: .cgSessionEventTap,
            place: .headInsertEventTap,
            options: .defaultTap,
            eventsOfInterest: CGEventMask(1 << CGEventType.keyDown.rawValue),
            callback: { _, type, event, userInfo -> Unmanaged<CGEvent>? in
                guard type == .keyDown,
                      let userInfo = userInfo else { return Unmanaged.passRetained(event) }
                let manager = Unmanaged<HotkeyManager>.fromOpaque(userInfo).takeUnretainedValue()
                let keyCode = event.getIntegerValueField(.keyboardEventKeycode)
                // Space keycode = 49
                if keyCode == 49 && manager.config.isFlagsBased && manager.isRecording && !manager.isLocked {
                    let isHotkeyHeld = manager.config.useFnKey ? manager.fnKeyPressed : manager.modifierComboActive
                    if isHotkeyHeld {
                        // Suppress the Space so it doesn't get inserted
                        return nil
                    }
                }
                return Unmanaged.passRetained(event)
            },
            userInfo: selfPtr
        )

        guard let tap = tap else {
            Unmanaged<HotkeyManager>.fromOpaque(selfPtr).release()
            print("⚠️ CGEventTap konnte nicht erstellt werden")
            return
        }

        self.eventTap = tap
        let runLoopSource = CFMachPortCreateRunLoopSource(kCFAllocatorDefault, tap, 0)
        CFRunLoopAddSource(CFRunLoopGetMain(), runLoopSource, .commonModes)
        CGEvent.tapEnable(tap: tap, enable: true)
        print("✅ CGEventTap aktiv – Space wird beim Lock unterdrückt")
    }

    private func stopEventTap() {
        if let tap = eventTap {
            CGEvent.tapEnable(tap: tap, enable: false)
            eventTap = nil
        }
    }
    
    private func handleFlagsChanged(_ event: NSEvent) {
        let newModifiers = event.modifierFlags.intersection(.deviceIndependentFlagsMask)
        let hadFunction = currentModifiers.contains(.function)
        let hasFunction = newModifiers.contains(.function)
        let hadCommand = currentModifiers.contains(.command)
        let hasCommand = newModifiers.contains(.command)
        
        currentModifiers = newModifiers
        
        // ============================================================
        // MODE 1: Fn (Globe) Key
        // ============================================================
        if config.useFnKey {
            // Command pressed while recording → Expand
            if isRecording && hasCommand && !hadCommand {
                onExpandChange?(true)
            } else if isRecording && !hasCommand && hadCommand {
                onExpandChange?(false)
            }
            
            // Fn + Command → Toggle Lock
            if hasFunction && hasCommand && !hadCommand && isRecording && !isLocked && fnKeyPressed {
                print("🔒 Aufnahme GELOCKT (Fn + ⌘)")
                isLocked = true
                onLockChange?(true)
                onExpandChange?(true)
                return
            }
            
            // Fn key pressed
            if hasFunction && !hadFunction {
                if !isRecording {
                    print("🔴 Aufnahme START (Fn)")
                    isRecording = true
                    isLocked = false
                    fnKeyPressed = true
                    onHotkeyChange(true)
                } else if isLocked {
                    print("⏹️ Aufnahme STOP (Fn - war gelockt)")
                    isLocked = false
                    onLockChange?(false)
                    isRecording = false
                    fnKeyPressed = false
                    onHotkeyChange(false)
                }
            }
            // Fn key released
            else if !hasFunction && hadFunction && isRecording && fnKeyPressed {
                if isLocked {
                    print("🔒 Aufnahme läuft weiter (gelockt)")
                    fnKeyPressed = false
                } else {
                    print("⏹️ Aufnahme STOP (Fn)")
                    isRecording = false
                    fnKeyPressed = false
                    onHotkeyChange(false)
                }
            }
            return
        }
        
        // ============================================================
        // MODE 2: Modifier-Only Combo (e.g. ⇧+⌥, ⇧+⌃, ⇧+⌘)
        // ============================================================
        if config.useModifierOnly {
            let requiredModifiers = NSEvent.ModifierFlags(rawValue: config.modifiers)
                .intersection(.deviceIndependentFlagsMask)
            // Remove .function from comparison since modifier-only combos don't use it
            let cleanRequired = requiredModifiers.subtracting(.function)
            let cleanCurrent = newModifiers.subtracting(.function)
            
            let hadAllRequired = modifierComboActive
            let hasAllRequired = cleanCurrent.contains(cleanRequired) && !cleanRequired.isEmpty
            
            if hasAllRequired && !hadAllRequired {
                // All required modifiers are now held
                if !isRecording {
                    print("🔴 Aufnahme START (\(config.displayString))")
                    isRecording = true
                    isLocked = false
                    modifierComboActive = true
                    onHotkeyChange(true)
                } else if isLocked {
                    // Was locked, pressing combo again → stop
                    print("⏹️ Aufnahme STOP (\(config.displayString) - war gelockt)")
                    isLocked = false
                    onLockChange?(false)
                    isRecording = false
                    modifierComboActive = false
                    onHotkeyChange(false)
                }
            }
            else if !hasAllRequired && hadAllRequired && isRecording {
                // A required modifier was released
                if isLocked {
                    print("🔒 Aufnahme läuft weiter (gelockt)")
                    modifierComboActive = false
                } else {
                    print("⏹️ Aufnahme STOP (Modifier losgelassen)")
                    isRecording = false
                    modifierComboActive = false
                    onHotkeyChange(false)
                }
            }
            return
        }
        
        // ============================================================
        // MODE 3: Modifier + Key (legacy, not recommended)
        // ============================================================
        let requiredModifiers = NSEvent.ModifierFlags(rawValue: config.modifiers)
        if isRecording && !isLocked && !currentModifiers.contains(requiredModifiers) {
            print("⏹️ Aufnahme STOP (Modifier losgelassen)")
            isRecording = false
            onHotkeyChange(false)
        }
    }
    
    private func handleKeyEvent(_ event: NSEvent) {
        // GLOBAL CANCEL: ESCAPE (53) or CMD + . (47)
        let isEscape = event.keyCode == 53
        let isCmdPeriod = event.keyCode == 47 && event.modifierFlags.contains(.command)
        
        if (isEscape || isCmdPeriod) && isRecording && event.type == .keyDown && !event.isARepeat {
            print("🚫 Cancel detected (Code: \(event.keyCode))")
            onCancel?()
            return
        }
        
        // Handle Space key for locking in Fn mode AND modifier-only mode
        // (Space is suppressed by CGEventTap so no character gets inserted)
        if config.isFlagsBased && event.keyCode == 49 && event.type == .keyDown && !event.isARepeat {
            let isHotkeyHeld = config.useFnKey ? fnKeyPressed : modifierComboActive
            if isRecording && !isLocked && isHotkeyHeld {
                print("🔒 Aufnahme GELOCKT (\(config.displayString) + Leertaste)")
                isLocked = true
                onLockChange?(true)
                return
            }
        }
        
        // Skip rest if we're in flags-based mode (Fn or modifier-only)
        if config.isFlagsBased { return }
        
        // ============================================================
        // MODE 3: Modifier + Key handling
        // ============================================================
        if event.isARepeat { return }
        guard event.keyCode == config.keyCode else { return }
        
        let requiredModifiers = NSEvent.ModifierFlags(rawValue: config.modifiers)
        let modifiersMatch = currentModifiers.contains(requiredModifiers)
        
        if event.type == .keyDown && modifiersMatch && !isRecording {
            print("🔴 Aufnahme START (\(config.displayString))")
            isRecording = true
            onHotkeyChange(true)
        } else if event.type == .keyUp && isRecording {
            print("⏹️ Aufnahme STOP")
            isRecording = false
            onHotkeyChange(false)
        }
    }
    
    func stopRecording() {
        if isRecording {
            isRecording = false
            fnKeyPressed = false
            modifierComboActive = false
            onHotkeyChange(false)
        }
    }
    
    deinit {
        stop()
        if let observer = configObserver {
            NotificationCenter.default.removeObserver(observer)
        }
    }
}
