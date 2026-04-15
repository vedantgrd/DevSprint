<?php require_once '../includes/csrf.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: ../admin/admin_login.php");
    exit();
}
require_once '../includes/db_connect.php';
$app_id = isset($_GET['app_id']) ? intval($_GET['app_id']) : 0;

$app_stmt = $conn->prepare("
    SELECT a.*, t.name as team_name, h.title as hackathon, t.id as team_id
    FROM applications a
    JOIN teams t ON a.team_id = t.id
    JOIN hackathons h ON a.hackathon_id = h.id
    WHERE a.id = ?
");
$app_stmt->bind_param("i", $app_id);
$app_stmt->execute();
$application = $app_stmt->get_result()->fetch_assoc();
$app_stmt->close();

if (!$application) die("Application not found.");

$members_stmt = $conn->prepare("
    SELECT u.first_name, u.last_name, u.email, u.skills, u.bio, u.github, u.linkedin, tm.status
    FROM team_members tm
    JOIN users u ON tm.user_id = u.id
    WHERE tm.team_id = ?
");
$members_stmt->bind_param("i", $application['team_id']);
$members_stmt->execute();
$members = $members_stmt->get_result();
$members_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Review Team | DevSprint Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/styles.css">
<style>
body { cursor:auto; }
.cursor,.cursor-ring { display:none; }
.review-wrap { max-width:1100px;margin:0 auto;padding:2rem; }

/* Top bar */
.admin-topbar {
    background:rgba(2,2,15,0.97);border-bottom:1px solid rgba(79,195,247,0.08);
    padding:1rem 2rem;display:flex;justify-content:space-between;align-items:center;
    position:sticky;top:0;z-index:100;
}
.topbar-title { font-family:'Orbitron',monospace;font-size:1rem;font-weight:700;background:linear-gradient(90deg,var(--plasma-cyan),var(--pulsar-violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text; }
.topbar-back {
    display:inline-flex;align-items:center;gap:0.5rem;
    font-family:'JetBrains Mono',monospace;font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase;
    color:var(--text-dim);text-decoration:none;transition:color 0.2s;
}
.topbar-back:hover { color:var(--plasma-cyan); }

/* App info banner */
.app-banner {
    background:linear-gradient(135deg,rgba(13,27,75,0.4),rgba(26,5,51,0.5));
    border:1px solid rgba(79,195,247,0.15);border-radius:var(--radius-lg);
    padding:2.5rem;margin-bottom:2rem;position:relative;overflow:hidden;
}
.app-banner::before { content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--plasma-cyan),var(--pulsar-violet),var(--nova-orange)); }
.app-banner-title { font-family:'Orbitron',monospace;font-size:0.7rem;letter-spacing:0.2em;text-transform:uppercase;color:var(--text-dim);margin-bottom:0.75rem; }
.app-banner-value { font-family:'Orbitron',monospace;font-size:1.6rem;font-weight:900;color:var(--text-bright);margin-bottom:0.3rem; }
.app-banner-meta { display:flex;gap:2rem;flex-wrap:wrap;margin-top:1rem; }
.app-meta-item { font-family:'JetBrains Mono',monospace;font-size:0.8rem;color:var(--text-dim); }
.app-meta-item strong { color:var(--text-bright); }

/* Member grid */
.member-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;margin-bottom:2rem; }
.member-review-card {
    background:rgba(255,255,255,0.02);border:1px solid rgba(79,195,247,0.1);
    border-radius:var(--radius-lg);padding:1.75rem;
    position:relative;overflow:hidden;transition:border-color 0.3s;
}
.member-review-card:hover { border-color:rgba(79,195,247,0.25); }
.member-review-card::before { content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--pulsar-violet),var(--plasma-cyan));transform:scaleX(0);transform-origin:left;transition:transform 0.4s; }
.member-review-card:hover::before { transform:scaleX(1); }
.member-card-name { font-family:'Orbitron',monospace;font-size:1rem;font-weight:700;color:var(--text-bright);margin-bottom:0.75rem;display:flex;align-items:center;justify-content:space-between;gap:0.5rem;flex-wrap:wrap; }
.member-detail-row { display:flex;gap:0.4rem;align-items:flex-start;margin-bottom:0.5rem; }
.detail-key { font-family:'JetBrains Mono',monospace;font-size:0.68rem;letter-spacing:0.1em;color:var(--plasma-cyan);text-transform:uppercase;white-space:nowrap;min-width:55px;padding-top:0.15rem; }
.detail-val { font-size:0.85rem;color:var(--text-mid);line-height:1.5; }
.member-links { display:flex;gap:1rem;margin-top:0.75rem;padding-top:0.75rem;border-top:1px solid rgba(79,195,247,0.07); }
.member-link { font-family:'JetBrains Mono',monospace;font-size:0.72rem;color:var(--pulsar-violet);text-decoration:none;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;transition:opacity 0.2s; }
.member-link:hover { opacity:0.75; }

/* Action panel */
.action-panel {
    background:rgba(255,255,255,0.02);border:1px solid rgba(79,195,247,0.1);
    border-radius:var(--radius-lg);padding:2.5rem;text-align:center;
}
.action-panel-title { font-family:'Orbitron',monospace;font-size:1.1rem;font-weight:700;color:var(--text-bright);margin-bottom:0.75rem; }
.action-panel-sub { color:var(--text-dim);font-size:0.9rem;margin-bottom:2rem;line-height:1.6; }
.action-btns { display:flex;justify-content:center;gap:1.5rem;flex-wrap:wrap; }

.accept-btn {
    padding:1rem 2.5rem;
    background:linear-gradient(135deg,var(--comet-green),#00b060);
    color:var(--void);border:none;border-radius:40px;
    font-family:'Orbitron',monospace;font-size:0.85rem;font-weight:700;
    letter-spacing:0.06em;cursor:pointer;transition:all 0.3s;
    box-shadow:0 0 20px rgba(0,230,118,0.2);
}
.accept-btn:hover { transform:translateY(-3px);box-shadow:0 0 40px rgba(0,230,118,0.35); }

.reject-btn {
    padding:1rem 2.5rem;
    background:linear-gradient(135deg,#ef4444,#b91c1c);
    color:#fff;border:none;border-radius:40px;
    font-family:'Orbitron',monospace;font-size:0.85rem;font-weight:700;
    letter-spacing:0.06em;cursor:pointer;transition:all 0.3s;
    box-shadow:0 0 20px rgba(239,68,68,0.2);
}
.reject-btn:hover { transform:translateY(-3px);box-shadow:0 0 40px rgba(239,68,68,0.35); }
</style>
</head>
<body>

<!-- Top bar -->
<div class="admin-topbar">
    <span class="topbar-title">DevSprint · Admin Review</span>
    <a href="../admin/admin_dashboard.php" class="topbar-back">← Dashboard</a>
</div>

<div class="review-wrap">

    <!-- App Banner -->
    <div class="app-banner">
        <div class="app-banner-title">Reviewing Application for</div>
        <div class="app-banner-value"><?= htmlspecialchars($application['hackathon']) ?></div>
        <div class="app-banner-meta">
            <div class="app-meta-item">🛸 Team: <strong><?= htmlspecialchars($application['team_name']) ?></strong></div>
            <div class="app-meta-item">
                📊 Status: 
                <?php
                $st = $application['status'];
                $bc = $st==='Accepted' ? 'badge-accepted' : ($st==='Rejected' ? 'badge-rejected' : 'badge-pending');
                ?>
                <span class="badge <?= $bc ?>"><?= htmlspecialchars($st) ?></span>
            </div>
        </div>
    </div>

    <div class="section-label" style="margin-bottom:1rem;">Team Roster</div>

    <!-- Member Cards -->
    <div class="member-grid">
        <?php while($m = $members->fetch_assoc()): ?>
        <div class="member-review-card">
            <div class="member-card-name">
                <?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?>
                <?php
                $ms = $m['status'];
                $mc = $ms === 'Accepted' ? 'badge-accepted' : ($ms === 'Rejected' ? 'badge-rejected' : 'badge-pending');
                ?>
                <span class="badge <?= $mc ?>"><?= htmlspecialchars($ms) ?></span>
            </div>
            <div class="member-detail-row">
                <span class="detail-key">Email</span>
                <span class="detail-val"><?= htmlspecialchars($m['email']) ?></span>
            </div>
            <div class="member-detail-row">
                <span class="detail-key">Skills</span>
                <span class="detail-val"><?= htmlspecialchars($m['skills'] ?: 'N/A') ?></span>
            </div>
            <div class="member-detail-row">
                <span class="detail-key">Bio</span>
                <span class="detail-val"><?= htmlspecialchars($m['bio'] ?: 'N/A') ?></span>
            </div>
            <?php if($m['github'] || $m['linkedin']): ?>
            <div class="member-links">
                <?php if($m['github']): ?><a href="<?= htmlspecialchars($m['github']) ?>" target="_blank" class="member-link">GitHub ↗</a><?php endif; ?>
                <?php if($m['linkedin']): ?><a href="<?= htmlspecialchars($m['linkedin']) ?>" target="_blank" class="member-link">LinkedIn ↗</a><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- Action Panel -->
    <div class="action-panel">
        <div class="action-panel-title">Final Administrative Decision</div>
        <p class="action-panel-sub">
            Approving or rejecting this application updates the hackathon status for
            <strong style="color:var(--text-bright);"><?= htmlspecialchars($application['team_name']) ?></strong> — all team members will be affected.
        </p>
        <div class="action-btns">
            <form action="../admin/admin_update_application.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                <input type="hidden" name="status" value="Accepted">
                <input type="hidden" name="app_id" value="<?= $application['id'] ?>">
                <button type="submit" class="accept-btn">✓ Accept Entire Team</button>
            </form>
            <form action="../admin/admin_update_application.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                <input type="hidden" name="status" value="Rejected">
                <input type="hidden" name="app_id" value="<?= $application['id'] ?>">
                <button type="submit" class="reject-btn">✕ Reject Team Application</button>
            </form>
        </div>
    </div>

</div>
</body>
</html>
