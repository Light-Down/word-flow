<?php
require_once __DIR__ . '/../security.php';
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Setup Guide — Wordflow</title>
  <meta name="description" content="Get Wordflow up and running in 3 steps. Watch the setup video and follow the screenshots." />
  <link rel="stylesheet" href="/assets/fonts.css" />
  <link rel="stylesheet" href="/assets/app.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,300,0,0" rel="stylesheet" />
  <style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
    }
    /* ── Hero layout ── */
    .setup-hero {
      background: #FDFBF7;
      padding-top: 7rem;
      padding-bottom: 5rem;
    }
    /* ── Video placeholder ── */
    .video-frame {
      position: relative;
      width: 100%;
      aspect-ratio: 16 / 9;
      background: #1A1A1A;
      border-radius: 1rem;
      overflow: hidden;
      box-shadow: 0 32px 80px rgba(26,26,26,0.18), 0 0 0 1px rgba(26,26,26,0.08);
    }
    .video-frame iframe {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      border: none;
    }
    /* placeholder shown until video is added */
    .video-placeholder {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 1rem;
      background: linear-gradient(135deg, #1A1A1A 0%, #2d2d2d 100%);
      color: rgba(255,255,255,0.5);
      font-family: 'Inter', sans-serif;
      font-size: 0.875rem;
    }
    .video-placeholder .play-icon {
      width: 56px; height: 56px;
      border-radius: 50%;
      border: 2px solid rgba(255,255,255,0.2);
      display: flex; align-items: center; justify-content: center;
    }
    /* ── Step cards ── */
    .step-card {
      background: #fff;
      border: 1px solid rgba(26,26,26,0.08);
      border-radius: 1rem;
      padding: 2rem;
      box-shadow: 0 4px 20px rgba(26,26,26,0.04);
    }
    .step-number {
      font-family: 'Newsreader', serif;
      font-style: italic;
      font-size: 3rem;
      line-height: 1;
      color: rgba(210,105,30,0.25);
      font-weight: 400;
    }
    /* ── Screenshot frames ── */
    .screenshot-frame {
      border-radius: 0.75rem;
      border: 1px solid rgba(26,26,26,0.1);
      overflow: hidden;
      box-shadow: 0 8px 24px rgba(26,26,26,0.08);
      background: #f5f5f5;
      aspect-ratio: 16/10;
      display: flex; align-items: center; justify-content: center;
      font-family: 'Inter', sans-serif;
      font-size: 0.8125rem;
      color: rgba(26,26,26,0.3);
    }
    /* ── Pill badge ── */
    .badge-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      font-family: 'Inter', sans-serif;
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: #D2691E;
      background: rgba(210,105,30,0.08);
      border: 1px solid rgba(210,105,30,0.18);
      border-radius: 9999px;
      padding: 0.3rem 0.875rem;
    }
    /* ── CTA bar ── */
    .cta-bar {
      background: #1A1A1A;
      border-radius: 1.25rem;
      padding: 3rem;
    }
  </style>
</head>
<body class="bg-background text-on-surface antialiased">

<?php include __DIR__ . '/../_nav.php'; ?>

<!-- ═══════════════════════════════════════════════
     HERO — 2 columns: text left, video right
════════════════════════════════════════════════ -->
<section class="setup-hero">
  <div class="max-w-7xl mx-auto px-6 md:px-10">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

      <!-- Left: Text -->
      <div>
        <span class="badge-pill mb-6 inline-flex">
          <span class="material-symbols-outlined" style="font-size:13px;">play_circle</span>
          Setup Guide
        </span>

        <h1 class="font-headline text-4xl md:text-5xl text-on-surface leading-tight mb-5">
          Get started in<br>3 simple steps.
        </h1>

        <p class="font-body text-lg text-on-surface-variant leading-relaxed mb-8 max-w-lg">
          Watch the video — it walks you through everything from allowing the app on macOS to entering your API key and recording your first voice note.
        </p>

        <!-- Quick step overview -->
        <div class="space-y-4">
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-on-surface text-background flex items-center justify-center font-body font-semibold text-sm">1</div>
            <div>
              <p class="font-body font-semibold text-on-surface text-sm">Allow the app on macOS</p>
              <p class="font-body text-on-surface-variant text-sm">System Settings → Privacy & Security → "Open Anyway"</p>
            </div>
          </div>
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-on-surface text-background flex items-center justify-center font-body font-semibold text-sm">2</div>
            <div>
              <p class="font-body font-semibold text-on-surface text-sm">Get your free Groq API key</p>
              <p class="font-body text-on-surface-variant text-sm">Free account at console.groq.com — takes 60 seconds</p>
            </div>
          </div>
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-on-surface text-background flex items-center justify-center font-body font-semibold text-sm">3</div>
            <div>
              <p class="font-body font-semibold text-on-surface text-sm">Finish setup in Wordflow</p>
              <p class="font-body text-on-surface-variant text-sm">The wizard guides you through permissions & hotkey</p>
            </div>
          </div>
        </div>

        <!-- Groq link -->
        <a href="https://console.groq.com" target="_blank" rel="noopener"
          class="inline-flex items-center gap-2 mt-8 font-body text-sm font-medium text-primary hover:opacity-70 transition-opacity">
          Get your free Groq API key
          <span class="material-symbols-outlined" style="font-size:16px;">arrow_outward</span>
        </a>
      </div>

      <!-- Right: Video -->
      <div>
        <div class="video-frame">
          <?php
          // Replace VIDEO_ID with your YouTube video ID after uploading
          $youtubeVideoId = ''; // e.g. 'dQw4w9WgXcQ'
          if (!empty($youtubeVideoId)): ?>
            <iframe
              src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeVideoId) ?>?rel=0&modestbranding=1"
              title="Wordflow Setup Guide"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen>
            </iframe>
          <?php else: ?>
            <!-- Placeholder until video is uploaded -->
            <div class="video-placeholder">
              <div class="play-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="white" opacity="0.6"><path d="M8 5v14l11-7z"/></svg>
              </div>
              <span>Setup video coming soon</span>
            </div>
          <?php endif; ?>
        </div>
        <p class="font-body text-xs text-on-surface-variant text-center mt-3 opacity-50">Full walkthrough · ~3 minutes</p>
      </div>

    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════
     STEP-BY-STEP SCREENSHOTS
════════════════════════════════════════════════ -->
<section class="py-24 bg-surface-container/40">
  <div class="max-w-6xl mx-auto px-6 md:px-10">

    <div class="text-center mb-16">
      <h2 class="font-headline text-3xl md:text-4xl text-on-surface mb-4">Step by step.</h2>
      <p class="font-body text-on-surface-variant max-w-md mx-auto">Prefer screenshots? Here's every step with visuals.</p>
    </div>

    <div class="space-y-20">

      <!-- Step 1: Gatekeeper -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
        <div>
          <div class="step-number mb-3">01</div>
          <h3 class="font-headline text-2xl text-on-surface mb-3">Allow Wordflow on macOS</h3>
          <p class="font-body text-on-surface-variant leading-relaxed mb-5">
            Because Wordflow isn't yet code-signed with an Apple Developer certificate, macOS blocks it on first launch. This is normal for indie apps.
          </p>
          <ol class="space-y-3">
            <li class="flex items-start gap-3 font-body text-sm text-on-surface-variant">
              <span class="flex-shrink-0 font-semibold text-on-surface">1.</span>
              Double-click Wordflow.app — macOS shows a warning. Click <strong class="text-on-surface">"Done"</strong>.
            </li>
            <li class="flex items-start gap-3 font-body text-sm text-on-surface-variant">
              <span class="flex-shrink-0 font-semibold text-on-surface">2.</span>
              Open <strong class="text-on-surface">System Settings → Privacy & Security</strong>
            </li>
            <li class="flex items-start gap-3 font-body text-sm text-on-surface-variant">
              <span class="flex-shrink-0 font-semibold text-on-surface">3.</span>
              Scroll down and click <strong class="text-on-surface">"Open Anyway"</strong> next to Wordflow.
            </li>
            <li class="flex items-start gap-3 font-body text-sm text-on-surface-variant">
              <span class="flex-shrink-0 font-semibold text-on-surface">4.</span>
              Confirm with your Mac password. You only need to do this once.
            </li>
          </ol>
        </div>
        <div class="screenshot-frame">
          <!-- Replace with actual screenshot: <img src="/assets/setup/step1-gatekeeper.png" alt="macOS Privacy & Security showing Open Anyway button" class="w-full h-full object-cover"> -->
          Screenshot coming soon
        </div>
      </div>

      <!-- Divider -->
      <div class="h-px bg-outline/15"></div>

      <!-- Step 2: Groq API Key -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
        <div class="md:order-2">
          <div class="step-number mb-3">02</div>
          <h3 class="font-headline text-2xl text-on-surface mb-3">Get your free Groq API key</h3>
          <p class="font-body text-on-surface-variant leading-relaxed mb-5">
            Wordflow uses Groq for blazing-fast voice transcription. Groq is completely free — no credit card, no subscription.
          </p>
          <ol class="space-y-3">
            <li class="flex items-start gap-3 font-body text-sm text-on-surface-variant">
              <span class="flex-shrink-0 font-semibold text-on-surface">1.</span>
              Go to <a href="https://console.groq.com" target="_blank" rel="noopener" class="text-primary hover:opacity-70 transition-opacity font-medium">console.groq.com</a> and create a free account.
            </li>
            <li class="flex items-start gap-3 font-body text-sm text-on-surface-variant">
              <span class="flex-shrink-0 font-semibold text-on-surface">2.</span>
              Click <strong class="text-on-surface">"API Keys"</strong> in the left sidebar.
            </li>
            <li class="flex items-start gap-3 font-body text-sm text-on-surface-variant">
              <span class="flex-shrink-0 font-semibold text-on-surface">3.</span>
              Click <strong class="text-on-surface">"Create API Key"</strong> — give it any name.
            </li>
            <li class="flex items-start gap-3 font-body text-sm text-on-surface-variant">
              <span class="flex-shrink-0 font-semibold text-on-surface">4.</span>
              Copy the key — you'll paste it into the Wordflow setup wizard.
            </li>
          </ol>
          <a href="https://console.groq.com" target="_blank" rel="noopener"
            class="inline-flex items-center gap-2 mt-6 font-body text-sm font-semibold bg-on-surface text-background px-5 py-2.5 rounded-full hover:opacity-85 transition-opacity">
            Open Groq Console
            <span class="material-symbols-outlined" style="font-size:15px;">arrow_outward</span>
          </a>
        </div>
        <div class="screenshot-frame md:order-1">
          <!-- Replace with actual screenshot: <img src="/assets/setup/step2-groq.png" alt="Groq Console showing API Keys page" class="w-full h-full object-cover"> -->
          Screenshot coming soon
        </div>
      </div>

      <!-- Divider -->
      <div class="h-px bg-outline/15"></div>

      <!-- Step 3: Setup Wizard -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
        <div>
          <div class="step-number mb-3">03</div>
          <h3 class="font-headline text-2xl text-on-surface mb-3">Finish setup in Wordflow</h3>
          <p class="font-body text-on-surface-variant leading-relaxed mb-5">
            The setup wizard opens automatically on first launch. Just paste your Groq key — it validates it instantly and walks you through the rest.
          </p>
          <ul class="space-y-3">
            <li class="flex items-center gap-3 font-body text-sm text-on-surface-variant">
              <span class="material-symbols-outlined text-primary" style="font-size:18px;">check_circle</span>
              Choose your language (EN / DE)
            </li>
            <li class="flex items-center gap-3 font-body text-sm text-on-surface-variant">
              <span class="material-symbols-outlined text-primary" style="font-size:18px;">check_circle</span>
              Paste your Groq API key — validated in seconds
            </li>
            <li class="flex items-center gap-3 font-body text-sm text-on-surface-variant">
              <span class="material-symbols-outlined text-primary" style="font-size:18px;">check_circle</span>
              Grant Accessibility & Microphone permissions
            </li>
            <li class="flex items-center gap-3 font-body text-sm text-on-surface-variant">
              <span class="material-symbols-outlined text-primary" style="font-size:18px;">check_circle</span>
              Set your hotkey and test it live
            </li>
          </ul>
        </div>
        <div class="screenshot-frame">
          <!-- Replace with actual screenshot: <img src="/assets/setup/step3-wizard.png" alt="Wordflow Setup Wizard showing API key step" class="w-full h-full object-cover"> -->
          Screenshot coming soon
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════
     CTA BAR
════════════════════════════════════════════════ -->
<section class="py-24 bg-background">
  <div class="max-w-4xl mx-auto px-6 md:px-10">
    <div class="cta-bar text-center">
      <h2 class="font-headline text-3xl md:text-4xl text-white mb-4">Ready to start?</h2>
      <p class="font-body text-white/60 mb-8 max-w-sm mx-auto">Download Wordflow free during Early Access and dictate anything, anywhere on your Mac.</p>
      <a href="/#early-access"
        class="inline-flex items-center gap-2 font-body font-semibold text-sm bg-white text-on-surface px-6 py-3 rounded-full hover:opacity-90 transition-opacity">
        <span style="width:6px;height:6px;border-radius:50%;background:#D2691E;flex-shrink:0;"></span>
        Download for free
      </a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../_footer.php'; ?>

</body>
</html>
