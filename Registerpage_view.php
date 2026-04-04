<?php require_once 'csrf.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Create your DevSprint account and start discovering India's top hackathons.">
<title>Register | DevSprint · Join the Mission</title>
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
    width:100%;max-width:560px;
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
.auth-logo { width:52px;height:52px;margin:0 auto 1.2rem;filter:drop-shadow(0 0 12px var(--plasma-cyan)); }
.auth-title { font-family:'Orbitron',monospace;font-size:1.7rem;font-weight:900;color:var(--text-bright);margin-bottom:0.5rem; }
.auth-sub { color:var(--text-dim);font-size:0.88rem;font-family:'JetBrains Mono',monospace; }

.form-row { display:grid;grid-template-columns:1fr 1fr;gap:1rem; }
@media(max-width:500px){ .form-row{grid-template-columns:1fr;} }

.auth-btn {
    width:100%;padding:1rem;
    background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));
    color:var(--void);border:none;border-radius:var(--radius-sm);
    font-family:'Orbitron',monospace;font-size:0.85rem;font-weight:700;
    letter-spacing:0.08em;cursor:pointer;
    transition:all 0.3s;position:relative;overflow:hidden;margin-top:0.5rem;
}
.auth-btn::before {
    content:'';position:absolute;inset:0;
    background:linear-gradient(135deg,var(--pulsar-violet),var(--plasma-cyan));
    opacity:0;transition:opacity 0.3s;
}
.auth-btn:hover::before { opacity:1; }
.auth-btn:hover { transform:translateY(-2px);box-shadow:0 0 40px rgba(0,229,255,0.3); }
.auth-btn span { position:relative;z-index:1; }

.auth-btn-ghost {
    width:100%;padding:0.85rem;
    background:transparent;color:var(--text-mid);
    border:1px solid rgba(79,195,247,0.15);
    border-radius:var(--radius-sm);
    font-family:'Syne',sans-serif;font-size:0.9rem;font-weight:600;
    cursor:pointer;transition:all 0.3s;margin-top:0.5rem;
}
.auth-btn-ghost:hover { border-color:rgba(79,195,247,0.3);color:var(--plasma-cyan); }

.error { color:#ef4444;font-size:0.75rem;margin-top:0.3rem;font-family:'JetBrains Mono',monospace;display:block; }
.password-rules {
    font-family:'JetBrains Mono',monospace;font-size:0.72rem;
    color:var(--text-dim);margin-top:0.4rem;line-height:1.5;
}

.auth-footer {
    text-align:center;margin-top:2rem;padding-top:1.5rem;
    border-top:1px solid rgba(79,195,247,0.08);
}
.auth-footer p { color:var(--text-dim);font-size:0.88rem;margin-bottom:0.5rem; }
.auth-footer a { color:var(--plasma-cyan);text-decoration:none;font-weight:700;font-size:0.9rem; }
.auth-footer a:hover { opacity:0.75; }

.back-link {
    position:fixed;top:1.5rem;left:2rem;z-index:500;
    display:flex;align-items:center;gap:0.5rem;
    color:var(--text-dim);text-decoration:none;font-size:0.8rem;
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
            <h1 class="auth-title">Join the Mission</h1>
            <p class="auth-sub">Create your DevSprint commander profile</p>
        </div>

        <form id="registerForm" method="POST" action="register.php">
            <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" id="firstName" name="firstName" placeholder="Ada" autocomplete="given-name">
                    <small class="error" id="firstNameError"></small>
                </div>
                <div class="form-group">
                    <label>Middle Name <span style="opacity:0.5;">(optional)</span></label>
                    <input type="text" id="middleName" name="middleName" placeholder="—" autocomplete="additional-name">
                </div>
            </div>

            <div class="form-group">
                <label>Last Name</label>
                <input type="text" id="lastName" name="lastName" placeholder="Lovelace" autocomplete="family-name">
                <small class="error" id="lastNameError"></small>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="text" id="email" name="email" placeholder="ada@devsprint.in" autocomplete="email">
                <small class="error" id="emailError"></small>
            </div>

            <div class="form-group">
                <label>Mobile Number</label>
                <input type="text" id="phone" name="phone" placeholder="+91 98765 43210" autocomplete="tel">
                <small class="error" id="phoneError"></small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="e.g. Cosmos@123" autocomplete="new-password">
                    </div>
                    <small class="error" id="passwordError"></small>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Repeat password" autocomplete="new-password">
                    </div>
                    <small class="error" id="confirmError"></small>
                </div>
            </div>
            <div class="password-rules" id="passwordRules">Must be 8+ chars with a number and special character.</div>

            <div class="form-group" style="margin-top:1rem;">
                <label>City</label>
                <input type="text" id="city" name="city" placeholder="Bangalore" autocomplete="address-level2">
                <small class="error" id="cityError"></small>
            </div>

            <button type="submit" class="auth-btn" name="register"><span>🚀 CREATE MY PROFILE</span></button>
            <button type="reset" class="auth-btn-ghost">Clear Form</button>
        </form>

        <div class="auth-footer">
            <p>Already have credentials?</p>
            <a href="login_view.php">Login → Mission Control</a>
        </div>
    </div>
</div>

<script src="script.js"></script>
<script src="reglogic.js"></script>
</body>
</html>