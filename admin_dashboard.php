<?php require_once 'csrf.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
require_once 'db_connect.php';

// Fetch Hackathons
$hackathons = $conn->query("SELECT * FROM hackathons ORDER BY created_at DESC");

// Fetch Applications
$apps_query = "
    SELECT a.id, u.first_name, u.last_name, u.email, h.title, a.applied_at, a.status, t.name as team_name
    FROM applications a 
    JOIN users u ON a.user_id = u.id 
    JOIN hackathons h ON a.hackathon_id = h.id 
    LEFT JOIN teams t ON a.team_id = t.id
    ORDER BY a.applied_at DESC
";
$applications = $conn->query($apps_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard | DevSprint</title>
<link rel="stylesheet" href="styles.css">
<style>
body { background: #0a0e27; color: white; font-family: 'Inter', sans-serif; margin: 0; }
.admin-header { background: rgba(10,14,39,0.95); padding: 20px; border-bottom: 1px solid rgba(139,92,246,0.2); display: flex; justify-content: space-between; align-items: center; }
.admin-header h2 { margin: 0; background: linear-gradient(135deg, #ffffff, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.container { max-width: 1200px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
.card { background: rgba(255,255,255,0.05); padding: 25px; border-radius: 12px; border: 1px solid rgba(139,92,246,0.2); }
table { width: 100%; border-collapse: collapse; margin-top: 15px; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.1); }
th { color: #8b5cf6; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; color: #cbd5e1; }
.form-group input, .form-group textarea { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #475569; background: #1e293b; color: white; box-sizing: border-box;}
.btn { background: linear-gradient(135deg, #8b5cf6, #ec4899); color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; }
@media(max-width: 900px) { .container { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<div class="admin-header">
    <h2>DevSprint Admin</h2>
    <div>
        <a href="index.php" style="color:#cbd5e1; margin-right:20px; text-decoration:none;">View Site</a>
        <a href="admin_logout.php" class="btn" style="text-decoration: none; padding: 8px 15px;">Logout</a>
    </div>
</div>

<div class="container">
    <!-- Add Hackathon -->
    <div class="card">
        <h3>Add New Hackathon</h3>
        <form action="admin_add_hackathon.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">

            <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
            <div class="form-group"><label>Location</label><input type="text" name="location" required></div>
            <div class="form-group"><label>Start Date</label><input type="date" name="date_start" required></div>
            <div class="form-group"><label>End Date</label><input type="date" name="date_end" required></div>
            <div class="form-group"><label>Prize Pool</label><input type="text" name="prize_pool" required></div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="4" required></textarea></div>
            <div class="form-group">
                <label>Application Type</label>
                <select name="application_type" required>
                    <option value="Both">Both (Individual or Team)</option>
                    <option value="Individual">Individual Only</option>
                    <option value="Team">Team Only</option>
                </select>
            </div>
            <button type="submit" class="btn" style="width: 100%;">Create Hackathon</button>
        </form>
    </div>

    <!-- Right Column: Lists -->
    <div>
        <div class="card" style="margin-bottom: 30px;">
            <h3>Recent Applications</h3>
            <div style="overflow-x:auto;">
            <table>
                <tr><th>User</th><th>Email</th><th>Hackathon</th><th>Date</th><th>Status</th><th>Actions</th></tr>
                <?php if($applications && $applications->num_rows > 0): while($a = $applications->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?><br><small style="color:#cbd5e1;"><?= htmlspecialchars($a['team_name']?'Team: '.$a['team_name']:'Individual') ?></small></td>
                    <td><?= htmlspecialchars($a['email']) ?></td>
                    <td><?= htmlspecialchars($a['title']) ?></td>
                    <td><?= date('M d, Y', strtotime($a['applied_at'])) ?></td>
                    <td>
                        <span style="padding:4px 8px; border-radius:12px; font-size:0.8em; font-weight:bold; background:<?= $a['status']=='Accepted'?'#10b981':($a['status']=='Rejected'?'#ef4444':'#f59e0b') ?>; color:white;">
                            <?= htmlspecialchars($a['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if($a['team_name']): ?>
                            <a href="admin_view_team.php?app_id=<?= $a['id'] ?>" class="btn">View Team Details</a>
                        <?php else: ?>
                            <form action="admin_update_application.php" method="POST" style="display:inline;">
    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">

                                <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
                                <input type="hidden" name="status" value="Accepted">
                                <button type="submit" style="background:#10b981; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;" title="Accept">✓</button>
                            </form>
                            <form action="admin_update_application.php" method="POST" style="display:inline;">
    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">

                                <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
                                <input type="hidden" name="status" value="Rejected">
                                <button type="submit" style="background:#ef4444; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;" title="Reject">✕</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="4">No applications yet.</td></tr>
                <?php endif; ?>
            </table>
            </div>
        </div>

        <div class="card">
            <h3>Active Hackathons</h3>
            <div style="overflow-x:auto;">
            <table>
                <tr><th>Title</th><th>Location</th><th>Start Date</th></tr>
                <?php if($hackathons && $hackathons->num_rows > 0): while($h = $hackathons->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($h['title']) ?></td>
                    <td><?= htmlspecialchars($h['location']) ?></td>
                    <td><?= htmlspecialchars($h['date_start']) ?></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="3">No hackathons exist.</td></tr>
                <?php endif; ?>
            </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
