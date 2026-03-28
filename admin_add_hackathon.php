<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $location = $_POST['location'];
    $date_start = $_POST['date_start'];
    $date_end = $_POST['date_end'];
    $prize_pool = $_POST['prize_pool'];
    $description = $_POST['description'];
    $application_type = $_POST['application_type'];

    $stmt = $conn->prepare("INSERT INTO hackathons (title, location, date_start, date_end, prize_pool, description, application_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $title, $location, $date_start, $date_end, $prize_pool, $description, $application_type);

    if ($stmt->execute()) {
        echo "<script>alert('Hackathon added successfully!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error adding hackathon.'); window.history.back();</script>";
    }
    $stmt->close();
}
$conn->close();
?>
