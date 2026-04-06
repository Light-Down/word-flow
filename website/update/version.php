<?php
// Serves the latest app version from Supabase app_versions table.
// Falls back to version.json if Supabase is unreachable.

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// ── Supabase config ──────────────────────────────────────────────────────────
$SUPABASE_URL  = 'https://amieachokpogaspaplxr.supabase.co';
$SUPABASE_ANON = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFtaWVhY2hva3BvZ2FzcGFwbHhyIiwicm9sZSI6ImFub24iLCJpYXQiOjE3Njk2MjkyNjgsImV4cCI6MjA4NTIwNTI2OH0.h7-dkHNAcJjwoGgnBQxUH8fIcNpzDT1q9nyEeWlDNq8';

// ── Fetch from Supabase ──────────────────────────────────────────────────────
$apiUrl = $SUPABASE_URL
    . '/rest/v1/app_versions'
    . '?select=version,download_url,release_notes,min_required'
    . '&is_latest=eq.true'
    . '&limit=1';

$ctx = stream_context_create([
    'http' => [
        'method'  => 'GET',
        'header'  => implode("\r\n", [
            'apikey: ' . $SUPABASE_ANON,
            'Authorization: Bearer ' . $SUPABASE_ANON,
            'Accept: application/json',
        ]),
        'timeout' => 5,
        'ignore_errors' => true,
    ],
]);

$raw = @file_get_contents($apiUrl, false, $ctx);

if ($raw !== false) {
    $rows = json_decode($raw, true);
    if (is_array($rows) && count($rows) > 0) {
        $row = $rows[0];
        echo json_encode([
            'version'     => $row['version']      ?? null,
            'downloadURL' => $row['download_url']  ?? null,
            'updateURL'   => 'https://word-flow.store/update',
            'releaseNotes'=> $row['release_notes'] ?? null,
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }
}

// ── Fallback: lokale version.json ────────────────────────────────────────────
$file = __DIR__ . '/version.json';
if (file_exists($file)) {
    echo file_get_contents($file);
} else {
    http_response_code(503);
    echo json_encode(['error' => 'Version nicht verfügbar']);
}
