<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/_mailer.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

csrf_verify();
rate_limit('feedback', 10, 900); // max 10 per IP per 15 min

// Honeypot — bots fill this, humans don't
if (!empty($_POST['website'] ?? '')) {
    echo json_encode(['status' => 'success', 'message' => 'Thank you!']);
    exit;
}

$name        = trim($_POST['name']    ?? '');
$email       = trim($_POST['email']   ?? '');
$message     = trim($_POST['message'] ?? '');
$app_version = trim($_POST['version'] ?? '');

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

// Save to DB
$db = get_db();
$db->prepare('INSERT INTO feedback (name, email, message, app_version) VALUES (?, ?, ?, ?)')
   ->execute([$name ?: null, $email ?: null, $message, $app_version ?: null]);

// Notify admin via Hostinger SMTP
smtp_send(
    to:      ADMIN_EMAIL,
    toName:  'Mark',
    subject: 'New Feedback' . ($name ? " from $name" : '') . ($app_version ? " (v$app_version)" : ''),
    html:    mailer_admin_feedback_html($name, $email, $message, $app_version)
);

// Send thank-you to user (only if they gave an email)
if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    smtp_send(
        to:      $email,
        toName:  $name,
        subject: 'Thanks for your feedback — Wordflow',
        html:    mailer_thank_you_html($name)
    );
}

echo json_encode(['status' => 'success', 'message' => 'Thank you for your feedback!']);
