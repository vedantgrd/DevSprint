<?php require_once 'csrf.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header("Location: login_view.php");
    exit();
}
require_once 'db_connect.php';
$user_id = $_SESSION['user_id'];

// Get all hackathons for create team dropdown
$hackathons_res = $conn->query("SELECT id, title FROM hackathons WHERE application_type IN ('Both', 'Team')");
$hackathons_list = [];
if ($hackathons_res) {
    while($row = $hackathons_res->fetch_assoc()) $hackathons_list[] = $row;
}

// Get user's teams
$stmt = $conn->prepare("SELECT t.id, t.name, t.leader_id, tm.status FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE tm.user_id = ? AND tm.status != 'Invited'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_teams = $stmt->get_result();
$stmt->close();

// Fetch user's invitations
$invites_stmt = $conn->prepare("SELECT t.id as team_id, t.name FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE tm.user_id = ? AND tm.status = 'Invited'");
$invites_stmt->bind_param("i", $user_id);
$invites_stmt->execute();
$invitations = $invites_stmt->get_result();
$invites_stmt->close();

// Get teams to explore
$explore_stmt = $conn->prepare("SELECT t.id, t.name, u.first_name, u.last_name FROM teams t JOIN users u ON t.leader_id = u.id WHERE t.id NOT IN (SELECT team_id FROM team_members WHERE user_id = ?) ORDER BY t.created_at DESC LIMIT 15");
$explore_stmt->bind_param("i", $user_id);
$explore_stmt->execute();
$explore_teams = $explore_stmt->get_result();
$explore_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Teams | DevSprint · Squad Control</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<style>
.teams-layout { display:grid;grid-template-columns:340px 1fr;gap:1.5rem;max-width:1200px;margin:0 auto;padding:2rem; }
@media(max-width:900px){ .teams-layout{grid-template-columns:1fr;} }

.t-card { background:rgba(255,255,255,0.02);border:1px solid rgba(79,195,247,0.1);border-radius:var(--radius-lg);padding:1.75rem;margin-bottom:1.5rem; }
.t-card:last-child { margin-bottom:0; }
.t-card-title { font-family:'Orbitron',monospace;font-size:1rem;font-weight:700;color:var(--text-bright);margin-bottom:1.2rem;padding-bottom:0.75rem;border-bottom:1px solid rgba(79,195,247,0.08);display:flex;align-items:center;gap:0.6rem; }

/* My team items */
.team-row {
    background:rgba(0,0,0,0.12);border:1px solid rgba(79,195,247,0.08);
    border-radius:var(--radius-md);padding:1.2rem;margin-bottom:0.85rem;
    transition:border-color 0.3s;
}
.team-row:hover { border-color:rgba(79,195,247,0.2); }
.team-row-header { display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem; }
.team-name { font-weight:700;color:var(--text-bright);font-size:1.05rem;display:flex;align-items:center;gap:0.5rem; }
.team-apply-bar {
    background:rgba(255,255,255,0.03);border-top:1px solid rgba(79,195,247,0.08);
    padding:0.75rem 0 0;margin-top:0.75rem;
    display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;
}
.team-select { flex:1;min-width:160px;padding:0.65rem 0.85rem;background:rgba(255,255,255,0.03);border:1px solid rgba(79,195,247,0.12);border-radius:var(--radius-sm);color:var(--text-bright);font-family:'Syne',sans-serif;font-size:0.85rem;outline:none; }
.team-select option { background:var(--deep); }

/* Explore teams */
.explore-list { max-height:420px;overflow-y:auto;padding-right:6px; }
.explore-list::-webkit-scrollbar { width:4px; }
.explore-list::-webkit-scrollbar-track { background:transparent; }
.explore-list::-webkit-scrollbar-thumb { background:rgba(79,195,247,0.2);border-radius:2px; }

.explore-row {
    background:rgba(0,0,0,0.1);border:1px solid rgba(124,77,255,0.1);
    border-radius:var(--radius-md);padding:1rem;margin-bottom:0.75rem;
    transition:border-color 0.3s;
}
.explore-row:hover { border-color:rgba(124,77,255,0.3); }
.explore-team-name { font-weight:700;color:var(--text-bright);margin-bottom:0.25rem;font-size:0.95rem; }
.explore-leader { font-family:'JetBrains Mono',monospace;font-size:0.72rem;color:var(--text-dim);margin-bottom:0.75rem; }

/* Invitation items */
.invite-row {
    background:rgba(245,158,11,0.05);border:1px solid rgba(245,158,11,0.2);
    border-radius:var(--radius-md);padding:1rem;margin-bottom:0.75rem;
    display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;
}
.invite-name { font-weight:700;color:#f59e0b;font-size:0.95rem; }
.invite-actions { display:flex;gap:0.5rem;flex-shrink:0; }

/* CTA submit */
.squad-btn {
    width:100%;padding:0.85rem;
    background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));
    color:var(--void);border:none;border-radius:var(--radius-sm);
    font-family:'Orbitron',monospace;font-size:0.78rem;font-weight:700;
    letter-spacing:0.06em;cursor:pointer;transition:all 0.3s;position:relative;overflow:hidden;
}
.squad-btn::before { content:'';position:absolute;inset:0;background:linear-gradient(135deg,var(--pulsar-violet),var(--plasma-cyan));opacity:0;transition:opacity 0.3s; }
.squad-btn:hover::before { opacity:1; }
.squad-btn:hover { transform:translateY(-2px); }
.squad-btn span { position:relative;z-index:1; }

.squad-btn-sm {
    padding:0.5rem 0.9rem;font-size:0.72rem;
    background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));
    color:var(--void);border:none;border-radius:var(--radius-sm);
    font-family:'Orbitron',monospace;font-weight:700;cursor:pointer;
    transition:all 0.2s;
}
.squad-btn-sm:hover { opacity:0.85; }
.squad-btn-sm.danger { background:linear-gradient(135deg,#ef4444,#b91c1c);color:#fff; }

.empty-teams { text-align:center;padding:2rem 0;color:var(--text-dim);font-size:0.9rem; }
.empty-teams .e-icon { font-size:2.5rem;display:block;opacity:0.4;margin-bottom:1rem; }
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
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="teams.php" class="active">Teams</a></li>
                <li><a href="matchmaking.php">Find Teammates</a></li>
                <li><a href="profile.php">My Profile</a></li>
                <li><a href="logout.php" class="nav-btn nav-btn-danger">Logout</a></li>
            <?php else: ?>
                <li><a href="login_view.php" class="nav-btn">Launch →</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="page-wrapper">
    <div style="max-width:1200px;margin:0 auto;padding:4rem 2rem 1rem;">
        <div class="section-label">Squad Control</div>
        <h1 class="section-title">My Teams</h1>
        <p class="section-desc" style="max-width:500px;margin-bottom:1rem;">Form your crew, send invites, apply to hackathons together, and dominate the leaderboard.</p>
    </div>

    <div class="teams-layout">
        <!-- LEFT COLUMN -->
        <div>
            <!-- Create Team -->
            <div class="t-card reveal">
                <div class="t-card-title">🛸 Create a Squad</div>
                <form action="create_team.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                    <div class="form-group">
                        <label>Team Name</label>
                        <input type="text" name="team_name" required placeholder="e.g. Codebreakers ⚡">
                    </div>
                    <button type="submit" class="squad-btn"><span>⚡ LAUNCH TEAM</span></button>
                </form>
            </div>

            <!-- Explore Teams -->
            <div class="t-card reveal d2">
                <div class="t-card-title">🔭 Explore Teams</div>
                <p style="color:var(--text-dim);font-size:0.83rem;margin-bottom:1rem;">Find an existing squad and request to join their mission.</p>
                <?php if($explore_teams && $explore_teams->num_rows > 0): ?>
                <div class="explore-list">
                    <?php while($et = $explore_teams->fetch_assoc()): ?>
                    <div class="explore-row">
                        <div class="explore-team-name"><?= htmlspecialchars($et['name']) ?></div>
                        <div class="explore-leader">👤 Led by <?= htmlspecialchars($et['first_name'] . ' ' . $et['last_name']) ?></div>
                        <form action="team_action.php" method="POST" style="margin:0;">
                            <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                            <input type="hidden" name="action" value="request_join">
                            <input type="hidden" name="team_id" value="<?= $et['id'] ?>">
                            <button type="submit" class="squad-btn" style="padding:0.5rem 0.9rem;font-size:0.72rem;width:100%;"><span>Request to Join →</span></button>
                        </form>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                    <p class="empty-teams"><span class="e-icon">🌌</span>No public teams available right now.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="reveal d1">
            <!-- Invitations -->
            <?php if($invitations && $invitations->num_rows > 0): ?>
            <div class="t-card" style="border-color:rgba(245,158,11,0.2);">
                <div class="t-card-title" style="color:#f59e0b;">✨ Pending Invitations</div>
                <?php while($inv = $invitations->fetch_assoc()): ?>
                <div class="invite-row">
                    <div class="invite-name">🛸 <?= htmlspecialchars($inv['name']) ?></div>
                    <div class="invite-actions">
                        <form action="team_action.php" method="POST" style="margin:0;">
                            <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                            <input type="hidden" name="action" value="accept_invite">
                            <input type="hidden" name="team_id" value="<?= $inv['team_id'] ?>">
                            <button type="submit" class="squad-btn-sm">Accept ✓</button>
                        </form>
                        <form action="team_action.php" method="POST" style="margin:0;">
                            <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                            <input type="hidden" name="action" value="decline_invite">
                            <input type="hidden" name="team_id" value="<?= $inv['team_id'] ?>">
                            <button type="submit" class="squad-btn-sm danger">Decline ✕</button>
                        </form>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>

            <!-- My Teams List -->
            <div class="t-card">
                <div class="t-card-title">🏆 My Squads</div>
                <?php if ($my_teams && $my_teams->num_rows > 0): ?>
                    <?php while($team = $my_teams->fetch_assoc()): ?>
                    <div class="team-row">
                        <div class="team-row-header">
                            <div class="team-name">
                                <?= htmlspecialchars($team['name']) ?>
                                <?php if($team['leader_id'] == $user_id): ?>
                                    <span class="badge badge-leader">Leader</span>
                                <?php else: ?>
                                    <span class="badge badge-member"><?= htmlspecialchars($team['status']) ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="team_details.php?id=<?= $team['id'] ?>" class="btn btn-ghost btn-sm"><span>View →</span></a>
                        </div>

                        <?php if($team['leader_id'] == $user_id && !empty($hackathons_list)): ?>
                        <div class="team-apply-bar">
                            <form action="apply.php" method="POST" style="margin:0;display:flex;gap:0.6rem;flex:1;flex-wrap:wrap;">
                                <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                                <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                                <select name="hackathon_id" required class="team-select">
                                    <option value="">— Apply to a Hackathon —</option>
                                    <?php foreach($hackathons_list as $h): ?>
                                        <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="squad-btn-sm"><span>Apply Now</span></button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-teams">
                        <span class="e-icon">👾</span>
                        You're not in any teams yet. Create one or join an existing squad!
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- FOOTER -->
<footer>
    <div class="footer-grid">
        <div class="footer-brand-col">
            <span class="nav-brand-text">DevSprint</span>
            <p>India's premier hackathon discovery platform. Launch your ideas into orbit.</p>
            <div class="footer-status"><div class="status-dot"></div> All systems operational</div>
        </div>
        <div class="footer-col"><h4>Navigate</h4><ul>
            <li><a href="hackathons.php">Hackathons</a></li>
            <li><a href="matchmaking.php">Find Teammates</a></li>
            <li><a href="teams.php">My Teams</a></li>
        </ul></div>
        <div class="footer-col"><h4>Account</h4><ul>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul></div>
        <div class="footer-col"><h4>Company</h4><ul>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul></div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 DevSprint · Build faster. Compete smarter.</p>
        <p>Crafted somewhere in the cosmos 🚀</p>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>
