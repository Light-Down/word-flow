<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

rate_limit('download', 10, 60); // max 10 requests per minute per IP

$token = trim($_GET['token'] ?? '');

if (empty($token) || strlen($token) !== 64) {
    http_response_code(400);
    show_error('Invalid download link.');
    exit;
}

$db   = get_db();
$stmt = $db->prepare('SELECT id, email, type FROM signups WHERE token = ? AND type = "free"');
$stmt->execute([$token]);
$signup = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$signup) {
    http_response_code(404);
    show_error('This download link is invalid or has expired.');
    exit;
}

$file = __DIR__ . '/files/dist/Wordflow.dmg';

// ─── Serve file when ?get=1 ────────────────────────────────────────────────
if (($_GET['get'] ?? '') === '1') {
    if (!file_exists($file)) {
        http_response_code(503);
        show_error('The download is not available yet. Please try again soon.');
        exit;
    }

    $db->prepare('UPDATE signups SET download_count = download_count + 1 WHERE id = ?')
       ->execute([$signup['id']]);

    header('Content-Type: application/x-apple-diskimage');
    header('Content-Disposition: attachment; filename="Wordflow.dmg"');
    header('Content-Length: ' . filesize($file));
    header('Cache-Control: no-cache, no-store');
    header('X-Content-Type-Options: nosniff');
    readfile($file);
    exit;
}

// ─── Download landing page ─────────────────────────────────────────────────
$safeToken   = htmlspecialchars($token);
$fileSize    = round(filesize($file) / 1024 / 1024, 1);
$downloadUrl = '/download?token=' . $safeToken . '&get=1';
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Download Wordflow</title>
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
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    .spinner {
      width: 18px; height: 18px;
      border: 2px solid #D2691E40;
      border-top-color: #D2691E;
      border-radius: 50%;
      animation: spin 0.7s linear infinite;
      display: inline-block;
    }
  </style>
</head>
<body class="bg-background text-on-surface min-h-screen flex flex-col">

  <!-- Nav -->
  <?php require __DIR__ . '/_nav.php'; ?>

  <!-- Content -->
  <main class="flex-1 flex items-center justify-center px-6 pt-20">
    <div class="max-w-lg w-full text-center py-16">

      <!-- Icon -->
      <div class="w-20 h-20 rounded-2xl bg-surface-container flex items-center justify-center mx-auto mb-8 border border-outline/30">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#D2691E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="7 10 12 15 17 10"/>
          <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
      </div>

      <h1 class="font-headline text-4xl md:text-5xl text-on-surface mb-4">You're in.</h1>
      <p class="font-body text-on-surface-variant text-lg leading-relaxed mb-10">
        Your download is starting automatically.<br>
        Wordflow for Mac &middot; <?= $fileSize ?> MB
      </p>

      <!-- Download button -->
      <a id="download-btn" href="<?= $downloadUrl ?>"
        class="inline-flex items-center gap-3 bg-primary text-on-primary font-body font-medium px-8 py-4 rounded-full hover:opacity-90 transition-opacity text-base">
        <span id="btn-icon" class="spinner"></span>
        <span id="btn-label">Downloading...</span>
      </a>

      <p id="manual-hint" class="font-body text-sm text-on-surface-variant mt-5 opacity-0 transition-opacity duration-500">
        Download did not start?
        <a href="<?= $downloadUrl ?>" class="text-primary underline hover:opacity-80">Click here</a>
      </p>

      <!-- Install steps -->
      <div class="mt-16 text-left border border-outline/30 rounded-xl bg-surface p-8 space-y-5">
        <p class="font-body text-xs tracking-widest uppercase text-on-surface-variant font-semibold">How to install</p>
        <?php
        $steps = [
            ['Open', 'the downloaded <strong>Wordflow.dmg</strong> file.'],
            ['Drag', 'Wordflow into your <strong>Applications</strong> folder.'],
            ['Launch', 'Wordflow from Applications or Spotlight.'],
            ['Enter', 'your <a href="https://console.groq.com/keys" class="text-primary underline hover:opacity-80" target="_blank" rel="noopener">Groq API key</a> on first start — it\'s free.'],
        ];
        foreach ($steps as $i => $step): ?>
        <div class="flex gap-4 items-start">
          <span class="font-headline text-primary text-lg leading-none mt-0.5 w-5 shrink-0"><?= $i + 1 ?>.</span>
          <p class="font-body text-on-surface-variant text-sm leading-relaxed">
            <strong class="text-on-surface font-medium"><?= $step[0] ?></strong> <?= $step[1] ?>
          </p>
        </div>
        <?php endforeach; ?>
      </div>

      <p class="font-body text-xs text-on-surface-variant opacity-50 mt-8">
        This is your personal download link. Keep it — it works for all future updates.
      </p>
    </div>
  </main>

  <?php require __DIR__ . '/_footer.php'; ?>

  <script>
    // Auto-trigger download
    window.addEventListener('load', function () {
      setTimeout(function () {
        window.location.href = '<?= $downloadUrl ?>';
      }, 800);

      // After 3s: swap spinner for checkmark, show manual hint
      setTimeout(function () {
        document.getElementById('btn-icon').outerHTML =
          '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
        document.getElementById('btn-label').textContent = 'Download started';
        document.getElementById('manual-hint').style.opacity = '1';
      }, 3000);
    });
  </script>
</body>
</html>

<?php
function show_error(string $message): void { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Download — Wordflow</title>
  <link rel="stylesheet" href="/assets/fonts.css" />
  <script src="/assets/tailwind.js"></script>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "primary": "#D2691E", "on-primary": "#FFFFFF",
            "background": "#FDFBF7", "on-surface": "#1A1A1A",
            "on-surface-variant": "#4A4A4A", "outline": "#D1CDC7",
          },
          fontFamily: { "headline": ["Newsreader", "serif"], "body": ["Inter", "sans-serif"] },
        },
      },
    }
  </script>
</head>
<body class="bg-background flex items-center justify-center min-h-screen px-6">
  <div class="text-center max-w-sm">
    <a href="/" class="font-headline italic text-on-surface-variant text-3xl block mb-12">Wordflow.</a>
    <h1 class="font-headline text-3xl text-on-surface mb-4">Oops.</h1>
    <p class="font-body text-on-surface-variant leading-relaxed mb-8"><?= htmlspecialchars($message) ?></p>
    <a href="/" class="font-body text-sm text-primary hover:opacity-80 transition-opacity">Back to wordflow.app</a>
  </div>
</body>
</html>
<?php }
