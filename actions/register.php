<?php
// Start session (if needed later for login)
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Database connection
require_once '../includes/db_connect.php';

// Check if form submitted
if (isset($_POST['register'])) {

    // Collect POST data and sanitize
    $first = trim($_POST['firstName']);
    $middle = trim($_POST['middleName']);
    $last = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm = $_POST['confirmPassword'];
    $city = trim($_POST['city']);

    // Password match check
    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match'); window.history.back();</script>";
        exit();
    }

    // Hash the password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL statement to avoid SQL injection
    $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, email, phone, password, city) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $first, $middle, $last, $email, $phone, $hashed, $city);

    // Execute and check
    if ($stmt->execute()) {
        // Registration success → alert + redirect to login
        echo "<script>
        alert('Registration Successful! Please login.');
        window.location.href='../login/login_view.php';
        </script>";
    } else {
        // If email already exists or other error
        $errorMsg = $conn->error;
        echo "<script>
        alert('Error during registration: " . addslashes($errorMsg) . "');
        window.history.back();
        </script>";
    }

    $stmt->close();
}

$conn->close();
?>