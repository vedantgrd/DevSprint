<?php require_once '../includes/csrf.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in to apply.'); window.location.href='../login/login_view.php';</script>";
    exit();
}
require_once '../includes/db_connect.php';
$user_id = $_SESSION['user_id']; // FIX: was missing in original
$hack_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get hackathon details & type
$stmt = $conn->prepare("SELECT title, application_type, location, date_start, date_end, prize_pool FROM hackathons WHERE id = ?");
$stmt->bind_param("i", $hack_id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows === 0) die("Hackathon not found.");
$hackathon = $res->fetch_assoc();
$stmt->close();

// Fetch user's led teams
$teams_stmt = $conn->prepare("SELECT id, name FROM teams WHERE leader_id = ?");
$teams_stmt->bind_param("i", $user_id);
$teams_stmt->execute();
$led_teams = $teams_stmt->get_result();
$teams_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply: <?= htmlspecialchars($hackathon['title']) ?> | DevSprint</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/styles.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<style>
.apply-layout {
    max-width:900px;margin:0 auto;padding:2rem;
    display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;
}
@media(max-width:700px){ .apply-layout{grid-template-columns:1fr;} }

/* Hackathon overview card */
.hack-overview {
    grid-column:1/-1;
    background:linear-gradient(135deg,rgba(13,27,75,0.5) 0%,rgba(26,5,51,0.5) 100%);
    border:1px solid rgba(79,195,247,0.15);border-radius:var(--radius-lg);
    padding:2.5rem;position:relative;overflow:hidden;
}
.hack-overview::before {
    content:'';position:absolute;top:0;left:0;right:0;height:2px;
    background:linear-gradient(90deg,var(--plasma-cyan),var(--pulsar-violet),var(--nova-orange));
}
.hack-h-badge {
    display:inline-flex;align-items:center;gap:6px;
    background:rgba(255,109,0,0.12);border:1px solid rgba(255,109,0,0.3);
    color:var(--nova-orange);padding:0.3rem 0.9rem;border-radius:40px;
    font-size:0.72rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:1rem;
    font-family:'JetBrains Mono',monospace;
}
.hack-h-title {
    font-family:'Orbitron',monospace;font-size:clamp(1.5rem,3vw,2.2rem);font-weight:900;
    color:var(--text-bright);margin-bottom:1.5rem;
}
.hack-h-meta { display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem; }
.hack-h-item { background:rgba(255,255,255,0.02);border:1px solid rgba(79,195,247,0.08);border-radius:var(--radius-sm);padding:1rem; }
.hack-h-key { font-family:'JetBrains Mono',monospace;font-size:0.62rem;letter-spacing:0.15em;color:var(--text-dim);text-transform:uppercase;margin-bottom:0.3rem; }
.hack-h-val { font-size:0.92rem;font-weight:700;color:var(--text-bright); }

/* Apply option cards */
.apply-option {
    background:rgba(255,255,255,0.02);border:1px solid rgba(79,195,247,0.1);
    border-radius:var(--radius-lg);padding:2rem;
    transition:all 0.4s;position:relative;overflow:hidden;
}
.apply-option::before {
    content:'';position:absolute;top:0;left:0;right:0;height:2px;
    background:linear-gradient(90deg,var(--plasma-cyan),var(--pulsar-violet));
    transform:scaleX(0);transform-origin:left;transition:transform 0.4s;
}
.apply-option:hover::before { transform:scaleX(1); }
.apply-option:hover { border-color:rgba(79,195,247,0.25);background:rgba(79,195,247,0.02); }
.option-icon { font-size:2.5rem;margin-bottom:1rem;display:block; }
.option-title { font-family:'Orbitron',monospace;font-size:1.1rem;font-weight:700;color:var(--text-bright);margin-bottom:0.5rem; }
.option-desc { color:var(--text-dim);font-size:0.85rem;line-height:1.6;margin-bottom:1.5rem; }

.team-select {
    width:100%;padding:0.85rem 1rem;
    background:rgba(255,255,255,0.03);border:1px solid rgba(79,195,247,0.15);
    border-radius:var(--radius-sm);color:var(--text-bright);
    font-family:'Syne',sans-serif;font-size:0.92rem;
    margin-bottom:1rem;outline:none;transition:border-color 0.3s;
}
.team-select:focus { border-color:var(--plasma-cyan); }
.team-select option { background:var(--deep); }

.apply-btn {
    width:100%;padding:0.9rem;
    background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));
    color:var(--void);border:none;border-radius:var(--radius-sm);
    font-family:'Orbitron',monospace;font-size:0.82rem;font-weight:700;
    letter-spacing:0.06em;cursor:pointer;transition:all 0.3s;position:relative;overflow:hidden;
}
.apply-btn::before { content:'';position:absolute;inset:0;background:linear-gradient(135deg,var(--pulsar-violet),var(--plasma-cyan));opacity:0;transition:opacity 0.3s; }
.apply-btn:hover::before { opacity:1; }
.apply-btn:hover { transform:translateY(-2px);box-shadow:0 0 30px rgba(0,229,255,0.25); }
.apply-btn span { position:relative;z-index:1; }

.warning-box {
    background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);
    border-radius:var(--radius-sm);padding:0.85rem 1rem;
    color:#f59e0b;font-size:0.82rem;font-family:'JetBrains Mono',monospace;
    margin-bottom:1rem;line-height:1.5;
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
                <li><a href="../profile/profile.php">My Profile</a></li>
                <li><a href="../actions/logout.php" class="nav-btn nav-btn-danger">Logout</a></li>
            <?php else: ?>
                <li><a href="../login/login_view.php" class="nav-btn">Launch →</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="page-wrapper">
    <div class="apply-layout">

        <!-- Hackathon Overview -->
        <div class="hack-overview reveal">
            <div class="hack-h-badge">🚀 Apply to Mission</div>
            <h1 class="hack-h-title"><?= htmlspecialchars($hackathon['title']) ?></h1>
            <div class="hack-h-meta">
                <div class="hack-h-item"><div class="hack-h-key">📍 Location</div><div class="hack-h-val"><?= htmlspecialchars($hackathon['location']) ?></div></div>
                <div class="hack-h-item"><div class="hack-h-key">📅 Dates</div><div class="hack-h-val"><?= date('M d', strtotime($hackathon['date_start'])) ?> – <?= date('M d, Y', strtotime($hackathon['date_end'])) ?></div></div>
                <div class="hack-h-item"><div class="hack-h-key">💰 Prize Pool</div><div class="hack-h-val" style="color:var(--comet-green);"><?= htmlspecialchars($hackathon['prize_pool']) ?></div></div>
                <div class="hack-h-item"><div class="hack-h-key">🎯 Format</div><div class="hack-h-val"><?= htmlspecialchars($hackathon['application_type']) ?></div></div>
            </div>
        </div>

        <?php if($hackathon['application_type'] === 'Individual' || $hackathon['application_type'] === 'Both'): ?>
        <!-- Individual Apply -->
        <div class="apply-option reveal d1">
            <span class="option-icon">👤</span>
            <div class="option-title">Apply Individually</div>
            <p class="option-desc">Participate on your own. You stay fully in control and are fully eligible for all prizes.</p>
            <form action="../actions/apply.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                <input type="hidden" name="hackathon_id" value="<?= $hack_id ?>">
                <button class="apply-btn" type="submit"><span>⚡ Apply as Individual</span></button>
            </form>
        </div>
        <?php endif; ?>

        <?php if($hackathon['application_type'] === 'Team' || $hackathon['application_type'] === 'Both'): ?>
        <!-- Team Apply -->
        <div class="apply-option reveal d2">
            <span class="option-icon">🛸</span>
            <div class="option-title">Apply With a Team</div>
            <p class="option-desc">Lead your squad into battle. Team applications unlock collaboration bonuses and extra tracks.</p>

            <?php
            // Re-fetch led teams for display
            $lt_stmt = $conn->prepare("SELECT id, name FROM teams WHERE leader_id = ?");
            $lt_stmt->bind_param("i", $user_id);
            $lt_stmt->execute();
            $lt_res = $lt_stmt->get_result();
            if ($lt_res && $lt_res->num_rows > 0):
            ?>
                <form action="../actions/apply.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                    <input type="hidden" name="hackathon_id" value="<?= $hack_id ?>">
                    <select name="team_id" required class="team-select">
                        <option value="">— Select your team —</option>
                        <?php while($t = $lt_res->fetch_assoc()): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <button class="apply-btn" type="submit"><span>🛸 Submit Team Application</span></button>
                </form>
            <?php else: ?>
                <div class="warning-box">⚠️ You must be a team leader to apply as a team. Create a team first.</div>
            <?php endif; ?>
            <?php $lt_stmt->close(); ?>

            <div style="display:flex;gap:0.75rem;margin-top:1rem;flex-wrap:wrap;">
                <a href="../teams/teams.php" class="btn btn-ghost btn-sm" style="flex:1;justify-content:center;"><span>Manage Teams</span></a>
                <a href="../teams/matchmaking.php" class="btn btn-ghost btn-sm" style="flex:1;justify-content:center;"><span>Find Teammates</span></a>
            </div>
        </div>
        <?php endif; ?>

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
            <li><a href="../hackathons/hackathons.php">Hackathons</a></li>
            <li><a href="../teams/teams.php">My Teams</a></li>
            <li><a href="../teams/matchmaking.php">Find Teammates</a></li>
        </ul></div>
        <div class="footer-col"><h4>Account</h4><ul>
            <li><a href="../profile/profile.php">Profile</a></li>
            <li><a href="../actions/logout.php">Logout</a></li>
        </ul></div>
        <div class="footer-col"><h4>Company</h4><ul>
            <li><a href="../about/about.php">About</a></li>
            <li><a href="../contact/contact.php">Contact</a></li>
        </ul></div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 DevSprint · Build faster. Compete smarter.</p>
        <p>Crafted somewhere in the cosmos 🚀</p>
    </div>
</footer>

<script src="../js/script.js"></script>
</body>
</html>
