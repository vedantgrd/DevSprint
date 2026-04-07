<?php require_once 'csrf.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) {
    header("Location: login_view.php");
    exit();
}
require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];
$team_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($team_id <= 0) { header("Location: teams.php"); exit(); }

// Get team details
$stmt = $conn->prepare("SELECT * FROM teams WHERE id = ?");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$team = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$team) die("Team not found.");

$is_leader = ($team['leader_id'] == $user_id);

// Get members
$members_stmt = $conn->prepare("SELECT u.id, u.first_name, u.last_name, u.email, u.skills, tm.status FROM team_members tm JOIN users u ON tm.user_id = u.id WHERE tm.team_id = ?");
$members_stmt->bind_param("i", $team_id);
$members_stmt->execute();
$members = $members_stmt->get_result();
$members_stmt->close();

$is_member = false;
$user_status = '';
$members_data = [];
while($m = $members->fetch_assoc()) {
    $members_data[] = $m;
    if($m['id'] == $user_id) {
        $is_member = true;
        $user_status = $m['status'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($team['name']) ?> | DevSprint · Team Hub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<style>
.td-layout { max-width:900px;margin:0 auto;padding:2rem;display:flex;flex-direction:column;gap:1.5rem; }

/* Team banner */
.team-banner {
    background:linear-gradient(135deg,rgba(13,27,75,0.5),rgba(26,5,51,0.5));
    border:1px solid rgba(79,195,247,0.15);border-radius:var(--radius-lg);
    padding:2.5rem;position:relative;overflow:hidden;
}
.team-banner::before {
    content:'';position:absolute;top:0;left:0;right:0;height:2px;
    background:linear-gradient(90deg,var(--plasma-cyan),var(--pulsar-violet),var(--nova-orange));
}
.back-link-inline {
    display:inline-flex;align-items:center;gap:5px;
    color:var(--text-dim);text-decoration:none;font-size:0.78rem;
    font-family:'JetBrains Mono',monospace;letter-spacing:0.1em;text-transform:uppercase;
    transition:color 0.2s;margin-bottom:1rem;
}
.back-link-inline:hover { color:var(--plasma-cyan); }
.team-banner-title { font-family:'Orbitron',monospace;font-size:clamp(1.5rem,4vw,2.5rem);font-weight:900;margin-bottom:0.5rem; }
.team-banner-sub { font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:var(--text-dim);margin-bottom:1.5rem; }

/* Member cards */
.members-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem; }
.member-card {
    background:rgba(0,0,0,0.15);border:1px solid rgba(79,195,247,0.1);
    border-radius:var(--radius-md);padding:1.2rem;
    transition:border-color 0.3s;
    display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;
}
.member-card:hover { border-color:rgba(79,195,247,0.25); }
.member-info { flex:1; }
.member-name {
    font-family:'Orbitron',monospace;font-size:0.9rem;font-weight:700;
    color:var(--text-bright);margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;
}
.member-skills { font-family:'JetBrains Mono',monospace;font-size:0.72rem;color:var(--text-dim);line-height:1.5; }
.member-actions { display:flex;flex-direction:column;gap:0.4rem;flex-shrink:0; }
.action-btn {
    padding:0.35rem 0.75rem;border:none;border-radius:var(--radius-sm);
    font-family:'JetBrains Mono',monospace;font-size:0.68rem;font-weight:700;
    cursor:pointer;transition:all 0.2s;letter-spacing:0.05em;
}
.action-accept { background:rgba(0,230,118,0.15);color:var(--comet-green);border:1px solid rgba(0,230,118,0.3); }
.action-accept:hover { background:rgba(0,230,118,0.25); }
.action-reject { background:rgba(239,68,68,0.12);color:#ef4444;border:1px solid rgba(239,68,68,0.25); }
.action-reject:hover { background:rgba(239,68,68,0.2); }

/* Chat */
.chat-card { background:rgba(255,255,255,0.02);border:1px solid rgba(79,195,247,0.1);border-radius:var(--radius-lg);padding:2rem; }
.chat-card-title { font-family:'Orbitron',monospace;font-size:1rem;font-weight:700;color:var(--text-bright);margin-bottom:1.5rem;padding-bottom:0.75rem;border-bottom:1px solid rgba(79,195,247,0.08);display:flex;align-items:center;gap:0.6rem; }
.chat-box {
    height:280px;overflow-y:auto;
    background:rgba(0,0,0,0.2);border:1px solid rgba(79,195,247,0.08);
    border-radius:var(--radius-md);padding:1rem;margin-bottom:1rem;
    display:flex;flex-direction:column;gap:0.75rem;
}
.chat-box::-webkit-scrollbar { width:4px; }
.chat-box::-webkit-scrollbar-track { background:transparent; }
.chat-box::-webkit-scrollbar-thumb { background:rgba(79,195,247,0.15);border-radius:2px; }
.chat-form { display:flex;gap:0.75rem; }
.chat-input {
    flex:1;padding:0.85rem 1rem;
    background:rgba(255,255,255,0.03);border:1px solid rgba(79,195,247,0.12);
    border-radius:var(--radius-sm);color:var(--text-bright);
    font-family:'Syne',sans-serif;font-size:0.9rem;outline:none;
    transition:border-color 0.3s;
}
.chat-input:focus { border-color:var(--plasma-cyan); }
.chat-input::placeholder { color:var(--text-dim); }
.chat-send {
    padding:0.85rem 1.2rem;
    background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));
    color:var(--void);border:none;border-radius:var(--radius-sm);
    font-family:'Orbitron',monospace;font-size:0.75rem;font-weight:700;
    cursor:pointer;transition:all 0.2s;white-space:nowrap;
}
.chat-send:hover { opacity:0.9;transform:translateY(-1px); }

/* Settings */
.settings-card { background:rgba(255,109,0,0.04);border:1px solid rgba(255,109,0,0.1);border-radius:var(--radius-lg);padding:1.5rem; }
.settings-card-title { font-family:'Orbitron',monospace;font-size:0.9rem;font-weight:700;color:var(--nova-orange);margin-bottom:1.2rem; }
.settings-row { display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;margin-bottom:0.75rem; }
.settings-input {
    flex:1;min-width:180px;padding:0.75rem 1rem;
    background:rgba(255,255,255,0.03);border:1px solid rgba(255,109,0,0.12);
    border-radius:var(--radius-sm);color:var(--text-bright);
    font-family:'Syne',sans-serif;font-size:0.9rem;outline:none;
}
.settings-input:focus { border-color:var(--nova-orange); }
.settings-btn {
    padding:0.75rem 1.2rem;border:none;border-radius:var(--radius-sm);
    font-family:'Orbitron',monospace;font-size:0.72rem;font-weight:700;
    cursor:pointer;transition:all 0.2s;
}
.update-btn { background:rgba(0,229,255,0.12);color:var(--plasma-cyan);border:1px solid rgba(0,229,255,0.25); }
.update-btn:hover { background:rgba(0,229,255,0.2); }
.delete-btn { background:rgba(239,68,68,0.12);color:#ef4444;border:1px solid rgba(239,68,68,0.25); }
.delete-btn:hover { background:rgba(239,68,68,0.22); }

/* Join request banner */
.join-request-banner {
    background:rgba(124,77,255,0.08);border:1px solid rgba(124,77,255,0.2);
    border-radius:var(--radius-md);padding:1.5rem;text-align:center;margin-bottom:1.5rem;
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
    <div class="td-layout">

        <!-- Team Banner -->
        <div class="team-banner reveal">
            <a href="teams.php" class="back-link-inline">← Back to Teams</a>
            <h1 class="team-banner-title">
                <span style="background:linear-gradient(90deg,var(--plasma-cyan),var(--pulsar-violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                    <?= htmlspecialchars($team['name']) ?>
                </span>
            </h1>
            <div class="team-banner-sub">Team ID: #<?= $team_id ?> · <?= count($members_data) ?> member<?= count($members_data) !== 1 ? 's' : '' ?></div>

            <?php if(!$is_member): ?>
            <div class="join-request-banner">
                <p style="color:var(--text-mid);margin-bottom:1rem;font-size:0.9rem;">You're not a member of this team yet. Request to join!</p>
                <form action="team_action.php" method="POST" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                    <input type="hidden" name="action" value="request_join">
                    <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                    <button type="submit" class="btn btn-primary btn-sm"><span>Request to Join →</span></button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- Team Members -->
        <div class="glass-card reveal d1" style="border-radius:var(--radius-lg);">
            <div style="font-family:'Orbitron',monospace;font-size:0.9rem;font-weight:700;color:var(--text-bright);margin-bottom:1.2rem;padding-bottom:0.75rem;border-bottom:1px solid rgba(79,195,247,0.08);">
                👥 Team Roster
            </div>
            <div class="members-grid">
                <?php foreach($members_data as $m): ?>
                <div class="member-card">
                    <div class="member-info">
                        <div class="member-name">
                            <?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?>
                            <?php if($m['id'] == $team['leader_id']): ?>
                                <span class="badge badge-leader">Leader</span>
                            <?php else: ?>
                                <span class="badge <?= $m['status'] === 'Accepted' ? 'badge-member' : 'badge-pending' ?>"><?= htmlspecialchars($m['status']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="member-skills"><?= htmlspecialchars($m['skills'] ?: 'No skills listed') ?></div>
                    </div>
                    <?php if($is_leader && $m['status'] === 'Pending'): ?>
                    <div class="member-actions">
                        <form action="team_action.php" method="POST" style="margin:0;">
                            <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                            <input type="hidden" name="action" value="accept">
                            <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                            <input type="hidden" name="target_user" value="<?= $m['id'] ?>">
                            <button type="submit" class="action-btn action-accept">✓ Accept</button>
                        </form>
                        <form action="team_action.php" method="POST" style="margin:0;">
                            <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                            <input type="hidden" name="target_user" value="<?= $m['id'] ?>">
                            <button type="submit" class="action-btn action-reject">✕ Reject</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Team Chat -->
        <?php if($is_member && $user_status === 'Accepted' || $is_leader): ?>
        <div class="chat-card reveal d2">
            <div class="chat-card-title">💬 Team Chat — Real-time</div>
            <div class="chat-box" id="chat-box">
                <div style="text-align:center;color:var(--text-dim);font-size:0.85rem;font-family:'JetBrains Mono',monospace;">Loading mission communications...</div>
            </div>
            <form class="chat-form" id="chat-form">
                <input type="hidden" id="chat-csrf" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                <input type="text" id="chat-msg" class="chat-input" required placeholder="Broadcast to your team...">
                <button type="submit" class="chat-send">Send ▶</button>
            </form>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const teamId = <?= $team['id'] ?>;
            const chatBox = document.getElementById('chat-box');
            const chatForm = document.getElementById('chat-form');
            const chatMsg = document.getElementById('chat-msg');
            const csrfToken = document.getElementById('chat-csrf').value;

            function loadMessages() {
                fetch(`api_chat.php?team_id=${teamId}`)
                .then(r => r.json())
                .then(msgs => {
                    chatBox.innerHTML = '';
                    if (msgs.length === 0) {
                        chatBox.innerHTML = '<div style="text-align:center;color:var(--text-dim);font-size:0.85rem;font-family:\'JetBrains Mono\',monospace;">Say hello to your squad! 🚀</div>';
                        return;
                    }
                    msgs.forEach(m => {
                        const isMe = m.sender_id == <?= $user_id ?>;
                        const bubble = document.createElement('div');
                        bubble.style.cssText = `max-width:75%;padding:0.75rem 1rem;border-radius:12px;align-self:${isMe?'flex-end':'flex-start'};background:${isMe?'linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet))':'rgba(255,255,255,0.05)'};color:${isMe?'var(--void)':'var(--text-bright)'};font-size:0.9rem;line-height:1.4;`;
                        bubble.innerHTML = `<span style="font-size:0.72em;font-weight:700;opacity:0.75;display:block;margin-bottom:3px;font-family:'JetBrains Mono',monospace;">${isMe ? 'YOU' : m.first_name.toUpperCase()}</span>${m.message}`;
                        chatBox.appendChild(bubble);
                    });
                    chatBox.scrollTop = chatBox.scrollHeight;
                });
            }

            chatForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const text = chatMsg.value.trim();
                if(!text) return;
                const fd = new FormData();
                fd.append('team_id', teamId);
                fd.append('message', text);
                fd.append('csrf_token', csrfToken);
                fetch('api_chat.php', { method:'POST', body:fd })
                .then(r => r.json())
                .then(() => { chatMsg.value=''; loadMessages(); });
            });

            loadMessages();
            setInterval(loadMessages, 3000);
        });
        </script>
        <?php endif; ?>

        <!-- Leader Settings -->
        <?php if($is_leader): ?>
        <div class="settings-card reveal d3">
            <div class="settings-card-title">⚙️ Team Settings — Leader Control</div>
            <div class="settings-row">
                <form action="team_action.php" method="POST" style="margin:0;display:flex;gap:0.75rem;flex:1;flex-wrap:wrap;">
                    <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                    <input type="hidden" name="action" value="update_team">
                    <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                    <input type="text" name="team_name" value="<?= htmlspecialchars($team['name']) ?>" required class="settings-input" placeholder="New team name...">
                    <button type="submit" class="settings-btn update-btn">Update Name</button>
                </form>
            </div>
            <form action="team_action.php" method="POST" onsubmit="return confirm('⚠️ Delete this team entirely? All members and applications will be removed. This cannot be undone.');">
                <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                <input type="hidden" name="action" value="delete_team">
                <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                <button type="submit" class="settings-btn delete-btn">🗑 Delete Team Entirely</button>
            </form>
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
