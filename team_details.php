<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];
$team_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($team_id <= 0) {
    header("Location: teams.php");
    exit();
}

// Get team details
$stmt = $conn->prepare("SELECT * FROM teams WHERE id = ?");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$team = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$team) {
    die("Team not found.");
}

$is_leader = ($team['leader_id'] == $user_id);

// Get members
$members_stmt = $conn->prepare("
    SELECT u.id, u.first_name, u.last_name, u.email, u.skills, tm.status 
    FROM team_members tm 
    JOIN users u ON tm.user_id = u.id 
    WHERE tm.team_id = ?
");
$members_stmt->bind_param("i", $team_id);
$members_stmt->execute();
$members = $members_stmt->get_result();
$members_stmt->close();

// Check if current user is part of the team
$is_member = false;
$user_status = '';
$members_data = [];
while($m = $members->fetch_assoc()) {
    $members_data[] = $m;
    if($m['id'] == $user_id) {
        $is_member = true;
        $user_status = $m['status'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($team['name']) ?> | DevSprint</title>
<link rel="stylesheet" href="styles.css">
<style>
body { margin:0; font-family:'Inter', sans-serif; background: #0a0e27; color: white; }
.container { max-width: 800px; margin: 40px auto; padding: 20px; }
.card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 20px; padding: 30px; }
.card h2 { color: #fff; margin-bottom: 5px; font-size: 2rem; background: linear-gradient(135deg, #ffffff, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.subtitle { color: #cbd5e1; margin-bottom: 25px; border-bottom: 1px solid rgba(139, 92, 246, 0.3); padding-bottom: 15px; }
.member-list { list-style: none; padding: 0; }
.member-item { background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; margin-bottom: 15px; border-left: 4px solid #8b5cf6; display:flex; justify-content:space-between; align-items:center; }
.member-item h4 { margin: 0 0 5px 0; color: #fff; }
.member-item p { margin: 0; font-size: 0.9em; color: #94a3b8; }
.btn { background: linear-gradient(135deg, #8b5cf6, #ec4899); color: white; padding: 8px 15px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; text-decoration:none; font-size: 0.9em; }
.btn-danger { background: #ef4444; }
.badge { padding: 4px 8px; border-radius: 12px; font-size: 0.8em; font-weight: bold; background: #10b981; color: white; }
.badge.Pending { background: #f59e0b; }
</style>
</head>
<body>
<nav>
    <div class="nav-container">
        <a href="index.php" class="nav-brand"><img src="logo.png" alt="Logo"><span class="nav-brand-text">DevSprint</span></a>
        <ul class="nav-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="hackathons.php">Hackathons</a></li>
            <li><a href="matchmaking.php">Find Teammates</a></li>
            <li><a href="teams.php" class="active">My Teams</a></li>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="logout.php" class="nav-btn" style="background: #ef4444;">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <div class="card">
        <a href="teams.php" style="color:#cbd5e1; text-decoration:none; font-size:0.9rem;">&larr; Back to Teams</a>
        <h2 style="margin-top:15px;"><?= htmlspecialchars($team['name']) ?></h2>

        <?php if(!$is_member): ?>
            <div style="text-align:center; margin-bottom: 30px;">
                <form action="team_action.php" method="POST">
                    <input type="hidden" name="action" value="request_join">
                    <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                    <button type="submit" class="btn" style="padding:12px 25px; font-size:1.1rem;">Request to Join Team</button>
                </form>
            </div>
        <?php endif; ?>

        <h3>Team Members</h3>
        <ul class="member-list">
            <?php foreach($members_data as $m): ?>
                <li class="member-item">
                    <div>
                        <h4><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?>
                            <?php if($m['id'] == $team['leader_id']) echo "<span style='font-size:0.7em; background:#ec4899; padding:2px 6px; border-radius:10px; margin-left:5px;'>Leader</span>"; ?>
                        </h4>
                        <p>Skills: <?= htmlspecialchars($m['skills'] ?: 'Not specified') ?></p>
                        <span class="badge <?= $m['status'] ?>"><?= $m['status'] ?></span>
                    </div>
                    <?php if($is_leader && $m['status'] === 'Pending'): ?>
                        <div>
                            <form action="team_action.php" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="accept">
                                <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                                <input type="hidden" name="target_user" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn">Accept</button>
                            </form>
                            <form action="team_action.php" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                                <input type="hidden" name="target_user" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn btn-danger">Reject</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if($is_leader): ?>
        <div style="margin-top:30px; padding-top:20px; border-top:1px solid rgba(139, 92, 246, 0.3);">
            <h3>Team Settings (Leader Only)</h3>
            <form action="team_action.php" method="POST" style="margin-bottom:15px; display:flex; gap:10px;">
                <input type="hidden" name="action" value="update_team">
                <input type="hidden" name="team_id" value="<?=$team['id']?>">
                <input type="text" name="team_name" value="<?=htmlspecialchars($team['name'])?>" required style="padding:10px; border-radius:5px; border:1px solid #475569; background:#1e293b; color:white; flex:1;">
                <button type="submit" class="btn" style="border-radius:5px;">Update Name</button>
            </form>
            <form action="team_action.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this team entirely? This will remove all members and applications tied to it.');">
                <input type="hidden" name="action" value="delete_team">
                <input type="hidden" name="team_id" value="<?=$team['id']?>">
                <button type="submit" class="btn btn-danger" style="border-radius:5px;">Delete Team Entirely</button>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
