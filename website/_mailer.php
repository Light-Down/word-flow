<?php
// Internal SMTP mailer — not directly accessible via web (blocked in .htaccess)

function smtp_send(string $to, string $toName, string $subject, string $html, string $text = ''): bool {
    $ctx = stream_context_create([
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);

    $socket = @stream_socket_client(
        'ssl://' . SMTP_HOST . ':' . SMTP_PORT,
        $errno, $errstr, 10,
        STREAM_CLIENT_CONNECT, $ctx
    );
    if (!$socket) return false;

    $r = fn()        => fgets($socket, 1024);
    $w = fn(string $l) => fwrite($socket, $l . "\r\n");

    $r(); // server greeting

    $w('EHLO word-flow.store');
    while ($line = fgets($socket, 1024)) {
        if (substr($line, 3, 1) === ' ') break;
    }

    $w('AUTH LOGIN');  $r();
    $w(base64_encode(SMTP_USER)); $r();
    $w(base64_encode(SMTP_PASS));
    $auth = $r();
    if (substr($auth, 0, 3) !== '235') { fclose($socket); return false; }

    $w('MAIL FROM:<' . SMTP_FROM . '>'); $r();
    $w('RCPT TO:<' . $to . '>');        $r();
    $w('DATA');                          $r();

    $boundary = md5(uniqid('wf', true));
    $text     = $text ?: strip_tags($html);

    $encodedName    = '=?UTF-8?B?' . base64_encode(SMTP_FROM_NAME) . '?=';
    $encodedToName  = $toName ? '=?UTF-8?B?' . base64_encode($toName) . '?=' : $to;
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    $msg = implode("\r\n", [
        "From: $encodedName <" . SMTP_FROM . ">",
        "To: $encodedToName <$to>",
        "Subject: $encodedSubject",
        "MIME-Version: 1.0",
        "Content-Type: multipart/alternative; boundary=\"$boundary\"",
        "",
        "--$boundary",
        "Content-Type: text/plain; charset=utf-8",
        "Content-Transfer-Encoding: base64",
        "",
        chunk_split(base64_encode($text)),
        "--$boundary",
        "Content-Type: text/html; charset=utf-8",
        "Content-Transfer-Encoding: base64",
        "",
        chunk_split(base64_encode($html)),
        "--$boundary--",
    ]);

    fwrite($socket, $msg . "\r\n.\r\n");
    $r();
    $w('QUIT');
    fclose($socket);
    return true;
}

function mailer_thank_you_html(string $name): string {
    $greeting = $name ? "Hey $name," : "Hey,";
    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#FDFBF7;font-family:Georgia,serif;">
<div style="max-width:560px;margin:40px auto;padding:40px 40px 32px;background:#ffffff;border-radius:16px;border:1px solid #EBE5DB;">
  <div style="font-style:italic;font-size:22px;color:#4A4A4A;margin-bottom:36px;">Wordflow.</div>
  <h1 style="font-size:26px;color:#1A1A1A;margin:0 0 12px;font-weight:normal;">Thanks for your feedback.</h1>
  <p style="color:#4A4A4A;font-size:15px;line-height:1.7;margin:0 0 12px;">$greeting</p>
  <p style="color:#4A4A4A;font-size:15px;line-height:1.7;margin:0 0 24px;">
    I read every message personally. Your feedback helps shape where Wordflow goes next — so thank you for taking the time.
  </p>
  <p style="color:#4A4A4A;font-size:15px;line-height:1.7;margin:0 0 32px;">
    If you asked something specific, I'll get back to you soon.
  </p>
  <a href="https://word-flow.store" style="display:inline-block;background:#D2691E;color:#ffffff;padding:13px 26px;border-radius:8px;text-decoration:none;font-size:15px;">Back to Wordflow &rarr;</a>
  <p style="color:#AAAAAA;font-size:12px;margin-top:36px;line-height:1.6;border-top:1px solid #EBE5DB;padding-top:20px;">
    You received this because you sent feedback via word-flow.store.<br>
    &copy; 2026 Wordflow &middot; Quietly Crafted in Frankfurt.
  </p>
</div>
</body>
</html>
HTML;
}

function mailer_admin_contact_html(string $name, string $email, string $message): string {
    $name    = htmlspecialchars($name    ?: '—');
    $email   = htmlspecialchars($email);
    $message = nl2br(htmlspecialchars($message));
    $time    = date('d.m.Y H:i');
    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#FDFBF7;font-family:Georgia,serif;">
<div style="max-width:560px;margin:40px auto;padding:40px;background:#ffffff;border-radius:16px;border:1px solid #EBE5DB;">
  <div style="font-style:italic;font-size:20px;color:#4A4A4A;margin-bottom:28px;">Wordflow.</div>
  <h1 style="font-size:22px;color:#1A1A1A;margin:0 0 24px;font-weight:normal;">New Contact Message</h1>
  <table style="width:100%;border-collapse:collapse;font-size:14px;color:#4A4A4A;">
    <tr><td style="padding:8px 0;border-bottom:1px solid #EBE5DB;width:90px;color:#9A9A9A;">Name</td><td style="padding:8px 0;border-bottom:1px solid #EBE5DB;">$name</td></tr>
    <tr><td style="padding:8px 0;border-bottom:1px solid #EBE5DB;color:#9A9A9A;">Email</td><td style="padding:8px 0;border-bottom:1px solid #EBE5DB;"><a href="mailto:$email" style="color:#D2691E;">$email</a></td></tr>
    <tr><td style="padding:8px 0;color:#9A9A9A;vertical-align:top;">Message</td><td style="padding:8px 0;">$message</td></tr>
  </table>
  <p style="color:#AAAAAA;font-size:12px;margin-top:28px;">$time &middot; word-flow.store/contact</p>
</div>
</body>
</html>
HTML;
}

function mailer_admin_feedback_html(string $name, string $email, string $message, string $version): string {
    $name    = htmlspecialchars($name    ?: '—');
    $email   = htmlspecialchars($email   ?: '—');
    $message = nl2br(htmlspecialchars($message));
    $version = htmlspecialchars($version ?: '—');
    $time    = date('d.m.Y H:i');
    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#FDFBF7;font-family:Georgia,serif;">
<div style="max-width:560px;margin:40px auto;padding:40px;background:#ffffff;border-radius:16px;border:1px solid #EBE5DB;">
  <div style="font-style:italic;font-size:20px;color:#4A4A4A;margin-bottom:28px;">Wordflow.</div>
  <h1 style="font-size:22px;color:#1A1A1A;margin:0 0 24px;font-weight:normal;">New Feedback</h1>
  <table style="width:100%;border-collapse:collapse;font-size:14px;color:#4A4A4A;">
    <tr><td style="padding:8px 0;border-bottom:1px solid #EBE5DB;width:90px;color:#9A9A9A;">Name</td><td style="padding:8px 0;border-bottom:1px solid #EBE5DB;">$name</td></tr>
    <tr><td style="padding:8px 0;border-bottom:1px solid #EBE5DB;color:#9A9A9A;">Email</td><td style="padding:8px 0;border-bottom:1px solid #EBE5DB;">$email</td></tr>
    <tr><td style="padding:8px 0;border-bottom:1px solid #EBE5DB;color:#9A9A9A;">Version</td><td style="padding:8px 0;border-bottom:1px solid #EBE5DB;">$version</td></tr>
    <tr><td style="padding:8px 0;color:#9A9A9A;vertical-align:top;">Message</td><td style="padding:8px 0;">$message</td></tr>
  </table>
  <p style="color:#AAAAAA;font-size:12px;margin-top:28px;">$time &middot; word-flow.store</p>
</div>
</body>
</html>
HTML;
}
