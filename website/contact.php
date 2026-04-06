<?php
error_reporting(0);
ini_set('display_errors', '0');
ob_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/_mailer.php';

ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

csrf_verify();
rate_limit('contact', 5, 900); // max 5 per IP per 15 min

// Honeypot
if (!empty($_POST['website'] ?? '')) {
    echo json_encode(['status' => 'success']);
    exit;
}

$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'A valid email address is required.']);
    exit;
}

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required.']);
    exit;
}

// Notify admin
smtp_send(
    to:      ADMIN_EMAIL,
    toName:  'Mark',
    subject: 'New Contact' . ($name ? " from $name" : ''),
    html:    mailer_admin_contact_html($name, $email, $message)
);

// Thank-you to user
smtp_send(
    to:      $email,
    toName:  $name,
    subject: 'Got your message — Wordflow',
    html:    mailer_thank_you_html($name)
);

echo json_encode(['status' => 'success', 'message' => 'Thanks! I\'ll get back to you soon.']);
