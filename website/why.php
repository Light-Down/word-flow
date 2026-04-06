<?php require_once __DIR__ . "/security.php"; csrf_token(); ?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>11 Reasons to Try Wordflow — Speak freely. Write brilliantly.</title>  <!-- Preload critical fonts -->
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
      transform: translateY(32px);
      transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1),
                  transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: 0.08s; }
    .reveal-delay-2 { transition-delay: 0.16s; }

    /* Hero fade */
    .hero-fade {
      opacity: 0; transform: translateY(20px);
      animation: heroFadeUp 0.85s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes heroFadeUp { to { opacity: 1; transform: translateY(0); } }
    .hd1 { animation-delay: 0.05s; }
    .hd2 { animation-delay: 0.18s; }
    .hd3 { animation-delay: 0.30s; }

    /* Reason cards */
    .reason-card {
      transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1),
                  box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reason-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 24px 56px rgba(26, 26, 26, 0.09);
    }

    /* Number accent */
    .reason-number {
      font-family: 'Newsreader', serif;
      font-style: italic;
      line-height: 1;
      color: #D2691E;
      opacity: 0.25;
    }

    /* CTA glow */
    .cta-glow {
      box-shadow: 0 0 60px rgba(210, 105, 30, 0.15);
    }

    /* ─── Fluid Typography ─── */
    .fluid-h1  { font-size: clamp(2rem,   4vw + 1rem,   4.5rem); line-height: 1.05; }
    .fluid-h2  { font-size: clamp(1.75rem, 3.5vw + 0.75rem, 3.75rem); line-height: 1.1; }
    .fluid-h3  { font-size: clamp(1.25rem, 2vw + 0.5rem, 2rem); }
    .fluid-lead{ font-size: clamp(1.0625rem, 1vw + 0.8rem, 1.375rem); }
    .fluid-body{ font-size: clamp(1rem, 0.5vw + 0.875rem, 1.25rem); }

    /* ─── Prevent horizontal overflow ─── */
    body { overflow-x: hidden; }
  </style>
</head>

<body class="bg-background text-on-surface font-body selection:bg-primary/20">

  <div id="scroll-progress" aria-hidden="true"></div>

  <!-- Nav -->
  <?php require __DIR__ . '/_nav.php'; ?>

  <main class="pt-32 md:pt-40 pb-16 md:pb-32">

    <!-- Hero -->
    <section class="max-w-4xl mx-auto px-5 md:px-8 text-center mb-12 md:mb-24">
      <div class="hero-fade hd1 inline-flex items-center gap-3 bg-surface-container px-5 py-2 rounded-full border border-outline/30 mb-6 md:mb-8">
        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
        <span class="text-xs uppercase tracking-[0.2em] text-on-surface-variant font-semibold">11 Reasons</span>
      </div>
      <h1 class="hero-fade hd2 font-headline fluid-h1 text-on-surface mb-6 md:mb-8">
        Why Wordflow <br /><span class="italic text-primary font-light">changes how you write.</span>
      </h1>
      <p class="hero-fade hd3 fluid-lead text-on-surface-variant font-light leading-relaxed max-w-2xl mx-auto">
        Not another AI writing tool. Not another subscription. Just the fastest way to turn your spoken thoughts into exactly the right words — in any app, any tone, any context.
      </p>
    </section>

    <!-- 11 Reasons Grid -->
    <section class="max-w-7xl mx-auto px-5 md:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 md:gap-6">

        <!-- Row 1: [01 wide] [02] -->

        <!-- 01 — col-span-2 -->
        <div class="reveal reason-card lg:col-span-2 bg-background rounded-2xl p-10 border border-outline/20 space-y-6" style="box-shadow: 0 4px 24px rgba(26,26,26,0.05);">
          <div class="flex items-start justify-between">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
              <span class="material-symbols-outlined text-primary">payments</span>
            </div>
            <span class="reason-number text-5xl">01</span>
          </div>
          <h2 class="font-headline text-3xl text-on-surface">No monthly bill. Ever.</h2>
          <p class="text-on-surface-variant font-light leading-relaxed max-w-xl">
            €25 once. That's it. No subscription creep, no annual renewal, no "we're raising prices" email. Most speech-to-text tools cost €12–20 per month — that's up to €240 a year for the same thing. You own Wordflow forever, including all future updates.
          </p>
          <div class="flex items-center gap-6 pt-2">
            <div>
              <p class="font-headline text-5xl text-primary">€25</p>
              <p class="text-xs text-on-surface-variant font-light mt-1">one-time</p>
            </div>
            <div class="text-on-surface-variant/30 font-headline text-2xl">vs.</div>
            <div>
              <p class="font-headline text-3xl text-on-surface-variant/40 line-through">€180+</p>
              <p class="text-xs text-on-surface-variant/40 font-light mt-1">per year elsewhere</p>
            </div>
          </div>
          <div class="flex items-center gap-2 text-xs text-primary font-medium">
            <span class="material-symbols-outlined text-sm filled">check_circle</span>
            Groq free tier = €0/month running cost on top
          </div>
        </div>

        <!-- 02 -->
        <div class="reveal reveal-delay-1 reason-card bg-background rounded-2xl p-10 border border-outline/20 space-y-5" style="box-shadow: 0 4px 24px rgba(26,26,26,0.05);">
          <div class="flex items-start justify-between">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
              <span class="material-symbols-outlined text-primary">apps</span>
            </div>
            <span class="reason-number text-5xl">02</span>
          </div>
          <h2 class="font-headline text-2xl text-on-surface">Works in every app.</h2>
          <p class="text-on-surface-variant font-light leading-relaxed">
            Notion, Slack, Mail, VS Code, Terminal, any browser — if your cursor is there, Wordflow works there. No plugin, no extension, no switching windows.
          </p>
          <div class="pt-2 flex items-center gap-2 text-xs text-on-surface-variant/50 font-medium">
            <span class="material-symbols-outlined text-sm">keyboard</span>
            One hotkey. Instant output at your cursor.
          </div>
        </div>

        <!-- Row 2: [03] [04] [05] -->

        <!-- 03 -->
        <div class="reveal reason-card bg-on-surface rounded-2xl p-10 space-y-5" style="box-shadow: 0 4px 24px rgba(26,26,26,0.1);">
          <div class="flex items-start justify-between">
            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
              <span class="material-symbols-outlined text-primary">record_voice_over</span>
            </div>
            <span class="reason-number text-5xl text-primary" style="opacity: 0.3;">03</span>
          </div>
          <h2 class="font-headline text-2xl text-background">Your voice. Your tone. Not generic AI.</h2>
          <p class="text-background/60 font-light leading-relaxed">
            Smart Casual keeps your slang — "Digger", "krass", "whatever" stays exactly as you said it. Three profiles, zero sanitizing. It sounds like <em>you</em> at your best.
          </p>
          <div class="pt-2 flex items-center gap-2 text-xs text-primary font-medium">
            <span class="material-symbols-outlined text-sm filled">check_circle</span>
            Smart Casual · Smart Business · Professional
          </div>
        </div>

        <!-- 04 -->
        <div class="reveal reveal-delay-1 reason-card bg-background rounded-2xl p-10 border border-outline/20 space-y-5" style="box-shadow: 0 4px 24px rgba(26,26,26,0.05);">
          <div class="flex items-start justify-between">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
              <span class="material-symbols-outlined text-primary">bolt</span>
            </div>
            <span class="reason-number text-5xl">04</span>
          </div>
          <h2 class="font-headline text-2xl text-on-surface">Under one second.</h2>
          <p class="text-on-surface-variant font-light leading-relaxed">
            Groq is the fastest inference available. You speak, you press the hotkey, you blink — and your polished text is already at your cursor. No loading spinner, no waiting.
          </p>
          <div class="pt-2 flex items-center gap-2 text-xs text-on-surface-variant/50 font-medium">
            <span class="material-symbols-outlined text-sm">timer</span>
            Typically 0.4 – 0.8s end-to-end
          </div>
        </div>

        <!-- 05 -->
        <div class="reveal reveal-delay-2 reason-card bg-background rounded-2xl p-10 border border-outline/20 space-y-5" style="box-shadow: 0 4px 24px rgba(26,26,26,0.05);">
          <div class="flex items-start justify-between">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
              <span class="material-symbols-outlined text-primary">lock</span>
            </div>
            <span class="reason-number text-5xl">05</span>
          </div>
          <h2 class="font-headline text-2xl text-on-surface">Your data never touches our servers.</h2>
          <p class="text-on-surface-variant font-light leading-relaxed">
            Wordflow has no backend. Your audio goes from your Mac directly to Groq via your own API key — and nowhere else. No Wordflow server ever sees your text.
          </p>
          <div class="pt-2 flex items-center gap-2 text-xs text-primary font-medium">
            <span class="material-symbols-outlined text-sm filled">check_circle</span>
            BYOK — your key, your data, your control
          </div>
        </div>

        <!-- Row 3: [06] [07 wide] -->

        <!-- 06 -->
        <div class="reveal reason-card bg-surface-container rounded-2xl p-10 border border-outline/20 space-y-5" style="box-shadow: 0 4px 24px rgba(26,26,26,0.05);">
          <div class="flex items-start justify-between">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
              <span class="material-symbols-outlined text-primary">undo</span>
            </div>
            <span class="reason-number text-5xl">06</span>
          </div>
          <h2 class="font-headline text-2xl text-on-surface">Self-corrections just work.</h2>
          <p class="text-on-surface-variant font-light leading-relaxed">
            "Monday — no wait, Tuesday." Wordflow catches the correction and outputs "Tuesday." No double text, no cleanup needed.
          </p>
          <div class="bg-background rounded-xl p-4 font-mono text-sm border border-outline/20">
            <span class="text-on-surface-variant/50">"Monday — no wait, Tuesday"</span><br/>
            <span class="text-primary">→ "Tuesday"</span>
          </div>
        </div>

        <!-- 07 — col-span-2, FIXED -->
        <div class="reveal reveal-delay-1 reason-card lg:col-span-2 bg-secondary rounded-2xl p-10 space-y-6" style="box-shadow: 0 4px 24px rgba(44,62,80,0.2);">
          <div class="flex items-start justify-between">
            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
              <span class="material-symbols-outlined text-white/70">translate</span>
            </div>
            <span class="font-headline italic text-5xl text-white/15 leading-none">07</span>
          </div>
          <h2 class="font-headline text-3xl text-background">Speak German. Write in English.</h2>
          <p class="text-background/60 font-light leading-relaxed max-w-xl">
            Set your output language once in the app. No matter what language you speak, Wordflow always writes in your chosen language — perfect for non-native writers who think faster in their mother tongue.
          </p>
          <div class="flex items-center gap-4 bg-white/5 rounded-xl px-6 py-4 border border-white/10 w-fit font-mono text-sm">
            <span class="text-background/50 italic">"...ich denk das sollten wir besprechen"</span>
            <span class="material-symbols-outlined text-primary text-base">arrow_forward</span>
            <span class="text-background">"We should discuss this."</span>
          </div>
          <div class="flex items-center gap-2 text-xs text-background/40 font-medium">
            <span class="material-symbols-outlined text-sm">settings</span>
            Language can be switched at any time in settings
          </div>
        </div>

        <!-- Row 4: [08] [09] [10] -->

        <!-- 08 -->
        <div class="reveal reason-card bg-primary rounded-2xl p-10 space-y-5" style="box-shadow: 0 4px 24px rgba(210,105,30,0.2);">
          <div class="flex items-start justify-between">
            <div class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center">
              <span class="material-symbols-outlined text-background">description</span>
            </div>
            <span class="font-headline italic text-5xl text-background/20 leading-none">08</span>
          </div>
          <h2 class="font-headline text-2xl text-background">Meeting notes in seconds.</h2>
          <p class="text-background/70 font-light leading-relaxed">
            Rattle off your action items out loud after a call — Wordflow automatically structures them into bullet points. Ready to paste into Notion.
          </p>
          <div class="pt-2 flex items-center gap-2 text-xs text-background/60 font-medium">
            <span class="material-symbols-outlined text-sm filled">check_circle</span>
            Lists auto-detected from speech
          </div>
        </div>

        <!-- 09 -->
        <div class="reveal reveal-delay-1 reason-card bg-background rounded-2xl p-10 border border-outline/20 space-y-5" style="box-shadow: 0 4px 24px rgba(26,26,26,0.05);">
          <div class="flex items-start justify-between">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
              <span class="material-symbols-outlined text-primary">waving_hand</span>
            </div>
            <span class="reason-number text-5xl">09</span>
          </div>
          <h2 class="font-headline text-2xl text-on-surface">Built by someone who uses it daily.</h2>
          <p class="text-on-surface-variant font-light leading-relaxed">
            Not VC-backed. Not built to maximize retention. A tool I built because I was tired of paying monthly for something this simple. I use it every single day.
          </p>
          <p class="text-on-surface-variant/50 text-sm font-headline italic">"Built out of subscription fatigue. Owned forever."</p>
        </div>

        <!-- 10 -->
        <div class="reveal reveal-delay-2 reason-card bg-background rounded-2xl p-10 border border-outline/20 space-y-5" style="box-shadow: 0 4px 24px rgba(26,26,26,0.05);">
          <div class="flex items-start justify-between">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
              <span class="material-symbols-outlined text-primary">settings_suggest</span>
            </div>
            <span class="reason-number text-5xl">10</span>
          </div>
          <h2 class="font-headline text-2xl text-on-surface">Setup in under 5 minutes.</h2>
          <p class="text-on-surface-variant font-light leading-relaxed">
            Download, open, follow the Setup Wizard, paste your free Groq key. No config files, no terminal. If you can set up Spotify, you can set up Wordflow.
          </p>
          <div class="pt-2 flex items-center gap-2 text-xs text-primary font-medium">
            <span class="material-symbols-outlined text-sm filled">check_circle</span>
            Guided setup wizard included
          </div>
        </div>

        <!-- Row 5: [11 full width] -->

        <!-- 11 — col-span-3 -->
        <div class="reveal reason-card lg:col-span-3 bg-surface-container-high rounded-2xl p-7 md:p-12 border border-outline/20 flex flex-col md:flex-row gap-8 md:gap-10 items-start md:items-center" style="box-shadow: 0 4px 24px rgba(26,26,26,0.05);">
          <div class="space-y-4 flex-1">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-primary">hub</span>
              </div>
              <span class="reason-number text-5xl">11</span>
            </div>
            <h2 class="font-headline text-2xl md:text-3xl text-on-surface">One workflow. Every context.</h2>
            <p class="fluid-body text-on-surface-variant font-light leading-relaxed max-w-2xl">
              Email reply, Slack message, meeting notes, code comment, late-night idea dump — one hotkey, one muscle memory, infinite use cases. You stop thinking about the tool and start thinking about the work.
            </p>
          </div>
          <div class="flex flex-wrap gap-2 md:gap-3 w-full md:max-w-xs">
            <span class="bg-background text-on-surface font-medium text-sm px-4 py-2 rounded-full border border-outline/30">Emails</span>
            <span class="bg-background text-on-surface font-medium text-sm px-4 py-2 rounded-full border border-outline/30">Slack messages</span>
            <span class="bg-background text-on-surface font-medium text-sm px-4 py-2 rounded-full border border-outline/30">Meeting notes</span>
            <span class="bg-background text-on-surface font-medium text-sm px-4 py-2 rounded-full border border-outline/30">AI prompts</span>
            <span class="bg-background text-on-surface font-medium text-sm px-4 py-2 rounded-full border border-outline/30">Braindumps</span>
            <span class="bg-primary/10 text-primary font-medium text-sm px-4 py-2 rounded-full border border-primary/20">+ every app</span>
          </div>
        </div>

      </div>
    </section>

    <!-- CTA -->
    <section class="max-w-3xl mx-auto px-5 md:px-8 mt-16 md:mt-28 text-center">
      <div class="reveal bg-on-surface rounded-3xl p-8 md:p-16 cta-glow space-y-6 md:space-y-8">
        <h2 class="font-headline fluid-h2 text-background leading-[1.1]">
          Convinced? <span class="italic text-primary font-light">Good.</span>
        </h2>
        <p class="fluid-lead text-background/60 font-light leading-relaxed">
          First 100 users get Wordflow free. No credit card, no catch.
        </p>
        <a href="/#early-access"
          class="flex sm:inline-flex items-center justify-center gap-3 px-8 py-4 md:px-10 md:py-5 bg-primary rounded-full text-background font-semibold hover:bg-primary/90 transition-all duration-300 shadow-xl shadow-primary/20 group whitespace-nowrap min-h-[52px]">
          Claim my free copy
          <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
        </a>
        <p class="text-background/30 text-xs font-light">After 100 spots: €25 one-time · Mac only · Lifetime updates</p>
      </div>
    </section>

  </main>

  <?php require __DIR__ . '/_footer.php'; ?>

  <script>
    // Scroll progress
    const bar = document.getElementById('scroll-progress');
    window.addEventListener('scroll', () => {
      const total = document.documentElement.scrollHeight - window.innerHeight;
      bar.style.width = (window.scrollY / total * 100) + '%';
    }, { passive: true });

    // Reveal
    const ro = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) { e.target.classList.add('visible'); ro.unobserve(e.target); }
      });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => ro.observe(el));
  </script>

</body>
</html>
