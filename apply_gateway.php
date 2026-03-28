<?php require_once 'csrf.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in first.'); window.location.href='login_view.php';</script>";
    exit();
}
require_once 'db_connect.php';
$hack_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get hackathon details & type
$stmt = $conn->prepare("SELECT title, application_type FROM hackathons WHERE id = ?");
$stmt->bind_param("i", $hack_id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows===0) die("Hackathon not found.");
$hackathon = $res->fetch_assoc();
$stmt->close();

// Fetch user's led teams (any team can apply)
$teams_stmt = $conn->prepare("SELECT id, name FROM teams WHERE leader_id = ?");
$teams_stmt->bind_param("i", $user_id);
$teams_stmt->execute();
$led_teams = $teams_stmt->get_result();
$teams_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Apply to <?=$hackathon['title']?></title>
<link rel="stylesheet" href="styles.css">
<style>
body { margin:0; font-family:'Inter', sans-serif; background: #0a0e27; color: white; }
.container { max-width: 800px; margin: 40px auto; padding: 20px; text-align:center;}
.card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 20px; padding: 30px; margin-bottom: 20px;}
.btn { background: linear-gradient(135deg, #8b5cf6, #ec4899); color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size:1rem; text-decoration:none; display:inline-block; margin: 10px;}
</style>
</head>
<body>
<nav>
    <div class="nav-container">
        <a href="index.php" class="nav-brand"><img src="logo.png" alt="Logo"><span class="nav-brand-text">DevSprint</span></a>
        <ul class="nav-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="hackathons.php">Hackathons</a></li>
            <li><a href="teams.php">My Teams</a></li>
            <li><a href="profile.php">My Profile</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <h2>Apply: <?=htmlspecialchars($hackathon['title'])?></h2>
    
    <?php if($hackathon['application_type'] === 'Individual' || $hackathon['application_type'] === 'Both'): ?>
    <div class="card">
        <h3>Apply Individually</h3>
        <p>Participate on your own. You will still be eligible for standard prizes.</p>
        <form action="apply.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">

            <input type="hidden" name="hackathon_id" value="<?=$hack_id?>">
            <button class="btn" type="submit">Apply as Individual</button>
        </form>
    </div>
    <?php endif; ?>

    <?php if($hackathon['application_type'] === 'Team' || $hackathon['application_type'] === 'Both'): ?>
    <div class="card">
        <h3>Apply With a Team</h3>
        <p>Apply to this hackathon with a team you lead.</p>
        
        <?php if($led_teams && $led_teams->num_rows > 0): ?>
            <form action="apply.php" method="POST" style="margin-bottom:20px; margin-top:15px; background:rgba(0,0,0,0.2); padding:15px; border-radius:10px;">
    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">

                <input type="hidden" name="hackathon_id" value="<?=$hack_id?>">
                <select name="team_id" required style="padding:10px; border-radius:5px; border:1px solid #475569; background:#1e293b; color:white; width:100%; margin-bottom:10px;">
                    <option value="">-- Select Team to Apply --</option>
                    <?php while($t = $led_teams->fetch_assoc()): ?>
                        <option value="<?=$t['id']?>"><?=htmlspecialchars($t['name'])?></option>
                    <?php endwhile; ?>
                </select>
                <button class="btn" type="submit" style="width:100%; margin:0;">Submit Team Application</button>
            </form>
        <?php else: ?>
            <p style="color:#f59e0b; font-size:0.9em; margin-bottom:20px;">You must create and lead a team before you can apply as a team.</p>
        <?php endif; ?>

        <p>Need a team?</p>
        <a href="teams.php" class="btn">Manage & Create Teams</a>
        <a href="matchmaking.php" class="btn">Find Teammates</a>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
