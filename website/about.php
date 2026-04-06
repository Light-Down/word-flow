<?php require_once __DIR__ . "/security.php"; csrf_token(); ?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>About — Wordflow</title>  <!-- Preload critical fonts -->
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
      transition: opacity 0.75s cubic-bezier(0.16, 1, 0.3, 1),
                  transform 0.75s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: 0.10s; }
    .reveal-delay-2 { transition-delay: 0.20s; }

    .hero-fade {
      opacity: 0; transform: translateY(20px);
      animation: heroFadeUp 0.85s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .hero-fade-left {
      opacity: 0; transform: translateX(-40px);
      animation: heroFadeLeft 0.9s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    @keyframes heroFadeUp { to { opacity: 1; transform: translateY(0); } }
    @keyframes heroFadeLeft { to { opacity: 1; transform: translateX(0); } }
    .hd1 { animation-delay: 0.05s; }
    .hd2 { animation-delay: 0.15s; }
    .hd3 { animation-delay: 0.25s; }
    .hd4 { animation-delay: 0.35s; }

    /* Photo placeholder */
    .photo-placeholder {
      background: linear-gradient(135deg, #F2EDE6 0%, #EBE5DB 100%);
    }

    blockquote {
      border-left: 3px solid #D2691E;
    }
  </style>
</head>

<body class="bg-background text-on-surface font-body selection:bg-primary/20">

  <div id="scroll-progress" aria-hidden="true"></div>

  <!-- Nav -->
  <?php require __DIR__ . '/_nav.php'; ?>

  <main class="pt-40 pb-32">

    <!-- Hero -->
    <section class="max-w-6xl mx-auto px-8 mb-32">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-20 items-start">

        <!-- Left: Photo -->
        <div class="lg:col-span-4">
          <div class="hero-fade-left hd1 relative">
            <!-- Replace src with your actual photo -->
            <div class="w-full aspect-[3/4] rounded-3xl overflow-hidden relative">
              <img src="/mark.webp" alt="Mark, Maker of Wordflow" class="w-full h-full object-cover" />
              <div class="absolute bottom-0 left-0 right-0 p-8 bg-gradient-to-t from-on-surface/50 to-transparent">
                <p class="text-background font-headline italic text-2xl">Mark</p>
                <p class="text-background/60 text-sm font-light">Maker of Wordflow</p>
              </div>
            </div>
            <!-- Small badge -->
            <div class="absolute -bottom-5 -right-5 bg-background rounded-2xl px-5 py-3 border border-outline/20 shadow-lg flex items-center gap-3">
              <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
              <span class="text-xs font-semibold text-on-surface-variant uppercase tracking-[0.15em]">Daily user</span>
            </div>
          </div>
        </div>

        <!-- Right: Text -->
        <div class="lg:col-span-8 space-y-10 pt-4">
          <div class="space-y-4">
            <span class="hero-fade hd1 text-[10px] uppercase tracking-[0.35em] text-primary font-bold">The Maker</span>
            <h1 class="hero-fade hd2 font-headline text-6xl text-on-surface leading-[1.05]">
              Hi, I'm Mark. <br /><span class="italic text-primary font-light">I built this for myself.</span>
            </h1>
          </div>

          <div class="hero-fade hd3 space-y-6 text-xl text-on-surface-variant font-light leading-relaxed max-w-2xl">
            <p>
              At 29, I started an apprenticeship in IT System Management — and somewhere along the way, I fell in love with building things. First websites, then small AI projects. That's when I discovered something that changed how I work.
            </p>
            <p>
              I started talking to my computer instead of typing to it. Dictating prompts, structuring thoughts out loud, taking notes by speaking. I realized I could think <em>four times faster</em> than I could type — and the output was clearer and better structured too.
            </p>
          </div>

          <div class="hero-fade hd4 flex items-start gap-4 bg-surface-container rounded-2xl p-8 border border-outline/20">
            <span class="material-symbols-outlined text-primary text-2xl mt-1 shrink-0">format_quote</span>
            <blockquote class="pl-5">
              <p class="font-headline italic text-xl text-on-surface leading-relaxed">
                "I found an app I loved. But as an apprentice, I wasn't ready to pay that much monthly for something I knew could be built better, cheaper — and owned rather than rented."
              </p>
              <footer class="mt-3 text-sm text-on-surface-variant font-light">— Mark, Maker of Wordflow</footer>
            </blockquote>
          </div>
        </div>
      </div>
    </section>

    <!-- Story continued -->
    <section class="max-w-3xl mx-auto px-8 mb-28 space-y-8">

      <div class="reveal h-px bg-outline/20"></div>

      <div class="reveal space-y-6 text-lg text-on-surface-variant font-light leading-relaxed">
        <p>
          So I set myself a challenge: build it myself. A speech-to-text app that actually respects how people think — with messy starts, self-corrections, slang, and all — and transforms it into something polished, without a subscription attached.
        </p>
        <p>
          I had no idea how long it would take. But I dug in, learned what I needed to learn, and Wordflow slowly became real. What started as a personal experiment became something I use every single day — for prompting, for emails, for notes, for thinking out loud.
        </p>
      </div>

      <div class="reveal space-y-6 text-lg text-on-surface-variant font-light leading-relaxed">
        <p>
          Wordflow isn't a VC-backed startup trying to capture your data and upsell you next quarter. It's a tool built by one person, for one reason: because paying €15/month to talk to your own computer felt wrong. It still does.
        </p>
        <p>
          If you're the kind of person who thinks in voice, hates subscriptions, and wants their words to actually sound like <em>them</em> — Wordflow was built for you.
        </p>
      </div>

      <div class="reveal h-px bg-outline/20"></div>
    </section>

    <!-- Values / approach -->
    <section class="max-w-5xl mx-auto px-8 mb-28">
      <h2 class="reveal font-headline text-4xl text-on-surface mb-12 text-center">How I build.</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="reveal bg-background rounded-2xl p-8 border border-outline/20 space-y-4" style="box-shadow: 0 4px 20px rgba(26,26,26,0.04);">
          <span class="material-symbols-outlined text-primary text-3xl">person</span>
          <h3 class="font-headline text-xl text-on-surface">I use it myself. Daily.</h3>
          <p class="text-on-surface-variant font-light leading-relaxed text-sm">
            Every feature in Wordflow exists because I needed it. No feature exists because a roadmap said so. If it doesn't make my own workflow better, it doesn't ship.
          </p>
        </div>

        <div class="reveal reveal-delay-1 bg-background rounded-2xl p-8 border border-outline/20 space-y-4" style="box-shadow: 0 4px 20px rgba(26,26,26,0.04);">
          <span class="material-symbols-outlined text-primary text-3xl">verified</span>
          <h3 class="font-headline text-xl text-on-surface">Honest about what it does.</h3>
          <p class="text-on-surface-variant font-light leading-relaxed text-sm">
            I don't exaggerate features or make claims the app can't back up. What you read on this site is exactly what the app does — no more, no less.
          </p>
        </div>

        <div class="reveal reveal-delay-2 bg-background rounded-2xl p-8 border border-outline/20 space-y-4" style="box-shadow: 0 4px 20px rgba(26,26,26,0.04);">
          <span class="material-symbols-outlined text-primary text-3xl">update</span>
          <h3 class="font-headline text-xl text-on-surface">You buy it once. I keep improving it.</h3>
          <p class="text-on-surface-variant font-light leading-relaxed text-sm">
            €25 is a one-time payment. Lifetime updates means every improvement I ship — including the app-aware profiles coming later — goes to every existing user. No upgrade fees.
          </p>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="max-w-2xl mx-auto px-8 text-center">
      <div class="reveal space-y-8">
        <p class="text-on-surface-variant font-light text-lg">
          First 100 users get Wordflow completely free. I'd love for you to be one of them — and to hear what you think.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <a href="index.html#early-access"
            class="inline-flex items-center gap-3 px-10 py-5 bg-on-surface rounded-full text-background font-semibold hover:opacity-90 transition-opacity group">
            Claim my free copy
            <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
          </a>
          <a href="/why/"
            class="inline-flex items-center gap-2 px-8 py-5 bg-surface-container rounded-full text-on-surface font-medium hover:bg-surface-container-high transition-colors border border-outline/20">
            See 11 reasons why
          </a>
        </div>
      </div>
    </section>

  </main>

  <?php require __DIR__ . '/_footer.php'; ?>

  <script>
    const bar = document.getElementById('scroll-progress');
    window.addEventListener('scroll', () => {
      const total = document.documentElement.scrollHeight - window.innerHeight;
      bar.style.width = (window.scrollY / total * 100) + '%';
    }, { passive: true });

    const ro = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) { e.target.classList.add('visible'); ro.unobserve(e.target); }
      });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => ro.observe(el));
  </script>

</body>
</html>
