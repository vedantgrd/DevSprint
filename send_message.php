<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db_connect.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login_view.php?redirect=contact.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id     = (int)$_SESSION['user_id'];
    $name        = trim($_POST['name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $subject     = trim($_POST['subject'] ?? '');
    $message_txt = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$message_txt) {
        $_SESSION['contact_error'] = 'Please fill in all required fields.';
        header("Location: contact.php");
        exit();
    }

    // Insert into messages table (receiver_id = NULL means → admin)
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, subject, sender_name, sender_email, message_type) VALUES (?, NULL, ?, ?, ?, ?, 'contact')");
    $stmt->bind_param("issss", $user_id, $message_txt, $subject, $name, $email);

    if ($stmt->execute()) {
        $_SESSION['contact_success'] = true;
    } else {
        $_SESSION['contact_error'] = 'Failed to send message. Please try again.';
    }
    $stmt->close();
    $conn->close();
    header("Location: contact.php");
    exit();
}

header("Location: contact.php");
exit();
?>
