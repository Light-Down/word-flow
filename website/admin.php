<?php
ob_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/_mailer.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// ─── Login / Logout ───────────────────────────────────────────────────────────
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
    ob_end_clean();
    header('Location: /admin');
    exit;
}

// ─── Draft attachment directory ───────────────────────────────────────────────
define('DRAFT_DIR', __DIR__ . '/files/drafts/');
if (!is_dir(DRAFT_DIR)) mkdir(DRAFT_DIR, 0755, true);

// ─── Admin actions ────────────────────────────────────────────────────────────
$flash = null;

if (($_SESSION['admin'] ?? false) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $db     = get_db();

    // ensure drafts table
    $db->exec("CREATE TABLE IF NOT EXISTS email_drafts (
        id              INTEGER PRIMARY KEY AUTOINCREMENT,
        subject         TEXT NOT NULL,
        body_html       TEXT NOT NULL,
        recipient_type  TEXT NOT NULL DEFAULT 'free',
        attachment_path TEXT,
        attachment_name TEXT,
        attachment_mime TEXT,
        created_at      INTEGER NOT NULL,
        updated_at      INTEGER NOT NULL,
        sent_at         INTEGER
    )");

    // ── Release ──────────────────────────────────────────────────────────────
    if ($action === 'release') {
        $version      = trim($_POST['version'] ?? '');
        $releaseNotes = trim($_POST['release_notes'] ?? '');
        $sendEmails   = isset($_POST['send_emails']);

        if ($version && $releaseNotes) {
            $versionFile   = __DIR__ . '/update/version.json';
            $changelogFile = __DIR__ . '/update/changelog.json';

            file_put_contents($versionFile, json_encode([
                'version'      => $version,
                'updateURL'    => 'https://word-flow.store/update',
                'releaseNotes' => $releaseNotes,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            $changelog = [];
            if (file_exists($changelogFile)) {
                $existing = json_decode(file_get_contents($changelogFile), true);
                if (is_array($existing)) $changelog = $existing;
            }
            $notesArray = array_values(array_filter(array_map('trim', explode("\n", $releaseNotes))));
            array_unshift($changelog, ['version' => $version, 'date' => date('Y-m-d'), 'notes' => $notesArray]);
            file_put_contents($changelogFile, json_encode($changelog, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            $flash = ['type' => 'ok', 'msg' => "Version $version gespeichert."];

            if ($sendEmails) {
                $users = $db->query('SELECT email, token FROM signups WHERE type = "free" AND token IS NOT NULL')->fetchAll(PDO::FETCH_ASSOC);
                $sent  = 0;
                $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
                foreach ($users as $u) {
                    $sent += admin_send_update_email($u['email'], $version, $releaseNotes, $baseUrl . '/download?token=' . $u['token']);
                }
                $flash['msg'] .= " $sent Update-E-Mails verschickt.";
            }
        } else {
            $flash = ['type' => 'err', 'msg' => 'Version und Release Notes sind erforderlich.'];
        }
    }

    // ── Send emails only ─────────────────────────────────────────────────────
    if ($action === 'send_emails') {
        $vInfo   = json_decode(file_get_contents(__DIR__ . '/update/version.json') ?: '{}', true);
        $version = $vInfo['version'] ?? '?';
        $notes   = $vInfo['releaseNotes'] ?? '';
        $users   = $db->query('SELECT email, token FROM signups WHERE type = "free" AND token IS NOT NULL')->fetchAll(PDO::FETCH_ASSOC);
        $sent    = 0;
        $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        foreach ($users as $u) {
            $sent += admin_send_update_email($u['email'], $version, $notes, $baseUrl . '/download?token=' . $u['token']);
        }
        $flash = ['type' => 'ok', 'msg' => "$sent Update-E-Mails verschickt (v$version)."];
    }

    // ── Save / update draft ──────────────────────────────────────────────────
    if ($action === 'save_draft') {
        $draftId       = (int)($_POST['draft_id'] ?? 0);
        $subject       = trim($_POST['draft_subject'] ?? '');
        $bodyHtml      = trim($_POST['draft_body'] ?? '');
        $recipientType = $_POST['recipient_type'] ?? 'free';
        $now           = time();

        if (!$subject || !$bodyHtml) {
            $flash = ['type' => 'err', 'msg' => 'Betreff und Inhalt sind erforderlich.'];
        } else {
            // Handle file upload
            $attPath = null; $attName = null; $attMime = null;
            if (!empty($_FILES['attachment']['name'])) {
                $file     = $_FILES['attachment'];
                $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
                $destPath = DRAFT_DIR . $now . '_' . $safeName;
                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $attPath = $destPath;
                    $attName = $safeName;
                    $attMime = $file['type'] ?: 'application/octet-stream';
                }
            }

            if ($draftId) {
                // keep existing attachment if no new file uploaded
                $existing = $db->prepare('SELECT attachment_path, attachment_name, attachment_mime FROM email_drafts WHERE id = ?')->execute([$draftId]);
                $existingRow = $db->prepare('SELECT attachment_path, attachment_name, attachment_mime FROM email_drafts WHERE id = ?');
                $existingRow->execute([$draftId]);
                $existingRow = $existingRow->fetch(PDO::FETCH_ASSOC);
                if (!$attPath && $existingRow) {
                    $attPath = $existingRow['attachment_path'];
                    $attName = $existingRow['attachment_name'];
                    $attMime = $existingRow['attachment_mime'];
                }
                $db->prepare('UPDATE email_drafts SET subject=?, body_html=?, recipient_type=?, attachment_path=?, attachment_name=?, attachment_mime=?, updated_at=? WHERE id=?')
                   ->execute([$subject, $bodyHtml, $recipientType, $attPath, $attName, $attMime, $now, $draftId]);
                $flash = ['type' => 'ok', 'msg' => 'Entwurf gespeichert.'];
            } else {
                $db->prepare('INSERT INTO email_drafts (subject, body_html, recipient_type, attachment_path, attachment_name, attachment_mime, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?)')
                   ->execute([$subject, $bodyHtml, $recipientType, $attPath, $attName, $attMime, $now, $now]);
                $flash = ['type' => 'ok', 'msg' => 'Entwurf erstellt.'];
            }
        }
    }

    // ── Delete draft ─────────────────────────────────────────────────────────
    if ($action === 'delete_draft') {
        $draftId = (int)($_POST['draft_id'] ?? 0);
        if ($draftId) {
            $row = $db->prepare('SELECT attachment_path FROM email_drafts WHERE id = ?');
            $row->execute([$draftId]);
            $row = $row->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['attachment_path'] && file_exists($row['attachment_path'])) {
                unlink($row['attachment_path']);
            }
            $db->prepare('DELETE FROM email_drafts WHERE id = ?')->execute([$draftId]);
            $flash = ['type' => 'ok', 'msg' => 'Entwurf gelöscht.'];
        }
    }

    // ── Send draft (test or real) ─────────────────────────────────────────────
    if ($action === 'send_draft' || $action === 'send_draft_test') {
        $draftId = (int)($_POST['draft_id'] ?? 0);
        $testEmail = trim($_POST['test_email'] ?? '');
        if ($draftId) {
            $stmt = $db->prepare('SELECT * FROM email_drafts WHERE id = ?');
            $stmt->execute([$draftId]);
            $draft = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($draft) {
                $attachment = [];
                if ($draft['attachment_path'] && file_exists($draft['attachment_path'])) {
                    $attachment = ['path' => $draft['attachment_path'], 'name' => $draft['attachment_name'], 'mime' => $draft['attachment_mime']];
                }
                if ($action === 'send_draft_test') {
                    $to = $testEmail ?: ADMIN_EMAIL;
                    $ok = smtp_send($to, 'Test', '[TEST] ' . $draft['subject'], $draft['body_html'], '', $attachment);
                    $flash = $ok
                        ? ['type' => 'ok',  'msg' => "Test-E-Mail an $to gesendet."]
                        : ['type' => 'err', 'msg' => 'Test-E-Mail fehlgeschlagen.'];
                } else {
                    // Send to real recipients
                    if ($draft['recipient_type'] === 'free') {
                        $users = $db->query('SELECT email FROM signups WHERE type = "free"')->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        $users = $db->query('SELECT email FROM signups')->fetchAll(PDO::FETCH_ASSOC);
                    }
                    $sent = 0;
                    foreach ($users as $u) {
                        if (smtp_send($u['email'], '', $draft['subject'], $draft['body_html'], '', $attachment)) $sent++;
                    }
                    $db->prepare('UPDATE email_drafts SET sent_at = ? WHERE id = ?')->execute([time(), $draftId]);
                    $flash = ['type' => 'ok', 'msg' => "$sent E-Mails erfolgreich versendet."];
                }
            }
        }
    }
}

// ─── Preview draft (GET) ──────────────────────────────────────────────────────
if (($_SESSION['admin'] ?? false) && ($_GET['action'] ?? '') === 'preview_draft') {
    $draftId = (int)($_GET['id'] ?? 0);
    $db = get_db();
    $db->exec("CREATE TABLE IF NOT EXISTS email_drafts (id INTEGER PRIMARY KEY AUTOINCREMENT, subject TEXT, body_html TEXT, recipient_type TEXT, attachment_path TEXT, attachment_name TEXT, attachment_mime TEXT, created_at INTEGER, updated_at INTEGER, sent_at INTEGER)");
    $stmt = $db->prepare('SELECT * FROM email_drafts WHERE id = ?');
    $stmt->execute([$draftId]);
    $draft = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($draft) {
        ob_end_clean();
        header('Content-Type: text/html; charset=utf-8');
        echo $draft['body_html'];
        exit;
    }
}

// ─── Login screen ─────────────────────────────────────────────────────────────
if (!($_SESSION['admin'] ?? false)):
    ob_end_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Wordflow Admin</title>
  <link rel="stylesheet" href="/assets/fonts.css" />
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: 'Inter', system-ui, sans-serif; background: #FDFBF7; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
    .card { background: white; padding: 2.5rem; border-radius: 1.25rem; box-shadow: 0 8px 32px rgba(0,0,0,.07); display: flex; flex-direction: column; gap: 1rem; min-width: 300px; border: 1px solid #EBE5DB; }
    .logo { font-family: Georgia, serif; font-style: italic; font-size: 1.5rem; color: #4A4A4A; margin: 0 0 .5rem; }
    input[type=password] { padding: .75rem 1rem; border: 1px solid #D1CDC7; border-radius: .625rem; font-size: 1rem; font-family: inherit; outline: none; transition: border-color .15s; }
    input[type=password]:focus { border-color: #D2691E; }
    button { padding: .75rem; background: #1A1A1A; color: white; border: none; border-radius: .625rem; font-size: .9rem; cursor: pointer; font-family: inherit; font-weight: 600; transition: opacity .15s; }
    button:hover { opacity: .85; }
  </style>
</head>
<body>
  <form class="card" method="post">
    <p class="logo">Wordflow.</p>
    <h2 style="margin:0;font-size:1.1rem;font-weight:600;">Admin</h2>
    <input type="password" name="password" placeholder="Passwort" autofocus autocomplete="current-password" />
    <button type="submit">Login</button>
  </form>
</body>
</html>
<?php
    exit;
endif;

// ─── Data ─────────────────────────────────────────────────────────────────────
$db = get_db();

$db->exec("CREATE TABLE IF NOT EXISTS email_drafts (
    id INTEGER PRIMARY KEY AUTOINCREMENT, subject TEXT NOT NULL, body_html TEXT NOT NULL,
    recipient_type TEXT NOT NULL DEFAULT 'free', attachment_path TEXT, attachment_name TEXT,
    attachment_mime TEXT, created_at INTEGER NOT NULL, updated_at INTEGER NOT NULL, sent_at INTEGER
)");
$db->exec("CREATE TABLE IF NOT EXISTS roadmap_votes (id INTEGER PRIMARY KEY AUTOINCREMENT, vote TEXT NOT NULL, ip TEXT NOT NULL, created INTEGER NOT NULL)");
$db->exec("CREATE TABLE IF NOT EXISTS roadmap_notify (id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL, feature TEXT NOT NULL, created INTEGER NOT NULL)");

$total_free     = (int) $db->query('SELECT COUNT(*) FROM signups WHERE type = "free"')->fetchColumn();
$total_waitlist = (int) $db->query('SELECT COUNT(*) FROM signups WHERE type = "waitlist"')->fetchColumn();
$total_feedback = (int) $db->query('SELECT COUNT(*) FROM feedback')->fetchColumn();
$total_votes    = (int) $db->query('SELECT COUNT(*) FROM roadmap_votes')->fetchColumn();
$total_notifies = (int) $db->query('SELECT COUNT(*) FROM roadmap_notify')->fetchColumn();

$signups   = $db->query('SELECT * FROM signups ORDER BY created_at DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
$feedbacks = $db->query('SELECT * FROM feedback ORDER BY created_at DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);
$drafts    = $db->query('SELECT * FROM email_drafts ORDER BY updated_at DESC')->fetchAll(PDO::FETCH_ASSOC);

// Roadmap vote results
$voteResults = $db->query('SELECT vote, COUNT(*) as cnt FROM roadmap_votes GROUP BY vote ORDER BY cnt DESC')->fetchAll(PDO::FETCH_ASSOC);
$maxVotes    = !empty($voteResults) ? max(array_column($voteResults, 'cnt')) : 1;

// Roadmap notify grouped by feature
$notifyResults = $db->query('SELECT feature, COUNT(*) as cnt FROM roadmap_notify GROUP BY feature ORDER BY cnt DESC')->fetchAll(PDO::FETCH_ASSOC);

// Current version
$versionFile    = __DIR__ . '/update/version.json';
$currentVersion = ['version' => '', 'releaseNotes' => ''];
if (file_exists($versionFile)) {
    $v = json_decode(file_get_contents($versionFile), true);
    if (is_array($v)) $currentVersion = $v;
}

// Edit draft?
$editDraft = null;
if (!empty($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM email_drafts WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editDraft = $stmt->fetch(PDO::FETCH_ASSOC);
}

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Wordflow Admin</title>
  <link rel="stylesheet" href="/assets/fonts.css" />
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: 'Inter', system-ui, sans-serif; background: #F7F3EE; color: #1A1A1A; margin: 0; padding: 0; }

    /* ── Layout ── */
    .admin-wrap { max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem 4rem; }

    /* ── Top bar ── */
    .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid #EBE5DB; }
    .topbar-logo { font-family: Georgia, serif; font-style: italic; font-size: 1.5rem; color: #1A1A1A; }
    .topbar-right { display: flex; align-items: center; gap: 1rem; font-size: .85rem; color: #4A4A4A; }
    .topbar-right a { color: #D2691E; text-decoration: none; }

    /* ── Section heading ── */
    .section-title { font-size: 1rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #4A4A4A; margin: 2.5rem 0 1rem; display: flex; align-items: center; gap: .5rem; }
    .section-title::after { content: ''; flex: 1; height: 1px; background: #EBE5DB; }

    /* ── Stats ── */
    .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: .5rem; }
    .stat { background: white; border-radius: .875rem; padding: 1.25rem 1.5rem; border: 1px solid #EBE5DB; }
    .stat-val { font-size: 2rem; font-weight: 700; color: #D2691E; line-height: 1; }
    .stat-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .1em; color: #9A9A9A; margin-top: .3rem; }

    /* ── Panel ── */
    .panel { background: white; border-radius: .875rem; border: 1px solid #EBE5DB; padding: 1.5rem; margin-bottom: 1.5rem; }
    .panel-header { font-weight: 600; font-size: .95rem; margin: 0 0 1.25rem; display: flex; align-items: center; justify-content: space-between; }

    /* ── Form elements ── */
    label.field-label { display: block; font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: #4A4A4A; margin-bottom: .35rem; margin-top: 1rem; }
    label.field-label:first-child { margin-top: 0; }
    input[type=text], input[type=email], textarea, select {
      width: 100%; padding: .65rem .9rem; border: 1px solid #D1CDC7; border-radius: .5rem;
      font-size: .9rem; font-family: inherit; outline: none; background: #FDFBF7;
      transition: border-color .15s;
    }
    input[type=text]:focus, input[type=email]:focus, textarea:focus, select:focus { border-color: #D2691E; background: white; }
    textarea { resize: vertical; min-height: 140px; font-family: 'Menlo', 'Monaco', monospace; font-size: .82rem; }
    input[type=file] { font-size: .85rem; color: #4A4A4A; }
    .check-label { display: flex; align-items: center; gap: .5rem; font-size: .875rem; color: #4A4A4A; cursor: pointer; }

    /* ── Buttons ── */
    .btn { display: inline-flex; align-items: center; gap: .4rem; padding: .55rem 1.2rem; border-radius: .5rem; font-size: .85rem; font-weight: 600; cursor: pointer; border: none; font-family: inherit; transition: all .15s; text-decoration: none; }
    .btn-primary   { background: #1A1A1A; color: white; }
    .btn-primary:hover { background: #2a2a2a; }
    .btn-orange    { background: #D2691E; color: white; }
    .btn-orange:hover { opacity: .88; }
    .btn-ghost     { background: #F2EDE6; color: #1A1A1A; border: 1px solid #EBE5DB; }
    .btn-ghost:hover { background: #EBE5DB; }
    .btn-danger    { background: #fdecea; color: #c62828; border: 1px solid #ef9a9a; }
    .btn-danger:hover { background: #fcd5d2; }
    .btn-sm        { padding: .35rem .85rem; font-size: .78rem; }
    .btn-row       { display: flex; gap: .75rem; align-items: center; flex-wrap: wrap; margin-top: 1.25rem; }

    /* ── Flash ── */
    .flash-ok  { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; padding: .75rem 1rem; border-radius: .625rem; margin-bottom: 1.5rem; font-size: .9rem; }
    .flash-err { background: #fdecea; color: #c62828; border: 1px solid #ef9a9a; padding: .75rem 1rem; border-radius: .625rem; margin-bottom: 1.5rem; font-size: .9rem; }

    /* ── Table ── */
    .table-wrap { overflow-x: auto; border-radius: .75rem; border: 1px solid #EBE5DB; }
    table { width: 100%; border-collapse: collapse; font-size: .85rem; background: white; }
    th { background: #F2EDE6; padding: .65rem 1rem; text-align: left; font-size: .7rem; text-transform: uppercase; letter-spacing: .1em; color: #4A4A4A; white-space: nowrap; }
    td { padding: .65rem 1rem; border-top: 1px solid #F2EDE6; vertical-align: top; color: #1A1A1A; }
    tr:hover td { background: #FDFBF7; }

    /* ── Badges ── */
    .badge { display: inline-block; padding: .15rem .55rem; border-radius: 9999px; font-size: .7rem; font-weight: 700; letter-spacing: .05em; }
    .badge-free     { background: #D2691E22; color: #D2691E; }
    .badge-waitlist { background: #2C3E5022; color: #2C3E50; }
    .badge-sent     { background: #e8f5e9; color: #2e7d32; }
    .badge-draft    { background: #F2EDE6; color: #4A4A4A; }
    .version-badge  { background: #D2691E22; color: #D2691E; padding: .15rem .6rem; border-radius: 9999px; font-size: .78rem; font-weight: 600; margin-left: .5rem; }

    /* ── Vote bar chart ── */
    .vote-bar-wrap { display: flex; flex-direction: column; gap: .75rem; }
    .vote-row { display: grid; grid-template-columns: 180px 1fr 48px; align-items: center; gap: .75rem; font-size: .85rem; }
    .vote-label { color: #4A4A4A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .vote-bar-bg { background: #F2EDE6; border-radius: 9999px; height: 10px; overflow: hidden; }
    .vote-bar-fill { background: #D2691E; border-radius: 9999px; height: 10px; transition: width .6s cubic-bezier(.16,1,.3,1); }
    .vote-count { font-weight: 700; color: #1A1A1A; text-align: right; font-size: .8rem; }

    /* ── Notify feature list ── */
    .notify-row { display: flex; align-items: center; justify-content: space-between; padding: .6rem 0; border-bottom: 1px solid #F2EDE6; font-size: .875rem; }
    .notify-row:last-child { border-bottom: none; }

    /* ── Draft card ── */
    .draft-card { border: 1px solid #EBE5DB; border-radius: .75rem; padding: 1rem 1.25rem; background: white; display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }
    .draft-card + .draft-card { margin-top: .75rem; }
    .draft-meta { font-size: .75rem; color: #9A9A9A; margin-top: .3rem; }
    .draft-actions { display: flex; gap: .5rem; flex-shrink: 0; flex-wrap: wrap; justify-content: flex-end; }

    /* ── Draft editor ── */
    .draft-editor { background: white; border-radius: .875rem; border: 2px solid #D2691E; padding: 1.5rem; margin-bottom: 1.5rem; }
    .draft-editor h3 { margin: 0 0 1.25rem; font-size: 1rem; }

    /* ── Divider ── */
    hr.div { border: none; border-top: 1px solid #EBE5DB; margin: 1.5rem 0; }

    @media (max-width: 640px) {
      .vote-row { grid-template-columns: 120px 1fr 40px; }
      .admin-wrap { padding: 1.25rem 1rem 3rem; }
      .stats { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
</head>
<body>
<div class="admin-wrap">

  <!-- Top bar -->
  <div class="topbar">
    <span class="topbar-logo">Wordflow.</span>
    <div class="topbar-right">
      <span>Admin Panel</span>
      <a href="/admin?action=logout">Logout →</a>
    </div>
  </div>

  <?php if ($flash): ?>
  <div class="flash-<?= $flash['type'] === 'ok' ? 'ok' : 'err' ?>"><?= htmlspecialchars($flash['msg']) ?></div>
  <?php endif; ?>

  <!-- ── Stats ──────────────────────────────────────────────────── -->
  <div class="stats">
    <div class="stat"><div class="stat-val"><?= $total_free ?>/<?= MAX_FREE_USERS ?></div><div class="stat-label">Free Slots</div></div>
    <div class="stat"><div class="stat-val"><?= MAX_FREE_USERS - $total_free ?></div><div class="stat-label">Slots frei</div></div>
    <div class="stat"><div class="stat-val"><?= $total_waitlist ?></div><div class="stat-label">Waitlist</div></div>
    <div class="stat"><div class="stat-val"><?= $total_feedback ?></div><div class="stat-label">Feedback</div></div>
    <div class="stat"><div class="stat-val"><?= $total_votes ?></div><div class="stat-label">Roadmap Votes</div></div>
    <div class="stat"><div class="stat-val"><?= $total_notifies ?></div><div class="stat-label">Notify Requests</div></div>
  </div>

  <!-- ── Release Management ─────────────────────────────────────── -->
  <div class="section-title">🚀 Release Management
    <?php if (!empty($currentVersion['version'])): ?>
    <span class="version-badge">v<?= htmlspecialchars($currentVersion['version']) ?></span>
    <?php endif; ?>
  </div>
  <div class="panel">
    <form method="post">
      <input type="hidden" name="action" value="release" />
      <label class="field-label">Neue Version</label>
      <input type="text" name="version" placeholder="z. B. 1.1.0" required />
      <label class="field-label">Release Notes <span style="text-transform:none;font-weight:400;opacity:.6">(eine pro Zeile)</span></label>
      <textarea name="release_notes" placeholder="Bugfix: Hotkey funktioniert&#10;Neu: Verlauf wird gefiltert"></textarea>
      <hr class="div" />
      <div class="btn-row">
        <button type="submit" class="btn btn-primary">💾 Speichern</button>
        <label class="check-label">
          <input type="checkbox" name="send_emails" value="1" checked />
          Update-E-Mail an alle Free-User senden
        </label>
      </div>
    </form>
    <hr class="div" />
    <p style="font-size:.82rem;color:#9A9A9A;margin:0 0 .75rem;">Nur E-Mails senden (ohne Version zu ändern):</p>
    <form method="post" onsubmit="return confirm('Update-E-Mails senden (v<?= htmlspecialchars($currentVersion['version']) ?>)?')">
      <input type="hidden" name="action" value="send_emails" />
      <button type="submit" class="btn btn-ghost">📨 E-Mails jetzt senden (v<?= htmlspecialchars($currentVersion['version']) ?>)</button>
    </form>
  </div>

  <!-- ── E-Mail Drafts ──────────────────────────────────────────── -->
  <div class="section-title">✉️ E-Mail Entwürfe</div>

  <!-- Draft editor (new or edit) -->
  <?php
  $isEditing = (bool)$editDraft;
  $df = $editDraft ?: ['id' => 0, 'subject' => '', 'body_html' => '', 'recipient_type' => 'free', 'attachment_name' => ''];
  ?>
  <div class="draft-editor">
    <h3><?= $isEditing ? '✏️ Entwurf bearbeiten #' . $df['id'] : '➕ Neuen Entwurf erstellen' ?></h3>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="save_draft" />
      <input type="hidden" name="draft_id" value="<?= (int)$df['id'] ?>" />

      <label class="field-label">Betreff</label>
      <input type="text" name="draft_subject" value="<?= htmlspecialchars($df['subject']) ?>" placeholder="z. B. Wordflow 1.2 ist da 🎉" required />

      <label class="field-label">Empfänger</label>
      <select name="recipient_type">
        <option value="free"<?= $df['recipient_type'] === 'free' ? ' selected' : '' ?>>Nur Free-User</option>
        <option value="all"<?= $df['recipient_type'] === 'all'  ? ' selected' : '' ?>>Alle (Free + Waitlist)</option>
      </select>

      <label class="field-label">E-Mail HTML</label>
      <textarea name="draft_body" rows="16" placeholder="<!DOCTYPE html>..."><?= htmlspecialchars($df['body_html']) ?></textarea>

      <label class="field-label">
        Anhang (optional)
        <?php if ($df['attachment_name']): ?>
          <span style="font-weight:400;opacity:.6"> — aktuell: <?= htmlspecialchars($df['attachment_name']) ?></span>
        <?php endif; ?>
      </label>
      <input type="file" name="attachment" accept=".zip,.dmg,.pdf,.png,.jpg" />

      <div class="btn-row">
        <button type="submit" class="btn btn-primary">💾 Entwurf speichern</button>
        <?php if ($isEditing): ?>
        <a href="/admin" class="btn btn-ghost">Abbrechen</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Draft list -->
  <?php if (empty($drafts)): ?>
  <p style="color:#9A9A9A;font-size:.875rem;">Noch keine Entwürfe vorhanden.</p>
  <?php else: ?>
  <div>
    <?php foreach ($drafts as $d): ?>
    <div class="draft-card">
      <div style="flex:1;min-width:0;">
        <div style="font-weight:600;font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($d['subject']) ?></div>
        <div class="draft-meta">
          <span class="badge <?= $d['sent_at'] ? 'badge-sent' : 'badge-draft' ?>"><?= $d['sent_at'] ? 'Gesendet ' . date('d.m.Y', $d['sent_at']) : 'Entwurf' ?></span>
          &nbsp;·&nbsp; <?= htmlspecialchars($d['recipient_type']) ?>
          &nbsp;·&nbsp; <?= date('d.m.Y H:i', $d['updated_at']) ?>
          <?php if ($d['attachment_name']): ?>&nbsp;·&nbsp; 📎 <?= htmlspecialchars($d['attachment_name']) ?><?php endif; ?>
        </div>
      </div>
      <div class="draft-actions">
        <!-- Preview -->
        <a href="/admin?action=preview_draft&id=<?= $d['id'] ?>" target="_blank" class="btn btn-ghost btn-sm">👁 Vorschau</a>
        <!-- Edit -->
        <a href="/admin?edit=<?= $d['id'] ?>" class="btn btn-ghost btn-sm">✏️ Bearbeiten</a>
        <!-- Test send -->
        <form method="post" style="display:inline;">
          <input type="hidden" name="action" value="send_draft_test" />
          <input type="hidden" name="draft_id" value="<?= $d['id'] ?>" />
          <input type="hidden" name="test_email" value="<?= htmlspecialchars(ADMIN_EMAIL) ?>" />
          <button type="submit" class="btn btn-ghost btn-sm">🧪 Test</button>
        </form>
        <!-- Real send -->
        <?php if (!$d['sent_at']): ?>
        <form method="post" style="display:inline;" onsubmit="return confirm('Wirklich an alle Empfänger senden?')">
          <input type="hidden" name="action" value="send_draft" />
          <input type="hidden" name="draft_id" value="<?= $d['id'] ?>" />
          <button type="submit" class="btn btn-orange btn-sm">📤 Senden</button>
        </form>
        <?php endif; ?>
        <!-- Delete -->
        <form method="post" style="display:inline;" onsubmit="return confirm('Entwurf löschen?')">
          <input type="hidden" name="action" value="delete_draft" />
          <input type="hidden" name="draft_id" value="<?= $d['id'] ?>" />
          <button type="submit" class="btn btn-danger btn-sm">🗑</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- ── Roadmap Votes ──────────────────────────────────────────── -->
  <div class="section-title">🗳️ Roadmap — Feature Votes</div>
  <div class="panel">
    <?php if (empty($voteResults)): ?>
    <p style="color:#9A9A9A;font-size:.875rem;margin:0;">Noch keine Votes.</p>
    <?php else: ?>
    <div class="vote-bar-wrap">
      <?php foreach ($voteResults as $v): ?>
      <?php $pct = $maxVotes > 0 ? round($v['cnt'] / $maxVotes * 100) : 0; ?>
      <div class="vote-row">
        <span class="vote-label" title="<?= htmlspecialchars($v['vote']) ?>"><?= htmlspecialchars($v['vote']) ?></span>
        <div class="vote-bar-bg">
          <div class="vote-bar-fill" style="width:<?= $pct ?>%"></div>
        </div>
        <span class="vote-count"><?= $v['cnt'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <p style="font-size:.75rem;color:#9A9A9A;margin:1rem 0 0;">Gesamt: <?= $total_votes ?> Vote<?= $total_votes !== 1 ? 's' : '' ?></p>
    <?php endif; ?>
  </div>

  <!-- ── Roadmap Notify ─────────────────────────────────────────── -->
  <div class="section-title">🔔 Roadmap — Feature Notify</div>
  <div class="panel">
    <?php if (empty($notifyResults)): ?>
    <p style="color:#9A9A9A;font-size:.875rem;margin:0;">Noch keine Notify-Anfragen.</p>
    <?php else: ?>
    <div>
      <?php foreach ($notifyResults as $n): ?>
      <div class="notify-row">
        <span><?= htmlspecialchars($n['feature']) ?></span>
        <span class="badge badge-free"><?= $n['cnt'] ?> <?= $n['cnt'] === 1 ? 'Person' : 'Personen' ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- ── Signups ────────────────────────────────────────────────── -->
  <div class="section-title">👥 Signups</div>
  <div class="table-wrap">
    <table>
      <tr><th>Email</th><th>Type</th><th>Downloads</th><th>Datum</th></tr>
      <?php foreach ($signups as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['email']) ?></td>
        <td><span class="badge badge-<?= $s['type'] ?>"><?= $s['type'] ?></span></td>
        <td><?= (int)($s['download_count'] ?? 0) ?></td>
        <td style="white-space:nowrap;color:#9A9A9A;"><?= $s['created_at'] ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>

  <!-- ── Feedback ──────────────────────────────────────────────── -->
  <div class="section-title">💬 Feedback</div>
  <div class="table-wrap">
    <table>
      <tr><th>Name</th><th>Email</th><th>Nachricht</th><th>Version</th><th>Datum</th></tr>
      <?php foreach ($feedbacks as $f): ?>
      <tr>
        <td><?= htmlspecialchars($f['name'] ?? '—') ?></td>
        <td><?= htmlspecialchars($f['email'] ?? '—') ?></td>
        <td style="max-width:320px;"><?= nl2br(htmlspecialchars($f['message'])) ?></td>
        <td><?= htmlspecialchars($f['app_version'] ?? '—') ?></td>
        <td style="white-space:nowrap;color:#9A9A9A;"><?= $f['created_at'] ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>

</div>
</body>
</html>

<?php
// ─── Brevo Helper ─────────────────────────────────────────────────────────────

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
        CURLOPT_TIMEOUT => 10,
    ]);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
  <h1 style="font-size:26px;color:#1A1A1A;margin:0 0 8px;font-weight:normal;">Version ' . htmlspecialchars($version) . ' ist verfügbar</h1>
  <p style="color:#4A4A4A;font-size:15px;line-height:1.6;margin:0 0 24px;">Eine neue Version von Wordflow steht für dich bereit.</p>
  <ul style="color:#4A4A4A;font-size:14px;line-height:1.7;padding-left:20px;margin:0 0 32px;">' . $notesHtml . '</ul>
  <a href="' . htmlspecialchars($downloadLink) . '" style="display:inline-block;background:#D2691E;color:#ffffff;padding:14px 28px;border-radius:8px;text-decoration:none;font-size:15px;">Update herunterladen &rarr;</a>
  <p style="color:#9A9A9A;font-size:12px;margin-top:32px;line-height:1.6;">Du erhältst diese E-Mail, weil du zu den ersten Nutzern von Wordflow gehörst.</p>
</div></body></html>';

    return smtp_send($to, '', 'Wordflow ' . $version . ' — dein Update ist bereit', $html) ? 1 : 0;
}
