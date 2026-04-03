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

// Count current free slots used
$count = (int) $db->query('SELECT COUNT(*) FROM signups WHERE type = "free"')->fetchColumn();
$type  = $count < MAX_FREE_USERS ? 'free' : 'waitlist';

// Save to DB
$db->prepare('INSERT INTO signups (email, type) VALUES (?, ?)')->execute([$email, $type]);

// Add contact to Brevo
add_to_brevo($email, $type);

// Send confirmation email
send_confirmation($email, $type, $count + 1);

echo json_encode([
    'status'     => 'success',
    'type'       => $type,
    'slot'       => $type === 'free' ? ($count + 1) : null,
    'slots_left' => $type === 'free' ? (MAX_FREE_USERS - $count - 1) : 0,
    'message'    => $type === 'free'
        ? 'You\'re in! Check your inbox for your download link.'
        : 'You\'re on the waitlist! We\'ll notify you at launch.',
]);

// ─── Helpers ──────────────────────────────────────────────────────

function add_to_brevo(string $email, string $type): void {
    $payload = json_encode([
        'email'      => $email,
        'listIds'    => [BREVO_LIST_ID],
        'attributes' => ['TYPE' => $type],
        'updateEnabled' => false,
    ]);

    $ch = curl_init('https://api.brevo.com/v3/contacts');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'api-key: ' . BREVO_API_KEY,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function send_confirmation(string $email, string $type, int $slot): void {
    if ($type === 'free') {
        $subject = "Your free copy of Wordflow is ready 🎙";
        $body    = "Hi,\n\nYou're #$slot of " . MAX_FREE_USERS . " — welcome to the Wordflow beta!\n\n"
                 . "Download your copy here:\n" . DOWNLOAD_URL . "\n\n"
                 . "Setup is quick — just add your Groq API key (free tier is plenty for most users).\n\n"
                 . "I'd love to hear what you think. You can send feedback directly in the app.\n\n"
                 . "— Wordflow";
    } else {
        $subject = "You're on the Wordflow waitlist";
        $body    = "Hi,\n\nThe first 100 free slots are taken, but you're on the list.\n\n"
                 . "You'll be the first to know when Wordflow launches publicly at €25.\n\n"
                 . "— Wordflow";
    }

    $headers = "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n"
             . "Reply-To: " . ADMIN_EMAIL . "\r\n"
             . "Content-Type: text/plain; charset=UTF-8";

    mail($email, $subject, $body, $headers);

    // Also notify yourself
    mail(ADMIN_EMAIL, "New signup: $email ($type #$slot)", "Email: $email\nType: $type\nSlot: $slot", $headers);
}
