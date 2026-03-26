<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name, middle_name, last_name, email, phone, city FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch applications
$app_stmt = $conn->prepare("SELECT h.title, h.date_start, a.status, a.applied_at FROM applications a JOIN hackathons h ON a.hackathon_id = h.id WHERE a.user_id = ? ORDER BY a.applied_at DESC");
$app_stmt->bind_param("i", $user_id);
if ($app_stmt->execute()) {
    $applications = $app_stmt->get_result();
} else {
    $applications = []; // fallback
}
$app_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile | DevSprint</title>
<link rel="stylesheet" href="styles.css">
<style>
body { margin:0; font-family:'Inter', sans-serif; background: #0a0e27; color: white; }
.profile-container { max-width: 1000px; margin: 40px auto; padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
.card { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 20px; padding: 30px; }
.card h2 { color: #fff; margin-bottom: 20px; border-bottom: 2px solid rgba(139, 92, 246, 0.3); padding-bottom: 10px; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; color: #cbd5e1; font-weight: 500;}
.form-group input { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #475569; background: #1e293b; color: white; }
.btn-save { background: linear-gradient(135deg, #8b5cf6, #ec4899); color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; font-size: 1rem; }
.app-item { background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; margin-bottom: 15px; border-left: 4px solid #8b5cf6; }
.app-item h4 { margin: 0 0 5px 0; color: #ec4899; }
.app-item p { margin: 0; font-size: 0.9em; color: #cbd5e1; }
.status { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 0.8em; font-weight: bold; margin-top: 8px; }
.status.Pending { background: #f59e0b; color: #fff; }
.status.Accepted { background: #10b981; color: #fff; }
.status.Rejected { background: #ef4444; color: #fff; }

@media (max-width: 768px) {
    .profile-container { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<nav>
    <div class="nav-container">
        <a href="index.php" class="nav-brand"><img src="logo.png" alt="Logo"><span class="nav-brand-text">DevSprint</span></a>
        <ul class="nav-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="hackathons.php">Hackathons</a></li>
            <li><a href="profile.php" class="active">My Profile</a></li>
            <li><a href="logout.php" class="nav-btn" style="background: #ef4444;">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="profile-container">
    <div class="card">
        <h2>Update Profile</h2>
        <form action="update_profile.php" method="POST">
            <div class="form-group"><label>First Name</label><input type="text" name="first" value="<?= htmlspecialchars($user['first_name']) ?>" required></div>
            <div class="form-group"><label>Middle Name</label><input type="text" name="middle" value="<?= htmlspecialchars($user['middle_name']) ?>"></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="last" value="<?= htmlspecialchars($user['last_name']) ?>" required></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>"></div>
            <div class="form-group"><label>City</label><input type="text" name="city" value="<?= htmlspecialchars($user['city']) ?>"></div>
            <button type="submit" class="btn-save">Save Changes</button>
        </form>
    </div>

    <div class="card">
        <h2>My Applications</h2>
        <?php if ($applications && $applications->num_rows > 0): ?>
            <?php while($app = $applications->fetch_assoc()): ?>
                <div class="app-item">
                    <h4><?= htmlspecialchars($app['title']) ?></h4>
                    <p>Date: <?= htmlspecialchars($app['date_start']) ?></p>
                    <p>Applied on: <?= date('M d, Y', strtotime($app['applied_at'])) ?></p>
                    <span class="status <?= htmlspecialchars($app['status']) ?>"><?= htmlspecialchars($app['status']) ?></span>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You haven't applied to any hackathons yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
