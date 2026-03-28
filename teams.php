<?php require_once 'csrf.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header("Location: login_view.php");
    exit();
}
require_once 'db_connect.php';
$user_id = $_SESSION['user_id'];

// Get all hackathons for the create team dropdown (only Both or Team allowed)
$hackathons_res = $conn->query("SELECT id, title FROM hackathons WHERE application_type IN ('Both', 'Team')");
$hackathons_list = [];
if ($hackathons_res) {
    while($row = $hackathons_res->fetch_assoc()) {
        $hackathons_list[] = $row;
    }
}

// Get user's teams
$stmt = $conn->prepare("
    SELECT t.id, t.name, t.leader_id, tm.status 
    FROM team_members tm 
    JOIN teams t ON tm.team_id = t.id 
    WHERE tm.user_id = ? AND tm.status != 'Invited'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_teams = $stmt->get_result();
$stmt->close();

// Fetch user's invitations
$invites_stmt = $conn->prepare("
    SELECT t.id as team_id, t.name
    FROM team_members tm 
    JOIN teams t ON tm.team_id = t.id 
    WHERE tm.user_id = ? AND tm.status = 'Invited'
");
$invites_stmt->bind_param("i", $user_id);
$invites_stmt->execute();
$invitations = $invites_stmt->get_result();
$invites_stmt->close();

// Get teams to explore
$explore_stmt = $conn->prepare("
    SELECT t.id, t.name, u.first_name, u.last_name
    FROM teams t
    JOIN users u ON t.leader_id = u.id
    WHERE t.id NOT IN (
        SELECT team_id FROM team_members WHERE user_id = ?
    )
    ORDER BY t.created_at DESC LIMIT 15
");
$explore_stmt->bind_param("i", $user_id);
$explore_stmt->execute();
$explore_teams = $explore_stmt->get_result();
$explore_stmt->close();
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
    <div>
    <div class="card">
        <h2>Create a Squad</h2>
        <form action="create_team.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">

            <div class="form-group">
                <label>Team Name</label>
                <input type="text" name="team_name" required placeholder="e.g. Codebreakers">
            </div>
            <button type="submit" class="btn-primary">Create Team</button>
        </form>
    </div>

    <div class="card" style="margin-top:20px;">
        <h2>Explore Teams</h2>
        <p style="color:#cbd5e1; font-size:0.9em; margin-bottom:15px;">Looking for a squad? Request to join an existing team.</p>
        <?php if($explore_teams && $explore_teams->num_rows > 0): ?>
            <div style="max-height: 400px; overflow-y:auto; padding-right:10px;">
            <?php while($et = $explore_teams->fetch_assoc()): ?>
                <div style="background:rgba(0,0,0,0.2); padding:12px; border-radius:8px; margin-bottom:10px; border-left:3px solid #8b5cf6;">
                    <h4 style="margin:0 0 5px 0; color:#fff; font-size: 1.1em;"><?= htmlspecialchars($et['name']) ?></h4>
                    <p style="margin:0 0 8px 0; font-size:0.85em; color:#cbd5e1;">Leader: <?= htmlspecialchars($et['first_name'] . ' ' . $et['last_name']) ?></p>
                    <form action="team_action.php" method="POST" style="margin:0;">
    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">

                        <input type="hidden" name="action" value="request_join">
                        <input type="hidden" name="team_id" value="<?= $et['id'] ?>">
                        <button type="submit" class="btn-primary" style="padding: 6px 12px; font-size: 0.85em; width:100%;">Request to Join</button>
                    </form>
                </div>
            <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="color:#cbd5e1; font-size:0.9em;">No teams available to join right now.</p>
        <?php endif; ?>
    </div>
    </div>

    <div class="card">
        <?php if($invitations && $invitations->num_rows > 0): ?>
        <h2 style="color: #f59e0b;">✨ Pending Invitations</h2>
        <div style="margin-bottom: 30px;">
            <?php while($inv = $invitations->fetch_assoc()): ?>
                <div class="team-item" style="border-left-color:#f59e0b;">
                    <div>
                        <h4><?= htmlspecialchars($inv['name']) ?></h4>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <form action="team_action.php" method="POST" style="margin:0;">
    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">

                            <input type="hidden" name="action" value="accept_invite">
                            <input type="hidden" name="team_id" value="<?= $inv['team_id'] ?>">
                            <button type="submit" class="btn-primary" style="padding: 6px 15px; font-size: 0.9em; width:auto; margin:0;">Accept</button>
                        </form>
                        <form action="team_action.php" method="POST" style="margin:0;">
    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">

                            <input type="hidden" name="action" value="decline_invite">
                            <input type="hidden" name="team_id" value="<?= $inv['team_id'] ?>">
                            <button type="submit" class="btn-primary" style="background:#ef4444; padding: 6px 15px; font-size: 0.9em; width:auto; margin:0;">Decline</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <hr style="border-color: rgba(139, 92, 246, 0.3); margin-bottom: 25px;">
        <?php endif; ?>

        <h2>My Teams</h2>
        <?php if ($my_teams && $my_teams->num_rows > 0): ?>
            <?php while($team = $my_teams->fetch_assoc()): ?>
                <div class="team-item" style="display:flex; flex-direction:column; align-items:stretch; gap:10px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <h4><?= htmlspecialchars($team['name']) ?> 
                                <?php if($team['leader_id'] == $user_id) echo "<span style='font-size:0.7em; background:#8b5cf6; padding:2px 6px; border-radius:10px; vertical-align:middle;'>Leader</span>"; ?>
                            </h4>
                            <span class="status-badge <?= $team['status'] ?>"><?= $team['status'] ?></span>
                        </div>
                        <div>
                            <a href="team_details.php?id=<?= $team['id'] ?>" class="btn-primary" style="padding: 8px 15px; width: auto; font-size: 0.9em;">View</a>
                        </div>
                    </div>
                    <?php if($team['leader_id'] == $user_id && !empty($hackathons_list)): ?>
                        <div style="background: rgba(255,255,255,0.05); padding:10px; border-radius:8px; border-top: 1px solid rgba(255,255,255,0.1); margin-top:5px;">
                            <form action="apply.php" method="POST" style="margin:0; display:flex; gap:10px;">
    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">

                                <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                                <select name="hackathon_id" required style="padding:8px; border-radius:5px; background:#1e293b; color:white; border:1px solid #475569; flex:1;">
                                    <option value="">-- Apply this team to a Hackathon --</option>
                                    <?php foreach($hackathons_list as $h): ?>
                                        <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn-primary" style="padding: 8px 15px; width: auto; font-size: 0.9em;">Apply Now</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color:#cbd5e1;">You are not in any teams yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
