<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

csrf_verify();
rate_limit('signup', 5, 900); // max 5 per IP per 15 min

// Honeypot check
if (!empty($_POST['url'] ?? '')) {
    echo json_encode(['status' => 'success']);
    exit;
}

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

$db = get_db();

// Check if already signed up
$stmt = $db->prepare('SELECT id, type FROM signups WHERE email = ?');
$stmt->execute([$email]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo json_encode([
        'status'  => 'already_registered',
        'type'    => $existing['type'],
        'message' => $existing['type'] === 'free'
            ? 'You already have a free copy — check your inbox!'
            : 'You\'re already on the waitlist!',
    ]);
    exit;
}

// Use hard limit internally, show marketing limit on site
$count = (int) $db->query('SELECT COUNT(*) FROM signups WHERE type = "free"')->fetchColumn();
$type  = $count < MAX_FREE_USERS_HARD ? 'free' : 'waitlist';

// Generate unique download token for free users
$token = $type === 'free' ? bin2hex(random_bytes(32)) : null;

// Save to DB
$db->prepare('INSERT INTO signups (email, type, token) VALUES (?, ?, ?)')->execute([$email, $type, $token]);

// Add to Brevo contact list
brevo_add_contact($email, $type);

// Send confirmation via Brevo template
$templateId = $type === 'free' ? BREVO_TPL_FREE : BREVO_TPL_WAITLIST;
$downloadLink = $token
    ? (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/download?token=' . $token
    : null;

brevo_send_template($email, $templateId, [
    'download_url' => $downloadLink ?? DOWNLOAD_URL,
]);

// Notify yourself
brevo_send_mail(
    to:      ADMIN_EMAIL,
    subject: "New signup: $email ($type #" . ($count + 1) . ")",
    text:    "Email: $email\nType: $type\nSlot: " . ($count + 1)
);

echo json_encode([
    'status'     => 'success',
    'type'       => $type,
    'slot'       => $type === 'free' ? ($count + 1) : null,
    'slots_left' => max(0, MAX_FREE_USERS - $count - 1),
    'message'    => $type === 'free'
        ? 'You\'re in! Check your inbox for your download link.'
        : 'You\'re on the waitlist! We\'ll notify you at launch.',
]);

// ─── Brevo Helpers ────────────────────────────────────────────────────────

function brevo_request(string $endpoint, array $payload): array {
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
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response ?: '{}', true) ?? [];
}

function brevo_send_template(string $to, int $templateId, array $params = []): void {
    brevo_request('smtp/email', [
        'to'         => [['email' => $to]],
        'templateId' => $templateId,
        'params'     => $params,
    ]);
}

function brevo_add_contact(string $email, string $type): void {
    brevo_request('contacts', [
        'email'         => $email,
        'listIds'       => [BREVO_LIST_ID],
        'attributes'    => ['TYPE' => strtoupper($type)],
        'updateEnabled' => true,
    ]);
}

function brevo_send_mail(string $to, string $subject, string $text): void {
    brevo_request('smtp/email', [
        'sender'     => ['name' => FROM_NAME, 'email' => FROM_EMAIL],
        'to'         => [['email' => $to]],
        'replyTo'    => ['email' => ADMIN_EMAIL],
        'subject'    => $subject,
        'textContent' => $text,
    ]);
}
