<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Simple password protection — change this password!
$ADMIN_PASSWORD = 'wordflow2026';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $ADMIN_PASSWORD) {
        $_SESSION['admin'] = true;
    }
}

if ($_GET['action'] ?? '' === 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
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

$signups  = $db->query('SELECT * FROM signups ORDER BY created_at DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
$feedbacks = $db->query('SELECT * FROM feedback ORDER BY created_at DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);
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
    h2 { margin-top: 0; }
    a { color: #D2691E; }
  </style>
</head>
<body>
  <h1>Wordflow Admin &nbsp;<a href="?action=logout" style="font-size:.9rem;font-weight:400;">Logout</a></h1>

  <div class="stats">
    <div class="stat"><span><?= $total_free ?>/<?= MAX_FREE_USERS ?></span><small>Free Slots Used</small></div>
    <div class="stat"><span><?= MAX_FREE_USERS - $total_free ?></span><small>Slots Remaining</small></div>
    <div class="stat"><span><?= $total_waitlist ?></span><small>Waitlist</small></div>
    <div class="stat"><span><?= $total_feedback ?></span><small>Feedback Items</small></div>
  </div>

  <h2>Signups</h2>
  <table>
    <tr><th>Email</th><th>Type</th><th>Date</th></tr>
    <?php foreach ($signups as $s): ?>
    <tr>
      <td><?= htmlspecialchars($s['email']) ?></td>
      <td><span class="badge <?= $s['type'] ?>"><?= $s['type'] ?></span></td>
      <td><?= $s['created_at'] ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <h2>Feedback</h2>
  <table>
    <tr><th>Message</th><th>Email</th><th>Version</th><th>Date</th></tr>
    <?php foreach ($feedbacks as $f): ?>
    <tr>
      <td><?= htmlspecialchars($f['message']) ?></td>
      <td><?= htmlspecialchars($f['email'] ?? '—') ?></td>
      <td><?= htmlspecialchars($f['app_version'] ?? '—') ?></td>
      <td><?= $f['created_at'] ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
