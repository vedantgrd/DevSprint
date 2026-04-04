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

// Count stats
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
<link rel="stylesheet" href="styles.css">
<style>
body { cursor:auto; }
.cursor,.cursor-ring { display:none; }

/* Sidebar */
.dash-layout { display:grid;grid-template-columns:260px 1fr;min-height:100vh; }
.sidebar {
    background:rgba(2,2,15,0.97);border-right:1px solid rgba(79,195,247,0.08);
    padding:2rem 1.5rem;position:fixed;top:0;left:0;height:100vh;width:260px;
    overflow-y:auto;display:flex;flex-direction:column;z-index:100;
}
.sidebar-brand { display:flex;align-items:center;gap:10px;margin-bottom:2.5rem; }
.sidebar-brand-text { font-family:'Orbitron',monospace;font-size:1.05rem;font-weight:900;background:linear-gradient(90deg,var(--plasma-cyan),var(--pulsar-violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text; }
.sidebar-admin-badge {
    font-family:'JetBrains Mono',monospace;font-size:0.62rem;letter-spacing:0.15em;text-transform:uppercase;
    color:var(--nova-orange);border:1px solid rgba(255,109,0,0.2);
    padding:0.2rem 0.6rem;border-radius:40px;margin-bottom:2rem;display:inline-block;
}
.sidebar-section { font-family:'JetBrains Mono',monospace;font-size:0.62rem;letter-spacing:0.2em;text-transform:uppercase;color:var(--text-dim);margin-bottom:0.75rem;margin-top:1.5rem; }
.sidebar-link {
    display:flex;align-items:center;gap:0.75rem;
    padding:0.7rem 0.85rem;border-radius:var(--radius-sm);
    color:var(--text-dim);text-decoration:none;
    font-size:0.85rem;font-weight:600;transition:all 0.2s;margin-bottom:0.2rem;
    letter-spacing:0.03em;
}
.sidebar-link:hover,.sidebar-link.active { color:var(--text-bright);background:rgba(79,195,247,0.07); }
.sidebar-link .link-icon { opacity:0.8;font-size:1rem;width:18px;text-align:center; }
.sidebar-logout {
    margin-top:auto;padding-top:1.5rem;border-top:1px solid rgba(79,195,247,0.08);
}

/* Main content */
.dash-main { margin-left:260px;padding:2rem;min-height:100vh; }
.dash-header {
    display:flex;justify-content:space-between;align-items:center;
    margin-bottom:2rem;padding-bottom:1.5rem;
    border-bottom:1px solid rgba(79,195,247,0.08);
}
.dash-title { font-family:'Orbitron',monospace;font-size:1.4rem;font-weight:900;color:var(--text-bright); }
.dash-breadcrumb { font-family:'JetBrains Mono',monospace;font-size:0.72rem;color:var(--text-dim);margin-top:0.3rem; }

/* Stats row */
.stats-row { display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem; }
.stat-card {
    background:rgba(255,255,255,0.02);border:1px solid rgba(79,195,247,0.1);
    border-radius:var(--radius-md);padding:1.5rem;position:relative;overflow:hidden;
    transition:border-color 0.3s;
}
.stat-card::before { content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--plasma-cyan),var(--pulsar-violet));transform:scaleX(0);transform-origin:left;transition:transform 0.4s; }
.stat-card:hover::before { transform:scaleX(1); }
.stat-card:hover { border-color:rgba(79,195,247,0.2); }
.stat-card-icon { font-size:1.5rem;margin-bottom:0.75rem;opacity:0.8; }
.stat-card-num { font-family:'Orbitron',monospace;font-size:2rem;font-weight:900;background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;display:block;margin-bottom:0.3rem; }
.stat-card-label { font-family:'JetBrains Mono',monospace;font-size:0.68rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--text-dim); }

/* Grid layout for content */
.dash-grid { display:grid;grid-template-columns:340px 1fr;gap:1.5rem; }
@media(max-width:1100px){ .dash-grid{grid-template-columns:1fr;} .stats-row{grid-template-columns:1fr 1fr;} }
@media(max-width:900px){ .dash-layout{grid-template-columns:1fr;} .sidebar{position:relative;width:100%;height:auto;} .dash-main{margin-left:0;} }
@media(max-width:560px){ .stats-row{grid-template-columns:1fr 1fr;} }

/* Cards */
.d-card { background:rgba(255,255,255,0.02);border:1px solid rgba(79,195,247,0.1);border-radius:var(--radius-lg);padding:1.75rem; }
.d-card-title { font-family:'Orbitron',monospace;font-size:0.95rem;font-weight:700;color:var(--text-bright);margin-bottom:1.5rem;padding-bottom:0.75rem;border-bottom:1px solid rgba(79,195,247,0.08); }

/* Form spacing */
.add-submit {
    width:100%;padding:0.9rem;margin-top:0.5rem;
    background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));
    color:var(--void);border:none;border-radius:var(--radius-sm);
    font-family:'Orbitron',monospace;font-size:0.78rem;font-weight:700;
    letter-spacing:0.06em;cursor:pointer;transition:all 0.3s;
}
.add-submit:hover { opacity:0.9;transform:translateY(-1px); }

/* Inline action buttons */
.action-accept-btn { background:rgba(0,230,118,0.1);color:var(--comet-green);border:1px solid rgba(0,230,118,0.3);padding:0.3rem 0.65rem;border-radius:var(--radius-sm);font-size:0.75rem;font-weight:700;cursor:pointer;font-family:'JetBrains Mono',monospace;transition:background 0.2s; }
.action-accept-btn:hover { background:rgba(0,230,118,0.2); }
.action-reject-btn { background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.25);padding:0.3rem 0.65rem;border-radius:var(--radius-sm);font-size:0.75rem;font-weight:700;cursor:pointer;font-family:'JetBrains Mono',monospace;transition:background 0.2s; }
.action-reject-btn:hover { background:rgba(239,68,68,0.18); }
.team-view-btn { background:rgba(124,77,255,0.1);color:var(--pulsar-violet);border:1px solid rgba(124,77,255,0.25);padding:0.3rem 0.65rem;border-radius:var(--radius-sm);font-size:0.75rem;font-weight:700;cursor:pointer;font-family:'JetBrains Mono',monospace;text-decoration:none;display:inline-block;transition:background 0.2s; }
.team-view-btn:hover { background:rgba(124,77,255,0.18); }
</style>
</head>
<body>

<div class="dash-layout">

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <svg width="28" height="28" viewBox="0 0 40 40" fill="none"><circle cx="20" cy="20" r="8" fill="none" stroke="#00e5ff" stroke-width="1.5"/><ellipse cx="20" cy="20" rx="18" ry="7" fill="none" stroke="#7c4dff" stroke-width="1" transform="rotate(30 20 20)"/><circle cx="20" cy="20" r="3" fill="#00e5ff"/></svg>
        <span class="sidebar-brand-text">DevSprint</span>
    </div>
    <div class="sidebar-admin-badge">🔒 Admin Mode</div>

    <div class="sidebar-section">Navigation</div>
    <a href="admin_dashboard.php" class="sidebar-link active"><span class="link-icon">🏠</span>Dashboard</a>
    <a href="index.php" class="sidebar-link" target="_blank"><span class="link-icon">🌐</span>View Live Site</a>

    <div class="sidebar-section">Manage</div>
    <a href="#add-section" class="sidebar-link"><span class="link-icon">➕</span>Add Hackathon</a>
    <a href="#apps-section" class="sidebar-link"><span class="link-icon">📋</span>Applications</a>
    <a href="#hack-section" class="sidebar-link"><span class="link-icon">🏆</span>All Hackathons</a>

    <div class="sidebar-logout">
        <a href="admin_logout.php" class="sidebar-link" style="color:#ef4444;border:1px solid rgba(239,68,68,0.15);"><span class="link-icon">🚪</span>Logout</a>
    </div>
</aside>

<!-- MAIN -->
<main class="dash-main">
    <!-- Header -->
    <div class="dash-header">
        <div>
            <div class="dash-title">Mission Control</div>
            <div class="dash-breadcrumb">Admin · Dashboard · DevSprint Platform</div>
        </div>
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <div class="status-dot"></div>
            <span style="font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:var(--comet-green);">Systems Online</span>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-card-icon">🏆</div>
            <span class="stat-card-num"><?= $total_hack ?></span>
            <span class="stat-card-label">Hackathons</span>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon">👥</div>
            <span class="stat-card-num"><?= $total_users ?></span>
            <span class="stat-card-label">Registered Users</span>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon">📋</div>
            <span class="stat-card-num"><?= $total_apps ?></span>
            <span class="stat-card-label">Applications</span>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon">🛸</div>
            <span class="stat-card-num"><?= $total_teams ?></span>
            <span class="stat-card-label">Active Teams</span>
        </div>
    </div>

    <!-- Main grid -->
    <div class="dash-grid">

        <!-- Add Hackathon Form -->
        <div id="add-section" class="d-card">
            <div class="d-card-title">➕ Add New Hackathon</div>
            <form action="admin_add_hackathon.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                <div class="form-group"><label>Title</label><input type="text" name="title" required placeholder="e.g. CodeFest 2026"></div>
                <div class="form-group"><label>Location</label><input type="text" name="location" required placeholder="e.g. Bangalore / Online"></div>
                <div class="form-group"><label>Start Date</label><input type="date" name="date_start" required></div>
                <div class="form-group"><label>End Date</label><input type="date" name="date_end" required></div>
                <div class="form-group"><label>Prize Pool</label><input type="text" name="prize_pool" required placeholder="e.g. ₹50,000"></div>
                <div class="form-group"><label>Description</label><textarea name="description" rows="4" required placeholder="Describe the hackathon..."></textarea></div>
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

        <!-- Right column -->
        <div>
            <!-- Applications -->
            <div id="apps-section" class="d-card" style="margin-bottom:1.5rem;">
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
                            <?php if($applications && $applications->num_rows > 0): while($a = $applications->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span style="color:var(--text-bright);font-weight:600;"><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></span><br>
                                    <small style="font-family:'JetBrains Mono',monospace;font-size:0.68rem;color:var(--text-dim);"><?= htmlspecialchars($a['team_name'] ? '🛸 Team: '.$a['team_name'] : '👤 Individual') ?></small>
                                </td>
                                <td style="font-family:'JetBrains Mono',monospace;font-size:0.78rem;"><?= htmlspecialchars($a['email']) ?></td>
                                <td><?= htmlspecialchars($a['title']) ?></td>
                                <td style="font-family:'JetBrains Mono',monospace;font-size:0.78rem;"><?= date('M d, Y', strtotime($a['applied_at'])) ?></td>
                                <td>
                                    <?php
                                    $st = $a['status'];
                                    $bc = $st==='Accepted' ? 'badge-accepted' : ($st==='Rejected' ? 'badge-rejected' : 'badge-pending');
                                    ?>
                                    <span class="badge <?= $bc ?>"><?= htmlspecialchars($st) ?></span>
                                </td>
                                <td>
                                    <?php if($a['team_name']): ?>
                                        <a href="admin_view_team.php?app_id=<?= $a['id'] ?>" class="team-view-btn">View Team</a>
                                    <?php else: ?>
                                        <div style="display:flex;gap:0.4rem;">
                                            <form action="admin_update_application.php" method="POST" style="display:inline;margin:0;">
                                                <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                                                <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
                                                <input type="hidden" name="status" value="Accepted">
                                                <button type="submit" class="action-accept-btn" title="Accept">✓</button>
                                            </form>
                                            <form action="admin_update_application.php" method="POST" style="display:inline;margin:0;">
                                                <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                                                <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
                                                <input type="hidden" name="status" value="Rejected">
                                                <button type="submit" class="action-reject-btn" title="Reject">✕</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-dim);">No applications yet.</td></tr>
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
                            <tr><th>Title</th><th>Location</th><th>Start Date</th><th>Type</th></tr>
                        </thead>
                        <tbody>
                            <?php if($hackathons && $hackathons->num_rows > 0): while($h = $hackathons->fetch_assoc()): ?>
                            <tr>
                                <td style="color:var(--text-bright);font-weight:600;"><?= htmlspecialchars($h['title']) ?></td>
                                <td><?= htmlspecialchars($h['location']) ?></td>
                                <td style="font-family:'JetBrains Mono',monospace;font-size:0.8rem;"><?= htmlspecialchars($h['date_start']) ?></td>
                                <td><span class="badge badge-member" style="font-size:0.68rem;"><?= htmlspecialchars($h['application_type']) ?></span></td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--text-dim);">No hackathons found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

</div><!-- .dash-layout -->
</body>
</html>
