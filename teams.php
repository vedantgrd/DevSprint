<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'db_connect.php';
$user_id = $_SESSION['user_id'];

// Get all hackathons for the create team dropdown
$hackathons = $conn->query("SELECT id, title FROM hackathons");

// Get user's teams
$stmt = $conn->prepare("
    SELECT t.id, t.name, h.title as hackathon, t.leader_id, tm.status 
    FROM team_members tm 
    JOIN teams t ON tm.team_id = t.id 
    JOIN hackathons h ON t.hackathon_id = h.id 
    WHERE tm.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_teams = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Teams | DevSprint</title>
<link rel="stylesheet" href="styles.css">
<style>
body { margin:0; font-family:'Inter', sans-serif; background: #0a0e27; color: white; }
.container { max-width: 1000px; margin: 40px auto; padding: 20px; display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
.card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 20px; padding: 30px; }
.card h2 { color: #fff; margin-bottom: 20px; border-bottom: 2px solid rgba(139, 92, 246, 0.3); padding-bottom: 10px; font-size: 1.5rem; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; color: #cbd5e1; font-weight: 500;}
.form-group input, .form-group select { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #475569; background: #1e293b; color: white; box-sizing:border-box; }
.btn-primary { background: linear-gradient(135deg, #8b5cf6, #ec4899); color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; font-size: 1rem; text-decoration: none; display: inline-block; text-align: center;}
.team-item { background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; margin-bottom: 15px; border-left: 4px solid #ec4899; display: flex; justify-content: space-between; align-items: center;}
.team-item h4 { margin: 0 0 5px 0; color: #fff; font-size: 1.2rem; }
.team-item p { margin: 0; font-size: 0.9em; color: #cbd5e1; }
.status-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.8em; font-weight: bold; background: #10b981; color: white; }
.status-badge.Pending { background: #f59e0b; }
@media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
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
        <h2>Create a Team</h2>
        <form action="create_team.php" method="POST">
            <div class="form-group">
                <label>Team Name</label>
                <input type="text" name="team_name" required>
            </div>
            <div class="form-group">
                <label>Select Hackathon</label>
                <select name="hackathon_id" required>
                    <option value="">-- Choose Hackathon --</option>
                    <?php if($hackathons && $hackathons->num_rows > 0): while($h = $hackathons->fetch_assoc()): ?>
                        <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['title']) ?></option>
                    <?php endwhile; endif; ?>
                </select>
            </div>
            <button type="submit" class="btn-primary">Create Team</button>
        </form>
    </div>

    <div class="card">
        <h2>My Teams</h2>
        <?php if ($my_teams && $my_teams->num_rows > 0): ?>
            <?php while($team = $my_teams->fetch_assoc()): ?>
                <div class="team-item">
                    <div>
                        <h4><?= htmlspecialchars($team['name']) ?> 
                            <?php if($team['leader_id'] == $user_id) echo "<span style='font-size:0.7em; background:#8b5cf6; padding:2px 6px; border-radius:10px; vertical-align:middle;'>Leader</span>"; ?>
                        </h4>
                        <p><?= htmlspecialchars($team['hackathon']) ?></p>
                        <span class="status-badge <?= $team['status'] ?>"><?= $team['status'] ?></span>
                    </div>
                    <div>
                        <a href="team_details.php?id=<?= $team['id'] ?>" class="btn-primary" style="padding: 8px 15px; width: auto; font-size: 0.9em;">View</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color:#cbd5e1;">You are not in any teams yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
