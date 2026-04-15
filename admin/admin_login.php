<?php require_once '../includes/csrf.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    if ($user === 'admin' && $pass === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: ../admin/admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials. Access denied.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | DevSprint · Mission Control</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/styles.css">
<style>
body { min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem; }
.admin-login-wrap { width:100%;max-width:420px; }

.admin-login-card {
    background:rgba(255,255,255,0.02);
    border:1px solid rgba(79,195,247,0.15);
    border-radius:var(--radius-lg);
    padding:3rem 2.5rem;
    position:relative;overflow:hidden;
    backdrop-filter:blur(20px);
}
.admin-login-card::before {
    content:'';position:absolute;top:0;left:0;right:0;height:2px;
    background:linear-gradient(90deg,var(--nova-orange),var(--pulsar-violet),var(--plasma-cyan));
}

.admin-badge {
    display:inline-flex;align-items:center;gap:8px;
    font-family:'JetBrains Mono',monospace;font-size:0.7rem;letter-spacing:0.18em;
    text-transform:uppercase;color:var(--nova-orange);
    border:1px solid rgba(255,109,0,0.2);padding:0.3rem 0.9rem;border-radius:40px;
    margin-bottom:1.5rem;
}

.admin-title { font-family:'Orbitron',monospace;font-size:1.6rem;font-weight:900;color:var(--text-bright);margin-bottom:0.4rem; }
.admin-sub { font-family:'JetBrains Mono',monospace;font-size:0.78rem;color:var(--text-dim);margin-bottom:2rem; }

.admin-btn {
    width:100%;padding:0.95rem;
    background:linear-gradient(135deg,var(--nova-orange),var(--pulsar-violet));
    color:#fff;border:none;border-radius:var(--radius-sm);
    font-family:'Orbitron',monospace;font-size:0.85rem;font-weight:700;
    letter-spacing:0.08em;cursor:pointer;transition:all 0.3s;
    position:relative;overflow:hidden;
}
.admin-btn::before { content:'';position:absolute;inset:0;background:linear-gradient(135deg,var(--pulsar-violet),var(--nova-orange));opacity:0;transition:opacity 0.3s; }
.admin-btn:hover::before { opacity:1; }
.admin-btn:hover { transform:translateY(-2px);box-shadow:0 0 30px rgba(255,109,0,0.3); }
.admin-btn span { position:relative;z-index:1; }

.back-link {
    display:block;text-align:center;margin-top:1.5rem;
    font-family:'JetBrains Mono',monospace;font-size:0.75rem;letter-spacing:0.1em;
    text-transform:uppercase;color:var(--text-dim);text-decoration:none;
    transition:color 0.2s;
}
.back-link:hover { color:var(--plasma-cyan); }

/* Simple star bg without Three.js */
.star-field {
    position:fixed;top:0;left:0;width:100%;height:100%;z-index:-1;
    background:radial-gradient(ellipse 80% 60% at 10% 20%, rgba(13,27,75,0.8) 0%, transparent 60%),
               radial-gradient(ellipse 60% 80% at 90% 80%, rgba(26,5,51,0.9) 0%, transparent 60%),
               var(--void);
}
</style>
</head>
<body>
<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>
<div class="star-field">
    <div class="scanlines"></div>
</div>

<div class="admin-login-wrap">
    <div class="admin-login-card">
        <div class="admin-badge">🔒 Restricted Area</div>
        <h1 class="admin-title">Admin Portal</h1>
        <p class="admin-sub">Provide your clearance credentials to proceed.</p>

        <?php if(isset($error)): ?>
        <div class="alert alert-error" style="margin-bottom:1.5rem;">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autocomplete="username" placeholder="admin">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
            </div>
            <button type="submit" class="admin-btn"><span>🔐 ACCESS DASHBOARD</span></button>
        </form>

        <a href="../home/index.php" class="back-link">← Return to main site</a>
    </div>
</div>

<script src="../js/script.js"></script>
</body>
</html>
