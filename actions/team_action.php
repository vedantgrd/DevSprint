<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login_view.php");
    exit();
}
require_once '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];
    $team_id = intval($_POST['team_id']);
    $user_id = $_SESSION['user_id'];
    
    if ($action === 'request_join') {
        // User requests to join a team
        $stmt = $conn->prepare("INSERT INTO team_members (team_id, user_id, status) VALUES (?, ?, 'Pending')");
        $stmt->bind_param("ii", $team_id, $user_id);
        if ($stmt->execute()) {
            echo "<script>alert('Request sent!'); window.location.href='../teams/team_details.php?id=$team_id';</script>";
        } else {
            echo "<script>alert('Error: You may have already requested to join.'); window.history.back();</script>";
        }
        $stmt->close();
    } elseif ($action === 'invite') {
        // Leader invites a user
        $target_user = intval($_POST['target_user']);
        $check = $conn->prepare("SELECT id FROM teams WHERE id = ? AND leader_id = ?");
        $check->bind_param("ii", $team_id, $user_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $stmt = $conn->prepare("INSERT INTO team_members (team_id, user_id, status) VALUES (?, ?, 'Invited')");
            $stmt->bind_param("ii", $team_id, $target_user);
            if ($stmt->execute()) {
                echo "<script>alert('Invite sent!'); window.history.back();</script>";
            } else {
                echo "<script>alert('User already in team or invited.'); window.history.back();</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Unauthorized: You are not the team leader.'); window.history.back();</script>";
        }
        $check->close();
    } elseif ($action === 'accept' || $action === 'reject') {
        $target_user = intval($_POST['target_user']);
        $check = $conn->prepare("SELECT id FROM teams WHERE id = ? AND leader_id = ?");
        $check->bind_param("ii", $team_id, $user_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            if ($action === 'accept') {
                $stmt = $conn->prepare("UPDATE team_members SET status = 'Accepted' WHERE team_id = ? AND user_id = ?");
            } else {
                $stmt = $conn->prepare("DELETE FROM team_members WHERE team_id = ? AND user_id = ?");
            }
            $stmt->bind_param("ii", $team_id, $target_user);
            $stmt->execute();
            $stmt->close();
            header("Location: ../teams/team_details.php?id=$team_id");
            exit();
        }
        $check->close();
    } elseif ($action === 'accept_invite' || $action === 'decline_invite') {
        if ($action === 'accept_invite') {
            $stmt = $conn->prepare("UPDATE team_members SET status = 'Accepted' WHERE team_id = ? AND user_id = ? AND status = 'Invited'");
        } else {
            $stmt = $conn->prepare("DELETE FROM team_members WHERE team_id = ? AND user_id = ? AND status = 'Invited'");
        }
        $stmt->bind_param("ii", $team_id, $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: ../teams/teams.php");
        exit();
    } elseif ($action === 'update_team') {
        $team_name = trim($_POST['team_name']);
        if (!empty($team_name)) {
            $stmt = $conn->prepare("UPDATE teams SET name = ? WHERE id = ? AND leader_id = ?");
            $stmt->bind_param("sii", $team_name, $team_id, $user_id);
            if ($stmt->execute()) {
                echo "<script>alert('Team updated!'); window.location.href='../teams/team_details.php?id=$team_id';</script>";
            } else {
                echo "<script>alert('Error updating.'); window.history.back();</script>";
            }
            $stmt->close();
        }
    } elseif ($action === 'delete_team') {
        $stmt = $conn->prepare("DELETE FROM teams WHERE id = ? AND leader_id = ?");
        $stmt->bind_param("ii", $team_id, $user_id);
        if ($stmt->execute()) {
            echo "<script>alert('Team deleted completely.'); window.location.href='../teams/teams.php';</script>";
        } else {
            echo "<script>alert('Error deleting team.'); window.history.back();</script>";
        }
        $stmt->close();
    }
}
$conn->close();
?>
