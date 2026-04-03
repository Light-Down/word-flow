<!DOCTYPE html>
<html class="light" lang="en">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Privacy Policy — Wordflow</title>
  <link href="https://fonts.googleapis.com" rel="preconnect" />
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
  <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            "primary": "#D2691E",
            "background": "#FDFBF7",
            "on-surface": "#1A1A1A",
            "on-surface-variant": "#4A4A4A",
            "outline": "#D1CDC7",
            "surface-container": "#F2EDE6",
          },
          fontFamily: {
            "headline": ["Newsreader", "serif"],
            "body": ["Inter", "sans-serif"],
          },
        },
      },
    }
  </script>
  <style>
    .glass-nav {
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      background: rgba(253, 251, 247, 0.85);
    }
    h2 { margin-top: 3rem; }
  </style>
</head>

<body class="bg-background text-on-surface font-body">

  <nav class="fixed top-0 w-full z-50 glass-nav border-b border-outline/30">
    <div class="flex justify-between items-center h-20 px-8 max-w-5xl mx-auto">
      <a href="/" class="font-headline text-3xl font-bold text-on-surface tracking-tight">Wordflow</a>
      <a href="/" class="text-sm text-on-surface-variant hover:text-on-surface transition-colors">← Back to Wordflow</a>
    </div>
  </nav>

  <main class="pt-40 pb-32 max-w-2xl mx-auto px-8">

    <h1 class="font-headline text-5xl text-on-surface mb-2">Privacy Policy</h1>
    <p class="text-on-surface-variant font-light mb-4">Last updated: April 2026</p>
    <p class="text-sm text-on-surface-variant font-light mb-16">This privacy policy applies to the Wordflow website (wordflow.app) and is provided in accordance with the EU General Data Protection Regulation (GDPR).</p>

    <div class="space-y-6 text-on-surface-variant leading-relaxed">

      <section>
        <h2 class="font-semibold text-on-surface text-sm uppercase tracking-widest mb-3">1. Controller</h2>
        <p>The controller responsible for data processing on this website is:</p>
        <p class="mt-3 bg-surface-container rounded-xl p-5 text-sm">
          Mark Olenberg<br />
          Sossenheimer Riedstraße 18<br />
          65936 Frankfurt am Main, Germany<br />
          <a href="mailto:info@olenberg-media.de" class="text-primary hover:underline">info@olenberg-media.de</a>
        </p>
      </section>

      <section>
        <h2 class="font-semibold text-on-surface text-sm uppercase tracking-widest mb-3">2. What data we collect and why</h2>

        <h3 class="font-medium text-on-surface mt-5 mb-2">2.1 Early access sign-ups</h3>
        <p>When you submit your email address to join the early access list, we collect:</p>
        <ul class="list-disc list-inside mt-2 space-y-1 ml-2">
          <li>Your email address</li>
          <li>The date and time of sign-up</li>
          <li>Whether you received a free copy or were placed on the waitlist</li>
        </ul>
        <p class="mt-3"><strong>Purpose:</strong> To send you your download link and product updates.<br />
        <strong>Legal basis:</strong> Art. 6(1)(b) GDPR — necessary to fulfil your request.<br />
        <strong>Retention:</strong> Until you unsubscribe or request deletion.</p>

        <h3 class="font-medium text-on-surface mt-5 mb-2">2.2 Feedback submissions</h3>
        <p>If you submit feedback (via the website or the Wordflow app), we collect:</p>
        <ul class="list-disc list-inside mt-2 space-y-1 ml-2">
          <li>Your feedback message</li>
          <li>Optionally: your email address (if you choose to provide it)</li>
          <li>App version number</li>
        </ul>
        <p class="mt-3"><strong>Purpose:</strong> To improve the product.<br />
        <strong>Legal basis:</strong> Art. 6(1)(f) GDPR — legitimate interest in improving the service.<br />
        <strong>Retention:</strong> Until no longer needed for product development.</p>

        <h3 class="font-medium text-on-surface mt-5 mb-2">2.3 Data we do NOT collect</h3>
        <ul class="list-disc list-inside mt-2 space-y-1 ml-2">
          <li>No cookies are set on this website</li>
          <li>No analytics or tracking tools are used</li>
          <li>No audio recordings — your voice data goes directly from your device to Groq's API via your own API key. Wordflow has no backend that processes your audio or transcriptions.</li>
        </ul>
      </section>

      <section>
        <h2 class="font-semibold text-on-surface text-sm uppercase tracking-widest mb-3">3. Third-party services</h2>

        <h3 class="font-medium text-on-surface mt-5 mb-2">Brevo (email delivery)</h3>
        <p>We use Brevo (Sendinblue SAS, 7 rue de Madrid, 75008 Paris, France) to manage our email list and send confirmation emails. Your email address is transferred to and stored by Brevo.</p>
        <p class="mt-2">Brevo's privacy policy: <a href="https://www.brevo.com/legal/privacypolicy/" class="text-primary hover:underline" target="_blank" rel="noopener">brevo.com/legal/privacypolicy</a></p>
        <p class="mt-2">A data processing agreement (DPA) is in place with Brevo as required by Art. 28 GDPR.</p>

        <h3 class="font-medium text-on-surface mt-5 mb-2">Groq (in-app, not this website)</h3>
        <p>The Wordflow app uses the Groq API for transcription and text processing. This connection is established using <em>your own</em> Groq API key — no data passes through Wordflow's servers. Groq's privacy policy applies directly between you and Groq: <a href="https://groq.com/privacy-policy/" class="text-primary hover:underline" target="_blank" rel="noopener">groq.com/privacy-policy</a></p>

        <h3 class="font-medium text-on-surface mt-5 mb-2">Google Fonts &amp; CDN resources</h3>
        <p>This website loads fonts from Google Fonts and a Tailwind CSS CDN. These requests may transmit your IP address to Google and Tailwind's CDN provider. We plan to self-host these resources in a future update to eliminate this transfer.</p>
      </section>

      <section>
        <h2 class="font-semibold text-on-surface text-sm uppercase tracking-widest mb-3">4. Your rights under GDPR</h2>
        <p>You have the right to:</p>
        <ul class="list-disc list-inside mt-2 space-y-1 ml-2">
          <li><strong>Access</strong> — request a copy of the data we hold about you</li>
          <li><strong>Rectification</strong> — correct inaccurate data</li>
          <li><strong>Erasure</strong> — request deletion of your data ("right to be forgotten")</li>
          <li><strong>Restriction</strong> — limit how we process your data</li>
          <li><strong>Objection</strong> — object to processing based on legitimate interest</li>
          <li><strong>Portability</strong> — receive your data in a machine-readable format</li>
        </ul>
        <p class="mt-4">To exercise any of these rights, email us at <a href="mailto:info@olenberg-media.de" class="text-primary hover:underline">info@olenberg-media.de</a>. We will respond within 30 days.</p>
        <p class="mt-3">You also have the right to lodge a complaint with a supervisory authority. The responsible authority for Frankfurt am Main is:<br />
        <span class="mt-1 block">Der Hessische Beauftragte für Datenschutz und Informationsfreiheit (HBDI)<br />
        <a href="https://datenschutz.hessen.de" class="text-primary hover:underline" target="_blank" rel="noopener">datenschutz.hessen.de</a></span></p>
      </section>

      <section>
        <h2 class="font-semibold text-on-surface text-sm uppercase tracking-widest mb-3">5. Data security</h2>
        <p>We use technical and organisational measures to protect your data against unauthorised access, loss, or misuse. Our servers are hosted by Hostinger, which provides standard security measures for shared hosting environments.</p>
      </section>

      <section>
        <h2 class="font-semibold text-on-surface text-sm uppercase tracking-widest mb-3">6. Changes to this policy</h2>
        <p>We may update this privacy policy from time to time. The current version is always available at this URL. Material changes will be communicated via the email address you provided at sign-up.</p>
      </section>

    </div>

  </main>

  <footer class="border-t border-outline/20 py-10">
    <div class="max-w-2xl mx-auto px-8 flex flex-wrap gap-8 text-xs text-on-surface-variant">
      <a href="/impressum/" class="hover:text-on-surface transition-colors">Impressum</a>
      <a href="/datenschutz/" class="hover:text-on-surface transition-colors">Datenschutz</a>
      <a href="/agb/" class="hover:text-on-surface transition-colors">Terms & Conditions</a>
      <span class="ml-auto">© 2026 Mark Olenberg</span>
    </div>
  </footer>

</body>
</html>
