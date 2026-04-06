<?php
/**
 * Simple PHP File Cache - Start
 *
 * Speichert fertig gerenderte HTML-Seiten als Dateien.
 * Beim nächsten Aufruf wird die Datei direkt ausgeliefert (TTFB < 50ms).
 *
 * Usage: Ganz oben in jeder cachebaren PHP-Seite einbinden:
 * <?php require_once __DIR__ . '/includes/cache_start.php'; ?>
 *
 * Gecachte Seiten: index, why, about, agb, datenschutz, impressum, roadmap, changelog
 * Nie gecacht:     admin, signup, feedback, contact, download, update (dynamisch/POST)
 */

$cacheDir  = __DIR__ . '/../cache/';
$cacheTime = 604800; // 1 Woche

if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

$baseUrl   = strtok($_SERVER['REQUEST_URI'], '?');
$cacheKey  = md5($baseUrl);
$cacheFile = $cacheDir . 'page_' . $cacheKey . '.html';

$skipCache = false;

// 1. POST-Requests niemals cachen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skipCache = true;
}

// 2. Dynamische / nutzerspezifische Seiten niemals cachen
$noCacheRoutes = ['/admin', '/signup', '/feedback', '/contact', '/download', '/update'];
foreach ($noCacheRoutes as $route) {
    if (strpos($baseUrl, $route) !== false) {
        $skipCache = true;
        break;
    }
}

// 3. Nach Formular-Aktionen oder manuellem Bypass überspringen
if (isset($_GET['sent']) || isset($_GET['error']) || isset($_GET['nocache'])) {
    $skipCache = true;
}

// 4. Bei offenen Session-Fehlern überspringen
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!empty($_SESSION['form_errors'])) {
    $skipCache = true;
}

// 5. Im Development-Modus überspringen
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    $skipCache = true;
}

// Cache ausliefern wenn vorhanden und frisch
if (!$skipCache && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    header('X-PHP-Cache: HIT');
    header('X-Cache-Age: ' . (time() - filemtime($cacheFile)) . 's');
    readfile($cacheFile);
    exit;
}

// Cache MISS — Output-Buffering starten
header('X-PHP-Cache: MISS');
ob_start();
