<?php require_once __DIR__ . "/security.php"; ?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Update Available — Wordflow</title>
  <!-- Preload critical fonts -->
  <link rel="preload" href="/assets/fonts/1ab1ad55.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="/assets/fonts/78bd98e5.woff2" as="font" type="font/woff2" crossorigin>
  <!-- Fonts & Styles -->
  <link rel="stylesheet" href="/assets/fonts.css" />
  <link rel="stylesheet" href="/assets/app.css" />
</head>
<body class="bg-background text-on-surface min-h-screen flex flex-col items-center justify-center px-6">

<?php
$versionFile = __DIR__ . '/update/version.json';
$info = ['version' => '—', 'releaseNotes' => ''];
if (file_exists($versionFile)) {
    $decoded = json_decode(file_get_contents($versionFile), true);
    if (is_array($decoded)) $info = $decoded;
}
?>

  <div class="max-w-lg w-full text-center py-16">
    <a href="/" class="font-headline italic text-on-surface-variant text-3xl block mb-16">Wordflow.</a>

    <div class="bg-surface border border-outline/30 rounded-2xl p-10 mb-8">
      <div class="inline-flex items-center gap-2 bg-primary/10 text-primary text-xs font-body font-semibold tracking-widest uppercase px-3 py-1.5 rounded-full mb-6">
        Version <?= htmlspecialchars($info['version']) ?>
      </div>
      <h1 class="font-headline text-3xl text-on-surface mb-4">Your update is on its way.</h1>
      <p class="font-body text-on-surface-variant leading-relaxed mb-6">
        We've sent your personal download link to the email address you signed up with.<br>
        Check your inbox and click the link to install the latest version.
      </p>
      <?php if (!empty($info['releaseNotes'])): ?>
      <div class="text-left border-t border-outline/30 pt-6 mt-6">
        <p class="font-body text-xs tracking-widest uppercase text-on-surface-variant mb-3">What's new</p>
        <p class="font-body text-on-surface-variant text-sm leading-relaxed"><?= htmlspecialchars($info['releaseNotes']) ?></p>
      </div>
      <?php endif; ?>
    </div>

    <p class="font-body text-xs text-on-surface-variant opacity-60">
      Can't find the email? Check your spam folder or
      <a href="mailto:<?= defined('ADMIN_EMAIL') ? htmlspecialchars(ADMIN_EMAIL) : 'hi@word-flow.store' ?>" class="underline hover:opacity-100">contact us</a>.
    </p>

    <a href="/changelog/" class="inline-block mt-8 font-body text-sm text-primary hover:opacity-80 transition-opacity">
      View full changelog →
    </a>
  </div>

</body>
</html>
