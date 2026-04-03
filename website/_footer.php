<?php
if (!function_exists('csrf_token')) {
    require_once __DIR__ . '/security.php';
}
$_footerCsrf = csrf_token();
?>
<footer class="w-full bg-surface-container border-t border-outline/20 mt-auto" role="contentinfo">

  <!-- Main footer -->
  <div class="max-w-7xl mx-auto px-8 py-16 grid grid-cols-1 md:grid-cols-3 gap-12">

    <!-- Brand -->
    <div>
      <a href="/" class="font-headline italic text-on-surface text-3xl block mb-4">Wordflow.</a>
      <p class="font-body text-sm text-on-surface-variant leading-relaxed max-w-xs">
        Speak freely. Write brilliantly.<br>
        Mac menu bar speech-to-text, powered by Groq.
      </p>
      <a href="/#early-access"
        class="inline-flex items-center gap-2 mt-6 font-body text-sm font-medium bg-primary text-on-primary px-5 py-2.5 rounded-full hover:opacity-90 transition-opacity">
        Get Early Access
      </a>
    </div>

    <!-- Discover -->
    <div>
      <p class="font-body text-xs tracking-widest uppercase text-on-surface-variant font-semibold mb-5">Discover</p>
      <nav class="flex flex-col gap-3" aria-label="Footer discover navigation">
        <a class="font-body text-sm text-on-surface-variant hover:text-primary transition-colors" href="/#features">Features</a>
        <a class="font-body text-sm text-on-surface-variant hover:text-primary transition-colors" href="/why/">Why Wordflow</a>
        <a class="font-body text-sm text-on-surface-variant hover:text-primary transition-colors" href="/#pricing">Pricing</a>
        <a class="font-body text-sm text-on-surface-variant hover:text-primary transition-colors" href="/about/">About</a>
        <a class="font-body text-sm text-on-surface-variant hover:text-primary transition-colors" href="/changelog/">Changelog</a>
      </nav>
    </div>

    <!-- Legal & Contact -->
    <div>
      <p class="font-body text-xs tracking-widest uppercase text-on-surface-variant font-semibold mb-5">Legal & Contact</p>
      <nav class="flex flex-col gap-3" aria-label="Footer legal navigation">
        <a class="font-body text-sm text-on-surface-variant hover:text-primary transition-colors" href="/datenschutz/">Privacy Policy</a>
        <a class="font-body text-sm text-on-surface-variant hover:text-primary transition-colors" href="/agb/">Terms & Conditions</a>
        <a class="font-body text-sm text-on-surface-variant hover:text-primary transition-colors" href="/impressum/">Impressum</a>
        <button onclick="document.getElementById('contact-modal').classList.remove('hidden')"
          class="font-body text-sm text-on-surface-variant hover:text-primary transition-colors text-left">Contact</button>
        <button onclick="document.getElementById('feedback-modal').classList.remove('hidden')"
          class="font-body text-sm text-on-surface-variant hover:text-primary transition-colors text-left">Feedback</button>
      </nav>
    </div>

  </div>

  <!-- Bottom bar -->
  <div class="border-t border-outline/20">
    <div class="max-w-7xl mx-auto px-8 py-5 flex flex-col sm:flex-row justify-between items-center gap-3">
      <p class="font-body text-xs text-on-surface-variant opacity-60">© 2026 Wordflow · Quietly Crafted in Frankfurt.</p>
      <p class="font-body text-xs text-on-surface-variant opacity-60">Built for Mac · Powered by Groq Whisper</p>
    </div>
  </div>

</footer>

<!-- ─── Feedback Modal ─── -->
<div id="feedback-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-6" role="dialog" aria-modal="true" aria-labelledby="fb-modal-title">
  <div class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm" onclick="document.getElementById('feedback-modal').classList.add('hidden')"></div>
  <div class="relative bg-background rounded-2xl border border-outline/30 p-10 w-full max-w-lg" style="box-shadow:0 24px 64px rgba(0,0,0,.12);">
    <button onclick="document.getElementById('feedback-modal').classList.add('hidden')"
      class="absolute top-5 right-5 text-on-surface-variant hover:text-on-surface transition-colors focus:outline-none"
      aria-label="Close">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <h2 id="fb-modal-title" class="font-headline text-3xl text-on-surface mb-2">Share your thoughts</h2>
    <p class="font-body text-on-surface-variant text-sm mb-8">Got feedback, an idea, or a bug? I read every message.</p>
    <form id="feedback-form" class="space-y-4">
      <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true"
        style="position:absolute;left:-9999px;opacity:0;height:0;" />
      <div class="flex gap-3">
        <input id="fb-name" type="text" placeholder="Your name" autocomplete="name"
          class="flex-1 min-w-0 px-4 py-3 bg-surface-container border border-outline/40 rounded-lg font-body text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-primary transition-colors" />
        <input id="fb-email" type="email" placeholder="Email (optional)" autocomplete="email"
          class="flex-1 min-w-0 px-4 py-3 bg-surface-container border border-outline/40 rounded-lg font-body text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-primary transition-colors" />
      </div>
      <textarea id="fb-message" rows="4" placeholder="Your message…" required
        class="w-full px-4 py-3 bg-surface-container border border-outline/40 rounded-lg font-body text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-primary transition-colors resize-none"></textarea>
      <div class="flex items-center gap-4">
        <p id="fb-msg" class="hidden text-sm flex-1 font-body"></p>
        <button id="fb-btn" type="submit"
          class="ml-auto px-8 py-3 bg-on-surface text-background rounded-full font-body font-medium text-sm hover:opacity-90 transition-opacity min-h-[44px]">
          Send feedback
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ─── Contact Modal ─── -->
<div id="contact-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-6" role="dialog" aria-modal="true" aria-labelledby="ct-modal-title">
  <div class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm" onclick="document.getElementById('contact-modal').classList.add('hidden')"></div>
  <div class="relative bg-background rounded-2xl border border-outline/30 p-10 w-full max-w-lg" style="box-shadow:0 24px 64px rgba(0,0,0,.12);">
    <button onclick="document.getElementById('contact-modal').classList.add('hidden')"
      class="absolute top-5 right-5 text-on-surface-variant hover:text-on-surface transition-colors focus:outline-none"
      aria-label="Close">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <h2 id="ct-modal-title" class="font-headline text-3xl text-on-surface mb-2">Get in touch</h2>
    <p class="font-body text-on-surface-variant text-sm mb-8">Questions, partnerships, or just want to say hi — I'm listening.</p>
    <form id="contact-form" class="space-y-4">
      <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true"
        style="position:absolute;left:-9999px;opacity:0;height:0;" />
      <div class="flex gap-3">
        <input id="ct-name" type="text" placeholder="Your name" autocomplete="name"
          class="flex-1 min-w-0 px-4 py-3 bg-surface-container border border-outline/40 rounded-lg font-body text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-primary transition-colors" />
        <input id="ct-email" type="email" placeholder="Email address *" required autocomplete="email"
          class="flex-1 min-w-0 px-4 py-3 bg-surface-container border border-outline/40 rounded-lg font-body text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-primary transition-colors" />
      </div>
      <textarea id="ct-message" rows="4" placeholder="Your message…" required
        class="w-full px-4 py-3 bg-surface-container border border-outline/40 rounded-lg font-body text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:outline-none focus:border-primary transition-colors resize-none"></textarea>
      <div class="flex items-center gap-4">
        <p id="ct-msg" class="hidden text-sm flex-1 font-body"></p>
        <button id="ct-btn" type="submit"
          class="ml-auto px-8 py-3 bg-on-surface text-background rounded-full font-body font-medium text-sm hover:opacity-90 transition-opacity min-h-[44px]">
          Send message
        </button>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  const form = document.getElementById('feedback-form');
  if (!form) return;
  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('fb-btn');
    const msg = document.getElementById('fb-msg');
    const honeypot = form.querySelector('input[name="website"]').value;
    if (honeypot) return;
    btn.disabled = true;
    btn.textContent = 'Sending…';
    try {
      const res = await fetch('/feedback', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-CSRF-Token': '<?= htmlspecialchars($_footerCsrf) ?>',
        },
        body: new URLSearchParams({
          name:    document.getElementById('fb-name').value.trim(),
          email:   document.getElementById('fb-email').value.trim(),
          message: document.getElementById('fb-message').value.trim(),
          website: honeypot,
        })
      });
      const data = await res.json();
      msg.classList.remove('hidden');
      if (res.ok) {
        msg.textContent = 'Thanks! Got your message.';
        msg.className = 'text-sm text-primary font-body flex-1';
        form.reset();
        btn.textContent = 'Sent';
      } else {
        msg.textContent = data.error || 'Something went wrong.';
        msg.className = 'text-sm text-red-500 font-body flex-1';
        btn.disabled = false;
        btn.textContent = 'Send feedback';
      }
    } catch {
      msg.classList.remove('hidden');
      msg.textContent = 'Network error — please try again.';
      msg.className = 'text-sm text-red-500 font-body flex-1';
      btn.disabled = false;
      btn.textContent = 'Send feedback';
    }
  });
}());

(function () {
  const form = document.getElementById('contact-form');
  if (!form) return;
  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('ct-btn');
    const msg = document.getElementById('ct-msg');
    const honeypot = form.querySelector('input[name="website"]').value;
    if (honeypot) return;
    btn.disabled = true;
    btn.textContent = 'Sending…';
    try {
      const res = await fetch('/contact', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-CSRF-Token': '<?= htmlspecialchars($_footerCsrf) ?>',
        },
        body: new URLSearchParams({
          name:    document.getElementById('ct-name').value.trim(),
          email:   document.getElementById('ct-email').value.trim(),
          message: document.getElementById('ct-message').value.trim(),
          website: honeypot,
        })
      });
      const data = await res.json();
      msg.classList.remove('hidden');
      if (res.ok) {
        msg.textContent = data.message || 'Thanks! I\'ll get back to you soon.';
        msg.className = 'text-sm text-primary font-body flex-1';
        form.reset();
        btn.textContent = 'Sent';
      } else {
        msg.textContent = data.error || 'Something went wrong.';
        msg.className = 'text-sm text-red-500 font-body flex-1';
        btn.disabled = false;
        btn.textContent = 'Send message';
      }
    } catch {
      msg.classList.remove('hidden');
      msg.textContent = 'Network error — please try again.';
      msg.className = 'text-sm text-red-500 font-body flex-1';
      btn.disabled = false;
      btn.textContent = 'Send message';
    }
  });
}());
</script>
