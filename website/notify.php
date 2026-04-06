<?php
// Early Access signup — collects email with explicit marketing consent (DSGVO opt-in).
// Adds contact to Brevo and sends the Gumroad download link via email.

ob_start();
error_reporting(0);
ini_set('display_errors', '0');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

function json_out(array $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['error' => 'Method not allowed'], 405);
}

csrf_verify();
rate_limit('notify', 5, 900);

// Honeypot
if (!empty($_POST['url'] ?? '')) {
    json_out(['status' => 'success']);
}

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_out(['error' => 'Please enter a valid email address.'], 400);
}

// Opt-in pflichtfeld — muss aktiv gesetzt sein
$consent = $_POST['consent'] ?? '';
if ($consent !== '1') {
    json_out(['error' => 'Please accept the checkbox to continue.'], 400);
}

$db = get_db();

// Already signed up?
$stmt = $db->prepare('SELECT id FROM signups WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    // Schick trotzdem den Link — vielleicht haben sie ihn verloren
    brevo_send_download_email($email);
    json_out(['status' => 'already_registered', 'message' => "You're already on the list — we just resent your download link!"]);
}

// Save to DB
$db->prepare('INSERT INTO signups (email, type, token) VALUES (?, ?, ?)')
   ->execute([$email, 'early_access', null]);

// Add to Brevo with consent flag
brevo_add_contact($email, true);

// Send download link email
brevo_send_download_email($email);

// Notify admin
brevo_send_mail(
    ADMIN_EMAIL,
    "New Early Access signup: $email",
    "Email: $email\nType: early_access\nConsent: yes\n"
);

json_out(['status' => 'success']);

// ─── Brevo Helpers ──────────────────────────────────────────────────────────

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
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response ?: '{}', true) ?? [];
}

function brevo_add_contact(string $email, bool $consent = false): void {
    brevo_request('contacts', [
        'email'         => $email,
        'listIds'       => [BREVO_LIST_ID],
        'attributes'    => [
            'TYPE'            => 'EARLY_ACCESS',
            'MARKETING_OPTIN' => $consent ? 'YES' : 'NO',
        ],
        'updateEnabled' => true,
    ]);
}

function brevo_send_download_email(string $to): void {
    // Schick eine HTML-E-Mail mit dem Gumroad-Link
    $gumroadUrl = GUMROAD_URL;
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#FDFBF7;font-family:Georgia,serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#FDFBF7;padding:40px 0;">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;">
        <!-- Header -->
        <tr>
          <td style="padding:0 0 32px 0;">
            <p style="margin:0;font-size:22px;font-weight:700;color:#1A1A1A;letter-spacing:-0.5px;">Wordflow.</p>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td style="background:#fff;border-radius:16px;padding:40px;border:1px solid #E8E4DC;">
            <h1 style="margin:0 0 16px 0;font-size:28px;color:#1A1A1A;line-height:1.2;">
              Your free copy is ready.
            </h1>
            <p style="margin:0 0 24px 0;font-size:16px;color:#4A4A4A;line-height:1.6;">
              Thanks for joining Wordflow Early Access. During this phase,
              Wordflow is completely free — <strong>yours forever</strong>. No subscription, no catch.
            </p>
            <p style="margin:0 0 32px 0;font-size:16px;color:#4A4A4A;line-height:1.6;">
              Click the button below to download via Gumroad:
            </p>
            <!-- CTA Button -->
            <table cellpadding="0" cellspacing="0" style="margin:0 0 32px 0;">
              <tr>
                <td style="background:#D2691E;border-radius:100px;padding:16px 32px;">
                  <a href="{$gumroadUrl}" target="_blank"
                     style="color:#fff;font-size:16px;font-weight:600;text-decoration:none;font-family:Georgia,serif;white-space:nowrap;">
                    Download Wordflow — it's free →
                  </a>
                </td>
              </tr>
            </table>
            <p style="margin:0 0 8px 0;font-size:14px;color:#9A9A9A;line-height:1.5;">
              macOS only · Requires a free <a href="https://console.groq.com" style="color:#D2691E;">Groq API key</a>
              for speech recognition.
            </p>
            <p style="margin:0;font-size:13px;color:#C0BAB0;">
              You signed up with this email for Wordflow Early Access updates.
              You can unsubscribe at any time.
            </p>
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td style="padding:24px 0 0 0;">
            <p style="margin:0;font-size:12px;color:#C0BAB0;text-align:center;">
              Wordflow · word-flow.store · contact@word-flow.store
            </p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

    brevo_request('smtp/email', [
        'sender'      => ['name' => 'Wordflow', 'email' => 'contact@word-flow.store'],
        'to'          => [['email' => $to]],
        'replyTo'     => ['email' => ADMIN_EMAIL],
        'subject'     => 'Your free Wordflow download is here 🎉',
        'htmlContent' => $html,
    ]);
}

function brevo_send_mail(string $to, string $subject, string $text): void {
    brevo_request('smtp/email', [
        'sender'      => ['name' => FROM_NAME, 'email' => 'contact@word-flow.store'],
        'to'          => [['email' => $to]],
        'replyTo'     => ['email' => ADMIN_EMAIL],
        'subject'     => $subject,
        'textContent' => $text,
    ]);
}
