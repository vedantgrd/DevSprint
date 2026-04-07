<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
require_once 'csrf.php';
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_csrf_token();
    $app_id = intval($_POST['app_id']);
    $status = $_POST['status']; // 'Accepted' or 'Rejected'
    
    // update status
    $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $app_id);
    if($stmt->execute()) {
        header("Location: admin_dashboard.php");
    } else {
        echo "<script>alert('Error updating status'); window.history.back();</script>";
    }
    $stmt->close();
}
$conn->close();
?>
