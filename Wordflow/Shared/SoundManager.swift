//
//  SoundManager.swift
//  Wordflow
//
//  Handles sound effects and haptic feedback for recording states.
//  Supports custom sounds from bundle and system sound fallback.
//

import Foundation
#if os(macOS)
import AppKit
#endif
import AVFoundation

/// Manages sound effects and haptic feedback for the app.
class SoundManager {
    static let shared = SoundManager()
    
    private var audioPlayer: AVAudioPlayer?
    
    // Sound preferences
    var soundsEnabled: Bool {
        UserDefaults.standard.object(forKey: "soundsEnabled") as? Bool ?? true
    }
    
    private init() {}
    
    // MARK: - Haptic Feedback
    
    enum HapticType {
        case start
        case stop
        case error
        case success
    }
    
    func playHaptic(type: HapticType) {
        // Only play if sounds are enabled (treat as "Feedback" setting)
        guard soundsEnabled else { return }
        
        #if os(macOS)
        let performer = NSHapticFeedbackManager.defaultPerformer
        
        switch type {
        case .start:
            performer.perform(.alignment, performanceTime: .now)
        case .stop:
            performer.perform(.levelChange, performanceTime: .now)
        case .error:
            // Quick double vibration simulation using delay not ideal on main thread,
            // so just use a strong generic one
            performer.perform(.generic, performanceTime: .now)
        case .success:
             performer.perform(.alignment, performanceTime: .now)
        }
        #elseif os(iOS)
        // Future iOS implementation using UIImpactFeedbackGenerator
        // let generator = UIImpactFeedbackGenerator(style: .medium)
        // generator.impactOccurred()
        #endif
    }
    
    // MARK: - Sound Effects
    
    func playStartRecording() {
        guard soundsEnabled else { return }
        // Swoosh in effect - "Blow" is a soft whoosh
        if playCustomSound("start") == nil {
            playSystemSound("Blow", volume: 0.25)
        }
    }
    
    func playStopRecording() {
        guard soundsEnabled else { return }
        // Pop out effect / Stop
        if playCustomSound("stop") == nil {
            // Optional: System sound fallback for stop, or keep silent if preferred
            // playSystemSound("Tink", volume: 0.15) 
        }
    }
    
    func playLock() {
        guard soundsEnabled else { return }
        // Soft purr for lock
        if playCustomSound("lock") == nil {
            playSystemSound("Purr", volume: 0.15)
        }
    }
    
    func playUnlock() {
        // Silent
    }
    
    func playCancel() {
        // Silent
    }
    
    func playError() {
        guard soundsEnabled else { return }
        if playCustomSound("error") == nil {
            playSystemSound("Basso", volume: 0.25)
        }
    }
    
    func playSuccess() {
        guard soundsEnabled else { return }
        // Success - Hero is a satisfying completion sound
        if playCustomSound("success") == nil {
            playSystemSound("Hero", volume: 0.2)
        }
    }
    
    // MARK: - Custom Sound Loading
    
    @discardableResult
    private func playCustomSound(_ name: String) -> Bool? {
        // Try to find custom sound in Sounds folder
        let extensions = ["aiff", "mp3", "wav", "m4a"]
        
        for ext in extensions {
            if let url = Bundle.main.url(forResource: name, withExtension: ext, subdirectory: "Sounds") {
                do {
                    audioPlayer = try AVAudioPlayer(contentsOf: url)
                    audioPlayer?.volume = 0.3
                    audioPlayer?.play()
                    return true
                } catch {
                    print("Error playing custom sound: \(error)")
                }
            }
        }
        return nil  // No custom sound found, will fall back to system sound
    }
    
    // MARK: - System Sound Fallback
    
    private func playSystemSound(_ name: String, volume: Float = 0.5) {
        #if os(macOS)
        if let sound = NSSound(named: NSSound.Name(name)) {
            sound.volume = volume
            sound.play()
        }
        #endif
    }
}
