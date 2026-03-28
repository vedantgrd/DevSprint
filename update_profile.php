<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header("Location: login_view.php");
    exit();
}
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first = trim($_POST['first']);
    $middle = trim($_POST['middle']);
    $last = trim($_POST['last']);
    $phone = trim($_POST['phone']);
    $city = trim($_POST['city']);
    $skills = trim($_POST['skills'] ?? '');
    $github = trim($_POST['github'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, phone=?, city=?, skills=?, github=?, linkedin=?, bio=? WHERE id=?");
    $stmt->bind_param("sssssssssi", $first, $middle, $last, $phone, $city, $skills, $github, $linkedin, $bio, $user_id);
    
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
