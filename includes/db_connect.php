<?php
require_once __DIR__ . '/csrf.php';

// Turn off error reporting to screen (Security upgrade)
mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli("localhost", "root", "", "devsprint");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
