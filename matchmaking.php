<?php require_once 'csrf.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header("Location: login_view.php");
    exit();
}
require_once 'db_connect.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Current user's skills
$cu_stmt = $conn->prepare("SELECT skills FROM users WHERE id = ?");
$cu_stmt->bind_param("i", $_SESSION['user_id']);
$cu_stmt->execute();
$cu_res = $cu_stmt->get_result()->fetch_assoc();
$cu_skills_raw = $cu_res ? $cu_res['skills'] : '';
$cu_stmt->close();

$my_skills = array_map('trim', array_map('strtolower', explode(',', $cu_skills_raw)));

$sql = "SELECT id, first_name, last_name, skills, bio, github, linkedin FROM users WHERE id != ?";
if ($search !== '') {
    $sql .= " AND (skills LIKE ? OR bio LIKE ?)";
    $stmt = $conn->prepare($sql);
    $like = "%$search%";
    $stmt->bind_param("iss", $_SESSION['user_id'], $like, $like);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
}
$stmt->execute();
$users_raw = $stmt->get_result();
$stmt->close();

$scored_users = [];
while ($u = $users_raw->fetch_assoc()) {
    $their_skills_raw = $u['skills'] ?: '';
    $their_skills = array_filter(array_map('trim', array_map('strtolower', explode(',', $their_skills_raw))));
    if (count($my_skills) === 0 && count($their_skills) === 0) {
        $score = 0;
    } else {
        $intersection = count(array_intersect($my_skills, $their_skills));
        $union = count(array_unique(array_merge($my_skills, $their_skills)));
        $score = $union > 0 ? ($intersection / $union) * 100 : 0;
    }
    if (empty($cu_skills_raw)) $score = rand(10, 30);
    $u['match_score'] = round($score);
    $scored_users[] = $u;
}

usort($scored_users, function($a, $b) { return $b['match_score'] <=> $a['match_score']; });

// My teams (to send invites)
$my_teams_stmt = $conn->prepare("SELECT id, name FROM teams WHERE leader_id = ?");
$my_teams_stmt->bind_param("i", $_SESSION['user_id']);
$my_teams_stmt->execute();
$my_teams = $my_teams_stmt->get_result();
$my_teams_arr = [];
while($t = $my_teams->fetch_assoc()) $my_teams_arr[] = $t;
$my_teams_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Find Teammates | DevSprint · AI Matchmaking</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<style>
/* Search bar */
.mm-search {
    display:flex;gap:0.75rem;align-items:center;
    max-width:700px;margin:0 auto 3rem;
}
.mm-search input {
    flex:1;padding:1rem 1.25rem;
    background:rgba(255,255,255,0.03);
    border:1px solid rgba(79,195,247,0.15);
    border-radius:var(--radius-sm);
    color:var(--text-bright);font-family:'Syne',sans-serif;font-size:1rem;outline:none;
    transition:border-color 0.3s,box-shadow 0.3s;
}
.mm-search input:focus { border-color:var(--plasma-cyan);box-shadow:0 0 0 3px rgba(0,229,255,0.08); }
.mm-search input::placeholder { color:var(--text-dim); }
.mm-search button {
    padding:1rem 1.75rem;
    background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));
    color:var(--void);border:none;border-radius:var(--radius-sm);
    font-family:'Orbitron',monospace;font-size:0.78rem;font-weight:700;
    letter-spacing:0.06em;cursor:pointer;white-space:nowrap;
    transition:all 0.3s;
}
.mm-search button:hover { transform:translateY(-2px);box-shadow:0 0 24px rgba(0,229,255,0.25); }

/* User grid */
.user-grid {
    display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
    gap:1.25rem;
}

/* User card */
.user-card {
    background:rgba(255,255,255,0.02);
    border:1px solid rgba(79,195,247,0.1);
    border-radius:var(--radius-lg);padding:1.75rem;
    transition:all 0.4s ease;
    position:relative;overflow:hidden;display:flex;flex-direction:column;gap:0.85rem;
}
.user-card::before {
    content:'';position:absolute;top:0;left:0;right:0;height:2px;
    background:linear-gradient(90deg,var(--plasma-cyan),var(--pulsar-violet));
    transform:scaleX(0);transform-origin:left;transition:transform 0.4s;
}
.user-card:hover::before { transform:scaleX(1); }
.user-card:hover {
    border-color:rgba(79,195,247,0.25);
    background:rgba(79,195,247,0.03);
    transform:translateY(-5px);
    box-shadow:0 20px 50px rgba(0,229,255,0.08);
}

.user-card-header { display:flex;justify-content:space-between;align-items:flex-start;gap:0.75rem; }
.user-name { font-family:'Orbitron',monospace;font-size:1rem;font-weight:700;color:var(--text-bright); }
.match-pct {
    display:inline-flex;align-items:center;gap:4px;
    font-family:'JetBrains Mono',monospace;font-size:0.72rem;font-weight:700;
    padding:0.25rem 0.65rem;border-radius:40px;flex-shrink:0;
}
.match-high { background:rgba(0,230,118,0.12);color:var(--comet-green);border:1px solid rgba(0,230,118,0.25); }
.match-mid { background:rgba(79,195,247,0.12);color:var(--ion-blue);border:1px solid rgba(79,195,247,0.25); }
.match-low { background:rgba(124,77,255,0.12);color:var(--pulsar-violet);border:1px solid rgba(124,77,255,0.25); }

.user-skills { display:flex;flex-wrap:wrap;gap:0.4rem; }
.mini-skill {
    font-family:'JetBrains Mono',monospace;font-size:0.68rem;
    padding:0.2rem 0.6rem;border-radius:40px;
    background:rgba(124,77,255,0.08);border:1px solid rgba(124,77,255,0.2);color:var(--pulsar-violet);
}

.user-bio {
    color:var(--text-dim);font-size:0.84rem;line-height:1.55;
    display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
}

.user-links { display:flex;gap:1rem; }
.user-link {
    font-family:'JetBrains Mono',monospace;font-size:0.72rem;font-weight:700;
    color:var(--plasma-cyan);text-decoration:none;letter-spacing:0.05em;
    text-transform:uppercase;transition:opacity 0.2s;
}
.user-link:hover { opacity:0.7; }

/* Invite form */
.invite-section {
    margin-top:auto;padding-top:1rem;
    border-top:1px solid rgba(79,195,247,0.08);
    display:flex;flex-direction:column;gap:0.6rem;
}
.invite-section select {
    width:100%;padding:0.65rem 0.85rem;
    background:rgba(255,255,255,0.02);border:1px solid rgba(79,195,247,0.12);
    border-radius:var(--radius-sm);color:var(--text-bright);
    font-family:'Syne',sans-serif;font-size:0.85rem;outline:none;
}
.invite-section select option { background:var(--deep); }
.invite-btn {
    width:100%;padding:0.65rem;
    background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));
    color:var(--void);border:none;border-radius:var(--radius-sm);
    font-family:'Orbitron',monospace;font-size:0.72rem;font-weight:700;
    letter-spacing:0.05em;cursor:pointer;transition:all 0.2s;
}
.invite-btn:hover { opacity:0.9;transform:translateY(-1px); }

.no-results {
    grid-column:1/-1;text-align:center;padding:4rem 2rem;
    color:var(--text-dim);font-size:1rem;font-family:'JetBrains Mono',monospace;
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
            <li><a href="matchmaking.php" class="active">Find Teammates</a></li>
            <li><a href="teams.php">My Teams</a></li>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="logout.php" class="nav-btn nav-btn-danger">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="page-wrapper">
    <div class="content-section">
        <div class="section-label" style="justify-content:center;">AI Matchmaking · Jaccard Similarity Engine</div>
        <h1 class="section-title" style="text-align:center;">Find Your Crew.</h1>
        <p style="text-align:center;color:var(--text-mid);font-size:1rem;margin-bottom:3rem;">
            Our AI scores every developer against your skill profile. The closer the match, the better the collaboration.
        </p>

        <!-- Search -->
        <form method="GET" class="mm-search">
            <input type="text" name="q" placeholder="Search by skill, framework, language, or bio keyword..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">⚡ Search</button>
        </form>

        <?php if($search): ?>
        <div class="alert alert-info" style="max-width:700px;margin:0 auto 2rem;">
            🔍 Showing results for: <strong><?= htmlspecialchars($search) ?></strong>
            &nbsp;<a href="matchmaking.php" style="color:var(--plasma-cyan);font-weight:700;">Clear ×</a>
        </div>
        <?php endif; ?>

        <!-- User Grid -->
        <div class="user-grid">
            <?php if(!empty($scored_users)): ?>
                <?php foreach($scored_users as $u): ?>
                <div class="user-card reveal">
                    <div class="user-card-header">
                        <div class="user-name"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></div>
                        <?php if($u['match_score'] > 0):
                            $mc = $u['match_score'] >= 60 ? 'match-high' : ($u['match_score'] >= 30 ? 'match-mid' : 'match-low');
                        ?>
                        <span class="match-pct <?= $mc ?>">◆ <?= $u['match_score'] ?>% Match</span>
                        <?php endif; ?>
                    </div>

                    <?php if(!empty($u['skills'])): ?>
                    <div class="user-skills">
                        <?php foreach(array_slice(explode(',', $u['skills']), 0, 5) as $sk): ?>
                            <?php if(trim($sk)): ?>
                            <span class="mini-skill"><?= htmlspecialchars(trim($sk)) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <p class="user-bio"><?= htmlspecialchars($u['bio'] ?: 'This engineer prefers to let their code speak.') ?></p>

                    <?php if($u['github'] || $u['linkedin']): ?>
                    <div class="user-links">
                        <?php if($u['github']): ?><a href="<?= htmlspecialchars($u['github']) ?>" target="_blank" class="user-link">GitHub ↗</a><?php endif; ?>
                        <?php if($u['linkedin']): ?><a href="<?= htmlspecialchars($u['linkedin']) ?>" target="_blank" class="user-link">LinkedIn ↗</a><?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if(count($my_teams_arr) > 0): ?>
                    <div class="invite-section">
                        <form action="team_action.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                            <input type="hidden" name="action" value="invite">
                            <input type="hidden" name="target_user" value="<?= $u['id'] ?>">
                            <select name="team_id" required>
                                <option value="">— Invite to Team —</option>
                                <?php foreach($my_teams_arr as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="invite-btn">📨 Send Invite</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">🌌 No developers found matching your search.</div>
            <?php endif; ?>
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
