<?php require_once __DIR__ . "/security.php"; csrf_token(); ?>
<!DOCTYPE html>
<html class="light" lang="de">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Impressum — Wordflow</title>  <!-- Preload critical fonts -->
  <link rel="preload" href="/assets/fonts/1ab1ad55.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="/assets/fonts/78bd98e5.woff2" as="font" type="font/woff2" crossorigin>
  <!-- Fonts & Styles -->
  <link rel="stylesheet" href="/assets/fonts.css" />
  <link rel="stylesheet" href="/assets/app.css" />
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
        <p>E-Mail: <a href="mailto:contact@word-flow.store" class="text-primary hover:underline">contact@word-flow.store</a></p>
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
