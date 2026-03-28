<?php require_once 'csrf.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header("Location: login_view.php");
    exit();
}
require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name, middle_name, last_name, email, phone, city, bio, skills, github, linkedin FROM users WHERE id = ?");
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

<?php
$github_username = '';
if (!empty($user['github'])) {
    $parsed_url = parse_url($user['github']);
    if (isset($parsed_url['path'])) {
        $path_parts = explode('/', trim($parsed_url['path'], '/'));
        $github_username = $path_parts[0] ?? '';
    }
}
?>

<div class="profile-container">
    <div class="card">
        <h2>Update Profile</h2>
        <form action="update_profile.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">

            <div class="form-group"><label>First Name</label><input type="text" name="first" value="<?= htmlspecialchars($user['first_name']??'') ?>" required></div>
            <div class="form-group"><label>Middle Name</label><input type="text" name="middle" value="<?= htmlspecialchars($user['middle_name']??'') ?>"></div>
            <div class="form-group"><label>Last Name</label><input type="text" name="last" value="<?= htmlspecialchars($user['last_name']??'') ?>" required></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($user['phone']??'') ?>"></div>
            <div class="form-group"><label>City</label><input type="text" name="city" value="<?= htmlspecialchars($user['city']??'') ?>"></div>
            
            <h3 style="margin-top:20px; color:#c4b5fd;">Developer Info</h3>
            <div class="form-group"><label>Skills (comma separated)</label><input type="text" name="skills" value="<?= htmlspecialchars($user['skills']??'') ?>" placeholder="e.g. React, Python, UI/UX"></div>
            <div class="form-group"><label>GitHub URL</label><input type="text" name="github" value="<?= htmlspecialchars($user['github']??'') ?>" placeholder="https://github.com/yourusername"></div>
            <div class="form-group"><label>LinkedIn URL</label><input type="text" name="linkedin" value="<?= htmlspecialchars($user['linkedin']??'') ?>" placeholder="https://linkedin.com/in/yourusername"></div>
            <div class="form-group"><label>Bio</label><textarea name="bio" rows="4" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #475569; background: #1e293b; color: white;" placeholder="Tell us about yourself..."><?= htmlspecialchars($user['bio']??'') ?></textarea></div>

            <button type="submit" class="btn-save" style="margin-top: 15px;">Save Changes</button>
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

    <?php if ($github_username): ?>
    <div class="card" id="github-card">
        <h2>GitHub Activity</h2>
        <div id="github-spinner" style="color: #cbd5e1;">Loading GitHub data...</div>
        <div id="github-content" style="display:none;">
            <div style="display:flex; align-items:center; gap:15px; margin-bottom: 20px;">
                <img id="gh-avatar" src="" style="width: 60px; height: 60px; border-radius: 50%; border: 2px solid #8b5cf6;">
                <div>
                    <h3 id="gh-name" style="margin:0; color:#fff;"></h3>
                    <p style="margin:5px 0 0 0; font-size: 0.9em; color:#cbd5e1;"><span id="gh-repos">0</span> Repositories • <span id="gh-followers">0</span> Followers</p>
                </div>
            </div>
            <h4 style="color:#c4b5fd; margin-bottom: 10px;">Recent Repos</h4>
            <div id="gh-repos-list"></div>
        </div>
    </div>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        fetch('https://api.github.com/users/<?= htmlspecialchars($github_username) ?>')
            .then(res => res.json())
            .then(data => {
                if(data.message && data.message === "Not Found") {
                    document.getElementById('github-spinner').innerText = "GitHub user not found.";
                    return;
                }
                document.getElementById('github-spinner').style.display = 'none';
                document.getElementById('github-content').style.display = 'block';
                document.getElementById('gh-avatar').src = data.avatar_url;
                document.getElementById('gh-name').innerText = data.name || data.login;
                document.getElementById('gh-repos').innerText = data.public_repos;
                document.getElementById('gh-followers').innerText = data.followers;
                
                // Fetch repos
                fetch('https://api.github.com/users/<?= htmlspecialchars($github_username) ?>/repos?sort=updated&per_page=3')
                    .then(r => r.json())
                    .then(repos => {
                        let html = '';
                        if(repos.length > 0) {
                            repos.forEach(repo => {
                                html += `<div class="app-item" style="border-left-color: #ec4899; margin-bottom:10px;">
                                            <h4 style="margin:0 0 5px 0;"><a href="${repo.html_url}" target="_blank" style="color:#ec4899; text-decoration:none;">${repo.name}</a></h4>
                                            <p style="margin:0; font-size:0.85em; color:#cbd5e1;">${repo.description ? repo.description.substring(0, 60)+'...' : 'No description'}</p>
                                            ${repo.language ? `<span style="display:inline-block; margin-top:5px; font-size:0.8em; color:#8b5cf6; font-weight:bold;">${repo.language}</span>` : ''}
                                         </div>`;
                            });
                        } else {
                            html = '<p>No public repos.</p>';
                        }
                        document.getElementById('gh-repos-list').innerHTML = html;
                    });
            })
            .catch(err => {
                document.getElementById('github-spinner').innerText = "Failed to load GitHub data.";
            });
    });
    </script>
    <?php endif; ?>
</div>
</body>
</html>
