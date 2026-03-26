<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first = trim($_POST['first']);
    $middle = trim($_POST['middle']);
    $last = trim($_POST['last']);
    $phone = trim($_POST['phone']);
    $city = trim($_POST['city']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, phone=?, city=? WHERE id=?");
    $stmt->bind_param("sssssi", $first, $middle, $last, $phone, $city, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['first_name'] = $first;
        $_SESSION['last_name'] = $last;
        echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile.'); window.history.back();</script>";
    }
    $stmt->close();
}
$conn->close();
?>
