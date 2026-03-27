<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'db_connect.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "SELECT id, first_name, last_name, skills, bio, github, linkedin FROM users WHERE id != ?";
if ($search !== '') {
    $sql .= " AND (skills LIKE ? OR bio LIKE ?)";
    $stmt = $conn->prepare($sql);
    $like = "%$search%";
    $stmt->bind_param("iss", $_SESSION['user_id'], $like, $like);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
}

$stmt->execute();
$users = $stmt->get_result();
$stmt->close();

// Fetch teams led by current user to allow inviting
$my_teams_stmt = $conn->prepare("SELECT id, name FROM teams WHERE leader_id = ?");
$my_teams_stmt->bind_param("i", $_SESSION['user_id']);
$my_teams_stmt->execute();
$my_teams = $my_teams_stmt->get_result();
$my_teams_arr = [];
while($t = $my_teams->fetch_assoc()) $my_teams_arr[] = $t;
$my_teams_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Find Teammates | DevSprint</title>
<link rel="stylesheet" href="styles.css">
<style>
body { margin:0; font-family:'Inter', sans-serif; background: #0a0e27; color: white; }
.container { max-width: 1000px; margin: 40px auto; padding: 20px; }
.search-bar { display:flex; gap:10px; margin-bottom: 30px; }
.search-bar input { flex:1; padding: 15px; border-radius: 10px; border: 1px solid #475569; background: #1e293b; color: white; font-size:1rem; }
.btn-primary { background: linear-gradient(135deg, #8b5cf6, #ec4899); color: white; padding: 15px 30px; border: none; border-radius: 10px; cursor: pointer; font-weight: bold; font-size:1rem;}
.grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
.user-card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 20px; padding: 25px; transition: transform 0.3s; }
.user-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(139, 92, 246, 0.2); }
.user-card h3 { margin: 0 0 10px 0; color: #fff; font-size: 1.4rem; }
.skills { color: #8b5cf6; font-weight: bold; margin-bottom: 15px; font-size: 0.9em; }
.bio { color: #cbd5e1; font-size: 0.95em; line-height: 1.5; margin-bottom: 15px; height: 60px; overflow: hidden; text-overflow: ellipsis; }
.links a { color: #ec4899; text-decoration: none; margin-right: 15px; font-size: 0.9em; font-weight:bold; }
.links a:hover { text-decoration: underline; }
.invite-form { margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; }
.invite-form select { width: 100%; padding: 8px; border-radius: 5px; background: #1e293b; color: white; border: 1px solid #475569; margin-bottom: 10px; }
</style>
</head>
<body>
<nav>
    <div class="nav-container">
        <a href="index.php" class="nav-brand"><img src="logo.png" alt="Logo"><span class="nav-brand-text">DevSprint</span></a>
        <ul class="nav-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="hackathons.php">Hackathons</a></li>
            <li><a href="matchmaking.php" class="active">Find Teammates</a></li>
            <li><a href="teams.php">My Teams</a></li>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="logout.php" class="nav-btn" style="background: #ef4444;">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <h2 style="text-align:center; font-size:2.5rem; margin-bottom: 30px; background: linear-gradient(135deg, #ffffff, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Skill-Based Matchmaking</h2>
    <p style="text-align:center; color:#cbd5e1; margin-bottom:40px;">Find the perfect developers to complete your dream team. Search by skills, framework, or language.</p>

    <form method="GET" class="search-bar">
        <input type="text" name="q" placeholder="E.g. React, Python, UI/UX, Backend..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-primary">Search Engineers</button>
    </form>

    <div class="grid">
        <?php if($users && $users->num_rows > 0): while($u = $users->fetch_assoc()): ?>
            <div class="user-card">
                <h3><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></h3>
                <div class="skills"><?= htmlspecialchars($u['skills'] ?: 'No skills listed') ?></div>
                <div class="bio"><?= htmlspecialchars($u['bio'] ?: 'This user prefers to keep an air of mystery about them.') ?></div>
                <div class="links">
                    <?php if($u['github']): ?><a href="<?= htmlspecialchars($u['github']) ?>" target="_blank">GitHub</a><?php endif; ?>
                    <?php if($u['linkedin']): ?><a href="<?= htmlspecialchars($u['linkedin']) ?>" target="_blank">LinkedIn</a><?php endif; ?>
                </div>

                <?php if(count($my_teams_arr) > 0): ?>
                <div class="invite-form">
                    <form action="team_action.php" method="POST">
                        <input type="hidden" name="action" value="invite">
                        <input type="hidden" name="target_user" value="<?= $u['id'] ?>">
                        <select name="team_id" required>
                            <option value="">-- Invite to Team --</option>
                            <?php foreach($my_teams_arr as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn-primary" style="width:100%; padding: 8px;">Send Invite</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        <?php endwhile; else: ?>
            <p style="text-align:center; grid-column:1/-1; color:#94a3b8; font-size:1.2rem;">No developers found matching your criteria.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
