<?php require_once 'csrf.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}
require_once 'db_connect.php';

// Handle mark-as-read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $msg_id = (int)$_POST['msg_id'];
    $conn->query("UPDATE messages SET is_read = 1 WHERE id = $msg_id AND message_type = 'contact'");
    header("Location: admin_messages.php");
    exit();
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_msg'])) {
    $msg_id = (int)$_POST['msg_id'];
    $conn->query("DELETE FROM messages WHERE id = $msg_id AND message_type = 'contact'");
    header("Location: admin_messages.php");
    exit();
}

// Fetch all contact messages
$messages_query = "
    SELECT m.id, m.message, m.subject, m.sender_name, m.sender_email,
           m.is_read, m.created_at,
           u.first_name, u.last_name, u.email as user_email
    FROM messages m
    LEFT JOIN users u ON m.sender_id = u.id
    WHERE m.message_type = 'contact'
    ORDER BY m.is_read ASC, m.created_at DESC
";
$messages = $conn->query($messages_query);

$total_msgs  = $conn->query("SELECT COUNT(*) as c FROM messages WHERE message_type='contact'")->fetch_assoc()['c'] ?? 0;
$unread_msgs = $conn->query("SELECT COUNT(*) as c FROM messages WHERE message_type='contact' AND is_read=0")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messages | DevSprint · Admin</title>
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
.dash-layout { display: flex; min-height: 100vh; }

/* ── Sidebar ── */
.sidebar {
    width: 260px;
    flex-shrink: 0;
    background: rgba(2, 2, 15, 0.98);
    border-right: 1px solid rgba(79, 195, 247, 0.1);
    padding: 2rem 1.5rem;
    position: fixed;
    top: 0; left: 0;
    height: 100vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    z-index: 100;
}

.sidebar-brand {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 0.75rem; text-decoration: none;
}
.sidebar-brand-text {
    font-family: 'Orbitron', monospace;
    font-size: 1.1rem; font-weight: 900;
    background: linear-gradient(90deg, #00e5ff, #7c4dff);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.sidebar-admin-badge {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.62rem; letter-spacing: 0.15em; text-transform: uppercase;
    color: #ff6d00; border: 1px solid rgba(255,109,0,0.25);
    padding: 0.3rem 0.75rem; border-radius: 40px;
    margin-bottom: 2rem; display: inline-block;
    background: rgba(255,109,0,0.06);
}
.sidebar-section {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.62rem; letter-spacing: 0.2em; text-transform: uppercase;
    color: #7b8eb0; margin-bottom: 0.6rem; margin-top: 1.5rem;
}
.sidebar-link {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.65rem 0.85rem; border-radius: 6px;
    color: #7b8eb0; text-decoration: none;
    font-size: 0.85rem; font-weight: 600;
    transition: all 0.2s; margin-bottom: 0.2rem; letter-spacing: 0.03em;
}
.sidebar-link:hover, .sidebar-link.active {
    color: #e8f0ff; background: rgba(79,195,247,0.08);
}
.sidebar-link .link-icon { font-size: 1rem; width: 18px; text-align: center; flex-shrink: 0; }
.sidebar-logout {
    margin-top: auto; padding-top: 1.5rem;
    border-top: 1px solid rgba(79,195,247,0.08);
}

/* Unread badge on sidebar */
.sidebar-badge {
    margin-left: auto;
    background: rgba(255,61,87,0.2);
    color: #ff3d57;
    border: 1px solid rgba(255,61,87,0.4);
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.6rem; font-weight: 700;
    padding: 0.1rem 0.5rem; border-radius: 40px;
}

/* ── Main Content ── */
.dash-main {
    margin-left: 260px; padding: 2rem 2.5rem;
    min-height: 100vh; flex: 1; width: calc(100% - 260px);
}

/* ── Header ── */
.dash-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 2rem; padding-bottom: 1.5rem;
    border-bottom: 1px solid rgba(79,195,247,0.08);
}
.dash-title {
    font-family: 'Orbitron', monospace; font-size: 1.8rem;
    font-weight: 900; color: #e8f0ff; line-height: 1.1; margin-bottom: 0.4rem;
}
.dash-breadcrumb { font-family: 'JetBrains Mono', monospace; font-size: 0.72rem; color: #7b8eb0; }

.systems-status {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(0,230,118,0.06); border: 1px solid rgba(0,230,118,0.2);
    border-radius: 40px; flex-shrink: 0; margin-top: 0.25rem;
}
.status-dot {
    width: 7px; height: 7px; border-radius: 50%; background: #00e676;
    animation: statusBlink 2s ease-in-out infinite; flex-shrink: 0;
}
@keyframes statusBlink { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }

/* ── Stats ── */
.stats-row {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 1rem; margin-bottom: 2rem;
}
.stat-card {
    background: rgba(255,255,255,0.025); border: 1px solid rgba(79,195,247,0.12);
    border-radius: 14px; padding: 1.5rem; position: relative; overflow: hidden;
    transition: border-color 0.3s, transform 0.3s;
}
.stat-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, #00e5ff, #7c4dff);
    transform: scaleX(0); transform-origin: left; transition: transform 0.4s;
}
.stat-card:hover::before { transform: scaleX(1); }
.stat-card:hover { border-color: rgba(79,195,247,0.25); transform: translateY(-2px); }
.stat-card-icon { font-size: 1.5rem; margin-bottom: 0.75rem; display: block; }
.stat-card-num {
    font-family: 'Orbitron', monospace; font-size: 2.2rem; font-weight: 900;
    background: linear-gradient(135deg, #00e5ff, #7c4dff);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    display: block; margin-bottom: 0.35rem; line-height: 1;
}
.stat-card-label {
    font-family: 'JetBrains Mono', monospace; font-size: 0.68rem;
    letter-spacing: 0.12em; text-transform: uppercase; color: #7b8eb0;
}

/* ── Cards ── */
.d-card {
    background: rgba(255,255,255,0.02); border: 1px solid rgba(79,195,247,0.1);
    border-radius: 18px; padding: 1.75rem;
}
.d-card-title {
    font-family: 'Orbitron', monospace; font-size: 0.9rem; font-weight: 700;
    color: #e8f0ff; margin-bottom: 1.5rem; padding-bottom: 0.75rem;
    border-bottom: 1px solid rgba(79,195,247,0.08);
    display: flex; align-items: center; gap: 0.5rem;
}

/* ── Message Cards ── */
.messages-grid {
    display: flex; flex-direction: column; gap: 1rem;
}

.msg-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(79,195,247,0.1);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    transition: all 0.3s;
    position: relative;
}
.msg-card.unread {
    border-color: rgba(0,229,255,0.25);
    background: rgba(0,229,255,0.03);
}
.msg-card.unread::before {
    content: '';
    position: absolute; top: 0; left: 0;
    width: 3px; height: 100%;
    background: linear-gradient(180deg, #00e5ff, #7c4dff);
    border-radius: 3px 0 0 3px;
}
.msg-card:hover {
    border-color: rgba(79,195,247,0.3);
    transform: translateY(-1px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.msg-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 1rem; gap: 1rem; flex-wrap: wrap;
}
.msg-sender-info { flex: 1; }
.msg-sender-name {
    font-size: 1rem; font-weight: 700; color: #e8f0ff; margin-bottom: 0.25rem;
}
.msg-sender-email {
    font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: var(--plasma-cyan);
}
.msg-meta {
    display: flex; flex-direction: column; align-items: flex-end; gap: 0.4rem; flex-shrink: 0;
}
.msg-time {
    font-family: 'JetBrains Mono', monospace; font-size: 0.68rem; color: #7b8eb0;
    white-space: nowrap;
}

.msg-subject {
    font-family: 'Orbitron', monospace; font-size: 0.82rem; font-weight: 700;
    color: var(--pulsar-violet); margin-bottom: 0.75rem;
    letter-spacing: 0.04em;
}
.msg-subject-none {
    font-family: 'JetBrains Mono', monospace; font-size: 0.75rem;
    color: #7b8eb0; margin-bottom: 0.75rem; font-style: italic;
}

.msg-body {
    background: rgba(0,0,0,0.2); border: 1px solid rgba(79,195,247,0.08);
    border-radius: var(--radius-sm); padding: 1rem;
    color: #a8b8d8; font-size: 0.88rem; line-height: 1.7;
    white-space: pre-wrap; word-break: break-word;
    margin-bottom: 1rem;
}

.msg-actions {
    display: flex; gap: 0.5rem; flex-wrap: wrap;
}

.badge {
    display: inline-block; padding: 0.2rem 0.65rem; border-radius: 40px;
    font-size: 0.68rem; font-weight: 700;
    font-family: 'JetBrains Mono', monospace; letter-spacing: 0.05em;
}
.badge-unread { background: rgba(0,229,255,0.12); color: #00e5ff; border: 1px solid rgba(0,229,255,0.3); }
.badge-read   { background: rgba(0,230,118,0.12); color: #00e676; border: 1px solid rgba(0,230,118,0.3); }

/* ── Action Buttons ── */
.btn-mark-read {
    background: rgba(0,230,118,0.1); color: #00e676;
    border: 1px solid rgba(0,230,118,0.3);
    padding: 0.35rem 0.85rem; border-radius: 6px;
    font-size: 0.75rem; font-weight: 700; cursor: pointer;
    font-family: 'JetBrains Mono', monospace; transition: background 0.2s;
}
.btn-mark-read:hover { background: rgba(0,230,118,0.22); }

.btn-delete {
    background: rgba(239,68,68,0.1); color: #ef4444;
    border: 1px solid rgba(239,68,68,0.25);
    padding: 0.35rem 0.85rem; border-radius: 6px;
    font-size: 0.75rem; font-weight: 700; cursor: pointer;
    font-family: 'JetBrains Mono', monospace; transition: background 0.2s;
}
.btn-delete:hover { background: rgba(239,68,68,0.2); }

/* ── Empty State ── */
.empty-state {
    text-align: center; padding: 4rem 2rem;
    color: #7b8eb0;
}
.empty-state .empty-icon { font-size: 3.5rem; margin-bottom: 1rem; opacity: 0.5; }
.empty-state p { font-family: 'JetBrains Mono', monospace; font-size: 0.88rem; }

/* ── Filter Bar ── */
.filter-bar {
    display: flex; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap; align-items: center;
}
.filter-btn {
    padding: 0.45rem 1.1rem;
    border-radius: 40px; font-size: 0.78rem; font-weight: 700;
    font-family: 'JetBrains Mono', monospace; cursor: pointer;
    border: 1px solid rgba(79,195,247,0.2); background: transparent;
    color: #7b8eb0; transition: all 0.2s; text-decoration: none;
}
.filter-btn:hover, .filter-btn.active {
    background: rgba(0,229,255,0.1); color: #00e5ff;
    border-color: rgba(0,229,255,0.4);
}
.filter-btn.active-danger {
    background: rgba(255,61,87,0.1); color: #ff3d57;
    border-color: rgba(255,61,87,0.4);
}

/* ── Responsive ── */
@media (max-width: 900px) {
    .sidebar { position: relative; width: 100%; height: auto; }
    .dash-main { margin-left: 0; width: 100%; padding: 1.5rem; }
    .dash-layout { flex-direction: column; }
    .stats-row { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 560px) {
    .stats-row { grid-template-columns: 1fr 1fr; }
    .dash-main { padding: 1rem; }
    .msg-header { flex-direction: column; }
    .msg-meta { align-items: flex-start; }
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
    <a href="admin_dashboard.php" class="sidebar-link">
        <span class="link-icon">🏠</span> Dashboard
    </a>
    <a href="index.php" class="sidebar-link" target="_blank">
        <span class="link-icon">🌐</span> View Live Site
    </a>

    <div class="sidebar-section">Manage</div>
    <a href="admin_dashboard.php#add-section" class="sidebar-link">
        <span class="link-icon">➕</span> Add Hackathon
    </a>
    <a href="admin_dashboard.php#apps-section" class="sidebar-link">
        <span class="link-icon">📋</span> Applications
    </a>
    <a href="admin_dashboard.php#hack-section" class="sidebar-link">
        <span class="link-icon">🏆</span> All Hackathons
    </a>
    <a href="admin_messages.php" class="sidebar-link active">
        <span class="link-icon">📨</span> Messages
        <?php if ($unread_msgs > 0): ?>
            <span class="sidebar-badge"><?= $unread_msgs ?></span>
        <?php endif; ?>
    </a>

    <div class="sidebar-logout">
        <a href="admin_logout.php" class="sidebar-link" style="color:#ef4444; border:1px solid rgba(239,68,68,0.15); border-radius:6px;">
            <span class="link-icon">🚪</span> Logout
        </a>
    </div>
</aside>

<!-- ── MAIN CONTENT ── -->
<main class="dash-main">

    <!-- Header -->
    <div class="dash-header">
        <div>
            <div class="dash-title">Transmissions</div>
            <div class="dash-breadcrumb">Admin &nbsp;·&nbsp; Messages &nbsp;·&nbsp; DevSprint Platform</div>
        </div>
        <div class="systems-status">
            <div class="status-dot"></div>
            <span style="font-family:'JetBrains Mono',monospace; font-size:0.75rem; color:#00e676; white-space:nowrap;">Systems Online</span>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <span class="stat-card-icon">📨</span>
            <span class="stat-card-num"><?= $total_msgs ?></span>
            <span class="stat-card-label">Total Messages</span>
        </div>
        <div class="stat-card">
            <span class="stat-card-icon">🔔</span>
            <span class="stat-card-num" style="background:linear-gradient(135deg,#ff3d57,#ff6d00);-webkit-background-clip:text;background-clip:text;"><?= $unread_msgs ?></span>
            <span class="stat-card-label">Unread</span>
        </div>
        <div class="stat-card">
            <span class="stat-card-icon">✅</span>
            <span class="stat-card-num" style="background:linear-gradient(135deg,#00e676,#00e5ff);-webkit-background-clip:text;background-clip:text;"><?= $total_msgs - $unread_msgs ?></span>
            <span class="stat-card-label">Read</span>
        </div>
    </div>

    <!-- Messages Card -->
    <div class="d-card">
        <div class="d-card-title">
            📡 Incoming Transmissions
            <?php if ($unread_msgs > 0): ?>
                <span class="badge badge-unread" style="margin-left:0.5rem;"><?= $unread_msgs ?> NEW</span>
            <?php endif; ?>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <a href="admin_messages.php" class="filter-btn active" id="filter-all">All Messages</a>
            <a href="admin_messages.php?filter=unread" class="filter-btn <?= (($_GET['filter'] ?? '') === 'unread') ? 'active-danger' : '' ?>" id="filter-unread">🔔 Unread Only</a>
            <a href="admin_messages.php?filter=read" class="filter-btn <?= (($_GET['filter'] ?? '') === 'read') ? 'active' : '' ?>" id="filter-read">✅ Read Only</a>
        </div>

        <!-- Messages List -->
        <div class="messages-grid">
            <?php
            $has_messages = false;
            $filter = $_GET['filter'] ?? 'all';

            if ($messages && $messages->num_rows > 0):
                while ($msg = $messages->fetch_assoc()):
                    // Apply filter
                    if ($filter === 'unread' && $msg['is_read']) continue;
                    if ($filter === 'read'   && !$msg['is_read']) continue;
                    $has_messages = true;

                    $display_name  = htmlspecialchars($msg['sender_name'] ?: ($msg['first_name'] . ' ' . $msg['last_name']));
                    $display_email = htmlspecialchars($msg['sender_email'] ?: $msg['user_email']);
                    $display_time  = date('M d, Y · H:i', strtotime($msg['created_at']));
                    $is_unread     = !$msg['is_read'];
            ?>
            <div class="msg-card <?= $is_unread ? 'unread' : '' ?>">
                <div class="msg-header">
                    <div class="msg-sender-info">
                        <div class="msg-sender-name">
                            <?= $display_name ?>
                            <?php if ($is_unread): ?>
                                <span class="badge badge-unread" style="margin-left:0.4rem;font-size:0.6rem;">NEW</span>
                            <?php else: ?>
                                <span class="badge badge-read" style="margin-left:0.4rem;font-size:0.6rem;">READ</span>
                            <?php endif; ?>
                        </div>
                        <div class="msg-sender-email">📧 <?= $display_email ?></div>
                    </div>
                    <div class="msg-meta">
                        <span class="msg-time">🕐 <?= $display_time ?></span>
                        <span style="font-family:'JetBrains Mono',monospace;font-size:0.62rem;color:#7b8eb0;">#MSG-<?= str_pad($msg['id'], 4, '0', STR_PAD_LEFT) ?></span>
                    </div>
                </div>

                <?php if (!empty($msg['subject'])): ?>
                    <div class="msg-subject">📌 <?= htmlspecialchars($msg['subject']) ?></div>
                <?php else: ?>
                    <div class="msg-subject-none">— No subject provided —</div>
                <?php endif; ?>

                <div class="msg-body"><?= htmlspecialchars($msg['message']) ?></div>

                <div class="msg-actions">
                    <?php if ($is_unread): ?>
                        <form method="POST" action="admin_messages.php" style="display:inline;">
                            <input type="hidden" name="msg_id" value="<?= $msg['id'] ?>">
                            <button type="submit" name="mark_read" class="btn-mark-read">✔ Mark as Read</button>
                        </form>
                    <?php endif; ?>

                    <a href="mailto:<?= $display_email ?>?subject=Re: <?= urlencode($msg['subject'] ?: 'Your DevSprint Message') ?>" class="btn-mark-read" style="text-decoration:none;display:inline-block;">
                        ↩ Reply via Email
                    </a>

                    <form method="POST" action="admin_messages.php" style="display:inline;"
                          onsubmit="return confirm('Delete this message permanently?');">
                        <input type="hidden" name="msg_id" value="<?= $msg['id'] ?>">
                        <button type="submit" name="delete_msg" class="btn-delete">🗑 Delete</button>
                    </form>
                </div>
            </div>
            <?php endwhile; endif; ?>

            <?php if (!$has_messages): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <p>No transmissions received yet.<br>Messages from users will appear here.</p>
            </div>
            <?php endif; ?>
        </div><!-- end messages-grid -->
    </div><!-- end d-card -->

</main>
</div><!-- end dash-layout -->

<script>
// Highlight active filter
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const filter = urlParams.get('filter') || 'all';
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active', 'active-danger'));
    if (filter === 'all')    document.getElementById('filter-all').classList.add('active');
    if (filter === 'unread') document.getElementById('filter-unread').classList.add('active-danger');
    if (filter === 'read')   document.getElementById('filter-read').classList.add('active');
});
</script>
</body>
</html>
