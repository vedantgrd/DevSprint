<?php require_once 'csrf.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header("Location: login_view.php");
    exit();
}
require_once 'db_connect.php';

$user_id = intval($_SESSION['user_id']);

// Mark all as read upon opening inbox
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");

// Fetch Notifications
$notif_stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notifications = $notif_stmt->get_result();
$notif_stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox & Notifications | DevSprint</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <style>
        .page-wrapper { padding-top: 6rem; padding-bottom: 4rem; max-width: 900px; margin: 0 auto; width: 90%; }
        
        .inbox-header {
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(79,195,247,0.15);
            padding-bottom: 1rem;
        }
        .inbox-title {
            font-family: 'Orbitron', monospace;
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            background: linear-gradient(90deg, var(--plasma-cyan), var(--pulsar-violet));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .inbox-subtitle {
            font-family: 'JetBrains Mono', monospace;
            color: var(--text-dim);
            font-size: 0.85rem;
            letter-spacing: 0.05em;
        }

        .notif-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(79,195,247,0.1);
            border-left: 4px solid var(--plasma-cyan);
            border-radius: var(--radius-md);
            padding: 1.5rem 2rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .notif-card:hover { border-color: rgba(79,195,247,0.3); transform: translateX(5px); }
        .notif-card::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
            background: linear-gradient(180deg, var(--plasma-cyan), var(--pulsar-violet));
            opacity: 0.5; transition: opacity 0.3s;
        }
        .notif-card:hover::before { opacity: 1; }

        .notif-title { font-family: 'Orbitron', monospace; font-size: 1.1rem; color: var(--text-bright); margin-bottom: 0.75rem; letter-spacing: 0.02em; display:flex; align-items:center; gap:0.5rem; }
        .notif-body { font-size: 0.9rem; color: var(--text-dim); line-height: 1.6; margin-bottom: 1rem; }
        .notif-meta { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: var(--text-dim); border-top: 1px solid rgba(79,195,247,0.08); padding-top: 0.75rem; display:flex; justify-content:space-between; }
        
        .empty-inbox { text-align: center; padding: 4rem 0; }
        .empty-icon { font-size: 3rem; opacity: 0.5; margin-bottom: 1rem; display: block; }
        .empty-text { font-family: 'Syne', sans-serif; color: var(--text-dim); font-size: 1.1rem; }
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
            <li><a href="matchmaking.php">Find Teammates</a></li>
            <li><a href="profile.php">My Profile</a></li>
            <li><a href="inbox.php" class="active" style="color:var(--plasma-cyan);">🔔 Inbox</a></li>
            <li><a href="logout.php" class="nav-btn nav-btn-danger">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="page-wrapper">
    <div class="inbox-header">
        <h1 class="inbox-title">Mission Inbox</h1>
        <div class="inbox-subtitle">Communique from HQ and Admin Updates</div>
    </div>

    <div class="inbox-container">
        <?php if ($notifications && $notifications->num_rows > 0): ?>
            <?php while($n = $notifications->fetch_assoc()): ?>
                <div class="notif-card" style="<?= !$n['is_read'] ? 'box-shadow: 0 0 15px rgba(0,229,255,0.1);' : '' ?>">
                    <div class="notif-title">
                        <span>💬</span> 
                        <?= htmlspecialchars($n['title']) ?>
                    </div>
                    <div class="notif-body">
                        <?= nl2br(htmlspecialchars($n['message'])) ?>
                    </div>
                    <div class="notif-meta">
                        <span>Received</span>
                        <span><?= date('D, M d Y - H:i', strtotime($n['created_at'])) ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-inbox">
                <span class="empty-icon">📭</span>
                <p class="empty-text">Your inbox is completely clear. No new transmissions.</p>
                <a href="hackathons.php" class="btn btn-ghost" style="margin-top:2rem;">Explore Hackathons</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
