<?php require_once 'csrf.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
require_once 'db_connect.php';

$hackathons = $conn->query("SELECT * FROM hackathons ORDER BY created_at DESC");

$apps_query = "
    SELECT a.id, u.first_name, u.last_name, u.email, h.title, a.applied_at, a.status, t.name as team_name
    FROM applications a
    JOIN users u ON a.user_id = u.id
    JOIN hackathons h ON a.hackathon_id = h.id
    LEFT JOIN teams t ON a.team_id = t.id
    ORDER BY a.applied_at DESC
";
$applications = $conn->query($apps_query);

$total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'] ?? 0;
$total_hack  = $conn->query("SELECT COUNT(*) as c FROM hackathons")->fetch_assoc()['c'] ?? 0;
$total_apps  = $conn->query("SELECT COUNT(*) as c FROM applications")->fetch_assoc()['c'] ?? 0;
$total_teams = $conn->query("SELECT COUNT(*) as c FROM teams")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | DevSprint · Mission Control</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
/* ── Reset & Base ── */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
html { scroll-behavior: smooth; }

:root {
    --void: #00000a;
    --deep: #02020f;
    --plasma-cyan: #00e5ff;
    --pulsar-violet: #7c4dff;
    --nova-orange: #ff6d00;
    --comet-green: #00e676;
    --danger-red: #ff3d57;
    --text-dim: #7b8eb0;
    --text-mid: #a8b8d8;
    --text-bright: #e8f0ff;
    --radius-sm: 6px;
    --radius-md: 14px;
    --radius-lg: 24px;
}

body {
    font-family: 'Syne', sans-serif;
    background: #00000a;
    color: #e8f0ff;
    overflow-x: hidden;
    cursor: auto;
    min-height: 100vh;
}

/* ── Layout ── */
.dash-layout {
    display: flex;
    min-height: 100vh;
}

/* ── Sidebar ── */
.sidebar {
    width: 260px;
    flex-shrink: 0;
    background: rgba(2, 2, 15, 0.98);
    border-right: 1px solid rgba(79, 195, 247, 0.1);
    padding: 2rem 1.5rem;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    z-index: 100;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 0.75rem;
    text-decoration: none;
}

.sidebar-brand-text {
    font-family: 'Orbitron', monospace;
    font-size: 1.1rem;
    font-weight: 900;
    background: linear-gradient(90deg, #00e5ff, #7c4dff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.sidebar-admin-badge {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.62rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: #ff6d00;
    border: 1px solid rgba(255, 109, 0, 0.25);
    padding: 0.3rem 0.75rem;
    border-radius: 40px;
    margin-bottom: 2rem;
    display: inline-block;
    background: rgba(255, 109, 0, 0.06);
}

.sidebar-section {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.62rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #7b8eb0;
    margin-bottom: 0.6rem;
    margin-top: 1.5rem;
}

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 0.85rem;
    border-radius: 6px;
    color: #7b8eb0;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.2s;
    margin-bottom: 0.2rem;
    letter-spacing: 0.03em;
}

.sidebar-link:hover,
.sidebar-link.active {
    color: #e8f0ff;
    background: rgba(79, 195, 247, 0.08);
}

.sidebar-link .link-icon {
    font-size: 1rem;
    width: 18px;
    text-align: center;
    flex-shrink: 0;
}

.sidebar-logout {
    margin-top: auto;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(79, 195, 247, 0.08);
}

/* ── Main Content ── */
.dash-main {
    margin-left: 260px;
    padding: 2rem 2.5rem;
    min-height: 100vh;
    flex: 1;
    width: calc(100% - 260px);
}

/* ── Dashboard Header ── */
.dash-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid rgba(79, 195, 247, 0.08);
}

.dash-title {
    font-family: 'Orbitron', monospace;
    font-size: 1.8rem;
    font-weight: 900;
    color: #e8f0ff;
    line-height: 1.1;
    margin-bottom: 0.4rem;
}

.dash-breadcrumb {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.72rem;
    color: #7b8eb0;
}

.systems-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(0, 230, 118, 0.06);
    border: 1px solid rgba(0, 230, 118, 0.2);
    border-radius: 40px;
    flex-shrink: 0;
    margin-top: 0.25rem;
}

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #00e676;
    animation: statusBlink 2s ease-in-out infinite;
    flex-shrink: 0;
}

@keyframes statusBlink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

/* ── Stats Row ── */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: rgba(255, 255, 255, 0.025);
    border: 1px solid rgba(79, 195, 247, 0.12);
    border-radius: 14px;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
    transition: border-color 0.3s, transform 0.3s;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, #00e5ff, #7c4dff);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s;
}

.stat-card:hover::before { transform: scaleX(1); }
.stat-card:hover {
    border-color: rgba(79, 195, 247, 0.25);
    transform: translateY(-2px);
}

.stat-card-icon {
    font-size: 1.5rem;
    margin-bottom: 0.75rem;
    display: block;
}

.stat-card-num {
    font-family: 'Orbitron', monospace;
    font-size: 2.2rem;
    font-weight: 900;
    background: linear-gradient(135deg, #00e5ff, #7c4dff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: block;
    margin-bottom: 0.35rem;
    line-height: 1;
}

.stat-card-label {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.68rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #7b8eb0;
}

/* ── Content Grid ── */
.dash-grid {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 1.5rem;
    align-items: start;
}

/* ── Cards ── */
.d-card {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(79, 195, 247, 0.1);
    border-radius: 18px;
    padding: 1.75rem;
}

.d-card-title {
    font-family: 'Orbitron', monospace;
    font-size: 0.9rem;
    font-weight: 700;
    color: #e8f0ff;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid rgba(79, 195, 247, 0.08);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* ── Form Elements ── */
.form-group { margin-bottom: 1rem; }

.form-group label {
    display: block;
    margin-bottom: 0.45rem;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #7b8eb0;
    font-family: 'JetBrains Mono', monospace;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.8rem 1rem;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(79, 195, 247, 0.15);
    border-radius: 6px;
    color: #e8f0ff;
    font-family: 'Syne', sans-serif;
    font-size: 0.9rem;
    transition: border-color 0.3s, box-shadow 0.3s;
    outline: none;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: #00e5ff;
    box-shadow: 0 0 0 3px rgba(0, 229, 255, 0.08);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #7b8eb0;
}

.form-group select option {
    background: #02020f;
    color: #e8f0ff;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.add-submit {
    width: 100%;
    padding: 0.9rem;
    margin-top: 0.75rem;
    background: linear-gradient(135deg, #00e5ff, #7c4dff);
    color: #00000a;
    border: none;
    border-radius: 6px;
    font-family: 'Orbitron', monospace;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    cursor: pointer;
    transition: all 0.3s;
}

.add-submit:hover {
    opacity: 0.88;
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(0, 229, 255, 0.2);
}

/* ── Tables ── */
.overflow-table {
    overflow-x: auto;
    border-radius: 10px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 500px;
}

.data-table th {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.68rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: #00e5ff;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid rgba(79, 195, 247, 0.15);
    text-align: left;
    white-space: nowrap;
    background: rgba(79, 195, 247, 0.03);
}

.data-table td {
    padding: 0.9rem 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    color: #a8b8d8;
    font-size: 0.88rem;
    vertical-align: middle;
}

.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: rgba(79, 195, 247, 0.03); }

/* ── Badges ── */
.badge {
    display: inline-block;
    padding: 0.25rem 0.7rem;
    border-radius: 40px;
    font-size: 0.72rem;
    font-weight: 700;
    font-family: 'JetBrains Mono', monospace;
    letter-spacing: 0.05em;
    white-space: nowrap;
}

.badge-pending  { background: rgba(245,158,11,0.12); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); }
.badge-accepted { background: rgba(0,230,118,0.12);  color: #00e676; border: 1px solid rgba(0,230,118,0.3); }
.badge-rejected { background: rgba(239,68,68,0.12);  color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
.badge-member   { background: rgba(79,195,247,0.12);  color: #4fc3f7; border: 1px solid rgba(79,195,247,0.3); }

/* ── Action Buttons ── */
.action-accept-btn {
    background: rgba(0,230,118,0.1);
    color: #00e676;
    border: 1px solid rgba(0,230,118,0.3);
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    font-family: 'JetBrains Mono', monospace;
    transition: background 0.2s;
}
.action-accept-btn:hover { background: rgba(0,230,118,0.22); }

.action-reject-btn {
    background: rgba(239,68,68,0.1);
    color: #ef4444;
    border: 1px solid rgba(239,68,68,0.25);
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    font-family: 'JetBrains Mono', monospace;
    transition: background 0.2s;
}
.action-reject-btn:hover { background: rgba(239,68,68,0.2); }

.team-view-btn {
    background: rgba(124,77,255,0.1);
    color: #7c4dff;
    border: 1px solid rgba(124,77,255,0.25);
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    font-family: 'JetBrains Mono', monospace;
    text-decoration: none;
    display: inline-block;
    transition: background 0.2s;
}
.team-view-btn:hover { background: rgba(124,77,255,0.2); }

/* ── Responsive ── */
@media (max-width: 1200px) {
    .stats-row { grid-template-columns: repeat(2, 1fr); }
    .dash-grid  { grid-template-columns: 1fr; }
}

@media (max-width: 900px) {
    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }
    .dash-main {
        margin-left: 0;
        width: 100%;
        padding: 1.5rem;
    }
    .dash-layout { flex-direction: column; }
}

@media (max-width: 560px) {
    .stats-row { grid-template-columns: repeat(2, 1fr); }
    .dash-main { padding: 1rem; }
}
</style>
</head>
<body>

<div class="dash-layout">

<!-- ── SIDEBAR ── -->
<aside class="sidebar">
    <a href="index.php" class="sidebar-brand">
        <svg width="28" height="28" viewBox="0 0 40 40" fill="none">
            <circle cx="20" cy="20" r="8" fill="none" stroke="#00e5ff" stroke-width="1.5"/>
            <ellipse cx="20" cy="20" rx="18" ry="7" fill="none" stroke="#7c4dff" stroke-width="1" transform="rotate(30 20 20)"/>
            <ellipse cx="20" cy="20" rx="18" ry="7" fill="none" stroke="#00e5ff" stroke-width="1" opacity="0.4" transform="rotate(-30 20 20)"/>
            <circle cx="20" cy="20" r="3" fill="#00e5ff"/>
        </svg>
        <span class="sidebar-brand-text">DevSprint</span>
    </a>

    <div class="sidebar-admin-badge">🔒 Admin Mode</div>

    <div class="sidebar-section">Navigation</div>
    <a href="admin_dashboard.php" class="sidebar-link active">
        <span class="link-icon">🏠</span> Dashboard
    </a>
    <a href="index.php" class="sidebar-link" target="_blank">
        <span class="link-icon">🌐</span> View Live Site
    </a>

    <div class="sidebar-section">Manage</div>
    <a href="#add-section" class="sidebar-link">
        <span class="link-icon">➕</span> Add Hackathon
    </a>
    <a href="#apps-section" class="sidebar-link">
        <span class="link-icon">📋</span> Applications
    </a>
    <a href="#hack-section" class="sidebar-link">
        <span class="link-icon">🏆</span> All Hackathons
    </a>

    <div class="sidebar-logout">
        <a href="admin_logout.php" class="sidebar-link" style="color: #ef4444; border: 1px solid rgba(239,68,68,0.15); border-radius: 6px;">
            <span class="link-icon">🚪</span> Logout
        </a>
    </div>
</aside>

<!-- ── MAIN CONTENT ── -->
<main class="dash-main">

    <!-- Header -->
    <div class="dash-header">
        <div>
            <div class="dash-title">Mission Control</div>
            <div class="dash-breadcrumb">Admin &nbsp;·&nbsp; Dashboard &nbsp;·&nbsp; DevSprint Platform</div>
        </div>
        <div class="systems-status">
            <div class="status-dot"></div>
            <span style="font-family:'JetBrains Mono',monospace; font-size:0.75rem; color:#00e676; white-space:nowrap;">Systems Online</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-row">
        <div class="stat-card">
            <span class="stat-card-icon">🏆</span>
            <span class="stat-card-num"><?= $total_hack ?></span>
            <span class="stat-card-label">Hackathons</span>
        </div>
        <div class="stat-card">
            <span class="stat-card-icon">👥</span>
            <span class="stat-card-num"><?= $total_users ?></span>
            <span class="stat-card-label">Registered Users</span>
        </div>
        <div class="stat-card">
            <span class="stat-card-icon">📋</span>
            <span class="stat-card-num"><?= $total_apps ?></span>
            <span class="stat-card-label">Applications</span>
        </div>
        <div class="stat-card">
            <span class="stat-card-icon">🛸</span>
            <span class="stat-card-num"><?= $total_teams ?></span>
            <span class="stat-card-label">Active Teams</span>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="dash-grid">

        <!-- Add Hackathon Form -->
        <div id="add-section" class="d-card">
            <div class="d-card-title">➕ Add New Hackathon</div>
            <form action="admin_add_hackathon.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required placeholder="e.g. CodeFest 2026">
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" required placeholder="e.g. Bangalore / Online">
                </div>
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="date_start" required>
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="date_end" required>
                </div>
                <div class="form-group">
                    <label>Prize Pool</label>
                    <input type="text" name="prize_pool" required placeholder="e.g. ₹50,000">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" required placeholder="Describe the hackathon..."></textarea>
                </div>
                <div class="form-group">
                    <label>Application Type</label>
                    <select name="application_type" required>
                        <option value="Both">Both (Individual or Team)</option>
                        <option value="Individual">Individual Only</option>
                        <option value="Team">Team Only</option>
                    </select>
                </div>
                <button type="submit" class="add-submit">🚀 CREATE HACKATHON</button>
            </form>
        </div>

        <!-- Right Column -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">

            <!-- Applications Table -->
            <div id="apps-section" class="d-card">
                <div class="d-card-title">📋 Recent Applications</div>
                <div class="overflow-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Hackathon</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($applications && $applications->num_rows > 0): while ($a = $applications->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span style="color:#e8f0ff; font-weight:600;">
                                        <?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?>
                                    </span><br>
                                    <small style="font-family:'JetBrains Mono',monospace; font-size:0.68rem; color:#7b8eb0;">
                                        <?= htmlspecialchars($a['team_name'] ? '🛸 Team: ' . $a['team_name'] : '👤 Individual') ?>
                                    </small>
                                </td>
                                <td style="font-family:'JetBrains Mono',monospace; font-size:0.78rem;">
                                    <?= htmlspecialchars($a['email']) ?>
                                </td>
                                <td><?= htmlspecialchars($a['title']) ?></td>
                                <td style="font-family:'JetBrains Mono',monospace; font-size:0.78rem; white-space:nowrap;">
                                    <?= date('M d, Y', strtotime($a['applied_at'])) ?>
                                </td>
                                <td>
                                    <?php
                                        $st = $a['status'];
                                        $bc = $st === 'Accepted' ? 'badge-accepted' : ($st === 'Rejected' ? 'badge-rejected' : 'badge-pending');
                                    ?>
                                    <span class="badge <?= $bc ?>"><?= htmlspecialchars($st) ?></span>
                                </td>
                                <td>
                                    <?php if ($a['team_name']): ?>
                                        <a href="admin_view_team.php?app_id=<?= $a['id'] ?>" class="team-view-btn">View Team</a>
                                    <?php else: ?>
                                        <div style="display:flex; gap:0.4rem;">
                                            <form action="admin_update_application.php" method="POST" style="display:inline; margin:0;">
                                                <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                                                <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
                                                <input type="hidden" name="status" value="Accepted">
                                                <button type="submit" class="action-accept-btn" title="Accept">✓ Accept</button>
                                            </form>
                                            <form action="admin_update_application.php" method="POST" style="display:inline; margin:0;">
                                                <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                                                <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
                                                <input type="hidden" name="status" value="Rejected">
                                                <button type="submit" class="action-reject-btn" title="Reject">✕ Reject</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding:2.5rem; color:#7b8eb0;">
                                    No applications yet.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Hackathons List -->
            <div id="hack-section" class="d-card">
                <div class="d-card-title">🏆 Active Hackathons</div>
                <div class="overflow-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Location</th>
                                <th>Start Date</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($hackathons && $hackathons->num_rows > 0): while ($h = $hackathons->fetch_assoc()): ?>
                            <tr>
                                <td style="color:#e8f0ff; font-weight:600;"><?= htmlspecialchars($h['title']) ?></td>
                                <td><?= htmlspecialchars($h['location']) ?></td>
                                <td style="font-family:'JetBrains Mono',monospace; font-size:0.8rem; white-space:nowrap;">
                                    <?= htmlspecialchars($h['date_start']) ?>
                                </td>
                                <td>
                                    <span class="badge badge-member" style="font-size:0.68rem;">
                                        <?= htmlspecialchars($h['application_type']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding:2.5rem; color:#7b8eb0;">
                                    No hackathons found.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- end right column -->
    </div><!-- end dash-grid -->

</main>
</div><!-- end dash-layout -->

<script>
// Sidebar smooth scroll for anchor links
document.querySelectorAll('.sidebar-link[href^="#"]').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        var target = document.querySelector(this.getAttribute('href'));
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
</script>
</body>
</html>