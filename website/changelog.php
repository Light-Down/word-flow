<?php require_once __DIR__ . "/security.php"; ?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Changelog — Wordflow</title>
  <link rel="stylesheet" href="/assets/fonts.css" />
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
<body class="bg-background text-on-surface min-h-screen">

  <!-- Nav -->
  <?php require __DIR__ . '/_nav.php'; ?>

  <main class="pt-40 pb-32 px-6 max-w-2xl mx-auto">
    <h1 class="font-headline text-5xl text-on-surface mb-4">Changelog</h1>
    <p class="font-body text-on-surface-variant text-lg mb-16 leading-relaxed">Every update, every fix — in one place.</p>

<?php
$changelogFile = __DIR__ . '/update/changelog.json';
$releases = [];
if (file_exists($changelogFile)) {
    $data = json_decode(file_get_contents($changelogFile), true);
    if (is_array($data)) {
        $releases = array_reverse($data); // newest first
    }
}

if (empty($releases)):
?>
    <p class="font-body text-on-surface-variant">No releases yet.</p>
<?php else: ?>
    <div class="space-y-16">
<?php foreach ($releases as $index => $release): ?>
      <div class="flex gap-8">
        <div class="flex flex-col items-center">
          <div class="w-3 h-3 rounded-full mt-2 <?= $index === 0 ? 'bg-primary' : 'bg-outline' ?>"></div>
          <?php if ($index < count($releases) - 1): ?>
          <div class="w-px flex-1 bg-outline/40 mt-3"></div>
          <?php endif; ?>
        </div>
        <div class="pb-8 flex-1">
          <div class="flex items-baseline gap-4 mb-3">
            <span class="font-headline text-2xl text-on-surface">v<?= htmlspecialchars($release['version']) ?></span>
            <?php if ($index === 0): ?>
            <span class="font-body text-xs font-semibold tracking-widest uppercase bg-primary/10 text-primary px-2.5 py-1 rounded-full">Latest</span>
            <?php endif; ?>
            <span class="font-body text-sm text-on-surface-variant"><?= htmlspecialchars($release['date']) ?></span>
          </div>
          <ul class="space-y-2">
            <?php foreach ($release['notes'] as $note): ?>
            <li class="font-body text-on-surface-variant flex gap-3">
              <span class="text-primary mt-1 select-none">—</span>
              <span><?= htmlspecialchars($note) ?></span>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
<?php endforeach; ?>
    </div>
<?php endif; ?>
  </main>

  <?php require __DIR__ . '/_footer.php'; ?>

</body>
</html>
