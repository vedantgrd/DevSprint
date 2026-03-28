<?php
require_once 'csrf.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once 'db_connect.php';
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch messages for a team
    $team_id = isset($_GET['team_id']) ? intval($_GET['team_id']) : 0;
    
    // Verify user is member of team
    $check = $conn->prepare("SELECT tm.status, t.leader_id FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE tm.team_id = ? AND (tm.user_id = ? OR t.leader_id = ?) AND tm.status = 'Accepted'");
    $check->bind_param("iii", $team_id, $user_id, $user_id);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        $check->close();
        echo json_encode([]);
        exit();
    }
    $check->close();
    
    $stmt = $conn->prepare("
        SELECT m.id, m.message, m.created_at, u.first_name, u.last_name, m.sender_id 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE m.team_id = ? 
        ORDER BY m.created_at ASC
        LIMIT 100
    ");
    $stmt->bind_param("i", $team_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $messages = [];
    while($row = $res->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();
    echo json_encode($messages);
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Send a message
    // CSRF check already happens in csrf.php
    $team_id = isset($_POST['team_id']) ? intval($_POST['team_id']) : 0;
    $message = trim($_POST['message'] ?? '');
    
    if (empty($message) || $team_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data']);
        exit();
    }
    
    // Verify member
    $check = $conn->prepare("SELECT tm.status, t.leader_id FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE tm.team_id = ? AND (tm.user_id = ? OR t.leader_id = ?) AND tm.status = 'Accepted'");
    $check->bind_param("iii", $team_id, $user_id, $user_id);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        $check->close();
        http_response_code(403);
        echo json_encode(['error' => 'Not a team member']);
        exit();
    }
    $check->close();
    
    // Insert
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, team_id, message) VALUES (?, NULL, ?, ?)");
    $stmt->bind_param("iis", $user_id, $team_id, $message);
    if($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to send']);
    }
    $stmt->close();
}
?>
