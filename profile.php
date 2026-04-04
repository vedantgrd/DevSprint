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
.profile-layout {
    display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;
    max-width:1200px;margin:0 auto;padding:2rem;
}
@media(max-width:900px){ .profile-layout{grid-template-columns:1fr;} }

/* Commander banner */
.commander-banner {
    grid-column:1/-1;
    background:linear-gradient(135deg,rgba(13,27,75,0.6) 0%,rgba(26,5,51,0.6) 100%);
    border:1px solid rgba(79,195,247,0.15);border-radius:var(--radius-lg);
    padding:2.5rem;display:flex;align-items:center;gap:2rem;
    position:relative;overflow:hidden;
}
.commander-banner::before {
    content:'';position:absolute;top:0;left:0;right:0;height:2px;
    background:linear-gradient(90deg,var(--plasma-cyan),var(--pulsar-violet),var(--nova-orange));
}
.commander-avatar {
    width:90px;height:90px;border-radius:50%;
    border:2px solid var(--plasma-cyan);
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
    font-family:'Orbitron',monospace;font-size:2rem;font-weight:900;
    color:var(--plasma-cyan);
    background:rgba(0,229,255,0.05);
    box-shadow:0 0 30px rgba(0,229,255,0.2);
}
.commander-info { flex:1; }
.commander-name { font-family:'Orbitron',monospace;font-size:1.8rem;font-weight:900;color:var(--text-bright);margin-bottom:0.4rem; }
.commander-email { font-family:'JetBrains Mono',monospace;font-size:0.82rem;color:var(--text-dim);margin-bottom:1rem; }
.skills-cloud { display:flex;flex-wrap:wrap;gap:0.5rem; }
.skill-tag {
    font-family:'JetBrains Mono',monospace;font-size:0.72rem;padding:0.25rem 0.75rem;
    border-radius:40px;border:1px solid rgba(79,195,247,0.2);
    color:var(--plasma-cyan);background:rgba(0,229,255,0.06);
}
.commander-actions { display:flex;flex-direction:column;gap:0.75rem;align-items:flex-end;flex-shrink:0; }
@media(max-width:600px){
    .commander-banner{flex-direction:column;text-align:center;}
    .commander-actions{align-items:center;}
    .skills-cloud{justify-content:center;}
}

/* Profile cards */
.p-card { background:rgba(255,255,255,0.02);border:1px solid rgba(79,195,247,0.1);border-radius:var(--radius-lg);padding:2rem;transition:border-color 0.3s; }
.p-card:hover { border-color:rgba(79,195,247,0.2); }
.p-card-title {
    font-family:'Orbitron',monospace;font-size:1rem;font-weight:700;
    color:var(--text-bright);margin-bottom:1.5rem;padding-bottom:0.75rem;
    border-bottom:1px solid rgba(79,195,247,0.08);
    display:flex;align-items:center;gap:0.75rem;
}
.p-card-title .card-icon { font-size:1.1rem;opacity:0.8; }

/* Save button */
.save-btn {
    width:100%;padding:0.9rem;margin-top:1rem;
    background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));
    color:var(--void);border:none;border-radius:var(--radius-sm);
    font-family:'Orbitron',monospace;font-size:0.82rem;font-weight:700;
    letter-spacing:0.08em;cursor:pointer;transition:all 0.3s;position:relative;overflow:hidden;
}
.save-btn::before { content:'';position:absolute;inset:0;background:linear-gradient(135deg,var(--pulsar-violet),var(--plasma-cyan));opacity:0;transition:opacity 0.3s; }
.save-btn:hover::before { opacity:1; }
.save-btn:hover { transform:translateY(-2px);box-shadow:0 0 30px rgba(0,229,255,0.25); }
.save-btn span { position:relative;z-index:1; }

/* Application items */
.app-item {
    background:rgba(0,0,0,0.15);padding:1.2rem;border-radius:var(--radius-md);
    margin-bottom:1rem;border-left:3px solid var(--pulsar-violet);
    transition:border-color 0.2s;
}
.app-item:hover { border-left-color:var(--plasma-cyan); }
.app-item h4 { font-family:'Orbitron',monospace;font-size:0.92rem;color:var(--plasma-cyan);margin-bottom:0.5rem; }
.app-item p { color:var(--text-dim);font-size:0.82rem;margin:0.2rem 0;font-family:'JetBrains Mono',monospace; }

/* GitHub card */
.gh-meta { display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem; }
.gh-avatar { width:56px;height:56px;border-radius:50%;border:2px solid var(--pulsar-violet); }
.gh-name { font-weight:700;color:var(--text-bright);margin-bottom:0.25rem; }
.gh-stats { font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:var(--text-dim); }

.repo-card {
    background:rgba(0,0,0,0.15);padding:1rem;border-radius:var(--radius-sm);
    margin-bottom:0.75rem;border-left:3px solid var(--nova-orange);
    transition:transform 0.2s;
}
.repo-card:hover { transform:translateX(4px); }
.repo-name { color:var(--nova-orange);font-weight:700;text-decoration:none;font-size:0.9rem; }
.repo-name:hover { opacity:0.8; }
.repo-desc { color:var(--text-dim);font-size:0.8rem;margin:0.3rem 0;line-height:1.4; }
.repo-lang { font-family:'JetBrains Mono',monospace;font-size:0.72rem;color:var(--pulsar-violet);font-weight:700; }
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
