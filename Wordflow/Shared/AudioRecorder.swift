//
//  AudioRecorder.swift
//  Wordflow
//
//  Handles audio recording using AVFoundation.
//  Records to M4A format with real-time level metering for waveform visualization.
//

import Foundation
import Swift
import AVFoundation
import Combine
import SwiftUI
#if os(iOS)
import UIKit
#endif


/// Manages audio recording with real-time level metering for waveform display.
class AudioRecorder: NSObject, ObservableObject {
    @Published var isRecording = false
    @Published var audioLevel: Float = 0.0
    @Published var audioFileURL: URL?
    @Published var lastRecordingDuration: TimeInterval = 0
    
    private var audioRecorder: AVAudioRecorder?
    private var levelTimer: Timer?
    private var recordingStartTime: Date?
    
    // Minimum recording duration to send to API (in seconds)
    static let minimumDuration: TimeInterval = 0.4
    
    override init() {
        super.init()
    }
    
    func startRecording() {
        // Request microphone permission
        AVCaptureDevice.requestAccess(for: .audio) { [weak self] granted in
            guard granted else {
                print("Microphone permission denied")
                return
            }
            
            DispatchQueue.main.async {
                self?.beginRecording()
            }
        }
    }
    
    private func beginRecording() {
        // iOS: Activate Audio Session
        #if os(iOS)
        do {
            let session = AVAudioSession.sharedInstance()
            try session.setCategory(.playAndRecord, mode: .default, options: [.defaultToSpeaker, .allowBluetooth])
            try session.setActive(true)
        } catch {
            print("Failed to set up audio session: \(error)")
        }
        #endif
        
        // All providers now use M4A - Google v2 API supports it
        let audioFilename = getDocumentsDirectory().appendingPathComponent("recording.m4a")
        audioFileURL = audioFilename
        
        // M4A/AAC for all providers (Google v2 API supports it)
        let settings: [String: Any] = [
            AVFormatIDKey: Int(kAudioFormatMPEG4AAC),
            AVSampleRateKey: 44100,
            AVNumberOfChannelsKey: 1,
            AVEncoderAudioQualityKey: AVAudioQuality.high.rawValue
        ]
        
        do {
            audioRecorder = try AVAudioRecorder(url: audioFilename, settings: settings)
            audioRecorder?.isMeteringEnabled = true
            audioRecorder?.prepareToRecord()
            audioRecorder?.record()
            isRecording = true
            recordingStartTime = Date()
            
            // Start level metering timer (15 Hz) to reduce CPU load significantly
            levelTimer = Timer.scheduledTimer(withTimeInterval: 1.0/15.0, repeats: true) { [weak self] _ in
                self?.updateAudioLevel()
            }
            
            LogManager.shared.log("Audio Recording started (M4A/44.1kHz) to: \(audioFilename.path)")
            
        } catch {
            print("Failed to start recording: \(error)")
            LogManager.shared.log("Failed to start recording: \(error)")
        }
    }
    
    private func updateAudioLevel() {
        guard let recorder = audioRecorder, recorder.isRecording else { return }
        
        recorder.updateMeters()
        let power = recorder.averagePower(forChannel: 0)
        
        // Normalize from -160...0 dB to 0...1
        // Original simple logic that worked well for the user
        let normalizedPower = max(0, (power + 50) / 50)
        
        DispatchQueue.main.async {
            withAnimation(.linear(duration: 1.0/15.0)) {
                self.audioLevel = normalizedPower
            }
        }
    }
    
    func stopRecording() {
        levelTimer?.invalidate()
        levelTimer = nil
        
        // Calculate duration
        if let startTime = recordingStartTime {
            lastRecordingDuration = Date().timeIntervalSince(startTime)
        } else {
            lastRecordingDuration = 0
        }
        recordingStartTime = nil
        
        audioRecorder?.stop()
        isRecording = false
        audioLevel = 0.0
        
        LogManager.shared.log("Recording stopped. Duration: \(String(format: "%.2f", lastRecordingDuration))s")
    }
    
    /// Returns true if the last recording was long enough to process
    var isLastRecordingValid: Bool {
        return lastRecordingDuration >= Self.minimumDuration
    }
    
    private func getDocumentsDirectory() -> URL {
        // Use temporary directory to avoid "Files and Folders" permission issues on macOS
        FileManager.default.temporaryDirectory
    }
}
