<?php require_once 'csrf.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Login to DevSprint — your launchpad for India's best hackathons.">
<title>Login | DevSprint · Mission Control</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<style>
.auth-wrapper {
    min-height:100vh;display:flex;align-items:center;justify-content:center;
    padding:5rem 1.5rem 3rem;
}
.auth-card {
    width:100%;max-width:480px;
    background:rgba(255,255,255,0.02);
    border:1px solid rgba(79,195,247,0.15);
    border-radius:var(--radius-lg);
    padding:3rem 2.5rem;
    backdrop-filter:blur(20px);
    position:relative;overflow:hidden;
}
.auth-card::before {
    content:'';position:absolute;top:0;left:0;right:0;height:2px;
    background:linear-gradient(90deg,var(--plasma-cyan),var(--pulsar-violet),var(--nova-orange));
}
.auth-header { text-align:center;margin-bottom:2.5rem; }
.auth-logo {
    width:60px;height:60px;margin:0 auto 1.5rem;
    filter:drop-shadow(0 0 12px var(--plasma-cyan));
}
.auth-title {
    font-family:'Orbitron',monospace;font-size:1.8rem;font-weight:900;
    color:var(--text-bright);margin-bottom:0.5rem;
}
.auth-sub { color:var(--text-dim);font-size:0.9rem;font-family:'JetBrains Mono',monospace; }

.auth-btn {
    width:100%;padding:1rem;
    background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));
    color:var(--void);border:none;border-radius:var(--radius-sm);
    font-family:'Orbitron',monospace;font-size:0.9rem;font-weight:700;
    letter-spacing:0.08em;cursor:pointer;
    transition:all 0.3s;position:relative;overflow:hidden;
}
.auth-btn::before {
    content:'';position:absolute;inset:0;
    background:linear-gradient(135deg,var(--pulsar-violet),var(--plasma-cyan));
    opacity:0;transition:opacity 0.3s;
}
.auth-btn:hover::before { opacity:1; }
.auth-btn:hover { transform:translateY(-2px);box-shadow:0 0 40px rgba(0,229,255,0.3); }
.auth-btn span { position:relative;z-index:1; }

.social-divider {
    display:flex;align-items:center;gap:1rem;margin:2rem 0;
    font-family:'JetBrains Mono',monospace;font-size:0.7rem;
    letter-spacing:0.15em;color:var(--text-dim);text-transform:uppercase;
}
.social-divider::before,.social-divider::after {
    content:'';flex:1;height:1px;background:rgba(79,195,247,0.1);
}

.social-row { display:flex;justify-content:center;gap:1.5rem; }
.social-btn {
    width:56px;height:56px;border-radius:50%;
    border:1px solid rgba(79,195,247,0.15);
    background:rgba(255,255,255,0.02);
    display:flex;align-items:center;justify-content:center;
    transition:all 0.3s;cursor:pointer;text-decoration:none;
}
.social-btn:hover {
    border-color:var(--plasma-cyan);
    background:rgba(0,229,255,0.06);
    transform:translateY(-3px);
    box-shadow:0 8px 24px rgba(0,229,255,0.15);
}
.social-btn img { width:28px;height:28px;object-fit:contain; }

.auth-footer {
    text-align:center;margin-top:2rem;padding-top:1.5rem;
    border-top:1px solid rgba(79,195,247,0.08);
}
.auth-footer p { color:var(--text-dim);font-size:0.88rem;margin-bottom:0.75rem; }
.auth-footer a {
    color:var(--plasma-cyan);text-decoration:none;font-weight:700;
    font-size:0.9rem;transition:opacity 0.2s;
}
.auth-footer a:hover { opacity:0.75; }

.back-link {
    position:fixed;top:1.5rem;left:2rem;z-index:500;
    display:flex;align-items:center;gap:0.5rem;
    color:var(--text-dim);text-decoration:none;font-size:0.82rem;
    font-family:'JetBrains Mono',monospace;letter-spacing:0.1em;
    text-transform:uppercase;transition:color 0.2s;
}
.back-link:hover { color:var(--plasma-cyan); }
</style>
</head>
<body>
<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>
<canvas id="cosmos-canvas"></canvas>
<div class="nebula-overlay"></div>
<div class="scanlines"></div>

<a href="index.php" class="back-link">← Back to Home</a>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <svg class="auth-logo" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="20" cy="20" r="8" fill="none" stroke="#00e5ff" stroke-width="1.5"/>
                <ellipse cx="20" cy="20" rx="18" ry="7" fill="none" stroke="#7c4dff" stroke-width="1" transform="rotate(30 20 20)"/>
                <ellipse cx="20" cy="20" rx="18" ry="7" fill="none" stroke="#00e5ff" stroke-width="1" opacity="0.4" transform="rotate(-30 20 20)"/>
                <circle cx="20" cy="20" r="3" fill="#00e5ff"/>
            </svg>
            <h1 class="auth-title">Mission Login</h1>
            <p class="auth-sub">Enter credentials to access mission control</p>
        </div>

        <form action="login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="commander@devsprint.in" required autocomplete="email">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••••••" required autocomplete="current-password">
            </div>

            <button type="submit" class="auth-btn"><span>▶ INITIATE LAUNCH</span></button>
        </form>

        <div class="social-divider">Or continue with</div>

        <div class="social-row">
            <a href="#" class="social-btn" title="Google">
                <img src="google.png" alt="Google">
            </a>
            <a href="#" class="social-btn" title="GitHub">
                <img src="git.png" alt="GitHub">
            </a>
            <a href="#" class="social-btn" title="LinkedIn">
                <img src="linkedin.png" alt="LinkedIn">
            </a>
        </div>

        <div class="auth-footer">
            <p>New to the mission?</p>
            <a href="Registerpage_view.php">Create a free account →</a>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
