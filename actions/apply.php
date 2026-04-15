<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in first to apply.'); window.location.href='../login/login_view.php';</script>";
    exit();
}
require_once '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hack_id = intval($_POST['hackathon_id']);
    $user_id = $_SESSION['user_id'];
    $team_id = isset($_POST['team_id']) && intval($_POST['team_id']) > 0 ? intval($_POST['team_id']) : null;

    // Check if already applied
    if ($team_id) {
        $check = $conn->prepare("SELECT id FROM applications WHERE team_id = ? AND hackathon_id = ?");
        $check->bind_param("ii", $team_id, $hack_id);
    } else {
        $check = $conn->prepare("SELECT id FROM applications WHERE user_id = ? AND hackathon_id = ? AND team_id IS NULL");
        $check->bind_param("ii", $user_id, $hack_id);
    }
    
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo "<script>alert('You or your team have already applied to this hackathon.'); window.location.href='../profile/profile.php';</script>";
    } else {
        if ($team_id) {
            $stmt = $conn->prepare("INSERT INTO applications (user_id, hackathon_id, team_id) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $hack_id, $team_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO applications (user_id, hackathon_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $hack_id);
        }
        
        if ($stmt->execute()) {
            echo "<script>alert('Application successful!'); window.location.href='../profile/profile.php';</script>";
        } else {
            echo "<script>alert('Error submitting application.'); window.history.back();</script>";
        }
        $stmt->close();
    }
    $check->close();
}
$conn->close();
?>
