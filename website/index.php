<?php
require_once __DIR__ . '/security.php';
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="csrf-token" content="<?= htmlspecialchars($csrf) ?>" />
  <!-- ── Supabase Magic Link → App Deep Link Handler ───────────────────────
       Supabase redirects to this page with #access_token=... in the fragment.
       We immediately forward everything to wordflow://activate so the app
       can pick up the session. The user sees a brief "Opening Wordflow…" screen.
  ──────────────────────────────────────────────────────────────────────── -->
  <script>
    (function () {
      var hash = window.location.hash;
      if (hash && hash.indexOf('access_token=') !== -1) {
        // Build the deep link: wordflow://activate#access_token=...
        var deepLink = 'wordflow://activate' + hash;
        // Redirect the app via custom scheme
        window.location.href = deepLink;
        // Show a simple overlay so the user sees something while the app opens
        document.addEventListener('DOMContentLoaded', function () {
          document.body.innerHTML =
            '<div style="position:fixed;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#FDFBF7;font-family:Georgia,serif;gap:16px;">' +
            '<p style="font-size:28px;font-weight:700;color:#1A1A1A;margin:0;">Wordflow.</p>' +
            '<p style="font-size:15px;color:#4A4A4A;margin:0;">Opening Wordflow&hellip;</p>' +
            '<p style="font-size:13px;color:#9A9A9A;margin-top:8px;">If the app does not open, make sure Wordflow is installed and running.</p>' +
            '</div>';
        });
      }
    })();
  </script>
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Wordflow — Speak freely. Write brilliantly.</title>
  <!-- Preload critical fonts -->
  <link rel="preload" href="/assets/fonts/1ab1ad55.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="/assets/fonts/78bd98e5.woff2" as="font" type="font/woff2" crossorigin>
  <!-- Fonts & Styles -->
  <link rel="stylesheet" href="/assets/fonts.css" />
  <link rel="stylesheet" href="/assets/app.css" />
  <style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
    }

    .material-symbols-outlined.filled {
      font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }

    .glass-nav {
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      background: rgba(253, 251, 247, 0.85);
    }

    .editorial-shadow {
      box-shadow: 0 20px 50px rgba(26, 26, 26, 0.05);
    }

    .ambient-shadow {
      box-shadow: 0px 12px 32px rgba(57, 56, 52, 0.08);
    }

    .pill-gradient {
      background: #1A1A1A;
    }

    .inner-glow {
      box-shadow: inset 0 1px 0 0 rgba(255, 255, 255, 0.1);
    }

    h1,
    h2,
    h3,
    .font-serif {
      font-variant-ligatures: common-ligatures;
    }

    @keyframes waveform {

      0%,
      100% {
        height: 4px;
      }

      50% {
        height: 20px;
      }
    }

    .wave-bar {
      animation: waveform 0.8s ease-in-out infinite;
    }

    .wave-bar:nth-child(1) {
      animation-delay: 0s;
    }

    .wave-bar:nth-child(2) {
      animation-delay: 0.1s;
    }

    .wave-bar:nth-child(3) {
      animation-delay: 0.2s;
    }

    .wave-bar:nth-child(4) {
      animation-delay: 0.3s;
    }

    .wave-bar:nth-child(5) {
      animation-delay: 0.4s;
    }

    .wave-bar:nth-child(6) {
      animation-delay: 0.3s;
    }

    .wave-bar:nth-child(7) {
      animation-delay: 0.2s;
    }

    .wave-bar:nth-child(8) {
      animation-delay: 0.1s;
    }

    @keyframes cursor-blink {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: 0;
      }
    }

    .cursor-blink {
      animation: cursor-blink 1s step-end infinite;
    }

    .mode-card {
      transition: all 0.25s ease;
    }

    .mode-card:hover {
      transform: translateY(-4px);
    }

    /* ─── Smooth Scroll ─── */
    html {
      scroll-behavior: smooth;
    }

    /* ─── Scroll Progress Bar ─── */
    #scroll-progress {
      position: fixed;
      top: 0; left: 0;
      height: 2px;
      width: 0%;
      background: #D2691E;
      z-index: 200;
      transition: width 0.08s linear;
    }

    /* ─── Scroll Reveal ─── */
    .reveal {
      opacity: 0;
      transform: translateY(36px);
      transition: opacity 0.75s cubic-bezier(0.16, 1, 0.3, 1),
                  transform 0.75s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reveal.visible {
      opacity: 1;
      transform: translateY(0);
    }
    .reveal-delay-1 { transition-delay: 0.10s; }
    .reveal-delay-2 { transition-delay: 0.20s; }
    .reveal-delay-3 { transition-delay: 0.30s; }
    .reveal-delay-4 { transition-delay: 0.40s; }

    /* ─── Hero Entrance ─── */
    .hero-fade {
      opacity: 0;
      transform: translateY(24px);
      animation: heroFadeUp 0.85s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    /* LCP-Element sofort sichtbar — Animation läuft trotzdem */
    .hero-lcp {
      opacity: 1 !important;
    }
    .hero-slide-right {
      opacity: 0;
      transform: translateX(48px);
      animation: heroSlideRight 0.95s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes heroFadeUp {
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes heroSlideRight {
      to { opacity: 1; transform: translateX(0); }
    }
    .hero-d1 { animation-delay: 0.05s; }
    .hero-d2 { animation-delay: 0.18s; }
    .hero-d3 { animation-delay: 0.30s; }
    .hero-d4 { animation-delay: 0.44s; }
    .hero-d5 { animation-delay: 0.58s; }
    .hero-d6 { animation-delay: 0.30s; }

    /* ─── Marquee ─── */
    .marquee-outer {
      overflow: hidden;
      -webkit-mask-image: linear-gradient(to right, transparent 0%, black 10%, black 90%, transparent 100%);
      mask-image: linear-gradient(to right, transparent 0%, black 10%, black 90%, transparent 100%);
    }
    .marquee-track {
      display: flex;
      width: max-content;
      animation: marquee 28s linear infinite;
    }
    .marquee-track:hover {
      animation-play-state: paused;
    }
    @keyframes marquee {
      0%   { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }

    /* ─── Parallax blobs ─── */
    .parallax-blob {
      will-change: transform;
      transition: transform 0.1s linear;
    }

    /* ─── Coming Soon Glow Card ─── */
    .coming-soon-card {
      background:
        linear-gradient(#FAF7F2, #FAF7F2) padding-box,
        linear-gradient(135deg, rgba(210,105,30,0.5), rgba(210,105,30,0.08), rgba(210,105,30,0.5)) border-box;
      border: 1px solid transparent;
      box-shadow: 0 0 40px rgba(210, 105, 30, 0.07), 0 2px 16px rgba(26,26,26,0.04);
    }

    /* ─── Card hover lift ─── */
    .hover-lift {
      transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1),
                  box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .hover-lift:hover {
      transform: translateY(-6px);
      box-shadow: 0 28px 60px rgba(26, 26, 26, 0.10);
    }

    /* ─── Prevent orphaned single words on headlines & balanced text ─── */
    h1, h2, h3, h4, p, .fluid-lead, .fluid-body {
      text-wrap: balance;
      orphans: 3;
      widows: 3;
    }
    /* ─── Calculator slider ────────────────────────────────────────── */
    .calc-slider {
      -webkit-appearance: none;
      appearance: none;
      width: 100%;
      height: 4px;
      border-radius: 9999px;
      background: rgba(180,174,166,0.25);
      outline: none;
    }
    .calc-slider::-webkit-slider-thumb {
      -webkit-appearance: none;
      appearance: none;
      width: 22px;
      height: 22px;
      border-radius: 50%;
      background: #1A1A1A;
      cursor: grab;
      border: 3px solid #FDFBF7;
      box-shadow: 0 1px 6px rgba(26,26,26,0.25);
      transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
      will-change: transform;
    }
    .calc-slider::-moz-range-thumb {
      width: 22px;
      height: 22px;
      border-radius: 50%;
      background: #1A1A1A;
      cursor: grab;
      border: 3px solid #FDFBF7;
      box-shadow: 0 1px 6px rgba(26,26,26,0.25);
      transition: background 0.15s ease, transform 0.15s ease;
    }
    .calc-slider:active::-webkit-slider-thumb {
      cursor: grabbing;
      transform: scale(1.15);
      background: #D2691E;
      box-shadow: 0 2px 12px rgba(210,105,30,0.35);
    }
    .calc-slider:active::-moz-range-thumb {
      cursor: grabbing;
      transform: scale(1.15);
      background: #D2691E;
    }
    .calc-slider:hover::-webkit-slider-thumb {
      background: #333;
      box-shadow: 0 2px 10px rgba(26,26,26,0.3);
    }
    /* ─── Calculator result numbers ─────────────────────────────────── */
    .calc-result-num {
      font-size: clamp(2rem, 5vw, 2.75rem);
      transition: opacity 0.1s ease;
    }
    .calc-result-unit {
      font-size: clamp(0.9rem, 2vw, 1.1rem);
    }

    /* ─── Hero demo area: no balance (breaks animation layout) ─────── */
    #hero-demo *, #hero-demo-mobile * {
      text-wrap: unset;
      orphans: unset;
      widows: unset;
    }

    /* ─── Fluid Typography (clamp: mobile → desktop) ─── */
    /* h1: 32px → 72px */
    .fluid-h1  { font-size: clamp(2rem,   4vw + 1rem,   4.5rem); line-height: 1.1; }
    /* h2: 28px → 60px */
    .fluid-h2  { font-size: clamp(1.75rem, 3.5vw + 0.75rem, 3.75rem); line-height: 1.1; }
    /* h3: 20px → 32px */
    .fluid-h3  { font-size: clamp(1.25rem, 2vw + 0.5rem,   2rem); }
    /* lead paragraph: 17px → 22px */
    .fluid-lead{ font-size: clamp(1.0625rem, 1vw + 0.8rem,  1.375rem); }
    /* body paragraph: 16px → 20px */
    .fluid-body{ font-size: clamp(1rem,    0.5vw + 0.875rem, 1.25rem); }

    /* ─── Fluid Section Spacing ─── */
    /* 48px mobile → 160px desktop */
    .fluid-section { padding-top: clamp(3rem, 8vw, 10rem); padding-bottom: clamp(3rem, 8vw, 10rem); }
    /* 32px mobile → 128px desktop (header spacers, gaps) */
    .fluid-mb { margin-bottom: clamp(2rem, 6vw, 8rem); }

    /* ─── Prevent horizontal overflow ─── */
    body { overflow-x: hidden; }

    /* ─── Demo animation helpers ─── */
    .demo-strike {
      position: relative;
      color: rgba(74,74,74,0.45);
    }
    .demo-strike::after {
      content: '';
      position: absolute;
      left: 0; top: 50%;
      width: 0; height: 1.5px;
      background: #D2691E;
      transition: width 0.25s ease;
    }
    .demo-strike.struck::after { width: 100%; }
    .demo-insert {
      color: #D2691E;
      opacity: 0;
      transition: opacity 0.2s ease;
    }
    .demo-insert.shown { opacity: 1; }
  </style>
</head>

<body class="bg-background text-on-surface font-body selection:bg-primary/20">

  <div id="scroll-progress" aria-hidden="true"></div>

  <!-- ─── Navigation ─── -->
  <?php require __DIR__ . '/_nav.php'; ?>

  <main class="pt-32 md:pt-40">

    <!-- ─── Hero ─── -->
    <section class="max-w-7xl mx-auto px-5 md:px-8 fluid-mb" aria-labelledby="hero-headline">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-20 items-center">

        <!-- Left: Copy -->
        <div class="lg:col-span-7 space-y-8 md:space-y-12">
          <div class="space-y-6 md:space-y-8">
            <div
              class="hero-fade hero-d1 inline-flex items-center gap-3 bg-surface-container px-5 py-2 rounded-full border border-outline/30">
              <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
              <span class="text-xs uppercase tracking-[0.2em] text-on-surface-variant font-semibold">
                <span class="hidden sm:inline">Mac Menu Bar App · BYOK · Free Lifetime during Early Access</span>
                <span class="sm:hidden">BYOK · Free for first 100</span>
              </span>
            </div>
            <h1 id="hero-headline"
              class="hero-fade hero-d2 font-headline fluid-h1 text-on-surface tracking-tight">
              Write <span class="italic text-primary font-light">as fast as you think.</span>
            </h1>
            <div class="max-w-xl space-y-4 md:space-y-6">
              <p class="hero-fade hero-d3 hero-lcp fluid-lead text-on-surface-variant font-light leading-relaxed">
                No more typing, formatting, or deleting. Just speak — Wordflow turns your messy thoughts into polished text in under a second. No subscription, ever.
              </p>
              <p class="hero-fade hero-d3 fluid-body text-on-surface font-semibold leading-relaxed italic">
                Great speech-to-text shouldn't cost €15 a month.
              </p>
            </div>
          </div>

          <!-- Hotkey Flow Indicator -->
          <div class="hero-fade hero-d4 flex items-center gap-3 text-sm text-on-surface-variant font-medium">
            <div class="flex items-center gap-2 bg-surface-container px-4 py-2 rounded-lg border border-outline/30">
              <span class="material-symbols-outlined text-sm text-primary" aria-hidden="true">keyboard</span>
              <span class="font-mono text-on-surface font-semibold text-xs">Hotkey</span>
            </div>
            <span class="material-symbols-outlined text-base text-outline" aria-hidden="true">arrow_forward</span>
            <span>Speech</span>
            <span class="material-symbols-outlined text-base text-outline" aria-hidden="true">arrow_forward</span>
            <span class="text-primary font-semibold">Magic ✦</span>
          </div>

          <p class="hero-fade hero-d4 text-sm text-on-surface-variant/50 font-light italic">Built out of subscription fatigue. Owned forever.</p>

          <div class="hero-fade hero-d5 flex flex-col sm:flex-row items-stretch sm:items-center gap-4 sm:gap-8">
            <a href="#early-access"
              class="pill-gradient inner-glow px-8 py-4 sm:px-10 sm:py-5 rounded-full text-background font-semibold editorial-shadow flex items-center justify-center gap-3 group transition-transform hover:-translate-y-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 min-h-[52px] sm:min-h-[44px]">
              Download for free →
              <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform"
                aria-hidden="true">arrow_forward</span>
            </a>
            <div class="flex items-center gap-3 sm:gap-4">
              <div class="flex -space-x-3" aria-hidden="true">
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-full border-2 border-background bg-surface-container-high flex items-center justify-center text-xs font-bold font-headline">JD</div>
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-full border-2 border-background bg-primary/20 text-primary flex items-center justify-center text-xs font-bold font-headline">MK</div>
                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-full border-2 border-background bg-secondary text-background flex items-center justify-center text-xs font-bold font-headline">AS</div>
              </div>
              <span class="text-sm text-on-surface-variant font-medium">Be one of the first 100</span>
            </div>
          </div>
        </div>

        <!-- Right: Live Demo -->
        <div class="hero-slide-right hero-d6 lg:col-span-5 relative mt-6 lg:mt-0" id="hero-demo">

          <!-- ── Desktop: 3 bubbles stacked ── -->
          <div class="hidden lg:flex flex-col gap-3">

            <!-- Step 1 label -->
            <div class="demo-step-label flex items-center gap-3 opacity-0 transition-opacity duration-500" id="dl-label-1">
              <div class="flex items-center gap-2 bg-red-400/8 border border-red-400/20 rounded-full px-3 py-1">
                <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></span>
                <span class="material-symbols-outlined text-red-400" style="font-size:12px;">mic</span>
                <span class="text-[10px] font-semibold uppercase tracking-[0.14em] text-red-500">You speak</span>
              </div>
              <div class="flex-1 h-px bg-outline/15"></div>
            </div>

            <!-- Bubble 1: Recording -->
            <div class="opacity-0 transition-opacity duration-500" id="dl-bubble-1">
              <div class="bg-on-surface rounded-2xl p-6 border border-on-surface-variant/10 relative overflow-hidden" style="box-shadow:0 8px 32px rgba(26,26,26,0.12)">
                <div class="absolute inset-0 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10 space-y-4">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                      <div class="w-2 h-2 rounded-full bg-red-400 animate-pulse"></div>
                      <span class="text-[10px] uppercase tracking-[0.2em] text-background/50 font-bold">Recording · 0:04</span>
                    </div>
                  </div>
                  <div class="flex items-end gap-1 h-6" aria-hidden="true">
                    <div class="wave-bar w-1.5 bg-primary/50 rounded-full"></div>
                    <div class="wave-bar w-1.5 bg-primary/70 rounded-full"></div>
                    <div class="wave-bar w-1.5 bg-primary/90 rounded-full"></div>
                    <div class="wave-bar w-1.5 bg-primary rounded-full"></div>
                    <div class="wave-bar w-1.5 bg-primary rounded-full"></div>
                    <div class="wave-bar w-1.5 bg-primary/80 rounded-full"></div>
                    <div class="wave-bar w-1.5 bg-primary/60 rounded-full"></div>
                    <div class="wave-bar w-1.5 bg-primary/40 rounded-full"></div>
                    <div class="wave-bar w-1.5 bg-primary/30 rounded-full"></div>
                  </div>
                  <p class="text-background/70 font-body text-base leading-snug h-[5rem] overflow-hidden" id="dl-text-1" aria-live="polite"></p>
                </div>
              </div>
            </div>

            <!-- Step 2 label -->
            <div class="flex items-center gap-3 opacity-0 transition-opacity duration-500" id="dl-label-2">
              <div class="flex items-center gap-2 bg-primary/8 border border-primary/20 rounded-full px-3 py-1">
                <span class="material-symbols-outlined text-primary" style="font-size:12px;">auto_awesome</span>
                <span class="text-[10px] font-semibold uppercase tracking-[0.14em] text-primary">Enhancing</span>
                <span class="text-[10px] font-bold text-primary/70" id="dl-timer">· 0.0s</span>
              </div>
              <div class="flex-1 h-px bg-outline/15"></div>
            </div>

            <!-- Bubble 2: Enhancing -->
            <div class="opacity-0 transition-opacity duration-500" id="dl-bubble-2">
              <div class="bg-surface-container rounded-2xl p-6 border border-outline/25" style="box-shadow:0 4px 20px rgba(26,26,26,0.06)">
                <div class="flex items-center gap-2 mb-4">
                  <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-on-surface-variant/50">Smart Business</span>
                </div>
                <p class="font-body text-base leading-relaxed h-[5rem] overflow-hidden" id="dl-text-2" aria-live="polite"></p>
              </div>
            </div>

            <!-- Step 3 label -->
            <div class="flex items-center gap-3 opacity-0 transition-opacity duration-500" id="dl-label-3">
              <div class="flex items-center gap-2 bg-primary/8 border border-primary/25 rounded-full px-3 py-1">
                <span class="material-symbols-outlined text-primary filled" style="font-size:12px;">check_circle</span>
                <span class="text-[10px] font-semibold uppercase tracking-[0.14em] text-primary">Ready at your cursor</span>
              </div>
              <div class="flex-1 h-px bg-outline/15"></div>
            </div>

            <!-- Bubble 3: Output -->
            <div class="opacity-0 transition-opacity duration-500" id="dl-bubble-3">
              <div class="bg-background rounded-2xl p-6 border border-outline/20" style="box-shadow:0 4px 20px rgba(26,26,26,0.06)">
                <div class="flex items-center gap-2 mb-4">
                  <span class="material-symbols-outlined filled text-primary text-base">check_circle</span>
                  <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Output · Smart Business</span>
                  <span class="ml-auto flex items-center gap-1 text-[10px] text-on-surface-variant/40">
                    <span class="material-symbols-outlined text-xs">content_copy</span>
                    Copied
                  </span>
                </div>
                <p class="text-on-surface font-body text-base leading-snug h-[3.5rem] overflow-hidden" id="dl-text-3" aria-live="polite"></p>
              </div>
            </div>

            <!-- Slogan after all 3 done -->
            <div id="dl-slogan" class="opacity-0 transition-opacity duration-700 text-center pt-1">
              <div class="inline-flex items-center gap-2 text-on-surface-variant/50">
                <div class="h-px w-8 bg-outline/30"></div>
                <span class="text-[11px] font-semibold uppercase tracking-[0.18em]">Perfect output · under a second</span>
                <div class="h-px w-8 bg-outline/30"></div>
              </div>
            </div>

          </div>

          <!-- ── Mobile: stacked cards, one at a time ── -->
          <div class="lg:hidden relative" style="min-height: 220px;">

            <!-- Step dots -->
            <div class="flex items-center justify-between mb-4 px-1">
              <div class="flex items-center gap-2" id="mob-step-label">
                <span class="flex items-center justify-center w-5 h-5 rounded-full bg-red-400/15 text-red-400 text-[10px] font-bold" id="mob-step-num">1</span>
                <span class="text-xs font-semibold text-on-surface-variant tracking-wide uppercase" id="mob-step-name">You speak</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="mob-dot w-1.5 h-1.5 rounded-full bg-primary transition-all duration-300" data-step="0"></span>
                <span class="mob-dot w-1.5 h-1.5 rounded-full bg-outline/40 transition-all duration-300" data-step="1"></span>
                <span class="mob-dot w-1.5 h-1.5 rounded-full bg-outline/40 transition-all duration-300" data-step="2"></span>
              </div>
            </div>

            <!-- Mobile card container -->
            <div class="relative overflow-hidden rounded-2xl" style="min-height:180px;">

              <!-- Mobile Bubble 1 -->
              <div class="mob-card absolute inset-0 transition-all duration-500" id="mob-bubble-1" style="opacity:1;transform:translateY(0);">
                <div class="bg-on-surface rounded-2xl p-5 h-full border border-on-surface-variant/10" style="box-shadow:0 8px 32px rgba(26,26,26,0.12)">
                  <div class="flex items-center gap-2.5 mb-3">
                    <div class="w-2 h-2 rounded-full bg-red-400 animate-pulse"></div>
                    <span class="text-[10px] uppercase tracking-[0.2em] text-background/50 font-bold">Recording · 0:04</span>
                  </div>
                  <div class="flex items-end gap-1 h-5 mb-3" aria-hidden="true">
                    <div class="wave-bar w-1 bg-primary/50 rounded-full"></div>
                    <div class="wave-bar w-1 bg-primary/70 rounded-full"></div>
                    <div class="wave-bar w-1 bg-primary/90 rounded-full"></div>
                    <div class="wave-bar w-1 bg-primary rounded-full"></div>
                    <div class="wave-bar w-1 bg-primary rounded-full"></div>
                    <div class="wave-bar w-1 bg-primary/80 rounded-full"></div>
                    <div class="wave-bar w-1 bg-primary/60 rounded-full"></div>
                  </div>
                  <p class="text-background/70 font-body text-sm leading-snug" id="mob-text-1"></p>
                </div>
              </div>

              <!-- Mobile Bubble 2 -->
              <div class="mob-card absolute inset-0 transition-all duration-500" id="mob-bubble-2" style="opacity:0;transform:translateY(24px);pointer-events:none;">
                <div class="bg-surface-container rounded-2xl p-5 h-full border border-outline/25" style="box-shadow:0 4px 20px rgba(26,26,26,0.06)">
                  <div class="flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-primary text-sm">auto_awesome</span>
                    <span class="text-[10px] font-bold uppercase tracking-[0.15em] text-primary">Enhancing · <span id="mob-timer">0.0s</span></span>
                  </div>
                  <p class="font-body text-sm leading-snug text-on-surface" id="mob-text-2"></p>
                </div>
              </div>

              <!-- Mobile Bubble 3 -->
              <div class="mob-card absolute inset-0 transition-all duration-500" id="mob-bubble-3" style="opacity:0;transform:translateY(24px);pointer-events:none;">
                <div class="bg-background rounded-2xl p-5 h-full border border-outline/20" style="box-shadow:0 4px 20px rgba(26,26,26,0.06)">
                  <div class="flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined filled text-primary text-sm">check_circle</span>
                    <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-primary">Ready at your cursor</span>
                  </div>
                  <p class="text-on-surface font-body text-sm leading-snug" id="mob-text-3"></p>
                </div>
              </div>

            </div>

            <!-- Mobile slogan -->
            <div id="mob-slogan" class="opacity-0 transition-opacity duration-700 text-center pt-3">
              <div class="inline-flex items-center gap-2 text-on-surface-variant/50">
                <div class="h-px w-6 bg-outline/30"></div>
                <span class="text-[10px] font-semibold uppercase tracking-[0.18em]">Perfect output · under a second</span>
                <div class="h-px w-6 bg-outline/30"></div>
              </div>
            </div>
          </div>

          <div class="parallax-blob absolute -bottom-8 -left-8 w-40 h-40 bg-primary/10 rounded-full blur-3xl -z-10" aria-hidden="true"></div>
        </div>
      </div>
    </section>

    <!-- ─── Integration Strip ─── -->
    <section class="py-14 border-y border-outline/20 bg-surface-container-low" aria-label="Works in every app">
      <p class="text-center text-xs uppercase tracking-[0.3em] text-on-surface-variant/50 font-semibold mb-8">Wherever you already work</p>
      <div class="marquee-outer">
        <div class="marquee-track" aria-hidden="true">
          <!-- Set 1 -->
          <div class="flex items-center gap-14 px-7">
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">code</span><span class="font-medium text-sm tracking-wide">VS Code</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">terminal</span><span class="font-medium text-sm tracking-wide">Terminal</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">forum</span><span class="font-medium text-sm tracking-wide">Slack</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">mail</span><span class="font-medium text-sm tracking-wide">Mail</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">edit_note</span><span class="font-medium text-sm tracking-wide">Notion</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">language</span><span class="font-medium text-sm tracking-wide">Any browser</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">description</span><span class="font-medium text-sm tracking-wide">Google Docs</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">table_chart</span><span class="font-medium text-sm tracking-wide">Excel</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">chat</span><span class="font-medium text-sm tracking-wide">iMessage</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">apps</span><span class="font-medium text-sm tracking-wide">Every app</span></div>
          </div>
          <!-- Set 2 (identical — seamless loop) -->
          <div class="flex items-center gap-14 px-7">
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">code</span><span class="font-medium text-sm tracking-wide">VS Code</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">terminal</span><span class="font-medium text-sm tracking-wide">Terminal</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">forum</span><span class="font-medium text-sm tracking-wide">Slack</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">mail</span><span class="font-medium text-sm tracking-wide">Mail</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">edit_note</span><span class="font-medium text-sm tracking-wide">Notion</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">language</span><span class="font-medium text-sm tracking-wide">Any browser</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">description</span><span class="font-medium text-sm tracking-wide">Google Docs</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">table_chart</span><span class="font-medium text-sm tracking-wide">Excel</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">chat</span><span class="font-medium text-sm tracking-wide">iMessage</span></div>
            <div class="flex items-center gap-2.5 text-on-surface-variant/60 whitespace-nowrap"><span class="material-symbols-outlined text-xl">apps</span><span class="font-medium text-sm tracking-wide">Every app</span></div>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── Pain Points ─── -->
    <style>
      /* Section background with very subtle warm gradient */
      .pain-section-bg {
        background: #F7F3EE;
      }

      /* Each row: large number left, text right — generous spacing */
      .pain-item {
        display: grid;
        grid-template-columns: 5rem 1fr;
        gap: 2rem;
        align-items: baseline;
        padding: 2.75rem 0;
        border-bottom: 1px solid rgba(180,174,166,0.2);
        cursor: default;
      }
      .pain-item:first-of-type { border-top: 1px solid rgba(180,174,166,0.2); }
      @media (max-width: 640px) {
        .pain-item { grid-template-columns: 3rem 1fr; gap: 1.25rem; padding: 2rem 0; }
      }

      /* Large Newsreader italic numeral — the Apple "detail" touch */
      .pain-num {
        font-family: 'Newsreader', serif;
        font-style: italic;
        font-weight: 300;
        font-size: clamp(2rem, 2.5vw, 2.75rem);
        line-height: 1;
        color: rgba(210,105,30,0.35);
        user-select: none;
        transition: color 0.3s cubic-bezier(0.16,1,0.3,1);
      }
      .pain-item:hover .pain-num { color: #D2691E; }

      /* Statement text — slightly larger than body, not bold */
      .pain-stmt {
        font-family: 'Inter', sans-serif;
        font-weight: 300;
        font-size: clamp(1.0625rem, 0.8vw + 0.75rem, 1.3125rem);
        line-height: 1.6;
        color: #3A3A3A;
        transition: color 0.3s ease;
      }
      .pain-item:hover .pain-stmt { color: #1A1A1A; }

      /* Resolve line — right-aligned, editorial italic */
      .pain-resolve-line {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2.5rem;
        padding-top: 0;
      }
      .pain-resolve-divider {
        flex: 1;
        height: 1px;
        background: rgba(180,174,166,0.3);
      }
      .pain-resolve-text {
        font-family: 'Newsreader', serif;
        font-style: italic;
        font-weight: 300;
        font-size: clamp(1rem, 0.8vw + 0.6rem, 1.25rem);
        color: #9A948E;
        white-space: nowrap;
      }
      .pain-resolve-text strong {
        color: #1A1A1A;
        font-style: italic;
        font-weight: 400;
      }
      .pain-headline {
        font-family: 'Newsreader', serif;
        font-style: italic;
        font-weight: 300;
        font-size: clamp(2.5rem, 5vw + 0.5rem, 4.5rem);
        line-height: 1.1;
        letter-spacing: -0.025em;
        color: #1A1A1A;
      }
    </style>

    <section class="pain-section-bg" style="padding: clamp(4.5rem,9vw,8rem) 0;" aria-label="Pain points">
      <div class="max-w-4xl mx-auto px-5 md:px-8">

        <!-- Headline — big, italic, Newsreader — not a label -->
        <div class="mb-14 md:mb-20 reveal">
          <span class="text-[10px] uppercase tracking-[0.4em] text-primary font-bold block mb-5">The honest bit</span>
          <h2 class="pain-headline">
            Sound familiar?
          </h2>
        </div>

        <!-- Rows -->
        <div>
          <div class="pain-item reveal">
            <span class="pain-num" aria-hidden="true">01</span>
            <p class="pain-stmt">You spend 10 minutes writing the perfect prompt. The AI misses the point. You rewrite it. Again.</p>
          </div>
          <div class="pain-item reveal reveal-delay-1">
            <span class="pain-num" aria-hidden="true">02</span>
            <p class="pain-stmt">You need to send a 3-line Slack update. Two minutes later you're still rewriting the first sentence.</p>
          </div>
          <div class="pain-item reveal reveal-delay-2">
            <span class="pain-num" aria-hidden="true">03</span>
            <p class="pain-stmt">Your voice memo from the meeting is a wall of 'ähms', false starts, and 'wait, no—'</p>
          </div>
        </div>

        <!-- Resolve -->
        <div class="pain-resolve-line reveal">
          <div class="pain-resolve-divider"></div>
          <p class="pain-resolve-text">Wordflow closes <strong>that gap.</strong></p>
        </div>

      </div>
    </section>

    <!-- ─── Writing Modes ─── -->
    <section class="fluid-section max-w-7xl mx-auto px-5 md:px-8" id="modes" aria-labelledby="modes-headline">
      <div class="text-center mb-12 md:mb-20 space-y-4 md:space-y-6">
        <span class="reveal text-[10px] uppercase tracking-[0.4em] text-primary font-bold">Prompt Profiles</span>
        <h2 id="modes-headline" class="reveal reveal-delay-1 font-headline fluid-h2 text-on-surface">Same voice. Three registers.</h2>
        <p class="reveal reveal-delay-2 fluid-lead text-on-surface-variant max-w-xl mx-auto font-light leading-relaxed">
          This is unfiltered. Straight from your brain to the microphone. Now watch what Wordflow does with it.
        </p>
      </div>

      <style>
        /* Waveform bars */
        .waveform-bar {
          display: inline-block;
          width: 3px;
          background: rgba(210,105,30,0.55);
          border-radius: 9999px;
          animation: waveform-dance 1.2s ease-in-out infinite;
          transform-origin: bottom;
        }
        @keyframes waveform-dance {
          0%, 100% { transform: scaleY(0.35); opacity: 0.45; }
          50%       { transform: scaleY(1);    opacity: 1; }
        }

        /* Raw input card */
        .raw-input-card {
          background: #1A1A1A;
          border: 1px solid rgba(255,255,255,0.07);
          border-radius: 1.25rem;
          padding: 2rem 2.25rem;
          position: relative;
          overflow: hidden;
        }
        .raw-input-card::before {
          content: '';
          position: absolute;
          top: 0; left: 0; right: 0;
          height: 1px;
          background: linear-gradient(90deg, transparent, rgba(210,105,30,0.4), transparent);
        }
        .raw-input-card::after {
          content: '';
          position: absolute;
          inset: 0;
          background: radial-gradient(circle at center, rgba(210,105,30,0.10) 0%, transparent 70%);
          pointer-events: none;
        }

        /* Bridge — no pill, just text with ambient glow */
        .modes-bridge {
          display: flex;
          flex-direction: column;
          align-items: center;
          margin: 0 auto;
          padding: 0.5rem 0;
        }
        .modes-bridge-stem {
          width: 1px;
          height: 36px;
          background: linear-gradient(to bottom, rgba(210,105,30,0.5), rgba(210,105,30,0.15));
        }
        .modes-bridge-stem-faint {
          width: 1px;
          height: 36px;
          background: linear-gradient(to bottom, rgba(210,105,30,0.12), transparent);
        }
        .modes-bridge-label {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          background: radial-gradient(ellipse at center, rgba(210,105,30,0.08) 0%, transparent 70%);
          border-radius: 0.5rem;
        }
        .modes-bridge-label span {
          font-family: 'Inter', sans-serif;
          font-size: 0.6875rem;
          font-weight: 600;
          letter-spacing: 0.08em;
          text-transform: uppercase;
          color: rgba(210,105,30,0.7);
        }
        .bridge-sparkle {
          width: 4px; height: 4px;
          border-radius: 50%;
          background: #D2691E;
          animation: sparkle-pulse 2s ease-in-out infinite;
        }
        @keyframes sparkle-pulse {
          0%, 100% { opacity: 0.4; transform: scale(1); }
          50%       { opacity: 1;   transform: scale(1.5); }
        }

        /* Mode output cards */
        .mode-card-v2 {
          border-radius: 1.25rem;
          padding: 1.75rem 2rem;
          position: relative;
          overflow: hidden;
          transition: transform 0.3s cubic-bezier(0.16,1,0.3,1),
                      box-shadow 0.3s cubic-bezier(0.16,1,0.3,1);
        }
        .mode-card-v2:hover { transform: translateY(-4px); }
        .mode-card-v2.light {
          background: #FDFBF7;
          border: 1px solid rgba(180,174,166,0.3);
          box-shadow: 0 2px 12px rgba(26,26,26,0.06);
        }
        .mode-card-v2.light:hover {
          box-shadow: 0 12px 32px rgba(26,26,26,0.1);
        }
        .mode-card-v2.dark {
          background: #1A1A1A;
          border: 1px solid rgba(255,255,255,0.06);
          box-shadow: 0 2px 12px rgba(0,0,0,0.2);
        }
        .mode-card-v2.dark::after {
          content: '';
          position: absolute;
          inset: 0;
          background: radial-gradient(circle at center, rgba(210,105,30,0.13) 0%, transparent 70%);
          pointer-events: none;
        }
        .mode-card-v2.dark:hover {
          box-shadow: 0 12px 32px rgba(0,0,0,0.3);
        }
        /* Accent top line per card */
        .mode-card-v2::before {
          content: '';
          position: absolute;
          top: 0; left: 1.5rem; right: 1.5rem;
          height: 1px;
          background: linear-gradient(90deg, transparent, rgba(210,105,30,0.35), transparent);
          opacity: 0;
          transition: opacity 0.3s ease;
        }
        .mode-card-v2:hover::before { opacity: 1; }

        /* Profile name — orange, Newsreader italic */
        .mode-profile-name {
          font-family: 'Newsreader', serif;
          font-style: italic;
          font-weight: 400;
          font-size: 1.125rem;
          color: #D2691E;
          letter-spacing: -0.01em;
          line-height: 1;
        }
        .mode-profile-sub {
          font-family: 'Inter', sans-serif;
          font-size: 0.75rem;
          font-weight: 300;
          margin-top: 0.3rem;
          line-height: 1.4;
        }
        .mode-divider {
          height: 1px;
          margin: 1.25rem 0;
        }
        .light .mode-divider  { background: rgba(180,174,166,0.25); }
        .dark  .mode-divider  { background: rgba(255,255,255,0.08); }

        .mode-quote {
          font-family: 'Inter', sans-serif;
          font-weight: 300;
          line-height: 1.65;
          font-size: clamp(0.875rem, 0.3vw + 0.75rem, 1rem);
        }
        .light .mode-quote { color: #3A3A3A; }
        .dark  .mode-quote { color: rgba(253,251,247,0.75); }

        /* Stagger entrance animation */
        .mode-card-v2 { animation: card-rise 0.5s cubic-bezier(0.16,1,0.3,1) both; }
        .mode-card-v2:nth-child(1) { animation-delay: 0.05s; }
        .mode-card-v2:nth-child(2) { animation-delay: 0.15s; }
        .mode-card-v2:nth-child(3) { animation-delay: 0.25s; }
        @keyframes card-rise {
          from { opacity: 0; transform: translateY(16px); }
          to   { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: reduce) {
          .mode-card-v2 { animation: none; }
        }
      </style>

      <!-- Raw input card -->
      <div class="reveal max-w-3xl mx-auto">
        <div class="raw-input-card">
          <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2.5">
              <div class="w-2 h-2 rounded-full bg-red-400 animate-pulse"></div>
              <span class="text-[10px] uppercase tracking-[0.2em] text-white/40 font-bold">Raw Voice Input</span>
            </div>
            <!-- Animated waveform -->
            <div class="hidden sm:flex items-end gap-[3px] h-5" aria-hidden="true">
              <span class="waveform-bar" style="height:7px;  animation-delay:0s;"></span>
              <span class="waveform-bar" style="height:14px; animation-delay:0.12s;"></span>
              <span class="waveform-bar" style="height:9px;  animation-delay:0.24s;"></span>
              <span class="waveform-bar" style="height:20px; animation-delay:0.06s;"></span>
              <span class="waveform-bar" style="height:11px; animation-delay:0.18s;"></span>
              <span class="waveform-bar" style="height:17px; animation-delay:0.3s;"></span>
              <span class="waveform-bar" style="height:8px;  animation-delay:0.09s;"></span>
              <span class="waveform-bar" style="height:15px; animation-delay:0.21s;"></span>
              <span class="waveform-bar" style="height:6px;  animation-delay:0.15s;"></span>
            </div>
          </div>
          <p class="font-body font-light leading-relaxed" style="font-size:clamp(1rem,0.5vw+0.85rem,1.2rem);color:rgba(253,251,247,0.78);">
            "the meeting was good, basically we all agreed we need to
            <span style="color:rgba(255,140,50,0.9);">like</span> rebuild the API,
            <span style="color:rgba(255,140,50,0.9);">um,</span>
            <span style="color:rgba(255,140,50,0.75);text-decoration:line-through;text-decoration-color:rgba(255,140,50,0.4);">Tom was annoying about it but whatever,</span>
            it's the right call"
          </p>
        </div>
      </div>

      <!-- Bridge — clean, no pill -->
      <div class="modes-bridge" aria-hidden="true">
        <div class="modes-bridge-stem"></div>
        <div class="modes-bridge-label">
          <div class="bridge-sparkle"></div>
          <span>Wordflow cleans your input</span>
          <div class="bridge-sparkle" style="animation-delay:0.6s;"></div>
        </div>
        <div class="modes-bridge-stem-faint"></div>
      </div>

      <!-- Three Mode Output Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5 md:gap-7">

        <!-- Smart Casual -->
        <div class="mode-card-v2 light reveal">
          <div class="flex items-start justify-between">
            <div>
              <p class="mode-profile-name">Smart Casual</p>
              <p class="mode-profile-sub text-on-surface-variant/50">Keeps your slang &amp; personality</p>
            </div>
            <span class="material-symbols-outlined text-primary/20 text-xl mt-0.5" aria-hidden="true">record_voice_over</span>
          </div>
          <div class="mode-divider"></div>
          <p class="mode-quote">
            "Good meeting — we all agreed we need to rebuild the API. Tom was annoying about it, but whatever, it's the right call."
          </p>
        </div>

        <!-- Smart Business -->
        <div class="mode-card-v2 dark reveal reveal-delay-1">
          <div class="flex items-start justify-between">
            <div>
              <p class="mode-profile-name">Smart Business</p>
              <p class="mode-profile-sub" style="color:rgba(253,251,247,0.3);">Captures the intent, clears the noise</p>
            </div>
            <span class="material-symbols-outlined text-xl mt-0.5" style="color:rgba(253,251,247,0.15);" aria-hidden="true">auto_awesome</span>
          </div>
          <div class="mode-divider"></div>
          <p class="mode-quote">
            "The meeting was productive. We reached alignment on rebuilding the API — the right call."
          </p>
        </div>

        <!-- Professional -->
        <div class="mode-card-v2 light reveal reveal-delay-2" style="border-color:rgba(210,105,30,0.2);">
          <div class="flex items-start justify-between">
            <div>
              <p class="mode-profile-name">Professional</p>
              <p class="mode-profile-sub text-on-surface-variant/50">Executive tone — no greetings added on your behalf</p>
            </div>
            <span class="material-symbols-outlined text-primary/20 text-xl mt-0.5" aria-hidden="true">business_center</span>
          </div>
          <div class="mode-divider"></div>
          <p class="mode-quote">
            "The meeting was productive. Consensus was reached on rebuilding the API — the strategically sound course of action."
          </p>
        </div>
      </div>

      <!-- Footer line -->
      <p class="text-center mt-10 text-sm text-on-surface-variant/40 font-light italic">
        All three profiles work in German and English — Wordflow detects your language automatically.
      </p>

      <!-- Coming soon teaser -->
      <div class="reveal mt-12 md:mt-16 flex justify-center px-5 md:px-0">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2.5 sm:gap-3 px-5 py-4 rounded-xl bg-surface-container border border-outline/15 w-full sm:w-auto max-w-sm sm:max-w-none">
          <span class="text-[9px] font-bold uppercase tracking-[0.18em] text-primary bg-primary/8 border border-primary/15 rounded-full px-2.5 py-1 shrink-0 self-start sm:self-auto">Coming soon</span>
          <p class="text-sm text-on-surface-variant font-light leading-snug">More profiles — including custom profiles tailored to your voice.</p>
        </div>
      </div>
    </section>

    <!-- ─── Use Cases / Roles ─── -->
    <style>
      /* ── Role tabs – desktop vertical ─────────────────────────────── */
      .role-nav-desktop {
        display: none;
        flex-direction: column;
        gap: 2px;
        position: relative;
      }
      @media (min-width: 1024px) { .role-nav-desktop { display: flex; } }

      .role-tab-desk {
        position: relative;
        text-align: left;
        padding: 0.875rem 1.25rem;
        border-radius: 0.875rem;
        font-family: 'Inter', sans-serif;
        font-size: 0.875rem;
        font-weight: 500;
        color: #6B6B6B;
        background: transparent;
        border: none;
        cursor: pointer;
        transition: color 0.22s cubic-bezier(0.16,1,0.3,1),
                    padding-left 0.22s cubic-bezier(0.16,1,0.3,1);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        white-space: nowrap;
        outline: none;
        z-index: 1;
      }
      .role-tab-desk:hover { color: #1A1A1A; padding-left: 1.5rem; }
      .role-tab-desk.active { color: #1A1A1A; font-weight: 600; padding-left: 1.5rem; }
      .role-tab-desk .tab-dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: transparent;
        border: 1.5px solid #C4C0BA;
        flex-shrink: 0;
        transition: background 0.22s ease, border-color 0.22s ease, transform 0.22s cubic-bezier(0.16,1,0.3,1);
      }
      .role-tab-desk.active .tab-dot,
      .role-tab-desk:hover .tab-dot {
        background: #D2691E;
        border-color: #D2691E;
        transform: scale(1.3);
      }

      /* sliding indicator pill behind active tab */
      .role-tab-indicator {
        position: absolute;
        left: 0; right: 0;
        border-radius: 0.875rem;
        background: rgba(210,105,30,0.08);
        border: 1px solid rgba(210,105,30,0.18);
        pointer-events: none;
        transition: top 0.38s cubic-bezier(0.16,1,0.3,1), height 0.38s cubic-bezier(0.16,1,0.3,1);
        z-index: 0;
      }

      /* ── Role tabs – mobile horizontal ──────────────────────────────── */
      .role-nav-mobile {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding-bottom: 4px;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
      }
      .role-nav-mobile::-webkit-scrollbar { display: none; }
      @media (min-width: 1024px) { .role-nav-mobile { display: none; } }

      .role-tab-mob {
        flex-shrink: 0;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-family: 'Inter', sans-serif;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #6B6B6B;
        background: #EDE9E3;
        border: 1px solid transparent;
        cursor: pointer;
        transition: color 0.2s ease, background 0.2s ease, border-color 0.2s ease,
                    transform 0.2s cubic-bezier(0.16,1,0.3,1);
        outline: none;
        white-space: nowrap;
      }
      .role-tab-mob:hover { color: #1A1A1A; transform: translateY(-1px); }
      .role-tab-mob.active {
        color: #FDFBF7;
        background: #1A1A1A;
        border-color: transparent;
        transform: translateY(-1px);
      }

      /* ── Role content fade ───────────────────────────────────────────── */
      .role-content {
        animation: role-fade-in 0.38s cubic-bezier(0.16,1,0.3,1) both;
      }
      @keyframes role-fade-in {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
      }
      .role-content.hidden { display: none !important; }

      /* ── Use-case card ──────────────────────────────────────────────── */
      .use-card {
        background: #FDFBF7;
        border: 1px solid rgba(180,174,166,0.3);
        border-radius: 1.25rem;
        padding: 1.5rem;
        space-y: 0.75rem;
        transition: border-color 0.22s ease, box-shadow 0.22s ease, transform 0.22s cubic-bezier(0.16,1,0.3,1);
        position: relative;
        overflow: hidden;
      }
      .use-card::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse at bottom right, rgba(210,105,30,0.05) 0%, transparent 60%);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
      }
      .use-card:hover {
        border-color: rgba(210,105,30,0.3);
        box-shadow: 0 6px 24px rgba(26,26,26,0.08);
        transform: translateY(-2px);
      }
      .use-card:hover::after { opacity: 1; }
      .use-card-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        background: rgba(210,105,30,0.08);
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 0.875rem;
        transition: background 0.2s ease;
      }
      .use-card:hover .use-card-icon { background: rgba(210,105,30,0.14); }
    </style>

    <section class="fluid-section bg-surface-container-low" id="features" aria-labelledby="usecases-headline">
      <div class="max-w-7xl mx-auto px-5 md:px-8">
        <div class="text-center mb-12 md:mb-16 space-y-4">
          <span class="reveal text-[10px] uppercase tracking-[0.4em] text-primary font-bold">Built for how you work</span>
          <h2 id="usecases-headline" class="reveal reveal-delay-1 font-headline fluid-h2 text-on-surface">Your role. Your use cases.</h2>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 lg:gap-14">

          <!-- Left: Role tabs -->
          <div class="lg:w-56 shrink-0">
            <!-- Mobile tabs -->
            <div class="role-nav-mobile" role="tablist" aria-label="Select your role">
              <button class="role-tab-mob active" data-role="developer" role="tab" aria-selected="true">Developer</button>
              <button class="role-tab-mob" data-role="pm" role="tab" aria-selected="false">Product Manager</button>
              <button class="role-tab-mob" data-role="marketer" role="tab" aria-selected="false">Marketer</button>
              <button class="role-tab-mob" data-role="creative" role="tab" aria-selected="false">Creative</button>
              <button class="role-tab-mob" data-role="manager" role="tab" aria-selected="false">Manager</button>
            </div>
            <!-- Desktop tabs -->
            <div class="role-nav-desktop" role="tablist" aria-label="Select your role">
              <div class="role-tab-indicator" id="role-indicator"></div>
              <button class="role-tab-desk active" data-role="developer" role="tab" aria-selected="true">
                <span class="tab-dot"></span>Developer
              </button>
              <button class="role-tab-desk" data-role="pm" role="tab" aria-selected="false">
                <span class="tab-dot"></span>Product Manager
              </button>
              <button class="role-tab-desk" data-role="marketer" role="tab" aria-selected="false">
                <span class="tab-dot"></span>Marketer
              </button>
              <button class="role-tab-desk" data-role="creative" role="tab" aria-selected="false">
                <span class="tab-dot"></span>Creative
              </button>
              <button class="role-tab-desk" data-role="manager" role="tab" aria-selected="false">
                <span class="tab-dot"></span>Manager / Executive
              </button>
            </div>
          </div>

          <!-- Right: Use case cards -->
          <div class="flex-1">

            <!-- Developer / Vibe Coder -->
            <div class="role-content grid grid-cols-1 sm:grid-cols-2 gap-4" data-role="developer">
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">auto_fix_high</span></div><p class="font-semibold text-on-surface text-sm mb-2">First-try prompts</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Speak your intent naturally — Wordflow shapes it into a precise, structured prompt. No more rewriting because the AI "missed the point".</p></div>
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">psychology</span></div><p class="font-semibold text-on-surface text-sm mb-2">Think out loud, prompt better</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Describe what you want to build in plain speech. Get back a prompt with context, constraints, and examples — ready to paste.</p></div>
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">history_edu</span></div><p class="font-semibold text-on-surface text-sm mb-2">Document decisions</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Capture architectural choices and standup decisions out loud. No more blank Notion pages after the call.</p></div>
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">code</span></div><p class="font-semibold text-on-surface text-sm mb-2">Draft PR &amp; issue descriptions</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Speak a rough summary of your changes — Wordflow turns it into a clean, structured description. Done before you push.</p></div>
            </div>

            <!-- Product Manager -->
            <div class="role-content hidden grid grid-cols-1 sm:grid-cols-2 gap-4" data-role="pm">
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">checklist</span></div><p class="font-semibold text-on-surface text-sm mb-2">Capture meeting decisions</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Action items, decisions, owners — spoken raw, structured instantly. Ship the doc before the meeting ends.</p></div>
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">forum</span></div><p class="font-semibold text-on-surface text-sm mb-2">Stakeholder updates by voice</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Write Slack updates and stakeholder summaries in your tone — without spending 20 minutes on a 4-line message.</p></div>
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">edit_note</span></div><p class="font-semibold text-on-surface text-sm mb-2">Fill Notion docs from brainstorms</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Stream-of-consciousness voice input, clean structured output in your doc. Keeps the idea, drops the noise.</p></div>
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">description</span></div><p class="font-semibold text-on-surface text-sm mb-2">Feature specs, faster</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Speak through your feature idea. Get back a structured spec you can hand directly to engineering.</p></div>
            </div>

            <!-- Marketer -->
            <div class="role-content hidden grid grid-cols-1 sm:grid-cols-2 gap-4" data-role="marketer">
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">campaign</span></div><p class="font-semibold text-on-surface text-sm mb-2">Draft copy by speaking</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Say the rough version out loud. Get back polished copy variations — in the right tone for the right channel.</p></div>
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">lightbulb</span></div><p class="font-semibold text-on-surface text-sm mb-2">Capture ideas before they slip</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Hit the hotkey, speak the idea, pocket it. Your content calendar fills itself between other tasks.</p></div>
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">mail</span></div><p class="font-semibold text-on-surface text-sm mb-2">Client emails in your voice</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Write client briefs and outreach emails without losing your personality — or spending an hour on the opening line.</p></div>
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">analytics</span></div><p class="font-semibold text-on-surface text-sm mb-2">Campaign notes into Notion</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Post-call debrief, strategy notes, creative directions — voice in, structured doc out. Done before you open Notion.</p></div>
            </div>

            <!-- Creative -->
            <div class="role-content hidden grid grid-cols-1 sm:grid-cols-2 gap-4" data-role="creative">
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">draw</span></div><p class="font-semibold text-on-surface text-sm mb-2">Brainstorm, structured</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Speak your ideas raw — messy, circular, unfiltered. Get back clean, organized notes without losing a single thread.</p></div>
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">theater_comedy</span></div><p class="font-semibold text-on-surface text-sm mb-2">Pitch without friction</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Draft pitches and concepts while the idea is alive — before the blank page kills the momentum.</p></div>
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">call</span></div><p class="font-semibold text-on-surface text-sm mb-2">Feedback from calls, captured</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Hang up the call, hit the hotkey, speak the summary. It's documented before you open your laptop.</p></div>
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">record_voice_over</span></div><p class="font-semibold text-on-surface text-sm mb-2">Write with your voice</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Even on a bad typing day. Your voice is faster, more natural, and harder to second-guess than a keyboard.</p></div>
            </div>

            <!-- Manager / Executive -->
            <div class="role-content hidden grid grid-cols-1 sm:grid-cols-2 gap-4" data-role="manager">
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">mail</span></div><p class="font-semibold text-on-surface text-sm mb-2">Emails and Slack, by voice</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Voice-draft messages at the right level of formality — without typing a word or losing your tone.</p></div>
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">handshake</span></div><p class="font-semibold text-on-surface text-sm mb-2">Capture decisions live</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Speak the decision and context right after the call. Have a clean summary ready before your next meeting starts.</p></div>
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">groups</span></div><p class="font-semibold text-on-surface text-sm mb-2">Team updates without a blank page</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Speak what happened, what's next, and what you need from the team. Wordflow shapes it into a clear update.</p></div>
              <div class="use-card"><div class="use-card-icon"><span class="material-symbols-outlined text-primary" style="font-size:18px;" aria-hidden="true">task_alt</span></div><p class="font-semibold text-on-surface text-sm mb-2">Action items, instantly</p><p class="text-sm text-on-surface-variant font-light leading-relaxed">Dictate action items directly into your task manager — spoken naturally, formatted for work.</p></div>
            </div>

          </div>
        </div>
      </div>
    </section>

    <script>
    (function () {
      // ── Desktop indicator slider ────────────────────────────────────
      const indicator = document.getElementById('role-indicator');
      function positionIndicator(btn) {
        if (!indicator || !btn) return;
        const parent = btn.closest('.role-nav-desktop');
        if (!parent) return;
        const parentRect = parent.getBoundingClientRect();
        const btnRect    = btn.getBoundingClientRect();
        indicator.style.top    = (btnRect.top  - parentRect.top)  + 'px';
        indicator.style.height = btnRect.height + 'px';
      }

      // ── Switch role ─────────────────────────────────────────────────
      function switchRole(role) {
        // Desktop tabs
        document.querySelectorAll('.role-tab-desk').forEach(t => {
          t.classList.toggle('active', t.dataset.role === role);
          t.setAttribute('aria-selected', t.dataset.role === role);
        });
        // Mobile tabs
        document.querySelectorAll('.role-tab-mob').forEach(t => {
          t.classList.toggle('active', t.dataset.role === role);
          t.setAttribute('aria-selected', t.dataset.role === role);
        });
        // Slide indicator to active desktop tab
        const activeDesk = document.querySelector(`.role-tab-desk[data-role="${role}"]`);
        positionIndicator(activeDesk);

        // Content: fade out current, fade in new
        document.querySelectorAll('.role-content').forEach(c => {
          if (c.dataset.role === role) {
            c.classList.remove('hidden');
            // Re-trigger animation
            c.style.animation = 'none';
            c.offsetHeight; // reflow
            c.style.animation = '';
          } else {
            c.classList.add('hidden');
          }
        });
      }

      // ── Bind all tab buttons ────────────────────────────────────────
      document.querySelectorAll('.role-tab-desk, .role-tab-mob').forEach(btn => {
        btn.addEventListener('click', () => switchRole(btn.dataset.role));
      });

      // ── Init indicator position ─────────────────────────────────────
      const firstDesk = document.querySelector('.role-tab-desk[data-role="developer"]');
      // Use rAF to ensure layout is complete
      requestAnimationFrame(() => positionIndicator(firstDesk));

      // Re-position on resize
      window.addEventListener('resize', () => {
        const active = document.querySelector('.role-tab-desk.active');
        positionIndicator(active);
      });
    })();
    </script>

    <!-- ─── 3-Step Process ─── -->
    <style>
      /* Step number — large, editorial, fades in from above */
      .step-num {
        font-family: 'Newsreader', serif;
        font-style: italic;
        font-weight: 300;
        font-size: clamp(3rem, 5vw, 5rem);
        line-height: 1;
        letter-spacing: -0.03em;
        color: rgba(210,105,30,0.18);
        display: block;
        margin-bottom: 1.5rem;
        transition: color 0.35s cubic-bezier(0.16,1,0.3,1);
        user-select: none;
      }

      /* Step title */
      .step-title {
        font-family: 'Newsreader', serif;
        font-weight: 400;
        font-size: clamp(1.375rem, 1.5vw + 0.75rem, 1.875rem);
        line-height: 1.2;
        color: #1A1A1A;
        margin-bottom: 0.75rem;
        transition: color 0.25s ease;
      }

      /* Thin top accent line per step */
      .step-item {
        position: relative;
        padding-top: 2rem;
      }
      .step-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 2rem;
        height: 1px;
        background: rgba(210,105,30,0.35);
        transition: width 0.4s cubic-bezier(0.16,1,0.3,1);
      }
      .step-item:hover::before { width: 100%; }

      @media (max-width: 767px) {
        .step-item { text-align: center; }
        .step-item::before { left: 50%; transform: translateX(-50%); }
        .step-item:hover::before { width: 4rem; }
      }

      /* ─── Step numbers: reveal from dim → bright, stay bright ────── */
      .step-num.reveal {
        opacity: 0;
        transform: translateY(16px);
        transition: opacity 0.75s cubic-bezier(0.16,1,0.3,1),
                    transform 0.75s cubic-bezier(0.16,1,0.3,1),
                    color 0.75s cubic-bezier(0.16,1,0.3,1);
        color: rgba(210,105,30,0.18);
      }
      .step-num.reveal.visible {
        opacity: 1;
        transform: translateY(0);
        color: rgba(210,105,30,0.60);
      }

      /* Connector dots between steps (desktop) */
      .step-connector {
        display: none;
      }
      @media (min-width: 768px) {
        .step-connector {
          display: flex;
          align-items: center;
          justify-content: center;
          position: absolute;
          top: 2.25rem;
          gap: 3px;
        }
        .step-connector-dot {
          width: 3px; height: 3px;
          border-radius: 50%;
          background: rgba(210,105,30,0.3);
        }
      }
    </style>

    <section class="fluid-section bg-surface-container-low" aria-labelledby="process-headline">
      <div class="max-w-6xl mx-auto px-5 md:px-8">

        <!-- Header -->
        <div class="text-center mb-16 md:mb-24 space-y-3">
          <span class="text-[10px] uppercase tracking-[0.4em] text-primary font-bold">The Workflow</span>
          <h2 id="process-headline" class="font-headline fluid-h2 text-on-surface">Press. Speak. Done.</h2>
        </div>

        <!-- Steps -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-0 md:gap-16 relative">

          <!-- Connector dots row (desktop only) -->
          <div class="step-connector" style="left:calc(33.33% - 12px);" aria-hidden="true">
            <span class="step-connector-dot"></span>
            <span class="step-connector-dot" style="opacity:0.6;"></span>
            <span class="step-connector-dot" style="opacity:0.3;"></span>
          </div>
          <div class="step-connector" style="left:calc(66.66% - 12px);" aria-hidden="true">
            <span class="step-connector-dot"></span>
            <span class="step-connector-dot" style="opacity:0.6;"></span>
            <span class="step-connector-dot" style="opacity:0.3;"></span>
          </div>

          <!-- Step 1 -->
          <div class="step-item reveal">
            <span class="step-num reveal" aria-hidden="true">01</span>
            <h3 class="step-title">Capture</h3>
            <p class="fluid-body text-on-surface-variant font-light leading-relaxed">
              Hit your hotkey. Speak freely. No structure, no perfect grammar needed.
            </p>
          </div>

          <!-- Step 2 -->
          <div class="step-item reveal reveal-delay-1" style="border-top: none;">
            <span class="step-num reveal reveal-delay-2" aria-hidden="true">02</span>
            <h3 class="step-title">Refine</h3>
            <p class="fluid-body text-on-surface-variant font-light leading-relaxed">
              Filler words out. Meaning in. Sub-1-second via Groq.
            </p>
          </div>

          <!-- Step 3 -->
          <div class="step-item reveal reveal-delay-2">
            <span class="step-num reveal reveal-delay-4" aria-hidden="true">03</span>
            <h3 class="step-title">Deliver</h3>
            <p class="fluid-body text-on-surface-variant font-light leading-relaxed">
              Polished text lands at your cursor. Clipboard filled. Ship it.
            </p>
          </div>

        </div>
      </div>
    </section>

    <!-- ─── Time Savings Calculator ─── -->
    <section class="fluid-section max-w-7xl mx-auto px-5 md:px-8" aria-labelledby="calculator-headline">
      <div class="text-center mb-12 md:mb-20 space-y-4 md:space-y-6">
        <span class="reveal text-[10px] uppercase tracking-[0.4em] text-primary font-bold">The Math</span>
        <h2 id="calculator-headline" class="reveal reveal-delay-1 font-headline fluid-h2 text-on-surface">You speak 4&times; faster than you type.</h2>
        <p class="reveal reveal-delay-2 fluid-lead text-on-surface-variant max-w-xl mx-auto font-light leading-relaxed">
          Average speaking speed: ~130 words/min. Average typing speed: ~33 words/min. That's not a feature — that's physics.
        </p>
      </div>

      <div class="max-w-2xl mx-auto">
        <div class="bg-surface-container rounded-3xl p-8 md:p-12 editorial-shadow border border-outline/20 space-y-8 md:space-y-10">

          <!-- Input -->
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <label for="typing-hours" class="text-sm font-semibold text-on-surface">How many hours do you spend writing per day?</label>
              <span id="hours-display" class="text-2xl font-headline text-primary">2h</span>
            </div>
            <input
              type="range"
              id="typing-hours"
              min="0.5"
              max="8"
              step="0.1"
              value="2"
              class="w-full appearance-none cursor-pointer calc-slider"
              aria-label="Hours typing per day"
            />
            <div class="flex justify-between text-[10px] text-on-surface-variant/50 font-medium uppercase tracking-wide">
              <span>0.5h</span>
              <span>4h</span>
              <span>8h</span>
            </div>
          </div>

          <!-- Divider -->
          <div class="h-px bg-outline/20"></div>

          <!-- Output -->
          <div class="grid grid-cols-2 gap-4 md:gap-6">
            <div class="bg-background rounded-2xl p-4 md:p-6 ambient-shadow text-center">
              <p class="text-[10px] uppercase tracking-[0.2em] text-on-surface-variant/60 font-bold mb-2">Per week</p>
              <p class="font-headline leading-none text-primary calc-result-num" id="calc-week">7.5 <span class="calc-result-unit font-light text-on-surface-variant">hrs</span></p>
              <p class="text-xs text-on-surface-variant font-light mt-2">saved</p>
            </div>
            <div class="bg-on-surface rounded-2xl p-4 md:p-6 ambient-shadow text-center">
              <p class="text-[10px] uppercase tracking-[0.2em] text-background/60 font-bold mb-2">Per year</p>
              <p class="font-headline leading-none text-background calc-result-num" id="calc-year">360 <span class="calc-result-unit font-light text-background/70">hrs</span></p>
              <p class="text-xs text-background/70 font-light mt-2">saved</p>
            </div>
          </div>

          <p class="text-center text-[11px] text-on-surface-variant/40 font-light">Based on avg. 130 wpm speech vs. 33 wpm typing.</p>

        </div>
      </div>
    </section>

    <script>
    (function () {
      const slider  = document.getElementById('typing-hours');
      const display = document.getElementById('hours-display');
      const weekEl  = document.getElementById('calc-week');
      const yearEl  = document.getElementById('calc-year');

      let rafId = null;

      function fmt(val) {
        // Always 1 decimal place, strip trailing .0 for whole numbers
        const fixed = val.toFixed(1);
        return fixed.endsWith('.0') ? String(Math.round(val)) : fixed;
      }

      function updateCalc() {
        const hrs = parseFloat(slider.value);
        // Display hours: 1 decimal, strip .0
        display.textContent = fmt(hrs) + 'h';

        // ~75% time saved (130 vs 33 wpm ≈ 3.9×, saving ~74%)
        const savedPerWeek = hrs * 0.75 * 5;
        const savedPerYear = savedPerWeek * 48;

        weekEl.innerHTML = fmt(savedPerWeek) + ' <span class="calc-result-unit font-light text-on-surface-variant">hrs</span>';
        yearEl.innerHTML = fmt(savedPerYear) + ' <span class="calc-result-unit font-light text-background/70">hrs</span>';
      }

      function onInput() {
        if (rafId) cancelAnimationFrame(rafId);
        rafId = requestAnimationFrame(updateCalc);
      }

      if (slider) {
        slider.addEventListener('input', onInput);
        requestAnimationFrame(updateCalc);
      }
    })();
    </script>

    <!-- ─── Cost Argument ─── -->
    <section class="fluid-section bg-surface-container-low" aria-labelledby="cost-headline">
      <div class="max-w-4xl mx-auto px-5 md:px-8 text-center">
        <div class="mb-12 md:mb-16 space-y-4">
          <span class="reveal text-[10px] uppercase tracking-[0.4em] text-primary font-bold">Ownership</span>
          <h2 id="cost-headline" class="reveal reveal-delay-1 font-headline fluid-h2 text-on-surface">The last speech tool you'll pay for.</h2>
        </div>

        <div class="reveal max-w-2xl mx-auto bg-background rounded-3xl editorial-shadow border border-outline/20 overflow-hidden">
          <!-- Header -->
          <div class="px-8 py-5 bg-surface-container border-b border-outline/20">
            <div class="grid grid-cols-2 text-left">
              <span class="text-[10px] uppercase tracking-[0.2em] text-on-surface-variant/60 font-bold">Tool</span>
              <span class="text-[10px] uppercase tracking-[0.2em] text-on-surface-variant/60 font-bold text-right">Annual Cost</span>
            </div>
          </div>
          <!-- Wispr Flow -->
          <div class="px-8 py-5 border-b border-outline/10">
            <div class="grid grid-cols-2 items-center">
              <span class="text-sm text-on-surface-variant font-light">Wispr Flow</span>
              <span class="text-sm text-on-surface-variant/50 font-light text-right line-through">$15/month = $180/year</span>
            </div>
          </div>
          <!-- SuperWhisper -->
          <div class="px-8 py-5 border-b border-outline/10">
            <div class="grid grid-cols-2 items-center">
              <span class="text-sm text-on-surface-variant font-light">SuperWhisper</span>
              <span class="text-sm text-on-surface-variant/50 font-light text-right line-through">$9.99/month = ~$120/year</span>
            </div>
          </div>
          <!-- Wordflow highlighted -->
          <div class="px-8 py-6 bg-primary/5 border-t-2 border-primary/20">
            <div class="grid grid-cols-2 items-center">
              <span class="font-headline text-on-surface text-base italic">Wordflow</span>
              <span class="font-headline text-primary text-xl whitespace-nowrap text-right">€25. Once. Forever.</span>
            </div>
          </div>
        </div>

        <p class="reveal mt-8 text-on-surface-variant font-light fluid-body max-w-xl mx-auto leading-relaxed">
          Plus: your API key, your data. No vendor lock-in. No usage caps from our side.<br />
          <span class="text-on-surface">Groq's free tier covers 95% of users.</span>
        </p>
      </div>
    </section>

    <!-- ─── Early Access ─── -->
    <section class="py-16 md:py-32 bg-on-surface relative overflow-hidden" id="early-access" aria-labelledby="early-access-headline">
      <!-- Radial glow — same as pricing card -->
      <div class="absolute inset-0 pointer-events-none" aria-hidden="true"
           style="background: radial-gradient(circle at center, rgba(210,105,30,0.10) 0%, transparent 65%);"></div>
      <div class="max-w-3xl mx-auto px-5 md:px-8 text-center space-y-8 md:space-y-10 relative z-10">
        <div class="inline-flex items-center gap-3 bg-white/10 px-4 py-2 rounded-full border border-white/10 max-w-full">
          <span class="w-2 h-2 rounded-full bg-primary animate-pulse shrink-0"></span>
          <span class="text-xs uppercase tracking-[0.2em] text-background/60 font-semibold">
            Early Access · Free Lifetime
          </span>
        </div>
        <h2 id="early-access-headline" class="font-headline fluid-h2 text-background leading-[1.1]">
          Get Wordflow <span class="italic text-primary font-light">free.</span>
        </h2>
        <p class="fluid-lead text-background/60 font-light leading-relaxed max-w-xl mx-auto">
          During Early Access, Wordflow is completely free — yours forever.
          <strong class="text-background/80">No credit card. No subscription. Lifetime access, on us.</strong>
        </p>

        <!-- Early Access Signup Form -->
        <form id="notify-form" class="flex flex-col gap-4 max-w-lg mx-auto" novalidate>
          <input type="text" name="url" id="notify-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;height:0;" />

          <!-- Email + Submit -->
          <div class="flex flex-col sm:flex-row gap-3">
            <input
              type="email"
              name="email"
              id="notify-email"
              placeholder="your@email.com"
              required
              autocomplete="email"
              class="flex-1 px-6 py-4 rounded-full bg-white/10 border border-white/20 text-background placeholder-background/40 focus:outline-none focus:border-primary focus:bg-white/15 transition-all min-h-[52px]"
            />
            <button
              type="submit"
              id="notify-btn"
              class="px-8 py-4 bg-primary rounded-full text-background font-semibold hover:bg-primary/90 transition-all duration-300 shadow-xl shadow-primary/20 whitespace-nowrap min-h-[52px] focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-on-surface disabled:opacity-50 disabled:cursor-not-allowed"
              disabled
            >
              Send me the download link →
            </button>
          </div>

          <!-- Opt-in checkbox (DSGVO) -->
          <label class="flex items-start gap-3 cursor-pointer text-left group">
            <input
              type="checkbox"
              id="notify-consent"
              name="consent"
              value="1"
              class="mt-1 w-4 h-4 shrink-0 accent-primary cursor-pointer"
            />
            <span class="text-background/50 text-sm font-light leading-relaxed group-hover:text-background/70 transition-colors">
              Yes, I'd like to receive the download link and occasional updates about Wordflow.
              I can unsubscribe at any time. See our
              <a href="/datenschutz" class="text-primary underline hover:no-underline">privacy policy</a>.
            </span>
          </label>
        </form>

        <div id="notify-message" class="hidden text-center text-background/80 text-sm"></div>

        <p class="text-background/30 text-xs font-light">
          Mac only · Early Access · Free lifetime during this phase
        </p>
      </div>
    </section>

    <!-- ─── BYOK + Pricing ─── -->
    <section class="fluid-section" id="pricing" aria-labelledby="pricing-headline">
      <div class="max-w-6xl mx-auto px-5 md:px-8">
        <div class="bg-surface-container-high rounded-3xl overflow-hidden border border-outline/10 ambient-shadow">
          <div class="flex flex-col md:flex-row w-full">

            <!-- Left: BYOK Pitch -->
            <div class="p-7 md:p-20 flex-1 space-y-8 md:space-y-10">
              <div
                class="inline-block bg-primary/10 px-5 py-1.5 rounded-full text-primary text-[10px] font-bold uppercase tracking-[0.2em]">
                Ownership Redefined</div>
              <h2 id="pricing-headline" class="font-headline text-on-surface" style="font-size:clamp(2rem,2.5vw+0.75rem,3rem);line-height:1.15;">Bring your own key.<br />Control your costs.</h2>
              <p class="text-on-surface-variant leading-relaxed font-light" style="font-size:clamp(0.9375rem,0.6vw+0.75rem,1.125rem);">
                Connect your Groq API key and take advantage of their massive free usage tier. No recurring
                subscriptions — just a single, one-time payment for the Wordflow interface.
              </p>

              <!-- Model options -->
              <div class="space-y-4">
                <p class="text-sm font-semibold text-on-surface uppercase tracking-widest text-[10px]">Supported Models</p>
                <div class="space-y-3">
                  <div>
                    <p class="text-xs font-semibold text-on-surface mb-1.5">Transcription</p>
                    <div class="flex flex-wrap gap-2">
                      <span class="bg-background/60 border border-outline/20 rounded-full px-3 py-1 text-xs text-on-surface-variant font-light whitespace-nowrap">Whisper Large v3</span>
                      <span class="bg-background/60 border border-outline/20 rounded-full px-3 py-1 text-xs text-on-surface-variant font-light whitespace-nowrap">Whisper v3 Turbo</span>
                    </div>
                  </div>
                  <div>
                    <p class="text-xs font-semibold text-on-surface mb-1.5">Text Cleanup</p>
                    <div class="flex flex-wrap gap-2">
                      <span class="bg-background/60 border border-outline/20 rounded-full px-3 py-1 text-xs text-on-surface-variant font-light whitespace-nowrap">Llama 3.3 70B</span>
                      <span class="bg-background/60 border border-outline/20 rounded-full px-3 py-1 text-xs text-on-surface-variant font-light whitespace-nowrap">Llama 4 Scout</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="pt-4 md:pt-6 flex items-center gap-4 md:gap-6 border-t border-outline/30">
                <span class="material-symbols-outlined text-primary text-2xl md:text-3xl" aria-hidden="true">key</span>
                <span class="text-sm md:text-base font-medium text-on-surface italic">Powered by Groq — the fastest inference available.</span>
              </div>
            </div>

            <!-- Right: Price -->
            <div
              class="w-full md:w-2/5 bg-on-surface p-7 md:p-16 flex flex-col justify-center items-center text-center space-y-5 md:space-y-6 relative overflow-hidden">
              <div
                class="absolute inset-0 opacity-10 pointer-events-none bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-primary to-transparent"
                aria-hidden="true"></div>

              <!-- Badge -->
              <div class="relative z-10 bg-primary/20 text-primary text-[10px] uppercase tracking-[0.2em] font-bold px-4 py-1.5 rounded-full">
                Wordflow Lifetime
              </div>

              <!-- Launch price -->
              <div class="relative z-10">
                <span id="price-counter" class="text-7xl font-headline text-background">€25</span>
                <p class="text-background/40 text-sm font-light mt-1">One-time · Lifetime updates</p>
              </div>

              <!-- Price ladder -->
              <div class="relative z-10 w-full space-y-2 border-t border-background/10 pt-6">
                <p class="text-background/40 text-[10px] uppercase tracking-[0.2em] font-bold mb-3">Price increases as we grow</p>
                <div class="flex items-center justify-between text-sm">
                  <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                    <span class="text-background font-semibold">First 100 users</span>
                  </span>
                  <span class="text-primary font-bold">Free</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                  <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-background/30"></span>
                    <span class="text-background/50">Early Bird</span>
                  </span>
                  <span class="text-background/50">€10</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                  <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-background/20"></span>
                    <span class="text-background/40">Launch price</span>
                  </span>
                  <span class="text-background/40">€25</span>
                </div>
              </div>

              <a href="#early-access"
                class="relative z-10 w-full py-5 bg-primary rounded-full text-background font-bold hover:bg-primary/90 transition-all duration-300 shadow-xl shadow-primary/20 text-center block focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-on-surface min-h-[44px]">
                Claim my free copy →
              </a>
              <p class="relative z-10 text-background/30 text-xs font-light">Mac only · Instant download · No subscription</p>
            </div>

          </div>
        </div>
      </div>
    </section>

  </main>

  <?php require __DIR__ . '/_footer.php'; ?>

  <script>
    // ─── Scroll Progress Bar ────────────────────────────────────────
    // ─── Smooth scroll to hash on page load ────────────────────────
    if (window.location.hash) {
      const target = document.querySelector(window.location.hash);
      if (target) {
        setTimeout(() => {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
      }
    }

    // ─── Smooth scroll for all anchor links ─────────────────────────
    document.querySelectorAll('a[href^="#"]').forEach(link => {
      link.addEventListener('click', e => {
        const target = document.querySelector(link.getAttribute('href'));
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          history.pushState(null, '', link.getAttribute('href'));
        }
      });
    });

    const progressBar = document.getElementById('scroll-progress');
    window.addEventListener('scroll', () => {
      const scrolled = window.scrollY;
      const total = document.documentElement.scrollHeight - window.innerHeight;
      if (total > 0) progressBar.style.width = (scrolled / total * 100) + '%';
    }, { passive: true });

    // ─── Scroll Reveal ──────────────────────────────────────────────
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          revealObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });

    document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

    // ─── Parallax Blobs ─────────────────────────────────────────────
    const blobs = document.querySelectorAll('.parallax-blob');
    if (blobs.length) {
      window.addEventListener('scroll', () => {
        const y = window.scrollY;
        blobs.forEach((blob, i) => {
          const speed = i % 2 === 0 ? 0.07 : 0.04;
          blob.style.transform = `translateY(${y * speed}px)`;
        });
      }, { passive: true });
    }

    // ─── Price Counter ──────────────────────────────────────────────
    const priceEl = document.getElementById('price-counter');
    if (priceEl) {
      let counted = false;
      new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !counted) {
          counted = true;
          let val = 0;
          const target = 25;
          const duration = 1000;
          const startTime = performance.now();
          const tick = (now) => {
            const elapsed = now - startTime;
            val = Math.min(Math.round((elapsed / duration) * target), target);
            priceEl.textContent = '€' + val;
            if (val < target) {
              requestAnimationFrame(tick);
            } else {
              // Animation done — flip to current early-access price
              setTimeout(() => {
                priceEl.style.transition = 'opacity 0.4s ease';
                priceEl.style.opacity = '0';
                setTimeout(() => {
                  priceEl.textContent = 'Free';
                  priceEl.style.opacity = '1';
                  const sub = priceEl.nextElementSibling;
                  if (sub) sub.textContent = 'For the first 100 users';
                }, 400);
              }, 600);
            }
          };
          requestAnimationFrame(tick);
        }
      }, { threshold: 0.6 }).observe(priceEl);
    }

    // ─── Stats Counters ─────────────────────────────────────────────
    const statEls = document.querySelectorAll('.stat-counter');
    if (statEls.length) {
      const statObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (!entry.isIntersecting) return;
          const el = entry.target;
          const target = parseFloat(el.dataset.target);
          const decimals = parseInt(el.dataset.decimal || '0');
          const duration = 1200;
          const startTime = performance.now();
          const tick = (now) => {
            const progress = Math.min((now - startTime) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const val = eased * target;
            el.textContent = decimals ? val.toFixed(decimals) : Math.round(val);
            if (progress < 1) requestAnimationFrame(tick);
          };
          requestAnimationFrame(tick);
          statObserver.unobserve(el);
        });
      }, { threshold: 0.5 });
      statEls.forEach(el => statObserver.observe(el));
    }

    // ─── Early Access Signup (Email + Opt-in) ───────────────────────
    const notifyForm    = document.getElementById('notify-form');
    const notifyConsent = document.getElementById('notify-consent');
    const notifyBtn     = document.getElementById('notify-btn');

    // Enable submit only when checkbox is checked
    if (notifyConsent && notifyBtn) {
      notifyConsent.addEventListener('change', () => {
        notifyBtn.disabled = !notifyConsent.checked;
      });
    }

    if (notifyForm) {
      notifyForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (document.getElementById('notify-honeypot').value) return;

        const email   = document.getElementById('notify-email').value.trim();
        const consent = notifyConsent && notifyConsent.checked ? '1' : '0';
        if (!email || consent !== '1') return;

        const btn    = document.getElementById('notify-btn');
        const msgBox = document.getElementById('notify-message');
        btn.disabled = true;
        btn.textContent = 'Sending…';

        try {
          const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
          const res = await fetch('/notify', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-CSRF-Token': csrfToken,
            },
            body: 'email=' + encodeURIComponent(email) + '&consent=' + consent,
          });
          const data = await res.json();

          if (data.status === 'success' || data.status === 'already_registered') {
            notifyForm.classList.add('hidden');
            msgBox.classList.remove('hidden');
            msgBox.innerHTML =
              '<div class="bg-primary/20 border border-primary/30 rounded-2xl px-8 py-6 text-background space-y-2">'
              + '<p class="text-2xl font-headline italic">Check your inbox! 🎙</p>'
              + '<p class="text-background/70">We\'ve sent your download link to <strong>' + email + '</strong>. See you on the other side.</p>'
              + '</div>';
          } else {
            msgBox.classList.remove('hidden');
            msgBox.innerHTML = '<p class="text-background/60">' + (data.error || 'Something went wrong. Try again.') + '</p>';
            btn.disabled = !notifyConsent.checked;
            btn.textContent = 'Send me the download link →';
          }
        } catch (err) {
          btn.disabled = !notifyConsent.checked;
          btn.textContent = 'Send me the download link →';
          msgBox.classList.remove('hidden');
          msgBox.innerHTML = '<p class="text-red-400">Network error — please try again.</p>';
        }
      });
    }

    // ─── Early Access Signup ────────────────────────────────────────
    const form    = document.getElementById('signup-form');
    const btn     = document.getElementById('signup-btn');
    const msgBox  = document.getElementById('signup-message');
    const counter = document.getElementById('slots-counter');

    if (form) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (document.getElementById('signup-honeypot').value) return; // Bot
        const email = document.getElementById('signup-email').value.trim();
        if (!email) return;

        btn.disabled = true;
        btn.textContent = 'Sending…';

        let data = null;
        try {
          const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
          const res  = await fetch('/signup', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-CSRF-Token': csrfToken,
            },
            body: 'email=' + encodeURIComponent(email),
          });
          data = await res.json();
        } catch (err) {
          console.error('Signup fetch/parse error:', err);
          btn.disabled = false;
          btn.textContent = 'Claim my free copy →';
          msgBox.classList.remove('hidden');
          msgBox.innerHTML = '<p class="text-red-400">Network error — please try again.</p>';
          return;
        }

        form.classList.add('hidden');
        msgBox.classList.remove('hidden');

        if (data.status === 'success' && data.type === 'free') {
          msgBox.innerHTML =
            '<div class="bg-primary/20 border border-primary/30 rounded-2xl px-8 py-6 text-background space-y-2">'
            + '<p class="text-2xl font-headline italic">You\'re in! 🎙</p>'
            + '<p class="text-background/70">Check your inbox — your download link is on its way.</p>'
            + '</div>';
        } else if (data.status === 'success' && data.type === 'waitlist') {
          msgBox.innerHTML =
            '<div class="bg-white/10 border border-white/10 rounded-2xl px-8 py-6 text-background space-y-2">'
            + '<p class="text-2xl font-headline italic">You\'re on the list!</p>'
            + '<p class="text-background/70">All 100 free slots are taken. We\'ll notify you at launch.</p>'
            + '</div>';
        } else {
          // Server returned an error status (already_registered, validation error, etc.)
          form.classList.remove('hidden');
          msgBox.innerHTML =
            '<p class="text-background/60">' + (data.message || data.error || 'Something went wrong. Try again.') + '</p>';
          btn.disabled = false;
          btn.textContent = 'Claim my free copy →';
        }
      });
    }

    // ─── Hero Demo Animation ────────────────────────────────────────
    (function () {
      // Respect reduced-motion
      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

      // Raw text segments: filler words get a muted primary tint so you already see what will be removed
      const RAW_SEGMENTS = [
        { t: 'yeah so ',           filler: true  },
        { t: 'ähm, ',              filler: true  },
        { t: "let's meet on ",     filler: false },
        { t: 'Monday',             filler: true  },
        { t: ' — no wait, ',       filler: true  },
        { t: 'Tuesday, and focus on the editorial angle, ', filler: false },
        { t: "it's underserved...", filler: false },
      ];
      const RAW   = RAW_SEGMENTS.map(s => s.t).join('');
      const FINAL = "Let's meet on Tuesday. Focus on the editorial angle — it's underserved.";

      // Bubble 2 is built as HTML spans for strikethrough + insert
      function buildEnhanced() {
        return [
          '<span class="demo-strike" id="ds-yeah">yeah so</span> ',
          '<span class="demo-strike" id="ds-ahm">ähm</span>, ',
          'let\'s meet on ',
          '<span class="demo-strike" id="ds-mon">Monday</span>',
          ' <span class="demo-insert" id="di-tue">Tuesday</span>',
          ', and focus on the editorial angle, ',
          "it's underserved."
        ].join('');
      }

      // Typewriter helper — plain text
      function typeInto(el, text, speed, cb) {
        let i = 0;
        el.textContent = '';
        function tick() {
          if (i < text.length) {
            el.textContent += text[i++];
            setTimeout(tick, speed + Math.random() * 14);
          } else if (cb) cb();
        }
        tick();
      }

      // Typewriter for bubble 1 — segments, filler words muted orange
      function typeSegments(el, segments, speed, cb) {
        el.innerHTML = '';
        let segIdx = 0, charIdx = 0;
        // pre-create all spans
        const spans = segments.map(s => {
          const span = document.createElement('span');
          if (s.filler) span.style.cssText = 'color:rgba(255,140,50,0.9);';
          el.appendChild(span);
          return span;
        });
        function tick() {
          if (segIdx >= segments.length) { if (cb) cb(); return; }
          const seg = segments[segIdx];
          if (charIdx < seg.t.length) {
            spans[segIdx].textContent += seg.t[charIdx++];
            setTimeout(tick, speed + Math.random() * 14);
          } else {
            segIdx++; charIdx = 0;
            setTimeout(tick, speed);
          }
        }
        tick();
      }

      // Show an element by opacity
      function show(el, cb) {
        el.style.opacity = '1';
        if (cb) setTimeout(cb, 500);
      }

      // ── Desktop ──────────────────────────────────────────────────
      const isDesktop = () => window.innerWidth >= 1024;

      function runDesktop() {
        const label1  = document.getElementById('dl-label-1');
        const bubble1 = document.getElementById('dl-bubble-1');
        const text1   = document.getElementById('dl-text-1');
        const label2  = document.getElementById('dl-label-2');
        const bubble2 = document.getElementById('dl-bubble-2');
        const text2   = document.getElementById('dl-text-2');
        const label3  = document.getElementById('dl-label-3');
        const bubble3 = document.getElementById('dl-bubble-3');
        const text3   = document.getElementById('dl-text-3');
        const timerEl  = document.getElementById('dl-timer');
        const sloganEl = document.getElementById('dl-slogan');
        if (!label1 || !text1) return;

        // Reset
        [label1,bubble1,label2,bubble2,label3,bubble3].forEach(el => el.style.opacity = '0');
        [text1,text3].forEach(el => el.textContent = '');
        if (text2) text2.innerHTML = '';
        if (timerEl)  timerEl.textContent  = '· 0.0s';
        if (sloganEl) sloganEl.style.opacity = '0';

        // Step 1: show label + bubble, type raw text with filler highlights
        setTimeout(() => {
          show(label1);
          setTimeout(() => {
            show(bubble1, () => {
              typeSegments(text1, RAW_SEGMENTS, 30, () => {

                // Step 2: show enhancing label + bubble
                setTimeout(() => {
                  show(label2);
                  setTimeout(() => {
                    show(bubble2, () => {
                      if (text2) text2.innerHTML = buildEnhanced();

                      // Animate timer 0.0 → 0.6s
                      let t = 0;
                      const timerInterval = setInterval(() => {
                        t += 0.1;
                        if (timerEl) timerEl.textContent = '· ' + t.toFixed(1) + 's';
                        if (t >= 0.6) clearInterval(timerInterval);
                      }, 100);

                      // Strikethrough one by one
                      const strikes = [
                        { el: 'ds-yeah', insert: null,     delay: 300  },
                        { el: 'ds-ahm',  insert: null,     delay: 700  },
                        { el: 'ds-mon',  insert: 'di-tue', delay: 1100 },
                      ];
                      strikes.forEach(s => {
                        setTimeout(() => {
                          const sEl = document.getElementById(s.el);
                          if (sEl) sEl.classList.add('struck');
                          if (s.insert) setTimeout(() => {
                            const iEl = document.getElementById(s.insert);
                            if (iEl) iEl.classList.add('shown');
                          }, 250);
                        }, s.delay);
                      });

                      // Step 3 after enhancements done
                      setTimeout(() => {
                        show(label3);
                        setTimeout(() => {
                          show(bubble3, () => {
                            typeInto(text3, FINAL, 34, () => {
                              // Show slogan
                              setTimeout(() => {
                                if (sloganEl) sloganEl.style.opacity = '1';
                                // Loop after 4.5s pause
                                setTimeout(runDesktop, 4500);
                              }, 400);
                            });
                          });
                        }, 200);
                      }, 2000);
                    });
                  }, 200);
                }, 400);

              });
            });
          }, 200);
        }, 800);
      }

      // ── Mobile ───────────────────────────────────────────────────
      function runMobile() {
        const stepNum  = document.getElementById('mob-step-num');
        const stepName = document.getElementById('mob-step-name');
        const dots     = document.querySelectorAll('.mob-dot');
        const b1 = document.getElementById('mob-bubble-1');
        const b2 = document.getElementById('mob-bubble-2');
        const b3 = document.getElementById('mob-bubble-3');
        const t1 = document.getElementById('mob-text-1');
        const t2 = document.getElementById('mob-text-2');
        const t3 = document.getElementById('mob-text-3');
        const mTimer   = document.getElementById('mob-timer');
        const mSloganEl = document.getElementById('mob-slogan');
        if (!b1 || !t1) return;

        const stepLabels = [
          { num: '1', name: 'You speak',          numCls: 'bg-red-400/15 text-red-400' },
          { num: '2', name: 'Enhancing',           numCls: 'bg-amber-400/20 text-amber-600' },
          { num: '3', name: 'Ready at your cursor',numCls: 'bg-primary/15 text-primary' },
        ];

        function setDot(idx) {
          dots.forEach((d, i) => {
            d.style.background = i === idx ? '#D2691E' : 'rgba(209,205,199,0.4)';
            d.style.width = i === idx ? '6px' : '6px';
          });
          if (stepNum)  { stepNum.textContent  = stepLabels[idx].num;  stepNum.className  = 'flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold ' + stepLabels[idx].numCls; }
          if (stepName) stepName.textContent = stepLabels[idx].name;
        }

        function showCard(show, hide1, hide2) {
          [hide1, hide2].filter(Boolean).forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(24px)';
            el.style.pointerEvents = 'none';
          });
          show.style.opacity = '1';
          show.style.transform = 'translateY(0)';
          show.style.pointerEvents = '';
        }

        // Reset
        [t1, t3].forEach(el => { if(el) el.textContent = ''; });
        if (t2) t2.innerHTML = '';
        if (mTimer) mTimer.textContent = '· 0.0s';
        if (mSloganEl) mSloganEl.style.opacity = '0';
        showCard(b1, b2, b3);
        setDot(0);

        // Step 1
        setTimeout(() => {
          typeSegments(t1, RAW_SEGMENTS, 30, () => {

            // → Step 2
            setTimeout(() => {
              setDot(1);
              showCard(b2, b1, b3);
              if (t2) t2.innerHTML = buildEnhanced();

              let tm = 0;
              const ti = setInterval(() => {
                tm += 0.1;
                if (mTimer) mTimer.textContent = '· ' + tm.toFixed(1) + 's';
                if (tm >= 0.6) clearInterval(ti);
              }, 100);

              const mStrikes = [
                { el: 'ds-yeah', insert: null,     delay: 300  },
                { el: 'ds-ahm',  insert: null,     delay: 700  },
                { el: 'ds-mon',  insert: 'di-tue', delay: 1100 },
              ];
              mStrikes.forEach(s => {
                setTimeout(() => {
                  const sEl = document.getElementById(s.el);
                  if (sEl) sEl.classList.add('struck');
                  if (s.insert) setTimeout(() => {
                    const iEl = document.getElementById(s.insert);
                    if (iEl) iEl.classList.add('shown');
                  }, 250);
                }, s.delay);
              });

              // → Step 3 (extra 800ms so user can read the corrections)
              setTimeout(() => {
                setDot(2);
                showCard(b3, b1, b2);
                typeInto(t3, FINAL, 34, () => {
                  // Show slogan
                  setTimeout(() => {
                    if (mSloganEl) mSloganEl.style.opacity = '1';
                    // Loop after 4.5s pause
                    setTimeout(runMobile, 4500);
                  }, 400);
                });
              }, 3000);

            }, 500);
          });
        }, 600);
      }

      // Start on correct breakpoint, restart on resize
      let currentMode = null;
      function startDemo() {
        const mode = isDesktop() ? 'desktop' : 'mobile';
        if (mode === currentMode) return;
        currentMode = mode;
        if (mode === 'desktop') runDesktop();
        else runMobile();
      }

      // Pause when tab not visible
      document.addEventListener('visibilitychange', () => {
        if (!document.hidden) startDemo();
      });

      window.addEventListener('resize', () => {
        const mode = isDesktop() ? 'desktop' : 'mobile';
        if (mode !== currentMode) { currentMode = null; startDemo(); }
      });

      // Kick off after hero entrance animation
      setTimeout(startDemo, 900);
    })();

  </script>
</body>

</html>