<?php require_once '../includes/csrf.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login_view.php");
    exit();
}
require_once '../includes/db_connect.php';

$user_id = intval($_SESSION['user_id']);
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

// NEW FEATURE: Count unread notifications
$unread_q = $conn->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0");
$unread_q->bind_param("i", $user_id);
$unread_q->execute();
$unread_count = $unread_q->get_result()->fetch_assoc()['unread'];
$unread_q->close();

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
<meta name="description" content="Your DevSprint commander profile — manage your details, skills, and applications.">
<title>My Profile | DevSprint · Commander Hub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/styles.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<style>
/* ── Profile Layout ── */
.profile-layout {
    max-width: 1100px;
    margin: 0 auto;
    padding: 2rem 2rem 5rem;
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}

/* ── Commander Banner ── */
.commander-banner {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(79,195,247,0.15);
    border-radius: var(--radius-lg);
    padding: 2.5rem;
    display: flex;
    align-items: center;
    gap: 2rem;
    position: relative;
    overflow: hidden;
}
.commander-banner::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, var(--plasma-cyan), var(--pulsar-violet), var(--nova-orange));
}
.commander-avatar {
    width: 80px; height: 80px; border-radius: 50%;
    background: linear-gradient(135deg, var(--plasma-cyan), var(--pulsar-violet));
    display: flex; align-items: center; justify-content: center;
    font-family: 'Orbitron', monospace; font-size: 2rem; font-weight: 900;
    color: var(--void); flex-shrink: 0;
    box-shadow: 0 0 30px rgba(0,229,255,0.3);
}
.commander-info { flex: 1; }
.commander-name {
    font-family: 'Orbitron', monospace; font-size: 1.6rem;
    font-weight: 900; color: var(--text-bright); margin-bottom: 0.35rem;
}
.commander-email {
    font-family: 'JetBrains Mono', monospace; font-size: 0.82rem;
    color: var(--plasma-cyan); margin-bottom: 1rem; letter-spacing: 0.05em;
}
.skills-cloud { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.skill-tag {
    padding: 0.25rem 0.75rem;
    background: rgba(124,77,255,0.12);
    border: 1px solid rgba(124,77,255,0.3);
    border-radius: 40px;
    font-size: 0.75rem; font-weight: 600;
    color: var(--pulsar-violet);
    font-family: 'JetBrains Mono', monospace;
    letter-spacing: 0.04em;
}
.commander-actions { display: flex; flex-direction: column; gap: 0.6rem; flex-shrink: 0; }

/* ── Two-column grid for cards ── */
.profile-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.75rem;
}

/* ── Profile Cards ── */
.p-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(79,195,247,0.12);
    border-radius: var(--radius-lg);
    padding: 2rem;
    position: relative;
    overflow: hidden;
}
.p-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, var(--plasma-cyan), var(--pulsar-violet));
}
.p-card-title {
    font-family: 'Orbitron', monospace;
    font-size: 0.9rem; font-weight: 700;
    color: var(--text-bright);
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid rgba(79,195,247,0.08);
    display: flex; align-items: center; gap: 0.5rem;
    letter-spacing: 0.04em;
}
.card-icon { font-size: 1.1rem; }

/* ── Save Button ── */
.save-btn {
    width: 100%; padding: 1rem;
    background: linear-gradient(135deg, var(--plasma-cyan), var(--pulsar-violet));
    color: var(--void); border: none; border-radius: var(--radius-sm);
    font-family: 'Orbitron', monospace; font-size: 0.85rem; font-weight: 700;
    letter-spacing: 0.08em; cursor: pointer; transition: all 0.3s;
    margin-top: 0.5rem; position: relative; overflow: hidden;
}
.save-btn::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(135deg, var(--pulsar-violet), var(--plasma-cyan));
    opacity: 0; transition: opacity 0.3s;
}
.save-btn:hover::before { opacity: 1; }
.save-btn:hover { transform: translateY(-2px); box-shadow: 0 0 30px rgba(0,229,255,0.25); }
.save-btn span { position: relative; z-index: 1; }

/* ── Edit Profile Button ── */
.edit-profile-btn {
    width: 100%; padding: 0.85rem;
    background: transparent;
    border: 1px solid rgba(0,229,255,0.4);
    color: var(--plasma-cyan);
    border-radius: var(--radius-sm);
    font-family: 'Orbitron', monospace; font-size: 0.8rem; font-weight: 700;
    letter-spacing: 0.08em; cursor: pointer; transition: all 0.3s;
    margin-top: 0.5rem;
}
.edit-profile-btn:hover { background: rgba(0,229,255,0.1); transform: translateY(-2px); }
.cancel-edit-btn {
    width: 100%; padding: 0.75rem;
    background: transparent;
    border: 1px solid rgba(123,142,176,0.3);
    color: var(--text-dim);
    border-radius: var(--radius-sm);
    font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; font-weight: 700;
    cursor: pointer; transition: all 0.2s; margin-top: 0.5rem;
}
.cancel-edit-btn:hover { border-color: rgba(255,61,87,0.4); color: #ff3d57; }

/* ── Profile View Rows ── */
.profile-view-rows { display: flex; flex-direction: column; gap: 0.9rem; }
.profile-view-row {
    display: flex; flex-direction: column; gap: 0.2rem;
    padding: 0.85rem 1rem;
    background: rgba(0,0,0,0.2);
    border: 1px solid rgba(79,195,247,0.08);
    border-radius: var(--radius-sm);
    transition: border-color 0.2s;
}
.profile-view-row:hover { border-color: rgba(79,195,247,0.2); }
.profile-view-label {
    font-family: 'JetBrains Mono', monospace; font-size: 0.68rem;
    letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-dim);
}
.profile-view-value {
    font-size: 0.95rem; color: var(--text-bright); font-weight: 600;
    word-break: break-word;
}
.profile-view-value.empty { color: var(--text-dim); font-style: italic; font-size: 0.85rem; font-weight: 400; }

/* ── Application items ── */
.app-item {
    background: rgba(0,0,0,0.25);
    border: 1px solid rgba(79,195,247,0.08);
    border-left: 3px solid var(--pulsar-violet);
    padding: 1.1rem 1.25rem;
    border-radius: var(--radius-md);
    margin-bottom: 0.85rem;
    transition: border-color 0.3s;
}
.app-item:hover { border-color: rgba(124,77,255,0.4); }
.app-item h4 {
    font-family: 'Orbitron', monospace;
    font-size: 0.85rem; font-weight: 700;
    color: var(--plasma-cyan); margin-bottom: 0.4rem; letter-spacing: 0.03em;
}
.app-item p {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.75rem; color: var(--text-dim); margin-bottom: 0.25rem;
}

/* ── GitHub card ── */
.gh-meta { display: flex; align-items: center; gap: 1.25rem; margin-bottom: 1.5rem; }
.gh-avatar { width: 60px; height: 60px; border-radius: 50%; border: 2px solid rgba(79,195,247,0.3); }
.gh-name { font-family: 'Orbitron', monospace; font-size: 1rem; font-weight: 700; color: var(--text-bright); margin-bottom: 0.35rem; }
.gh-stats { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: var(--text-dim); }
.repo-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(79,195,247,0.1);
    border-radius: var(--radius-md); padding: 1rem;
    transition: border-color 0.3s;
}
.repo-card:hover { border-color: rgba(79,195,247,0.3); }
.repo-name { font-family: 'Orbitron', monospace; font-size: 0.82rem; font-weight: 700; color: var(--plasma-cyan); text-decoration: none; display: block; margin-bottom: 0.4rem; }
.repo-name:hover { color: var(--pulsar-violet); }
.repo-desc { font-size: 0.8rem; color: var(--text-dim); margin-bottom: 0.5rem; line-height: 1.5; }
.repo-lang { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: var(--pulsar-violet); }

/* ── Alert flash ── */
.profile-alert {
    padding: 1rem 1.5rem; border-radius: var(--radius-md);
    font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;
    margin-bottom: 1rem;
}
.profile-alert.success { background: rgba(0,230,118,0.1); border: 1px solid rgba(0,230,118,0.3); color: var(--comet-green); }
.profile-alert.error   { background: rgba(239,68,68,0.1);  border: 1px solid rgba(239,68,68,0.3);  color: #ef4444; }

/* ── Responsive ── */
@media (max-width: 900px) {
    .profile-grid { grid-template-columns: 1fr; }
    .commander-banner { flex-direction: column; text-align: center; }
    .commander-actions { flex-direction: row; justify-content: center; }
    .skills-cloud { justify-content: center; }
}
@media (max-width: 600px) {
    .commander-name { font-size: 1.3rem; }
    .p-card { padding: 1.5rem; }
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
        <a href="../home/index.php" class="nav-brand">
            <div class="nav-logo">
                <svg viewBox="0 0 40 40" fill="none"><circle cx="20" cy="20" r="8" fill="none" stroke="#00e5ff" stroke-width="1.5"/><ellipse cx="20" cy="20" rx="18" ry="7" fill="none" stroke="#7c4dff" stroke-width="1" transform="rotate(30 20 20)"/><ellipse cx="20" cy="20" rx="18" ry="7" fill="none" stroke="#00e5ff" stroke-width="1" opacity="0.4" transform="rotate(-30 20 20)"/><circle cx="20" cy="20" r="3" fill="#00e5ff"/></svg>
            </div>
            <span class="nav-brand-text">DevSprint</span>
        </a>
        <button class="nav-toggle" id="nav-toggle">☰</button>
        <ul class="nav-menu" id="nav-menu">
            <li><a href="../home/index.php">Home</a></li>
            <li><a href="../hackathons/hackathons.php">Hackathons</a></li>
            <li><a href="../about/about.php">About</a></li>
            <li><a href="../contact/contact.php">Contact</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="../teams/teams.php">Teams</a></li>
                <li><a href="../teams/matchmaking.php">Find Teammates</a></li>
                <li><a href="../profile/profile.php" class="active">My Profile</a></li>
                <li><a href="../profile/inbox.php" style="color:var(--plasma-cyan);">🔔 Inbox <?= $unread_count > 0 ? "<span style='color:red;'>({$unread_count})</span>" : "" ?></a></li>
                <li><a href="../actions/logout.php" class="nav-btn nav-btn-danger">Logout</a></li>
            <?php else: ?>
                <li><a href="../login/login_view.php" class="nav-btn">Launch →</a></li>
            <?php endif; ?>
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

        <!-- Two-column grid -->
        <div class="profile-grid">

            <!-- Profile Card with View/Edit Mode -->
            <div class="p-card reveal d1" id="profile-card">
                <!-- Card Title dynamically changes -->
                <div class="p-card-title" id="profile-card-title"><span class="card-icon">🛸</span> Commander Profile</div>

                <?php if (isset($_SESSION['profile_success'])): ?>
                    <div class="profile-alert success">✅ <?= htmlspecialchars($_SESSION['profile_success']) ?></div>
                    <?php unset($_SESSION['profile_success']); ?>
                <?php elseif (isset($_SESSION['profile_error'])): ?>
                    <div class="profile-alert error">⚠️ <?= htmlspecialchars($_SESSION['profile_error']) ?></div>
                    <?php unset($_SESSION['profile_error']); ?>
                <?php endif; ?>

                <!-- ── VIEW MODE ── -->
                <div id="profile-view-mode">
                    <div class="profile-view-rows">
                        <div class="profile-view-row">
                            <span class="profile-view-label">First Name</span>
                            <span class="profile-view-value <?= empty($user['first_name']) ? 'empty' : '' ?>"><?= $user['first_name'] ? htmlspecialchars($user['first_name']) : '— not set —' ?></span>
                        </div>
                        <?php if (!empty($user['middle_name'])): ?>
                        <div class="profile-view-row">
                            <span class="profile-view-label">Middle Name</span>
                            <span class="profile-view-value"><?= htmlspecialchars($user['middle_name']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="profile-view-row">
                            <span class="profile-view-label">Last Name</span>
                            <span class="profile-view-value <?= empty($user['last_name']) ? 'empty' : '' ?>"><?= $user['last_name'] ? htmlspecialchars($user['last_name']) : '— not set —' ?></span>
                        </div>
                        <div class="profile-view-row">
                            <span class="profile-view-label">Email</span>
                            <span class="profile-view-value"><?= htmlspecialchars($user['email'] ?? '') ?></span>
                        </div>
                        <div class="profile-view-row">
                            <span class="profile-view-label">Phone</span>
                            <span class="profile-view-value <?= empty($user['phone']) ? 'empty' : '' ?>"><?= $user['phone'] ? htmlspecialchars($user['phone']) : '— not set —' ?></span>
                        </div>
                        <div class="profile-view-row">
                            <span class="profile-view-label">City</span>
                            <span class="profile-view-value <?= empty($user['city']) ? 'empty' : '' ?>"><?= $user['city'] ? htmlspecialchars($user['city']) : '— not set —' ?></span>
                        </div>
                        <div class="profile-view-row" style="border-top:1px solid rgba(79,195,247,0.12); margin-top:0.25rem; padding-top:1rem;">
                            <span class="profile-view-label">Skills</span>
                            <span class="profile-view-value <?= empty($user['skills']) ? 'empty' : '' ?>"><?= $user['skills'] ? htmlspecialchars($user['skills']) : '— not set —' ?></span>
                        </div>
                        <div class="profile-view-row">
                            <span class="profile-view-label">GitHub</span>
                            <?php if ($user['github']): ?>
                                <a href="<?= htmlspecialchars($user['github']) ?>" target="_blank" class="profile-view-value" style="color:var(--plasma-cyan);text-decoration:none;"><?= htmlspecialchars($user['github']) ?></a>
                            <?php else: ?>
                                <span class="profile-view-value empty">— not set —</span>
                            <?php endif; ?>
                        </div>
                        <div class="profile-view-row">
                            <span class="profile-view-label">LinkedIn</span>
                            <?php if ($user['linkedin']): ?>
                                <a href="<?= htmlspecialchars($user['linkedin']) ?>" target="_blank" class="profile-view-value" style="color:var(--plasma-cyan);text-decoration:none;"><?= htmlspecialchars($user['linkedin']) ?></a>
                            <?php else: ?>
                                <span class="profile-view-value empty">— not set —</span>
                            <?php endif; ?>
                        </div>
                        <div class="profile-view-row">
                            <span class="profile-view-label">Bio</span>
                            <span class="profile-view-value <?= empty($user['bio']) ? 'empty' : '' ?>" style="font-weight:400;font-size:0.9rem;line-height:1.6;"><?= $user['bio'] ? nl2br(htmlspecialchars($user['bio'])) : '— not set —' ?></span>
                        </div>
                    </div>
                    <button type="button" class="edit-profile-btn" id="btn-edit-profile" onclick="toggleProfileEdit(true)">
                        ✏ Edit Profile
                    </button>
                </div>

                <!-- ── EDIT MODE (hidden by default) ── -->
                <div id="profile-edit-mode" style="display:none;">
                    <form action="../actions/update_profile.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                        <div class="form-group"><label>First Name</label><input type="text" name="first" value="<?= htmlspecialchars($user['first_name']??'') ?>" required></div>
                        <div class="form-group"><label>Middle Name</label><input type="text" name="middle" value="<?= htmlspecialchars($user['middle_name']??'') ?>"></div>
                        <div class="form-group"><label>Last Name</label><input type="text" name="last" value="<?= htmlspecialchars($user['last_name']??'') ?>" required></div>
                        <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($user['phone']??'') ?>"></div>
                        <div class="form-group"><label>City</label><input type="text" name="city" value="<?= htmlspecialchars($user['city']??'') ?>"></div>

                        <div class="p-card-title" style="margin-top:1.5rem;font-size:0.8rem;border-bottom:none;padding-bottom:0;margin-bottom:1rem;">
                            <span class="card-icon">🔭</span> Developer Info
                        </div>
                        <div class="form-group"><label>Skills <span style="font-size:0.7rem;opacity:0.6;">(comma separated)</span></label><input type="text" name="skills" value="<?= htmlspecialchars($user['skills']??'') ?>" placeholder="e.g. React, Python, UI/UX"></div>
                        <div class="form-group"><label>GitHub URL</label><input type="text" name="github" value="<?= htmlspecialchars($user['github']??'') ?>" placeholder="https://github.com/username"></div>
                        <div class="form-group"><label>LinkedIn URL</label><input type="text" name="linkedin" value="<?= htmlspecialchars($user['linkedin']??'') ?>" placeholder="https://linkedin.com/in/username"></div>
                        <div class="form-group"><label>Bio</label><textarea name="bio" rows="4" placeholder="Tell the universe about yourself..."><?= htmlspecialchars($user['bio']??'') ?></textarea></div>

                        <button type="submit" class="save-btn"><span>💾 SAVE CHANGES</span></button>
                    </form>
                    <button type="button" class="cancel-edit-btn" onclick="toggleProfileEdit(false)">✖ Cancel</button>
                </div>
            </div>

            <!-- My Applications Card -->
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
                    <div style="text-align:center;padding:3rem 0;">
                        <div style="font-size:2.5rem;opacity:0.4;margin-bottom:1rem;">🚀</div>
                        <p style="color:var(--text-dim);font-size:0.9rem;margin-bottom:1.5rem;">You haven't applied to any missions yet.</p>
                        <a href="../hackathons/hackathons.php" class="btn btn-primary btn-sm"><span>Explore Hackathons</span></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- GitHub Activity (full width) -->
        <?php if ($github_username): ?>
        <div class="p-card reveal d3" id="github-card">
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

    </div><!-- end profile-layout -->
</div><!-- end page-wrapper -->

<!-- FOOTER -->
<footer>
    <div class="footer-grid">
        <div class="footer-brand-col">
            <span class="nav-brand-text">DevSprint</span>
            <p>India's premier hackathon discovery platform. Navigate the universe of tech competitions.</p>
            <div class="footer-status"><div class="status-dot"></div> All systems operational</div>
        </div>
        <div class="footer-col"><h4>Navigate</h4><ul>
            <li><a href="../hackathons/hackathons.php">Hackathons</a></li>
            <li><a href="../teams/matchmaking.php">Find Teammates</a></li>
            <li><a href="../teams/teams.php">My Teams</a></li>
        </ul></div>
        <div class="footer-col"><h4>Account</h4><ul>
            <li><a href="../profile/profile.php">My Profile</a></li>
            <li><a href="../actions/logout.php">Logout</a></li>
        </ul></div>
        <div class="footer-col"><h4>Company</h4><ul>
            <li><a href="../about/about.php">About</a></li>
            <li><a href="../contact/contact.php">Contact</a></li>
            <li><a href="../home/index.php">Home</a></li>
        </ul></div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 DevSprint · Build faster. Compete smarter. Sprint to success.</p>
        <p>Crafted somewhere in the cosmos 🚀</p>
    </div>
</footer>

<script src="../js/script.js"></script>
<script>
function toggleProfileEdit(editMode) {
    var viewMode = document.getElementById('profile-view-mode');
    var editModeEl = document.getElementById('profile-edit-mode');
    var cardTitle = document.getElementById('profile-card-title');

    if (editMode) {
        viewMode.style.display = 'none';
        editModeEl.style.display = 'block';
        cardTitle.innerHTML = '<span class="card-icon">✏️</span> Edit Commander Profile';
        // Smooth scroll to card
        document.getElementById('profile-card').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        editModeEl.style.display = 'none';
        viewMode.style.display = 'block';
        cardTitle.innerHTML = '<span class="card-icon">🛸</span> Commander Profile';
    }
}
</script>
</body>
</html>
