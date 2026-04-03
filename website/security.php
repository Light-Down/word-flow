<?php

// ─── CSRF ─────────────────────────────────────────────────────────────────

function csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $token    = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';
    if (!$expected || !hash_equals($expected, $token)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid request.']);
        exit;
    }
}

// ─── Rate Limiting ─────────────────────────────────────────────────────────

function rate_limit(string $endpoint, int $max, int $window_seconds): void {
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $db  = get_db();
    $db->exec("CREATE TABLE IF NOT EXISTS rate_limits (
        ip       TEXT NOT NULL,
        endpoint TEXT NOT NULL,
        hit_at   INTEGER NOT NULL
    )");

    $since = time() - $window_seconds;

    // Remove expired entries
    $db->prepare("DELETE FROM rate_limits WHERE hit_at < ?")->execute([$since]);

    // Count recent hits from this IP
    $stmt = $db->prepare("SELECT COUNT(*) FROM rate_limits WHERE ip = ? AND endpoint = ? AND hit_at >= ?");
    $stmt->execute([$ip, $endpoint, $since]);
    $count = (int) $stmt->fetchColumn();

    if ($count >= $max) {
        http_response_code(429);
        echo json_encode(['error' => 'Too many requests. Please try again later.']);
        exit;
    }

    // Record hit
    $db->prepare("INSERT INTO rate_limits (ip, endpoint, hit_at) VALUES (?, ?, ?)")
       ->execute([$ip, $endpoint, time()]);
}
