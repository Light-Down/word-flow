<?php

function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $db_path = __DIR__ . '/wordflow.db';
        $pdo = new PDO('sqlite:' . $db_path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec('PRAGMA journal_mode=WAL;');
        setup_db($pdo);
    }
    return $pdo;
}

function setup_db(PDO $db): void {
    $db->exec("
        CREATE TABLE IF NOT EXISTS signups (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            email        TEXT NOT NULL UNIQUE,
            type         TEXT NOT NULL DEFAULT 'waitlist',
            token        TEXT UNIQUE,
            download_count INTEGER DEFAULT 0,
            created_at   TEXT DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS feedback (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            name        TEXT,
            email       TEXT,
            message     TEXT NOT NULL,
            app_version TEXT,
            created_at  TEXT DEFAULT (datetime('now'))
        );
    ");
}
