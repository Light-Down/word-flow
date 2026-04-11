# OAuth Handoff – Google & Apple Sign-In für Wordflow

## Übersicht

Wordflow nutzt Supabase Auth mit Magic Link (kein Supabase Swift SDK – reines URLSession/REST).  
Google und Apple OAuth funktionieren genauso: Supabase öffnet den Browser, der User logt sich ein,  
und der Callback kommt als `wordflow://activate#access_token=...&refresh_token=...` zurück —  
**identisch zum Magic Link**. `handleDeepLink` braucht keine Änderung.

Die zwei neuen Methoden `signInWithGoogle()` und `signInWithApple()` sind bereits in  
`Wordflow/Shared/SupabaseService.swift` eingebaut.

---

## Schritt 1 – Supabase Dashboard konfigurieren

**Supabase Projekt:** `https://supabase.com/dashboard/project/amieachokpogaspaplxr`

### 1a – Redirect URL freischalten

`Authentication → URL Configuration → Redirect URLs`  
→ Eintragen: `wordflow://activate`

### 1b – Google Provider aktivieren

`Authentication → Providers → Google`

Benötigt einen Google OAuth Client:
1. [Google Cloud Console](https://console.cloud.google.com) → APIs & Services → Credentials
2. OAuth 2.0 Client ID erstellen → Typ: **Web application**
3. Authorized redirect URI: `https://amieachokpogaspaplxr.supabase.co/auth/v1/callback`
4. Client ID + Client Secret in Supabase eintragen → Save

### 1c – Apple Provider aktivieren

`Authentication → Providers → Apple`

Benötigt einen Apple Developer Account:
1. [developer.apple.com](https://developer.apple.com) → Certificates, IDs & Profiles
2. **Services ID** erstellen (Identifier z.B. `com.markolenberg.Wordflow.web`)
   - Sign In with Apple aktivieren
   - Return URL: `https://amieachokpogaspaplxr.supabase.co/auth/v1/callback`
3. **Key** erstellen mit Sign In with Apple aktiviert → Key-Datei (.p8) herunterladen
4. In Supabase eintragen:
   - Services ID: `com.markolenberg.Wordflow.web`
   - Team ID: (aus Apple Developer Account, oben rechts)
   - Key ID: (ID des erstellten Keys)
   - Private Key: Inhalt der .p8-Datei

---

## Schritt 2 – LoginView anpassen

In `Wordflow/macOS/LoginView.swift` zwei Buttons hinzufügen.  
Diese rufen die bereits fertigen Methoden auf:

```swift
// Google-Button
Button {
    SupabaseService.shared.signInWithGoogle()
} label: {
    HStack {
        Image(systemName: "globe")
        Text("Mit Google anmelden")
    }
    .frame(maxWidth: .infinity)
}
.buttonStyle(.bordered)

// Apple-Button
Button {
    SupabaseService.shared.signInWithApple()
} label: {
    HStack {
        Image(systemName: "apple.logo")
        Text("Mit Apple anmelden")
    }
    .frame(maxWidth: .infinity)
}
.buttonStyle(.borderedProminent)
.tint(.black)
```

Platzierung: unterhalb des Magic-Link-Bereichs, getrennt durch einen `Divider()` mit `Text("oder")`.

---

## Was bereits fertig ist

- `SupabaseService.signInWithGoogle()` → öffnet Supabase OAuth URL im Browser
- `SupabaseService.signInWithApple()` → öffnet Supabase OAuth URL im Browser
- `handleDeepLink(url:)` → verarbeitet den Token-Callback automatisch (unverändert)
- Der Deep Link `wordflow://activate` ist bereits in `Info.plist` / `WordflowApp.swift` registriert (prüfen!)

## Zu prüfen

Stelle sicher, dass in `WordflowApp.swift` der Deep Link Handler aktiv ist, z.B.:
```swift
.onOpenURL { url in
    Task { await SupabaseService.shared.handleDeepLink(url: url) }
}
```
