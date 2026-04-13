# Wordflow

**Speech-to-Text for Mac — fast, private, AI-powered.**

Wordflow lets you dictate text anywhere on your Mac with a single hotkey. Your voice is transcribed via Groq and optionally polished by an AI prompt profile — then pasted directly into whatever app you're using.

> Currently in Early Access. Bring Your Own Key (Groq API).

---

## Features

- One hotkey to start/stop recording from anywhere
- Transcription via Groq Whisper API (fast & accurate)
- AI text correction with customizable prompt profiles
- Transcription history
- Auto-paste into any app
- Menu bar app — always available, never in the way
- Account system with Magic Link + Google OAuth

---

## Requirements

- macOS 26 or later
- Xcode 15+
- A free [Groq API Key](https://console.groq.com)

---

## Getting Started

### 1. Clone the repo

```bash
git clone https://github.com/Light-Down/word-flow.git
cd word-flow
```

### 2. Set up your secrets

Copy the template and fill in your Supabase credentials:

```bash
cp Wordflow/Config/Secrets.xcconfig.template Wordflow/Config/Secrets.xcconfig
```

Open `Secrets.xcconfig` and replace the placeholders with your values.
You can find them in your Supabase project under **Settings → API**.

> `Secrets.xcconfig` is in `.gitignore` and will never be committed.

### 3. Link the config in Xcode

1. Open `Wordflow.xcodeproj`
2. Select the project (top of navigator) → **PROJECT → Wordflow** → **Info** tab
3. Under **Configurations**, set both **Debug** and **Release** (blue icons) to `Secrets`

### 4. Build & Run

```
Cmd + R
```

---

## Running Your Own Backend

Wordflow uses [Supabase](https://supabase.com) for auth and license management.

### 1. Create the database schema

Run the migration in your Supabase SQL Editor:

```
supabase/migrations/001_initial_schema.sql
```

This creates the `licenses`, `devices`, `app_versions`, and `downloads` tables with RLS policies.

### 2. Deploy the Edge Function

```bash
supabase functions deploy check-session --project-ref YOUR_PROJECT_REF
```

The function source is at `supabase/functions/check-session/index.ts`.

### 3. Configure auth

In your Supabase dashboard under **Authentication → URL Configuration**:
- Add `wordflow://activate` as a redirect URL

See `Wordflow/Shared/SupabaseService.swift` for the full API surface.

---

## Project Structure

```
Wordflow/
├── macOS/              # macOS-specific views (MenuBar, Settings, Overlay)
├── Shared/             # Core logic (SupabaseService, TranscriptionService, LicenseManager, ...)
├── Assets.xcassets/    # Icons, images
├── Sounds/             # Audio feedback
└── Config/
    ├── Secrets.xcconfig.template   # Copy this and rename to Secrets.xcconfig
    └── Secrets.xcconfig            # Your local secrets (gitignored)
```

---

## License

Wordflow is open source under the [GNU General Public License v3.0](LICENSE).

You are free to use, modify and distribute this software under the terms of the GPLv3.
Any derivative work must also be released under the GPLv3.

---

## Download

Pre-built releases: [word-flow.store](https://word-flow.store)

---

Made by [Mark Olenberg](https://github.com/Light-Down)
