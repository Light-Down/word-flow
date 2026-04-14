# Changelog

All notable changes to Wordflow are documented here.

---

## [Unreleased]

### Added

- **Hotkey-Profile-System** — Profiles are now selected directly via keyboard shortcuts while holding Fn, without opening any menu:
  - `Fn` → **Smart Casual** (default, white glow)
  - `Fn + Control` → **Email** (blue glow)
  - `Fn + Command` → **Tech** (purple glow)
- **Auto-Lock on Fn press** — Recording starts and locks immediately on the first Fn press. A second Fn press stops recording and triggers transcription. Push-to-talk is removed.
- **Profile color indicator** — The recording pill changes color to reflect the active profile in real time.
- **Email profile** — Formats dictations as ready-to-send emails: detects recipient, adds greeting and closing, no subject line.
- **Tech profile** — Recognizes file names, paths, extensions, and terminal commands and formats them correctly (e.g. "src Schrägstrich components" → `src/components`).
- **Email signature** — A name field in Settings → Prompt-Profile lets you set a sender name that is automatically appended after the closing in every email.
- **Prompt injection guard** — All AI prompts now start with an explicit editor-role instruction to prevent the AI from responding to transcription content instead of formatting it.

### Changed

- **Smart Casual, Email, Tech** replace the previous Smart Casual, Smart Business, and Professional profiles.
- Profile picker removed from menu bar and settings — profile selection happens exclusively via hotkeys.
- Settings → Prompt-Profile now shows a keyboard shortcut overview with color indicators instead of a profile picker.
- Stop button in recording pill is now permanently white (was orange).

### Fixed

- Profile color and selected profile are correctly reset to Smart Casual when a recording is cancelled.
- Active modifier profile no longer reverts when the modifier key is released before Fn — the profile is latched on press.
- State variable ordering in `HotkeyManager.handleFlagsChanged` made explicit to avoid ambiguity between old and new modifier state.
