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
    $applications = [];
}
$app_stmt->close();

// Parse GitHub username
$github_username = '';
if (!empty($user['github'])) {
    $parsed_url = parse_url($user['github']);
    if (isset($parsed_url['path'])) {
        $path_parts = explode('/', trim($parsed_url['path'], '/'));
        $github_username = $path_parts[0] ?? '';
    }
}

$display_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$skills_arr = array_filter(array_map('trim', explode(',', $user['skills'] ?? '')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile | DevSprint · Commander Hub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
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
<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>
<canvas id="cosmos-canvas"></canvas>
<div class="nebula-overlay"></div>
<div class="scanlines"></div>

<!-- NAV -->
<nav id="main-nav">
    <div class="nav-container">
        <a href="index.php" class="nav-brand">
            <div class="nav-logo">
                <svg viewBox="0 0 40 40" fill="none"><circle cx="20" cy="20" r="8" fill="none" stroke="#00e5ff" stroke-width="1.5"/><ellipse cx="20" cy="20" rx="18" ry="7" fill="none" stroke="#7c4dff" stroke-width="1" transform="rotate(30 20 20)"/><ellipse cx="20" cy="20" rx="18" ry="7" fill="none" stroke="#00e5ff" stroke-width="1" opacity="0.4" transform="rotate(-30 20 20)"/><circle cx="20" cy="20" r="3" fill="#00e5ff"/></svg>
            </div>
            <span class="nav-brand-text">DevSprint</span>
        </a>
        <button class="nav-toggle" id="nav-toggle">☰</button>
        <ul class="nav-menu" id="nav-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="hackathons.php">Hackathons</a></li>
            <li><a href="matchmaking.php">Find Teammates</a></li>
            <li><a href="teams.php">My Teams</a></li>
            <li><a href="profile.php" class="active">My Profile</a></li>
            <li><a href="logout.php" class="nav-btn nav-btn-danger">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="page-wrapper">
    <div class="profile-layout">

        <!-- Commander Banner -->
        <div class="commander-banner reveal">
            <div class="commander-avatar">
                <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="commander-info">
                <div class="commander-name"><?= htmlspecialchars($display_name ?: 'Commander') ?></div>
                <div class="commander-email">📡 <?= htmlspecialchars($user['email'] ?? '') ?></div>
                <?php if (!empty($skills_arr)): ?>
                <div class="skills-cloud">
                    <?php foreach($skills_arr as $sk): ?>
                        <span class="skill-tag"><?= htmlspecialchars($sk) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="commander-actions">
                <?php if($user['github']): ?>
                <a href="<?= htmlspecialchars($user['github']) ?>" target="_blank" class="btn btn-ghost btn-sm">
                    <span>GitHub ↗</span>
                </a>
                <?php endif; ?>
                <?php if($user['linkedin']): ?>
                <a href="<?= htmlspecialchars($user['linkedin']) ?>" target="_blank" class="btn btn-ghost btn-sm">
                    <span>LinkedIn ↗</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Update Profile -->
        <div class="p-card reveal d1">
            <div class="p-card-title"><span class="card-icon">🛸</span> Update Commander Profile</div>
            <form action="update_profile.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                <div class="form-group"><label>First Name</label><input type="text" name="first" value="<?= htmlspecialchars($user['first_name']??'') ?>" required></div>
                <div class="form-group"><label>Middle Name</label><input type="text" name="middle" value="<?= htmlspecialchars($user['middle_name']??'') ?>"></div>
                <div class="form-group"><label>Last Name</label><input type="text" name="last" value="<?= htmlspecialchars($user['last_name']??'') ?>" required></div>
                <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($user['phone']??'') ?>"></div>
                <div class="form-group"><label>City</label><input type="text" name="city" value="<?= htmlspecialchars($user['city']??'') ?>"></div>

                <div class="p-card-title" style="margin-top:1.5rem;font-size:0.85rem;"><span class="card-icon">🔭</span> Developer Info</div>
                <div class="form-group"><label>Skills (comma separated)</label><input type="text" name="skills" value="<?= htmlspecialchars($user['skills']??'') ?>" placeholder="e.g. React, Python, UI/UX"></div>
                <div class="form-group"><label>GitHub URL</label><input type="text" name="github" value="<?= htmlspecialchars($user['github']??'') ?>" placeholder="https://github.com/username"></div>
                <div class="form-group"><label>LinkedIn URL</label><input type="text" name="linkedin" value="<?= htmlspecialchars($user['linkedin']??'') ?>" placeholder="https://linkedin.com/in/username"></div>
                <div class="form-group"><label>Bio</label><textarea name="bio" rows="4" placeholder="Tell the universe about yourself..."><?= htmlspecialchars($user['bio']??'') ?></textarea></div>

                <button type="submit" class="save-btn"><span>💾 SAVE CHANGES</span></button>
            </form>
        </div>

        <!-- My Applications -->
        <div class="p-card reveal d2">
            <div class="p-card-title"><span class="card-icon">🏆</span> My Applications</div>
            <?php if ($applications && $applications->num_rows > 0): ?>
                <?php while($app = $applications->fetch_assoc()): ?>
                    <div class="app-item">
                        <h4><?= htmlspecialchars($app['title']) ?></h4>
                        <p>📅 Starts: <?= htmlspecialchars($app['date_start']) ?></p>
                        <p>⏰ Applied: <?= date('M d, Y', strtotime($app['applied_at'])) ?></p>
                        <?php
                        $st = htmlspecialchars($app['status']);
                        $badgeClass = $st === 'Accepted' ? 'badge-accepted' : ($st === 'Rejected' ? 'badge-rejected' : 'badge-pending');
                        ?>
                        <span class="badge <?= $badgeClass ?>" style="margin-top:0.5rem;"><?= $st ?></span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align:center;padding:2rem 0;">
                    <div style="font-size:2.5rem;opacity:0.4;margin-bottom:1rem;">🚀</div>
                    <p style="color:var(--text-dim);font-size:0.9rem;">You haven't applied to any missions yet.</p>
                    <a href="hackathons.php" class="btn btn-primary" style="margin-top:1rem;font-size:0.82rem;padding:0.7rem 1.5rem;"><span>Explore Hackathons</span></a>
                </div>
            <?php endif; ?>
        </div>

        <!-- GitHub Activity -->
        <?php if ($github_username): ?>
        <div class="p-card reveal d3" id="github-card" style="grid-column:1/-1;">
            <div class="p-card-title"><span class="card-icon">🐙</span> GitHub Activity — <?= htmlspecialchars($github_username) ?></div>
            <div id="github-spinner" style="color:var(--text-dim);font-family:'JetBrains Mono',monospace;font-size:0.85rem;">
                ⏳ Syncing with GitHub orbital data...
            </div>
            <div id="github-content" style="display:none;">
                <div class="gh-meta">
                    <img id="gh-avatar" src="" class="gh-avatar" alt="GitHub Avatar">
                    <div>
                        <div id="gh-name" class="gh-name"></div>
                        <div id="gh-stats" class="gh-stats"></div>
                    </div>
                </div>
                <div style="font-family:'Orbitron',monospace;font-size:0.8rem;color:var(--text-dim);margin-bottom:1rem;letter-spacing:0.1em;text-transform:uppercase;">Recent Repositories</div>
                <div id="gh-repos-list" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:0.75rem;"></div>
            </div>
        </div>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetch('https://api.github.com/users/<?= htmlspecialchars($github_username) ?>')
                .then(res => res.json())
                .then(data => {
                    if(data.message && data.message === "Not Found") {
                        document.getElementById('github-spinner').innerText = "⚠ GitHub user not found.";
                        return;
                    }
                    document.getElementById('github-spinner').style.display = 'none';
                    document.getElementById('github-content').style.display = 'block';
                    document.getElementById('gh-avatar').src = data.avatar_url;
                    document.getElementById('gh-name').innerText = data.name || data.login;
                    document.getElementById('gh-stats').innerText = data.public_repos + ' Repos · ' + data.followers + ' Followers · ' + (data.bio || '');
                    fetch('https://api.github.com/users/<?= htmlspecialchars($github_username) ?>/repos?sort=updated&per_page=6')
                        .then(r => r.json())
                        .then(repos => {
                            let html = '';
                            if(repos.length > 0) {
                                repos.forEach(repo => {
                                    html += `<div class="repo-card">
                                        <a href="${repo.html_url}" target="_blank" class="repo-name">${repo.name}</a>
                                        <p class="repo-desc">${repo.description ? repo.description.substring(0, 80) + (repo.description.length > 80 ? '…' : '') : 'No description provided'}</p>
                                        ${repo.language ? `<span class="repo-lang">◆ ${repo.language}</span>` : ''}
                                    </div>`;
                                });
                            } else {
                                html = '<p style="color:var(--text-dim);">No public repositories found.</p>';
                            }
                            document.getElementById('gh-repos-list').innerHTML = html;
                        });
                })
                .catch(() => {
                    document.getElementById('github-spinner').innerText = "⚠ Failed to load GitHub telemetry.";
                });
        });
        </script>
        <?php endif; ?>

    </div>
</div>

<!-- FOOTER -->
<footer>
    <div class="footer-grid">
        <div class="footer-brand-col">
            <span class="nav-brand-text">DevSprint</span>
            <p>India's premier hackathon discovery platform. Navigate the universe of tech competitions.</p>
            <div class="footer-status"><div class="status-dot"></div> All systems operational</div>
        </div>
        <div class="footer-col"><h4>Navigate</h4><ul>
            <li><a href="hackathons.php">Hackathons</a></li>
            <li><a href="matchmaking.php">Find Teammates</a></li>
            <li><a href="teams.php">My Teams</a></li>
        </ul></div>
        <div class="footer-col"><h4>Account</h4><ul>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul></div>
        <div class="footer-col"><h4>Company</h4><ul>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="index.php">Home</a></li>
        </ul></div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 DevSprint · Build faster. Compete smarter. Sprint to success.</p>
        <p>Crafted somewhere in the cosmos 🚀</p>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>
