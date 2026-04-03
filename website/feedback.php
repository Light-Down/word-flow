<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$message     = trim($_POST['message'] ?? '');
$email       = trim($_POST['email'] ?? '');
$app_version = trim($_POST['version'] ?? '');

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

// Save to DB
$db = get_db();
$db->prepare('INSERT INTO feedback (email, message, app_version) VALUES (?, ?, ?)')
   ->execute([$email ?: null, $message, $app_version ?: null]);

// Forward to your inbox
$subject = "Wordflow Feedback" . ($app_version ? " (v$app_version)" : "");
$body    = "Version: $app_version\n"
         . "Email: " . ($email ?: "anonymous") . "\n\n"
         . "Message:\n$message";

$headers = "From: Wordflow Feedback <" . FROM_EMAIL . ">\r\n"
         . "Content-Type: text/plain; charset=UTF-8";

mail(ADMIN_EMAIL, $subject, $body, $headers);

echo json_encode(['status' => 'success', 'message' => 'Thank you for your feedback!']);
