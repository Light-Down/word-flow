<?php
ob_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin'] = true;
        session_write_close();
        ob_end_clean();
        header('Location: /admin');
        exit;
    }
}

if (($_GET['action'] ?? '') === 'logout') {
    session_destroy();
    header('Location: /admin');
    exit;
}

// ─── Release Management (admin only) ──────────────────────────────────────────

$flash = null;

if (($_SESSION['admin'] ?? false) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Save new version + update changelog + optionally send emails
    if ($action === 'release') {
        $version      = trim($_POST['version'] ?? '');
        $releaseNotes = trim($_POST['release_notes'] ?? '');
        $sendEmails   = isset($_POST['send_emails']);

        if ($version && $releaseNotes) {
            $versionFile   = __DIR__ . '/update/version.json';
            $changelogFile = __DIR__ . '/update/changelog.json';

            // Write version.json
            file_put_contents($versionFile, json_encode([
                'version'      => $version,
                'updateURL'    => 'https://word-flow.store/update',
                'releaseNotes' => $releaseNotes,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            // Prepend to changelog.json
            $changelog = [];
            if (file_exists($changelogFile)) {
                $existing = json_decode(file_get_contents($changelogFile), true);
                if (is_array($existing)) $changelog = $existing;
            }
            $notesArray = array_filter(array_map('trim', explode("\n", $releaseNotes)));
            array_unshift($changelog, [
                'version' => $version,
                'date'    => date('Y-m-d'),
                'notes'   => array_values($notesArray),
            ]);
            file_put_contents($changelogFile, json_encode($changelog, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            $flash = ['type' => 'ok', 'msg' => "Version $version gespeichert."];

            // Send emails if requested
            if ($sendEmails) {
                $db    = get_db();
                $users = $db->query('SELECT email, token FROM signups WHERE type = "free" AND token IS NOT NULL')->fetchAll(PDO::FETCH_ASSOC);
                $sent  = 0;
                $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

                foreach ($users as $user) {
                    $downloadLink = $baseUrl . '/download?token=' . $user['token'];
                    $sent += admin_send_update_email($user['email'], $version, $releaseNotes, $downloadLink);
                }
                $flash['msg'] .= " $sent Update-E-Mails verschickt.";
            }
        } else {
            $flash = ['type' => 'err', 'msg' => 'Version und Release Notes sind erforderlich.'];
        }
    }

    // Send emails only (without version change)
    if ($action === 'send_emails') {
        $db    = get_db();
        $vInfo = json_decode(file_get_contents(__DIR__ . '/update/version.json') ?: '{}', true);
        $version = $vInfo['version'] ?? '?';
        $notes   = $vInfo['releaseNotes'] ?? '';
        $users   = $db->query('SELECT email, token FROM signups WHERE type = "free" AND token IS NOT NULL')->fetchAll(PDO::FETCH_ASSOC);
        $sent    = 0;
        $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

        foreach ($users as $user) {
            $downloadLink = $baseUrl . '/download?token=' . $user['token'];
            $sent += admin_send_update_email($user['email'], $version, $notes, $downloadLink);
        }
        $flash = ['type' => 'ok', 'msg' => "$sent Update-E-Mails verschickt (v$version)."];
    }
}

if (!($_SESSION['admin'] ?? false)):
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Wordflow Admin</title>
  <style>
    body { font-family: system-ui, sans-serif; background: #FDFBF7; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
    form { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 24px rgba(0,0,0,.08); display: flex; flex-direction: column; gap: 1rem; min-width: 280px; }
    input { padding: .75rem 1rem; border: 1px solid #D1CDC7; border-radius: .5rem; font-size: 1rem; }
    button { padding: .75rem; background: #D2691E; color: white; border: none; border-radius: .5rem; font-size: 1rem; cursor: pointer; }
  </style>
  <link rel="stylesheet" href="/assets/fonts.css" />
  <script src="/assets/tailwind.js"></script>
</head>
<body>
  <form method="post">
    <h2 style="margin:0;font-size:1.5rem;">Wordflow Admin</h2>
    <input type="password" name="password" placeholder="Password" autofocus />
    <button type="submit">Login</button>
  </form>
</body>
</html>
<?php
    exit;
endif;

$db = get_db();
$total_free     = (int) $db->query('SELECT COUNT(*) FROM signups WHERE type = "free"')->fetchColumn();
$total_waitlist = (int) $db->query('SELECT COUNT(*) FROM signups WHERE type = "waitlist"')->fetchColumn();
$total_feedback = (int) $db->query('SELECT COUNT(*) FROM feedback')->fetchColumn();
$signups   = $db->query('SELECT * FROM signups ORDER BY created_at DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
$feedbacks = $db->query('SELECT * FROM feedback ORDER BY created_at DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);

// Current version.json
$versionFile = __DIR__ . '/update/version.json';
$currentVersion = ['version' => '', 'releaseNotes' => ''];
if (file_exists($versionFile)) {
    $v = json_decode(file_get_contents($versionFile), true);
    if (is_array($v)) $currentVersion = $v;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Wordflow Admin</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: system-ui, sans-serif; background: #FDFBF7; color: #1A1A1A; margin: 0; padding: 2rem; }
    h1 { font-size: 1.75rem; margin-bottom: 2rem; }
    h2 { margin-top: 0; }
    .stats { display: flex; gap: 1.5rem; margin-bottom: 2.5rem; flex-wrap: wrap; }
    .stat { background: white; border-radius: 1rem; padding: 1.5rem 2rem; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    .stat span { display: block; font-size: 2.5rem; font-weight: 700; color: #D2691E; }
    .stat small { color: #4A4A4A; font-size: .8rem; text-transform: uppercase; letter-spacing: .1em; }
    table { width: 100%; border-collapse: collapse; background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.06); margin-bottom: 3rem; font-size: .9rem; }
    th { background: #F2EDE6; padding: .75rem 1rem; text-align: left; font-size: .75rem; text-transform: uppercase; letter-spacing: .1em; color: #4A4A4A; }
    td { padding: .75rem 1rem; border-top: 1px solid #EBE5DB; vertical-align: top; }
    .badge { display: inline-block; padding: .2rem .6rem; border-radius: 9999px; font-size: .7rem; font-weight: 600; }
    .free { background: #D2691E22; color: #D2691E; }
    .waitlist { background: #2C3E5022; color: #2C3E50; }
    a { color: #D2691E; }

    /* Release panel */
    .panel { background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 2px 12px rgba(0,0,0,.06); margin-bottom: 3rem; }
    .panel label { display: block; font-size: .8rem; text-transform: uppercase; letter-spacing: .1em; color: #4A4A4A; margin-bottom: .4rem; margin-top: 1rem; }
    .panel label:first-of-type { margin-top: 0; }
    .panel input[type=text], .panel textarea {
      width: 100%; padding: .65rem .9rem; border: 1px solid #D1CDC7; border-radius: .5rem;
      font-size: .95rem; font-family: inherit; resize: vertical;
    }
    .panel textarea { min-height: 120px; }
    .btn { display: inline-block; padding: .6rem 1.4rem; border-radius: .5rem; font-size: .9rem; cursor: pointer; border: none; font-family: inherit; }
    .btn-primary { background: #D2691E; color: white; }
    .btn-secondary { background: #F2EDE6; color: #1A1A1A; }
    .btn-row { display: flex; gap: .75rem; align-items: center; flex-wrap: wrap; margin-top: 1.25rem; }
    .check-label { display: flex; align-items: center; gap: .5rem; font-size: .9rem; color: #4A4A4A; cursor: pointer; }
    .flash-ok  { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; padding: .75rem 1rem; border-radius: .5rem; margin-bottom: 1.5rem; }
    .flash-err { background: #fdecea; color: #c62828; border: 1px solid #ef9a9a; padding: .75rem 1rem; border-radius: .5rem; margin-bottom: 1.5rem; }
    .version-badge { display: inline-block; background: #D2691E22; color: #D2691E; padding: .2rem .7rem; border-radius: 9999px; font-size: .8rem; font-weight: 600; margin-left: .5rem; }
    .divider { border: none; border-top: 1px solid #EBE5DB; margin: 1.5rem 0; }
  </style>
</head>
<body>
  <h1>Wordflow Admin &nbsp;<a href="/admin?action=logout" style="font-size:.9rem;font-weight:400;">Logout</a></h1>

  <?php if ($flash): ?>
  <div class="flash-<?= $flash['type'] === 'ok' ? 'ok' : 'err' ?>"><?= htmlspecialchars($flash['msg']) ?></div>
  <?php endif; ?>

  <div class="stats">
    <div class="stat"><span><?= $total_free ?>/<?= MAX_FREE_USERS ?></span><small>Free Slots Used</small></div>
    <div class="stat"><span><?= MAX_FREE_USERS - $total_free ?></span><small>Slots Remaining</small></div>
    <div class="stat"><span><?= $total_waitlist ?></span><small>Waitlist</small></div>
    <div class="stat"><span><?= $total_feedback ?></span><small>Feedback Items</small></div>
  </div>

  <!-- ─── Release Management ─── -->
  <h2>Release Management
    <?php if (!empty($currentVersion['version'])): ?>
    <span class="version-badge">aktuell: v<?= htmlspecialchars($currentVersion['version']) ?></span>
    <?php endif; ?>
  </h2>
  <div class="panel">
    <form method="post">
      <input type="hidden" name="action" value="release" />

      <label>Neue Version</label>
      <input type="text" name="version" placeholder="z. B. 1.1.0" value="" required />

      <label>Release Notes <span style="text-transform:none;font-size:.75rem;opacity:.6">(eine pro Zeile)</span></label>
      <textarea name="release_notes" placeholder="Bugfix: Hotkey funktioniert nun zuverlaessig&#10;Neu: Verlauf wird gefiltert&#10;Verbesserung: Schnellere Transkription"></textarea>

      <hr class="divider" />

      <div class="btn-row">
        <button type="submit" class="btn btn-primary">Speichern</button>
        <label class="check-label">
          <input type="checkbox" name="send_emails" value="1" checked />
          Update-E-Mail an alle Free-User senden
        </label>
      </div>
    </form>

    <hr class="divider" />

    <p style="font-size:.85rem;color:#4A4A4A;margin:0 0 .75rem;">Nur E-Mails verschicken (ohne Version zu aendern) — z. B. wenn du vergessen hast, den Haken zu setzen:</p>
    <form method="post" onsubmit="return confirm('Update-E-Mails an alle Free-User senden (v<?= htmlspecialchars($currentVersion['version']) ?>)?')">
      <input type="hidden" name="action" value="send_emails" />
      <button type="submit" class="btn btn-secondary">E-Mails jetzt senden (v<?= htmlspecialchars($currentVersion['version']) ?>)</button>
    </form>
  </div>

  <!-- ─── Signups ─── -->
  <h2>Signups</h2>
  <table>
    <tr><th>Email</th><th>Type</th><th>Downloads</th><th>Date</th></tr>
    <?php foreach ($signups as $s): ?>
    <tr>
      <td><?= htmlspecialchars($s['email']) ?></td>
      <td><span class="badge <?= $s['type'] ?>"><?= $s['type'] ?></span></td>
      <td><?= (int)($s['download_count'] ?? 0) ?></td>
      <td><?= $s['created_at'] ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <!-- ─── Feedback ─── -->
  <h2>Feedback</h2>
  <table>
    <tr><th>Name</th><th>Email</th><th>Message</th><th>Version</th><th>Date</th></tr>
    <?php foreach ($feedbacks as $f): ?>
    <tr>
      <td><?= htmlspecialchars($f['name'] ?? '—') ?></td>
      <td><?= htmlspecialchars($f['email'] ?? '—') ?></td>
      <td><?= htmlspecialchars($f['message']) ?></td>
      <td><?= htmlspecialchars($f['app_version'] ?? '—') ?></td>
      <td><?= $f['created_at'] ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>

<?php
// ─── Brevo Helper (Admin) ──────────────────────────────────────────────────────

function admin_brevo_request(string $endpoint, array $payload): bool {
    $ch = curl_init('https://api.brevo.com/v3/' . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'api-key: ' . BREVO_API_KEY,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $response = curl_exec($ch);
    $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code >= 200 && $code < 300;
}

function admin_send_update_email(string $to, string $version, string $notes, string $downloadLink): int {
    $notesHtml = '';
    foreach (array_filter(array_map('trim', explode("\n", $notes))) as $line) {
        $notesHtml .= '<li style="margin-bottom:6px;">' . htmlspecialchars($line) . '</li>';
    }

    $html = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body style="margin:0;padding:0;background:#FDFBF7;font-family:Georgia,serif;">
<div style="max-width:560px;margin:40px auto;padding:40px;background:#ffffff;border-radius:16px;border:1px solid #EBE5DB;">
  <div style="font-style:italic;font-size:22px;color:#4A4A4A;margin-bottom:32px;">Wordflow.</div>
  <h1 style="font-size:26px;color:#1A1A1A;margin:0 0 8px;font-weight:normal;">Version ' . htmlspecialchars($version) . ' ist verfuegbar</h1>
  <p style="color:#4A4A4A;font-size:15px;line-height:1.6;margin:0 0 24px;">Eine neue Version von Wordflow steht fuer dich bereit.</p>
  <ul style="color:#4A4A4A;font-size:14px;line-height:1.7;padding-left:20px;margin:0 0 32px;">' . $notesHtml . '</ul>
  <a href="' . htmlspecialchars($downloadLink) . '" style="display:inline-block;background:#D2691E;color:#ffffff;padding:14px 28px;border-radius:8px;text-decoration:none;font-size:15px;">Update herunterladen &rarr;</a>
  <p style="color:#9A9A9A;font-size:12px;margin-top:32px;line-height:1.6;">Dies ist dein persoenlicher Download-Link. Er funktioniert jetzt und fuer alle zukuenftigen Updates.<br>Du erhaeltst diese E-Mail, weil du zu den ersten Nutzern von Wordflow gehoerst.</p>
</div>
</body></html>';

    $ok = admin_brevo_request('smtp/email', [
        'sender'      => ['name' => FROM_NAME, 'email' => FROM_EMAIL],
        'to'          => [['email' => $to]],
        'replyTo'     => ['email' => ADMIN_EMAIL],
        'subject'     => 'Wordflow ' . $version . ' — dein Update ist bereit',
        'htmlContent' => $html,
    ]);

    return $ok ? 1 : 0;
}
