<?php
session_start();
require_once 'db_connect.php';

// Fetch hackathons
$result = $conn->query("SELECT * FROM hackathons ORDER BY date_start ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Hackathons | DevSprint</title>
<link rel="stylesheet" href="styles.css">
<style>
body { background: #0a0e27; color: white; margin: 0; font-family: 'Inter', sans-serif; }
.hackathons-container { max-width: 1000px; margin: 40px auto; padding: 20px; }
.hack-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(139,92,246,0.2); border-radius: 12px; padding: 30px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; transition: transform 0.3s; }
.hack-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(139,92,246,0.2); }
.hack-info h3 { margin: 0 0 12px 0; color: #fff; font-size: 1.5rem; }
.hack-info p { margin: 6px 0; color: #cbd5e1; font-size: 1rem; line-height: 1.5; }
.btn-primary { background: linear-gradient(135deg, #8b5cf6, #ec4899); color: white; padding: 12px 24px; border-radius: 30px; text-decoration: none; font-weight: bold; border: none; font-size: 1rem; cursor: pointer; transition: transform 0.3s; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(139,92,246,0.4); }
.page-title { text-align: center; margin-bottom: 40px; color: #fff; font-size: 2.5rem; background: linear-gradient(135deg, #ffffff, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
@media (max-width: 768px) { .hack-card { flex-direction: column; align-items: flex-start; gap: 20px; } }
</style>
</head>
<body>

<nav>
    <div class="nav-container">
        <a href="index.php" class="nav-brand"><img src="logo.png" alt="Logo"><span class="nav-brand-text">DevSprint</span></a>
        <ul class="nav-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="hackathons.php" class="active">Hackathons</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="profile.php">My Profile</a></li>
                <li><a href="logout.php" class="nav-btn" style="background: #ef4444;">Logout</a></li>
            <?php else: ?>
                <li><a href="login.html" class="nav-btn">Get Started</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="hackathons-container">
    <h2 class="page-title">Available Hackathons</h2>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="hack-card">
                <div class="hack-info">
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p><strong>📅 Date:</strong> <?= date('M d, Y', strtotime($row['date_start'])) ?> - <?= date('M d, Y', strtotime($row['date_end'])) ?></p>
                    <p><strong>📍 Location:</strong> <?= htmlspecialchars($row['location']) ?></p>
                    <p><strong>💰 Prize Pool:</strong> <?= htmlspecialchars($row['prize_pool']) ?></p>
                    <p style="margin-top: 15px; color: #94a3b8;"><?= htmlspecialchars($row['description']) ?></p>
                </div>
                <div class="hack-actions">
                    <form action="apply.php" method="POST">
                        <input type="hidden" name="hackathon_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn btn-primary">Apply Now</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align: center; font-size: 1.2rem; color: #cbd5e1;">More hackathons loading soon. Keep building!</p>
    <?php endif; ?>
</div>

</body>
</html>
<?php $conn->close(); ?>
