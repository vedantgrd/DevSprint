<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in first to apply.'); window.location.href='login.html';</script>";
    exit();
}
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hack_id = intval($_POST['hackathon_id']);
    $user_id = $_SESSION['user_id'];

    // Check if already applied
    $check = $conn->prepare("SELECT id FROM applications WHERE user_id = ? AND hackathon_id = ?");
    $check->bind_param("ii", $user_id, $hack_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo "<script>alert('You have already applied to this hackathon.'); window.location.href='profile.php';</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO applications (user_id, hackathon_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $hack_id);
        if ($stmt->execute()) {
            echo "<script>alert('Application successful!'); window.location.href='profile.php';</script>";
        } else {
            echo "<script>alert('Error submitting application.'); window.history.back();</script>";
        }
        $stmt->close();
    }
    $check->close();
}
$conn->close();
?>
