<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $team_name = trim($_POST['team_name']);
    $hackathon_id = intval($_POST['hackathon_id']);
    $user_id = $_SESSION['user_id'];

    if(!empty($team_name) && $hackathon_id > 0) {
        // Insert team
        $stmt = $conn->prepare("INSERT INTO teams (name, hackathon_id, leader_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $team_name, $hackathon_id, $user_id);
        
        if ($stmt->execute()) {
            $team_id = $stmt->insert_id;
            // Add leader as accepted member
            $member_stmt = $conn->prepare("INSERT INTO team_members (team_id, user_id, status) VALUES (?, ?, 'Accepted')");
            $member_stmt->bind_param("ii", $team_id, $user_id);
            $member_stmt->execute();
            $member_stmt->close();
            
            echo "<script>alert('Team Created!'); window.location.href='team_details.php?id=$team_id';</script>";
        } else {
            echo "<script>alert('Error creating team.'); window.history.back();</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Invalid details.'); window.history.back();</script>";
    }
}
$conn->close();
?>
