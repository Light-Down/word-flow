<?php
require_once __DIR__ . '/security.php';
csrf_token();
require_once __DIR__ . '/db.php';

// ─── Handle Notify Me (POST /roadmap/notify) ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'notify') {
    ob_start();
    header('Content-Type: application/json');
    csrf_verify();
    rate_limit('roadmap_notify', 5, 900);
    $email   = trim($_POST['email']   ?? '');
    $feature = trim($_POST['feature'] ?? '');
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        ob_end_clean();
        echo json_encode(['error' => 'A valid email is required.']);
        exit;
    }
    $db = get_db();
    $db->exec("CREATE TABLE IF NOT EXISTS roadmap_notify (
        id      INTEGER PRIMARY KEY AUTOINCREMENT,
        email   TEXT NOT NULL,
        feature TEXT NOT NULL,
        created INTEGER NOT NULL
    )");
    $db->prepare("INSERT INTO roadmap_notify (email, feature, created) VALUES (?, ?, ?)")
       ->execute([$email, $feature, time()]);
    ob_end_clean();
    echo json_encode(['status' => 'success']);
    exit;
}

// ─── Handle Vote (POST /roadmap/vote) ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'vote') {
    ob_start();
    header('Content-Type: application/json');
    csrf_verify();
    rate_limit('roadmap_vote', 3, 900);
    $vote = trim($_POST['vote'] ?? '');
    if (!$vote) {
        http_response_code(400);
        ob_end_clean();
        echo json_encode(['error' => 'No vote provided.']);
        exit;
    }
    $db = get_db();
    $db->exec("CREATE TABLE IF NOT EXISTS roadmap_votes (
        id      INTEGER PRIMARY KEY AUTOINCREMENT,
        vote    TEXT NOT NULL,
        ip      TEXT NOT NULL,
        created INTEGER NOT NULL
    )");
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    // One vote per IP
    $existing = $db->prepare("SELECT id FROM roadmap_votes WHERE ip = ?")->execute([$ip]);
    $db->prepare("INSERT INTO roadmap_votes (vote, ip, created) VALUES (?, ?, ?)")
       ->execute([$vote, $ip, time()]);
    ob_end_clean();
    echo json_encode(['status' => 'success']);
    exit;
}

$_csrf = csrf_token();
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Built in Public — What's Next for Wordflow</title>
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
    #scroll-progress {
      position: fixed; top: 0; left: 0;
      height: 2px; width: 0%;
      background: #D2691E; z-index: 200;
      transition: width 0.08s linear;
    }
    .reveal {
      opacity: 0;
      transform: translateY(28px);
      transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1),
                  transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: 0.08s; }
    .reveal-delay-2 { transition-delay: 0.16s; }

    .hero-fade {
      opacity: 0; transform: translateY(20px);
      animation: heroFadeUp 0.85s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes heroFadeUp { to { opacity: 1; transform: translateY(0); } }
    .hd1 { animation-delay: 0.05s; }
    .hd2 { animation-delay: 0.18s; }
    .hd3 { animation-delay: 0.30s; }
    .hd4 { animation-delay: 0.42s; }

    /* Phase column cards */
    .phase-card {
      transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1),
                  box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Notify modal */
    #notify-modal {
      transition: opacity 0.2s ease;
    }

    /* Vote option */
    .vote-option input:checked + label {
      background: #F2EDE6;
      border-color: #D2691E;
      color: #1A1A1A;
    }
    .vote-option label {
      transition: all 0.15s ease;
    }

    /* Fluid typography */
    .fluid-h1  { font-size: clamp(2rem, 4vw + 1rem, 4.5rem); line-height: 1.05; }
    .fluid-h2  { font-size: clamp(1.5rem, 3vw + 0.5rem, 3rem); line-height: 1.1; }
    .fluid-lead{ font-size: clamp(1.0625rem, 1vw + 0.8rem, 1.25rem); }

    body { overflow-x: hidden; }

    /* Hero background texture */
    .hero-section {
      position: relative;
    }

    /* Roadmap label big display */
    .roadmap-eyebrow {
      font-family: 'Newsreader', serif;
      font-style: italic;
      font-size: clamp(0.7rem, 1vw, 0.85rem);
      letter-spacing: 0.22em;
      text-transform: uppercase;
    }

    /* Stat pills */
    .stat-pill {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-pill:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(26,26,26,0.08);
    }

    /* Free access banner */
    .free-banner {
      background: linear-gradient(135deg, #1A1A1A 0%, #2C3E50 100%);
    }
  </style>
</head>

<body class="bg-background text-on-surface font-body selection:bg-primary/20">

  <div id="scroll-progress" aria-hidden="true"></div>

  <?php require __DIR__ . '/_nav.php'; ?>

  <main class="pt-32 md:pt-40 pb-16 md:pb-32" style="background: radial-gradient(ellipse 80% 40% at 50% 0%, rgba(210,105,30,0.06) 0%, transparent 60%)">

    <!-- ─── Hero ─────────────────────────────────────────────────── -->
    <section class="hero-section max-w-5xl mx-auto px-5 md:px-8 mb-16 md:mb-24">

      <!-- Top label row -->
      <div class="hero-fade hd1 flex flex-col sm:flex-row items-center justify-center gap-3 mb-10 md:mb-14">
        <div class="flex items-center gap-2 bg-surface-container border border-outline/30 rounded-full px-4 py-1.5">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          <span class="roadmap-eyebrow text-on-surface-variant">Built in Public</span>
        </div>
        <span class="hidden sm:block text-outline/60">·</span>
        <div class="flex items-center gap-2 bg-primary/8 border border-primary/20 rounded-full px-4 py-1.5">
          <span class="material-symbols-outlined text-primary" style="font-size:14px;">map</span>
          <span class="roadmap-eyebrow text-primary">Roadmap 2026</span>
        </div>
      </div>

      <!-- Main headline -->
      <div class="text-center mb-10 md:mb-12">
        <h1 class="hero-fade hd2 font-headline fluid-h1 text-on-surface mb-5">
          Not finished —<br />
          <span class="italic text-primary font-light">getting better.</span>
        </h1>
        <p class="hero-fade hd3 fluid-lead text-on-surface-variant font-light leading-relaxed max-w-2xl mx-auto">
          I build Wordflow for myself first. Here's what I'm actually working on,
          what's coming next, and where I want to take this. No fake deadlines — just honest progress.
        </p>
      </div>

      <!-- Stat pills row -->
      <div class="hero-fade hd3 flex flex-wrap items-center justify-center gap-3 mb-10">
        <div class="stat-pill flex items-center gap-2.5 bg-background border border-outline/25 rounded-full px-5 py-2.5" style="box-shadow: 0 2px 12px rgba(26,26,26,0.05);">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
          <span class="text-sm font-medium text-on-surface">4 things in progress</span>
        </div>
        <div class="stat-pill flex items-center gap-2.5 bg-background border border-outline/25 rounded-full px-5 py-2.5" style="box-shadow: 0 2px 12px rgba(26,26,26,0.05);">
          <span class="w-2.5 h-2.5 rounded-full bg-sky-400 shrink-0"></span>
          <span class="text-sm font-medium text-on-surface">4 features planned next</span>
        </div>
        <div class="stat-pill flex items-center gap-2.5 bg-background border border-outline/25 rounded-full px-5 py-2.5" style="box-shadow: 0 2px 12px rgba(26,26,26,0.05);">
          <span class="w-2.5 h-2.5 rounded-full bg-primary shrink-0"></span>
          <span class="text-sm font-medium text-on-surface">4 big dreams ahead</span>
        </div>
      </div>

      <!-- Free access banner -->
      <div class="hero-fade hd4">
        <div class="free-banner rounded-2xl px-6 py-5 flex flex-col sm:flex-row items-center gap-4 text-background" style="box-shadow: 0 8px 32px rgba(26,26,26,0.12);">
          <div class="flex items-center gap-3 flex-1">
            <span class="material-symbols-outlined text-primary text-xl filled shrink-0">stars</span>
            <div>
              <p class="font-semibold text-sm">Get updates as they ship — completely free.</p>
              <p class="text-background/50 text-xs font-light mt-0.5">First 100 users get early access at no cost, including every feature on this roadmap.</p>
            </div>
          </div>
          <a href="/#early-access"
            class="shrink-0 flex items-center gap-2 px-6 py-3 bg-primary text-background rounded-full text-sm font-semibold hover:opacity-90 transition-opacity whitespace-nowrap min-h-[44px]">
            Claim free copy
            <span class="material-symbols-outlined text-base">arrow_forward</span>
          </a>
        </div>
      </div>

    </section>

    <!-- ─── Now / Next / Future ───────────────────────────────────── -->
    <section class="max-w-7xl mx-auto px-5 md:px-8 mb-20 md:mb-32">

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- NOW -->
        <div class="reveal phase-card bg-background rounded-2xl border border-outline/20 overflow-hidden" style="box-shadow: 0 4px 24px rgba(26,26,26,0.05);">
          <!-- Header -->
          <div class="px-8 pt-8 pb-6 border-b border-outline/15">
            <div class="flex items-center gap-3 mb-3">
              <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></span>
              <span class="text-xs uppercase tracking-[0.2em] font-semibold text-on-surface-variant">Now</span>
            </div>
            <h2 class="font-headline text-2xl text-on-surface">On the stove.</h2>
            <p class="text-sm text-on-surface-variant font-light mt-1">What I'm actively working on right now.</p>
          </div>
          <!-- Items -->
          <ul class="px-8 py-6 space-y-4">
            <li class="flex items-start gap-3">
              <span class="material-symbols-outlined text-emerald-500 text-base mt-0.5 shrink-0 filled">check_circle</span>
              <span class="text-sm text-on-surface leading-snug">Bugfixes &amp; onboarding improvements</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="material-symbols-outlined text-emerald-500 text-base mt-0.5 shrink-0 filled">check_circle</span>
              <span class="text-sm text-on-surface leading-snug">Feedback button directly inside the app</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="material-symbols-outlined text-emerald-500 text-base mt-0.5 shrink-0 filled">check_circle</span>
              <span class="text-sm text-on-surface leading-snug">Auto-update mechanism</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="material-symbols-outlined text-emerald-500 text-base mt-0.5 shrink-0 filled">check_circle</span>
              <span class="text-sm text-on-surface leading-snug">Website improvements &amp; this roadmap</span>
            </li>
          </ul>
        </div>

        <!-- NEXT -->
        <div class="reveal reveal-delay-1 phase-card bg-secondary rounded-2xl overflow-hidden" style="box-shadow: 0 4px 24px rgba(44,62,80,0.18);">
          <!-- Header -->
          <div class="px-8 pt-8 pb-6 border-b border-white/10">
            <div class="flex items-center gap-3 mb-3">
              <span class="w-3 h-3 rounded-full bg-sky-400 shadow-sm shadow-sky-400/50"></span>
              <span class="text-xs uppercase tracking-[0.2em] font-semibold text-background/50">Next</span>
            </div>
            <h2 class="font-headline text-2xl text-background">On the menu.</h2>
            <p class="text-sm text-background/50 font-light mt-1">What's coming in the next phase.</p>
          </div>
          <!-- Items -->
          <ul class="px-8 py-6 space-y-4">
            <li class="flex items-start justify-between gap-3 group">
              <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-sky-400 text-base mt-0.5 shrink-0">schedule</span>
                <span class="text-sm text-background/80 leading-snug">Pill design customisation in-app</span>
              </div>
              <button onclick="openNotify('Pill design customisation')"
                class="shrink-0 text-[10px] font-semibold uppercase tracking-wider text-sky-400 border border-sky-400/30 px-2.5 py-1 rounded-full hover:bg-sky-400/10 transition-colors whitespace-nowrap">
                Notify me
              </button>
            </li>
            <li class="flex items-start justify-between gap-3 group">
              <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-sky-400 text-base mt-0.5 shrink-0">schedule</span>
                <span class="text-sm text-background/80 leading-snug">More tone profiles &amp; customisation</span>
              </div>
              <button onclick="openNotify('More tone profiles')"
                class="shrink-0 text-[10px] font-semibold uppercase tracking-wider text-sky-400 border border-sky-400/30 px-2.5 py-1 rounded-full hover:bg-sky-400/10 transition-colors whitespace-nowrap">
                Notify me
              </button>
            </li>
            <li class="flex items-start justify-between gap-3 group">
              <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-sky-400 text-base mt-0.5 shrink-0">schedule</span>
                <span class="text-sm text-background/80 leading-snug">Additional language support</span>
              </div>
              <button onclick="openNotify('Additional language support')"
                class="shrink-0 text-[10px] font-semibold uppercase tracking-wider text-sky-400 border border-sky-400/30 px-2.5 py-1 rounded-full hover:bg-sky-400/10 transition-colors whitespace-nowrap">
                Notify me
              </button>
            </li>
            <li class="flex items-start justify-between gap-3 group">
              <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-sky-400 text-base mt-0.5 shrink-0">schedule</span>
                <span class="text-sm text-background/80 leading-snug">More API providers (e.g. OpenAI)</span>
              </div>
              <button onclick="openNotify('More API providers')"
                class="shrink-0 text-[10px] font-semibold uppercase tracking-wider text-sky-400 border border-sky-400/30 px-2.5 py-1 rounded-full hover:bg-sky-400/10 transition-colors whitespace-nowrap">
                Notify me
              </button>
            </li>
          </ul>
        </div>

        <!-- FUTURE -->
        <div class="reveal reveal-delay-2 phase-card bg-on-surface rounded-2xl overflow-hidden" style="box-shadow: 0 4px 24px rgba(26,26,26,0.12);">
          <!-- Header -->
          <div class="px-8 pt-8 pb-6 border-b border-white/10">
            <div class="flex items-center gap-3 mb-3">
              <span class="w-3 h-3 rounded-full bg-primary shadow-sm shadow-primary/50"></span>
              <span class="text-xs uppercase tracking-[0.2em] font-semibold text-background/40">Future</span>
            </div>
            <h2 class="font-headline text-2xl text-background">The big dream.</h2>
            <p class="text-sm text-background/40 font-light mt-1">Longer horizon. Real ambition.</p>
          </div>
          <!-- Items -->
          <ul class="px-8 py-6 space-y-4">
            <li class="flex items-start justify-between gap-3">
              <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary text-base mt-0.5 shrink-0">explore</span>
                <span class="text-sm text-background/70 leading-snug">iOS Custom Keyboard App</span>
              </div>
              <button onclick="openNotify('iOS App')"
                class="shrink-0 text-[10px] font-semibold uppercase tracking-wider text-primary border border-primary/30 px-2.5 py-1 rounded-full hover:bg-primary/10 transition-colors whitespace-nowrap">
                Notify me
              </button>
            </li>
            <li class="flex items-start justify-between gap-3">
              <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary text-base mt-0.5 shrink-0">explore</span>
                <span class="text-sm text-background/70 leading-snug">Windows &amp; Linux support</span>
              </div>
              <button onclick="openNotify('Windows & Linux')"
                class="shrink-0 text-[10px] font-semibold uppercase tracking-wider text-primary border border-primary/30 px-2.5 py-1 rounded-full hover:bg-primary/10 transition-colors whitespace-nowrap">
                Notify me
              </button>
            </li>
            <li class="flex items-start justify-between gap-3">
              <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-primary text-base mt-0.5 shrink-0">explore</span>
                <span class="text-sm text-background/70 leading-snug">Credit system — no API key needed</span>
              </div>
              <button onclick="openNotify('Credit system')"
                class="shrink-0 text-[10px] font-semibold uppercase tracking-wider text-primary border border-primary/30 px-2.5 py-1 rounded-full hover:bg-primary/10 transition-colors whitespace-nowrap">
                Notify me
              </button>
            </li>
            <li class="flex items-start gap-3">
              <span class="material-symbols-outlined text-primary text-base mt-0.5 shrink-0">explore</span>
              <span class="text-sm text-background/70 leading-snug">Wordflow ecosystem: Mac + iOS + all platforms</span>
            </li>
          </ul>
        </div>

      </div>

      <!-- Disclaimer -->
      <p class="text-center text-xs text-on-surface-variant/50 mt-6 font-light">
        No fixed dates — just honest priorities. Things shift. I'll always be transparent about it.
      </p>

    </section>

    <!-- ─── Vote ──────────────────────────────────────────────────── -->
    <section class="max-w-3xl mx-auto px-5 md:px-8 mb-20 md:mb-32">
      <div class="reveal bg-surface-container rounded-3xl p-8 md:p-12 border border-outline/20">

        <div class="mb-8">
          <span class="text-xs uppercase tracking-[0.2em] font-semibold text-primary">Your voice matters</span>
          <h2 class="font-headline text-3xl md:text-4xl text-on-surface mt-2">
            If you could only pick one —<br />
            <span class="italic font-light text-on-surface-variant">what comes next?</span>
          </h2>
        </div>

        <form id="vote-form" class="space-y-3">
          <?php
          $voteOptions = [
            'iOS App'                => 'iOS App',
            'More tone profiles'     => 'More tone profiles &amp; customisation',
            'Windows & Linux'        => 'Windows &amp; Linux support',
            'Credit system'          => 'Credit system (no API key)',
            'More API providers'     => 'More API providers',
            'Language support'       => 'More language support',
          ];
          foreach ($voteOptions as $val => $label): ?>
          <div class="vote-option">
            <input type="radio" name="vote" id="vote-<?= htmlspecialchars($val) ?>" value="<?= htmlspecialchars($val) ?>" class="sr-only" />
            <label for="vote-<?= htmlspecialchars($val) ?>"
              class="flex items-center gap-4 w-full px-5 py-4 bg-background border border-outline/30 rounded-xl cursor-pointer hover:border-primary/40 hover:bg-surface-container-low font-body text-sm text-on-surface">
              <span class="w-4 h-4 rounded-full border-2 border-outline/50 shrink-0 flex items-center justify-center vote-dot">
                <span class="w-2 h-2 rounded-full bg-primary opacity-0 scale-0 transition-all duration-150 vote-fill"></span>
              </span>
              <?= $label ?>
            </label>
          </div>
          <?php endforeach; ?>

          <div class="pt-4 flex items-center gap-4">
            <p id="vote-msg" class="hidden text-sm font-body flex-1"></p>
            <button type="submit" id="vote-btn"
              class="ml-auto px-8 py-3 bg-on-surface text-background rounded-full font-body font-medium text-sm hover:opacity-90 transition-opacity min-h-[44px]">
              Cast my vote
            </button>
          </div>
        </form>

      </div>
    </section>

    <!-- ─── Build in Public ───────────────────────────────────────── -->
    <section class="max-w-3xl mx-auto px-5 md:px-8 mb-20 md:mb-28">
      <div class="reveal rounded-3xl bg-background border border-outline/20 p-8 md:p-12 text-center space-y-6" style="box-shadow: 0 4px 32px rgba(26,26,26,0.06);">

        <span class="inline-block text-3xl">🍳</span>
        <h2 class="font-headline text-3xl text-on-surface">
          I build this in public —<br />
          <span class="italic font-light text-primary">setbacks included.</span>
        </h2>
        <p class="text-on-surface-variant font-light leading-relaxed max-w-xl mx-auto">
          No polished press releases. Just honest updates, failed experiments, and the occasional breakthrough — shared as it happens. If you want to follow the journey:
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
          <!-- TikTok placeholder — replace href when ready -->
          <a href="#" aria-label="TikTok"
            class="flex items-center gap-3 px-6 py-3 bg-on-surface text-background rounded-full text-sm font-semibold hover:opacity-90 transition-opacity">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.22 8.22 0 0 0 4.82 1.55V6.79a4.85 4.85 0 0 1-1.05-.1z"/></svg>
            TikTok
          </a>
          <!-- YouTube placeholder — replace href when ready -->
          <a href="#" aria-label="YouTube"
            class="flex items-center gap-3 px-6 py-3 bg-surface-container text-on-surface rounded-full text-sm font-semibold border border-outline/20 hover:bg-surface-container-high transition-colors">
            <svg class="w-4 h-4 fill-current text-red-500" viewBox="0 0 24 24" aria-hidden="true"><path d="M23.5 6.19a3.02 3.02 0 0 0-2.12-2.14C19.54 3.5 12 3.5 12 3.5s-7.54 0-9.38.55A3.02 3.02 0 0 0 .5 6.19C0 8.04 0 12 0 12s0 3.96.5 5.81a3.02 3.02 0 0 0 2.12 2.14C4.46 20.5 12 20.5 12 20.5s7.54 0 9.38-.55a3.02 3.02 0 0 0 2.12-2.14C24 15.96 24 12 24 12s0-3.96-.5-5.81zM9.75 15.5V8.5l6.25 3.5-6.25 3.5z"/></svg>
            YouTube
          </a>
        </div>

        <p class="text-xs text-on-surface-variant/40 font-light">(Links coming soon — channels in the works)</p>

      </div>
    </section>

    <!-- ─── CTA ───────────────────────────────────────────────────── -->
    <section class="max-w-3xl mx-auto px-5 md:px-8">
      <div class="reveal bg-on-surface rounded-3xl p-8 md:p-16 text-center space-y-6" style="box-shadow: 0 0 60px rgba(210,105,30,0.12);">
        <h2 class="font-headline fluid-h2 text-background leading-[1.1]">
          Like what you see? <span class="italic text-primary font-light">Join early.</span>
        </h2>
        <p class="fluid-lead text-background/60 font-light leading-relaxed">
          First 100 users get Wordflow completely free — and receive every future update automatically,
          including everything on this roadmap.
        </p>
        <a href="/#early-access"
          class="flex sm:inline-flex items-center justify-center gap-3 px-8 py-4 md:px-10 md:py-5 bg-primary rounded-full text-background font-semibold hover:bg-primary/90 transition-all duration-300 shadow-xl shadow-primary/20 group min-h-[52px]">
          Claim my free copy
          <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
        </a>
        <p class="text-background/30 text-xs font-light">After 100 spots: €25 one-time · Mac only · Lifetime updates</p>
      </div>
    </section>

  </main>

  <?php require __DIR__ . '/_footer.php'; ?>

  <!-- ─── Notify Me Modal ───────────────────────────────────────── -->
  <div id="notify-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-6"
    role="dialog" aria-modal="true" aria-labelledby="notify-modal-title">
    <div class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm" onclick="closeNotify()"></div>
    <div class="relative bg-background rounded-2xl border border-outline/30 p-8 md:p-10 w-full max-w-md" style="box-shadow: 0 24px 64px rgba(0,0,0,.12);">
      <button onclick="closeNotify()" class="absolute top-5 right-5 text-on-surface-variant hover:text-on-surface transition-colors" aria-label="Close">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
      <span class="material-symbols-outlined text-primary text-3xl mb-4 block">notifications</span>
      <h3 id="notify-modal-title" class="font-headline text-2xl text-on-surface mb-1">Notify me when it's ready.</h3>
      <p id="notify-feature-label" class="text-sm text-on-surface-variant font-light mb-6"></p>
      <form id="notify-form" class="space-y-4">
        <input id="notify-email" type="email" placeholder="Your email address" required autocomplete="email"
          class="w-full px-4 py-3 bg-surface-container border border-outline/40 rounded-lg font-body text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-primary transition-colors" />
        <div class="flex items-center gap-4">
          <p id="notify-msg" class="hidden text-sm font-body flex-1"></p>
          <button id="notify-btn" type="submit"
            class="ml-auto px-8 py-3 bg-on-surface text-background rounded-full font-body font-medium text-sm hover:opacity-90 transition-opacity min-h-[44px]">
            Notify me
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // ── Scroll progress ──────────────────────────────────────────────
    const bar = document.getElementById('scroll-progress');
    window.addEventListener('scroll', () => {
      const total = document.documentElement.scrollHeight - window.innerHeight;
      bar.style.width = (window.scrollY / total * 100) + '%';
    }, { passive: true });

    // ── Reveal on scroll ─────────────────────────────────────────────
    const ro = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) { e.target.classList.add('visible'); ro.unobserve(e.target); }
      });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => ro.observe(el));

    // ── Vote radio custom styling ────────────────────────────────────
    document.querySelectorAll('.vote-option input[type=radio]').forEach(radio => {
      radio.addEventListener('change', () => {
        document.querySelectorAll('.vote-fill').forEach(dot => {
          dot.style.opacity = '0'; dot.style.transform = 'scale(0)';
        });
        const fill = radio.parentElement.querySelector('.vote-fill');
        if (fill) { fill.style.opacity = '1'; fill.style.transform = 'scale(1)'; }
      });
    });

    // ── Vote submit ──────────────────────────────────────────────────
    document.getElementById('vote-form').addEventListener('submit', async function(e) {
      e.preventDefault();
      const selected = this.querySelector('input[name="vote"]:checked');
      if (!selected) return;
      const btn = document.getElementById('vote-btn');
      const msg = document.getElementById('vote-msg');
      btn.disabled = true; btn.textContent = 'Sending…';
      try {
        const res = await fetch('/roadmap', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= htmlspecialchars($_csrf) ?>',
          },
          body: new URLSearchParams({ _action: 'vote', vote: selected.value })
        });
        const data = await res.json();
        msg.classList.remove('hidden');
        if (res.ok) {
          msg.textContent = 'Vote registered — thanks!';
          msg.className = 'text-sm text-primary font-body flex-1';
          btn.textContent = 'Voted ✓';
        } else {
          msg.textContent = data.error || 'Something went wrong.';
          msg.className = 'text-sm text-red-500 font-body flex-1';
          btn.disabled = false; btn.textContent = 'Cast my vote';
        }
      } catch {
        msg.classList.remove('hidden');
        msg.textContent = 'Network error — please try again.';
        msg.className = 'text-sm text-red-500 font-body flex-1';
        btn.disabled = false; btn.textContent = 'Cast my vote';
      }
    });

    // ── Notify modal ─────────────────────────────────────────────────
    let currentFeature = '';
    function openNotify(feature) {
      currentFeature = feature;
      document.getElementById('notify-feature-label').textContent = 'Feature: ' + feature;
      document.getElementById('notify-email').value = '';
      document.getElementById('notify-msg').classList.add('hidden');
      document.getElementById('notify-btn').disabled = false;
      document.getElementById('notify-btn').textContent = 'Notify me';
      document.getElementById('notify-form').reset();
      const modal = document.getElementById('notify-modal');
      modal.classList.remove('hidden');
      setTimeout(() => modal.style.opacity = '1', 10);
      document.getElementById('notify-email').focus();
    }
    function closeNotify() {
      document.getElementById('notify-modal').classList.add('hidden');
    }
    document.getElementById('notify-form').addEventListener('submit', async function(e) {
      e.preventDefault();
      const btn = document.getElementById('notify-btn');
      const msg = document.getElementById('notify-msg');
      const email = document.getElementById('notify-email').value.trim();
      btn.disabled = true; btn.textContent = 'Saving…';
      try {
        const res = await fetch('/roadmap', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= htmlspecialchars($_csrf) ?>',
          },
          body: new URLSearchParams({ _action: 'notify', email, feature: currentFeature })
        });
        const data = await res.json();
        msg.classList.remove('hidden');
        if (res.ok) {
          msg.textContent = "You're on the list!";
          msg.className = 'text-sm text-primary font-body flex-1';
          btn.textContent = 'Done ✓';
          setTimeout(closeNotify, 1800);
        } else {
          msg.textContent = data.error || 'Something went wrong.';
          msg.className = 'text-sm text-red-500 font-body flex-1';
          btn.disabled = false; btn.textContent = 'Notify me';
        }
      } catch {
        msg.classList.remove('hidden');
        msg.textContent = 'Network error — please try again.';
        msg.className = 'text-sm text-red-500 font-body flex-1';
        btn.disabled = false; btn.textContent = 'Notify me';
      }
    });

    // ── Close modal on Escape ────────────────────────────────────────
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeNotify(); });
  </script>

</body>
</html>
