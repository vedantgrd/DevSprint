<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.html");
    exit();
}
require_once 'db_connect.php';
$app_id = isset($_GET['app_id']) ? intval($_GET['app_id']) : 0;

$app_stmt = $conn->prepare("
    SELECT a.*, t.name as team_name, h.title as hackathon, t.id as team_id 
    FROM applications a 
    JOIN teams t ON a.team_id = t.id 
    JOIN hackathons h ON a.hackathon_id = h.id 
    WHERE a.id = ?
");
$app_stmt->bind_param("i", $app_id);
$app_stmt->execute();
$application = $app_stmt->get_result()->fetch_assoc();
$app_stmt->close();

if (!$application) {
    die("Application not found.");
}

$members_stmt = $conn->prepare("
    SELECT u.first_name, u.last_name, u.email, u.skills, u.bio, u.github, u.linkedin, tm.status 
    FROM team_members tm 
    JOIN users u ON tm.user_id = u.id 
    WHERE tm.team_id = ?
");
$members_stmt->bind_param("i", $application['team_id']);
$members_stmt->execute();
$members = $members_stmt->get_result();
$members_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Review Team | DevSprint Admin</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
.top-bar { background: #8b5cf6; color: white; padding: 15px 20px; display: flex; justify-content: space-between; }
.container { width: 90%; max-width: 1000px; margin: 20px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
h2 { margin-top: 0; color: #333; border-bottom: 2px solid #8b5cf6; padding-bottom: 10px; }
.card { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; background: #fafafa; }
.card h4 { margin: 0 0 10px 0; color: #8b5cf6; }
.card p { margin: 5px 0; font-size: 0.95em; color: #555; }
.btn { padding: 10px 15px; border: none; cursor: pointer; text-decoration: none; display: inline-block; border-radius: 5px; font-weight: bold; }
.btn-primary { background: #8b5cf6; color: white; }
.btn-success { background: #10b981; color: white; }
.btn-danger { background: #ef4444; color: white; }
.links a { color: #ec4899; text-decoration: none; margin-right: 15px; font-weight: bold; }
.badge { padding: 3px 8px; border-radius: 10px; font-size: 0.8em; background: #10b981; color: white; }
.badge.Pending { background: #f59e0b; }
.actions { margin-top: 30px; text-align: center; border-top: 1px solid #ddd; padding-top: 20px; }
</style>
</head>
<body>
<div class="top-bar">
    <h2>DevSprint Admin Area</h2>
    <a href="admin_dashboard.php" style="color:white; text-decoration:none; margin-top:5px;">&larr; Back to Dashboard</a>
</div>
<div class="container">
    <h2>Reviewing Application for <?= htmlspecialchars($application['hackathon']) ?></h2>
    <p><strong>Team Name:</strong> <?= htmlspecialchars($application['team_name']) ?></p>
    <p><strong>Current Status:</strong> <span style="font-weight:bold; color:#f59e0b;"><?= htmlspecialchars($application['status']) ?></span></p>

    <h3 style="margin-top:30px; color:#333;">Team Roster</h3>
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
        <?php while($m = $members->fetch_assoc()): ?>
            <div class="card">
                <h4><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?> <span class="badge <?= $m['status'] ?>"><?= $m['status'] ?></span></h4>
                <p><strong>Email:</strong> <?= htmlspecialchars($m['email']) ?></p>
                <p><strong>Skills:</strong> <?= htmlspecialchars($m['skills'] ?: 'N/A') ?></p>
                <p><strong>Bio:</strong> <?= htmlspecialchars($m['bio'] ?: 'N/A') ?></p>
                <div class="links" style="margin-top:10px;">
                    <?php if($m['github']): ?><a href="<?= htmlspecialchars($m['github']) ?>" target="_blank">GitHub</a><?php endif; ?>
                    <?php if($m['linkedin']): ?><a href="<?= htmlspecialchars($m['linkedin']) ?>" target="_blank">LinkedIn</a><?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="actions">
        <h3 style="margin-bottom:15px;">Final Administrative Action</h3>
        <p style="margin-bottom:20px; color:#666;">Approving or rejecting this application updates the hackathon status for everyone on this team.</p>
        <form action="admin_update_application.php" method="POST" style="display:inline-block; margin:0 10px;">
            <input type="hidden" name="status" value="Accepted">
            <input type="hidden" name="app_id" value="<?= $application['id'] ?>">
            <button type="submit" class="btn btn-success" style="font-size:1.1em; padding:12px 30px;">Accept Entire Team</button>
        </form>
        <form action="admin_update_application.php" method="POST" style="display:inline-block; margin:0 10px;">
            <input type="hidden" name="status" value="Rejected">
            <input type="hidden" name="app_id" value="<?= $application['id'] ?>">
            <button type="submit" class="btn btn-danger" style="font-size:1.1em; padding:12px 30px;">Reject Team Application</button>
        </form>
    </div>
</div>
</body>
</html>
