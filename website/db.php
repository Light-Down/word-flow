<?php
require_once __DIR__ . '/config.php';

function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    return $pdo;
}

// Run once on first deploy to create tables
function setup_db(): void {
    $db = get_db();
    $db->exec("
        CREATE TABLE IF NOT EXISTS signups (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            email      VARCHAR(255) NOT NULL UNIQUE,
            type       ENUM('free','waitlist') NOT NULL DEFAULT 'waitlist',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS feedback (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            email      VARCHAR(255),
            message    TEXT NOT NULL,
            app_version VARCHAR(20),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");
}
