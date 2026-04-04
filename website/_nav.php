<style>
  /* ─── Nav base ─────────────────────────────────────────────────── */
  .glass-nav {
    backdrop-filter: blur(24px) saturate(1.4);
    -webkit-backdrop-filter: blur(24px) saturate(1.4);
    background: rgba(253, 251, 247, 0.82);
    border-bottom: 1px solid rgba(209, 205, 199, 0.35);
  }

  /* ─── Desktop link with animated underline ─────────────────────── */
  .nav-link {
    position: relative;
    font-family: 'Inter', sans-serif;
    font-size: 0.8125rem;   /* 13px */
    font-weight: 500;
    letter-spacing: 0.01em;
    color: #4A4A4A;
    transition: color 0.18s ease;
    padding-bottom: 2px;
  }
  .nav-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0; right: 0;
    height: 1px;
    background: #D2691E;
    transform: scaleX(0);
    transform-origin: center;
    transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .nav-link:hover { color: #1A1A1A; }
  .nav-link:hover::after { transform: scaleX(1); }

  /* ─── Logo ──────────────────────────────────────────────────────── */
  .nav-logo {
    font-family: 'Newsreader', serif;
    font-style: italic;
    font-weight: 400;
    font-size: 1.5rem;
    letter-spacing: -0.01em;
    color: #1A1A1A;
    transition: opacity 0.18s ease;
  }
  .nav-logo:hover { opacity: 0.7; }

  /* ─── CTA button ────────────────────────────────────────────────── */
  .nav-cta {
    font-family: 'Inter', sans-serif;
    font-size: 0.8125rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    color: #FDFBF7;
    background: #1A1A1A;
    border-radius: 9999px;
    padding: 0.5rem 1.25rem;
    min-height: 36px;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    transition: background 0.18s ease, transform 0.18s ease;
    box-shadow: 0 1px 3px rgba(26,26,26,0.15), inset 0 1px 0 rgba(255,255,255,0.06);
  }
  .nav-cta:hover {
    background: #2a2a2a;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(26,26,26,0.18), inset 0 1px 0 rgba(255,255,255,0.06);
  }
  .nav-cta-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #D2691E;
    animation: pulse-dot 2s ease-in-out infinite;
    flex-shrink: 0;
  }
  @keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.6; transform: scale(0.85); }
  }

  /* ─── New badge ─────────────────────────────────────────────────── */
  .nav-badge {
    font-family: 'Inter', sans-serif;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #D2691E;
    background: rgba(210,105,30,0.1);
    border: 1px solid rgba(210,105,30,0.2);
    border-radius: 9999px;
    padding: 1px 6px;
    line-height: 1.5;
  }

  /* ─── Mobile drawer ─────────────────────────────────────────────── */
  .mobile-drawer {
    background: rgba(253,251,247,0.97);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
  }
  .mobile-nav-link {
    font-family: 'Inter', sans-serif;
    font-size: 0.9375rem;
    font-weight: 500;
    color: #4A4A4A;
    border-radius: 0.75rem;
    padding: 0.875rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.875rem;
    transition: background 0.15s ease, color 0.15s ease;
  }
  .mobile-nav-link:hover {
    background: #F2EDE6;
    color: #1A1A1A;
  }
  .mobile-nav-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    background: rgba(210,105,30,0.08);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }

  /* ─── Hamburger ─────────────────────────────────────────────────── */
  .hamburger-bar {
    display: block;
    height: 1.5px;
    background: #1A1A1A;
    border-radius: 9999px;
    transition: transform 0.28s cubic-bezier(0.16,1,0.3,1),
                opacity   0.2s ease,
                width     0.2s ease;
  }
</style>

<nav class="fixed top-0 w-full z-50 glass-nav" role="navigation" aria-label="Main navigation">
  <div class="flex justify-between items-center h-14 md:h-16 px-5 md:px-10 max-w-7xl mx-auto">

    <!-- Logo -->
    <a href="/" class="nav-logo" aria-label="Wordflow home">Wordflow.</a>

    <!-- Desktop nav links — centered -->
    <div class="hidden md:flex items-center gap-8">
      <a class="nav-link focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded" href="/#features">Use Cases</a>
      <a class="nav-link focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded" href="/why/">Why</a>
      <a class="nav-link focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded" href="/#pricing">Pricing</a>
      <a class="nav-link focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded" href="/about/">About</a>
      <a class="nav-link focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded" href="/roadmap/">Roadmap</a>
    </div>

    <!-- Right: CTA + hamburger -->
    <div class="flex items-center gap-3">

      <!-- Desktop CTA -->
      <a href="/#early-access"
        class="nav-cta hidden md:inline-flex focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
        <span class="nav-cta-dot" aria-hidden="true"></span>
        Get Early Access
      </a>

      <!-- Hamburger -->
      <button id="mobile-menu-btn" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-menu"
        class="md:hidden flex flex-col justify-center items-center w-9 h-9 gap-[5px] rounded-lg hover:bg-surface-container transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
        <span class="hamburger-bar w-[18px]"></span>
        <span class="hamburger-bar w-[18px]"></span>
        <span class="hamburger-bar w-[12px]"></span>
      </button>
    </div>
  </div>

  <!-- Mobile drawer -->
  <div id="mobile-menu"
    class="md:hidden overflow-hidden"
    style="max-height: 0; opacity: 0; transition: max-height 0.32s cubic-bezier(0.16,1,0.3,1), opacity 0.24s ease;">
    <div class="mobile-drawer px-4 pb-6 pt-3 border-t border-outline/20">

      <!-- Links -->
      <div class="space-y-0.5 mb-4">
        <a href="/#features" class="mobile-nav-link mobile-nav-close">
          <span class="mobile-nav-icon">
            <span class="material-symbols-outlined text-primary" style="font-size:16px;">auto_awesome</span>
          </span>
          Features
        </a>
        <a href="/why/" class="mobile-nav-link mobile-nav-close">
          <span class="mobile-nav-icon">
            <span class="material-symbols-outlined text-primary" style="font-size:16px;">help</span>
          </span>
          Why Wordflow
        </a>
        <a href="/#pricing" class="mobile-nav-link mobile-nav-close">
          <span class="mobile-nav-icon">
            <span class="material-symbols-outlined text-primary" style="font-size:16px;">sell</span>
          </span>
          Pricing
        </a>
        <a href="/about/" class="mobile-nav-link mobile-nav-close">
          <span class="mobile-nav-icon">
            <span class="material-symbols-outlined text-primary" style="font-size:16px;">person</span>
          </span>
          About
        </a>
        <a href="/roadmap/" class="mobile-nav-link mobile-nav-close">
          <span class="mobile-nav-icon">
            <span class="material-symbols-outlined text-primary" style="font-size:16px;">map</span>
          </span>
          Roadmap
        </a>
      </div>

      <!-- Divider -->
      <div class="h-px bg-outline/15 mx-2 mb-4"></div>

      <!-- Mobile CTA full-width -->
      <a href="/#early-access"
        class="mobile-nav-close flex items-center justify-center gap-2.5 w-full py-3.5 bg-on-surface text-background rounded-xl font-semibold text-sm hover:opacity-90 transition-opacity">
        <span class="nav-cta-dot"></span>
        Get Early Access
      </a>

    </div>
  </div>
</nav>

<script>
(function () {
  const btn  = document.getElementById('mobile-menu-btn');
  const menu = document.getElementById('mobile-menu');
  const bars = btn.querySelectorAll('.hamburger-bar');
  let open   = false;

  function toggleMenu(force) {
    open = (force !== undefined) ? force : !open;
    btn.setAttribute('aria-expanded', open);

    if (open) {
      menu.style.maxHeight = menu.scrollHeight + 'px';
      menu.style.opacity   = '1';
      bars[0].style.transform = 'translateY(6.5px) rotate(45deg)';
      bars[1].style.opacity   = '0';
      bars[2].style.transform = 'translateY(-6.5px) rotate(-45deg)';
      bars[2].style.width     = '18px';
    } else {
      menu.style.maxHeight = '0';
      menu.style.opacity   = '0';
      bars[0].style.transform = '';
      bars[1].style.opacity   = '1';
      bars[2].style.transform = '';
      bars[2].style.width     = '';
    }
  }

  btn.addEventListener('click', () => toggleMenu());

  document.querySelectorAll('.mobile-nav-close').forEach(el => {
    el.addEventListener('click', () => { if (open) toggleMenu(false); });
  });
})();
</script>
