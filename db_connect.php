<?php
$conn = new mysqli("localhost", "root", "", "devsprint");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
