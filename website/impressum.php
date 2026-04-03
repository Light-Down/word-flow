<?php require_once __DIR__ . "/security.php"; ?>
<!DOCTYPE html>
<html class="light" lang="de">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Impressum — Wordflow</title>  <link rel="stylesheet" href="/assets/fonts.css" />
  <script src="/assets/tailwind.js"></script>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "primary": "#D2691E",
            "on-primary": "#FFFFFF",
            "background": "#FDFBF7",
            "surface": "#F7F3EE",
            "on-surface": "#1A1A1A",
            "on-surface-variant": "#4A4A4A",
            "outline": "#D1CDC7",
            "surface-container": "#F2EDE6",
            "surface-container-high": "#EBE5DB",
            "surface-container-low": "#FAF7F2",
            "secondary": "#2C3E50",
          },
          fontFamily: {
            "headline": ["Newsreader", "serif"],
            "body": ["Inter", "sans-serif"],
          },
          borderRadius: {
            "DEFAULT": "0.5rem",
            "lg": "1rem",
            "xl": "1.5rem",
            "full": "9999px"
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
  </style>
</head>

<body class="bg-background text-on-surface font-body">

  <?php require __DIR__ . '/_nav.php'; ?>

  <main class="pt-40 pb-32 max-w-2xl mx-auto px-8">

    <h1 class="font-headline text-5xl text-on-surface mb-2">Impressum</h1>
    <p class="text-on-surface-variant font-light mb-16">Angaben gemäß § 5 TMG</p>

    <div class="space-y-12 text-on-surface-variant leading-relaxed">

      <section class="space-y-2">
        <h2 class="font-semibold text-on-surface text-sm uppercase tracking-widest">Anbieter</h2>
        <p>Mark Olenberg<br />
        Sossenheimer Riedstraße 18<br />
        65936 Frankfurt am Main<br />
        Deutschland</p>
      </section>

      <section class="space-y-2">
        <h2 class="font-semibold text-on-surface text-sm uppercase tracking-widest">Kontakt</h2>
        <p>E-Mail: <a href="mailto:info@olenberg-media.de" class="text-primary hover:underline">info@olenberg-media.de</a></p>
      </section>

      <section class="space-y-2">
        <h2 class="font-semibold text-on-surface text-sm uppercase tracking-widest">Umsatzsteuer</h2>
        <p>Gemäß § 19 UStG wird keine Umsatzsteuer berechnet (Kleinunternehmerregelung).</p>
      </section>

      <section class="space-y-2">
        <h2 class="font-semibold text-on-surface text-sm uppercase tracking-widest">Verantwortlich für den Inhalt</h2>
        <p>Mark Olenberg<br />
        Sossenheimer Riedstraße 18<br />
        65936 Frankfurt am Main</p>
      </section>

      <section class="space-y-2">
        <h2 class="font-semibold text-on-surface text-sm uppercase tracking-widest">Streitschlichtung</h2>
        <p>Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit:
        <a href="https://ec.europa.eu/consumers/odr" class="text-primary hover:underline" target="_blank" rel="noopener">https://ec.europa.eu/consumers/odr</a>.</p>
        <p class="mt-2">Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>
      </section>

      <section class="space-y-2">
        <h2 class="font-semibold text-on-surface text-sm uppercase tracking-widest">Haftung für Inhalte</h2>
        <p>Als Diensteanbieter sind wir gemäß § 7 Abs. 1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen.</p>
      </section>

    </div>

  </main>

  <?php require __DIR__ . '/_footer.php'; ?>

</body>
</html>
