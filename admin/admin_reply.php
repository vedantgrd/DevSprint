<?php
require_once '../includes/csrf.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_USERNAME', 'your-gmail@gmail.com');   // ← YOUR Gmail address
define('SMTP_PASSWORD', 'xxxxxxxxxxxxxxxx');    // ← YOUR 16-char App Password (without spaces)
define('SMTP_FROM',     'your-gmail@gmail.com');   // ← Same as USERNAME
define('SMTP_FROM_NAME','DevSprint Admin');
// ══════════════════════════════════════════════════════════════════════════════

// Must be admin
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// CSRF check
require_csrf_token();

require_once '../includes/db_connect.php';
require_once '../includes/PHPMailer/PHPMailer.php';
require_once '../includes/PHPMailer/SMTP.php';
require_once '../includes/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Gather & sanitize inputs
$to_email = trim($_POST['to_email'] ?? '');
$to_name  = trim($_POST['to_name']  ?? '');
$subject  = trim($_POST['subject']  ?? '');
$body     = trim($_POST['body']     ?? '');
$msg_id   = intval($_POST['msg_id'] ?? 0);

// Validation
if (empty($to_email) || !filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid recipient email address.']);
    exit();
}
if (empty($subject)) {
    echo json_encode(['success' => false, 'message' => 'Subject cannot be empty.']);
    exit();
}
if (empty($body)) {
    echo json_encode(['success' => false, 'message' => 'Reply body cannot be empty.']);
    exit();
}

// Quick check: warn if credentials not set
if (strpos(SMTP_USERNAME, 'your-gmail') !== false) {
    echo json_encode(['success' => false, 'message' => 'SMTP not configured yet. Please set your Gmail credentials in admin_reply.php.']);
    exit();
}

// ── Send via PHPMailer SMTP ───────────────────────────────────────────────────
$mail = new PHPMailer(true);

try {
    // Server
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    // Fix for XAMPP SSL certificate issues on Windows
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ];

    // Reduce debug noise — errors still captured via ErrorInfo
    $mail->SMTPDebug = SMTP::DEBUG_OFF;

    // From / To
    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addReplyTo(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addAddress($to_email, $to_name ?: 'User');

    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;

    $escaped_body = nl2br(htmlspecialchars($body));
    $safe_name    = htmlspecialchars($to_name ?: 'there');

    $mail->Body = <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body   { font-family:Arial,sans-serif; background:#f4f4f4; padding:20px; margin:0; color:#222; }
    .wrap  { max-width:600px; margin:0 auto; background:#fff; border-radius:10px; overflow:hidden;
             box-shadow:0 4px 20px rgba(0,0,0,0.08); }
    .hdr   { background:linear-gradient(135deg,#00e5ff,#7c4dff); padding:26px 32px; }
    .hdr h2{ margin:0; color:#fff; font-size:1.25rem; font-family:Arial,sans-serif; }
    .bdy   { padding:28px 32px; line-height:1.75; font-size:0.95rem; color:#333; }
    .ftr   { background:#f7f7f7; padding:14px 32px; font-size:0.75rem; color:#999;
             border-top:1px solid #eee; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="hdr"><h2>⚡ DevSprint &middot; Admin Reply</h2></div>
    <div class="bdy">
      <p>Hi <strong>{$safe_name}</strong>,</p>
      <p>{$escaped_body}</p>
    </div>
    <div class="ftr">
      Official reply from the DevSprint administration team.<br>
      Please do not reply directly to this email.
    </div>
  </div>
</body>
</html>
HTML;

    $mail->AltBody = strip_tags(str_replace('<br>', "\n", $escaped_body));

    $mail->send();

    // Mark original message as read
    if ($msg_id > 0) {
        $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND message_type = 'contact'");
        $stmt->bind_param('i', $msg_id);
        $stmt->execute();
        $stmt->close();
    }

    echo json_encode(['success' => true, 'message' => 'Reply sent to ' . htmlspecialchars($to_email) . ' successfully!']);

} catch (Exception $e) {
    error_log('DevSprint AdminReply Error: ' . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => 'Send failed: ' . $mail->ErrorInfo]);
}
