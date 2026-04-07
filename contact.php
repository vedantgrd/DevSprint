<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'csrf.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Contact the DevSprint team — reach out for questions, partnerships, or feedback.">
<title>Contact Us | DevSprint · Mission Support</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<style>
.contact-layout {
    max-width:1100px;margin:0 auto;padding:4rem 2rem;
    display:grid;grid-template-columns:1fr 1.2fr;gap:3rem;align-items:start;
}
@media(max-width:768px){ .contact-layout{grid-template-columns:1fr;} }

/* Info side */
.contact-info-card { position:sticky;top:90px; }
.contact-info-title { font-family:'Orbitron',monospace;font-size:1.8rem;font-weight:900;color:var(--text-bright);line-height:1.2;margin-bottom:1rem; }
.contact-info-title span { display:block;background:linear-gradient(90deg,var(--plasma-cyan),var(--pulsar-violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text; }
.contact-desc { color:var(--text-mid);font-size:0.95rem;line-height:1.7;margin-bottom:2.5rem; }

.contact-channels { display:flex;flex-direction:column;gap:1rem;margin-bottom:2.5rem; }
.contact-channel {
    display:flex;align-items:center;gap:1rem;
    padding:1rem 1.25rem;
    background:rgba(255,255,255,0.02);border:1px solid rgba(79,195,247,0.1);
    border-radius:var(--radius-md);transition:all 0.3s;
}
.contact-channel:hover { border-color:rgba(79,195,247,0.25);background:rgba(79,195,247,0.03); }
.channel-icon { font-size:1.4rem;width:28px;text-align:center;flex-shrink:0; }
.channel-label { font-family:'JetBrains Mono',monospace;font-size:0.65rem;letter-spacing:0.15em;text-transform:uppercase;color:var(--text-dim);margin-bottom:0.2rem; }
.channel-value { font-size:0.9rem;color:var(--text-bright);font-weight:600; }

/* Form card */
.contact-form-card {
    background:rgba(255,255,255,0.02);border:1px solid rgba(79,195,247,0.12);
    border-radius:var(--radius-lg);padding:2.5rem;
    position:relative;overflow:hidden;
}
.contact-form-card::before {
    content:'';position:absolute;top:0;left:0;right:0;height:2px;
    background:linear-gradient(90deg,var(--plasma-cyan),var(--pulsar-violet),var(--nova-orange));
}
.contact-form-title { font-family:'Orbitron',monospace;font-size:1.1rem;font-weight:700;color:var(--text-bright);margin-bottom:2rem; }

.contact-submit {
    width:100%;padding:1rem;
    background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));
    color:var(--void);border:none;border-radius:var(--radius-sm);
    font-family:'Orbitron',monospace;font-size:0.85rem;font-weight:700;
    letter-spacing:0.08em;cursor:pointer;transition:all 0.3s;
    margin-top:0.5rem;position:relative;overflow:hidden;
}
.contact-submit::before { content:'';position:absolute;inset:0;background:linear-gradient(135deg,var(--pulsar-violet),var(--plasma-cyan));opacity:0;transition:opacity 0.3s; }
.contact-submit:hover::before { opacity:1; }
.contact-submit:hover { transform:translateY(-2px);box-shadow:0 0 30px rgba(0,229,255,0.25); }
.contact-submit span { position:relative;z-index:1; }

/* Login gate banner */
.login-gate {
    background: rgba(124,77,255,0.08);
    border: 1px solid rgba(124,77,255,0.3);
    border-radius: var(--radius-md);
    padding: 1.5rem 2rem;
    text-align: center;
    margin-bottom: 1.5rem;
}
.login-gate p {
    color: var(--text-mid);
    font-size: 0.92rem;
    margin-bottom: 1rem;
    line-height: 1.6;
}
.login-gate a {
    display: inline-block;
    padding: 0.75rem 2rem;
    background: linear-gradient(135deg, var(--plasma-cyan), var(--pulsar-violet));
    color: var(--void);
    font-family: 'Orbitron', monospace;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    border-radius: var(--radius-sm);
    text-decoration: none;
    transition: all 0.3s;
}
.login-gate a:hover { transform: translateY(-2px); box-shadow: 0 0 20px rgba(0,229,255,0.25); }

/* Readonly email field */
input[readonly] {
    opacity: 0.7;
    cursor: not-allowed;
    background: rgba(255,255,255,0.01) !important;
    border-color: rgba(79,195,247,0.08) !important;
}

/* Team roster table */
.roster-section { max-width:1100px;margin:0 auto;padding:0 2rem 5rem; }
.roster-table-wrap { overflow-x:auto;border-radius:var(--radius-md);border:1px solid rgba(79,195,247,0.1); }

/* Status toast */
.toast-success {
    background:rgba(0,230,118,0.1);border:1px solid rgba(0,230,118,0.3);
    color:var(--comet-green);padding:1rem 1.5rem;border-radius:var(--radius-md);
    font-family:'JetBrains Mono',monospace;font-size:0.85rem;margin-bottom:1.5rem;
    display:none;
}
.toast-error {
    background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);
    color:#ef4444;padding:1rem 1.5rem;border-radius:var(--radius-md);
    font-family:'JetBrains Mono',monospace;font-size:0.85rem;margin-bottom:1.5rem;
    display:none;
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
            <li><a href="contact.php" class="active">Contact</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="teams.php">Teams</a></li>
                <li><a href="matchmaking.php">Find Teammates</a></li>
                <li><a href="profile.php">My Profile</a></li>
                <li><a href="logout.php" class="nav-btn nav-btn-danger">Logout</a></li>
            <?php else: ?>
                <li><a href="login_view.php" class="nav-btn">Launch →</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- Page Hero -->
<div style="padding:6rem 2rem 2rem;text-align:center;">
    <div class="section-label" style="justify-content:center;">Mission Support</div>
    <h1 class="page-hero-title">
        Get in Touch.<br><span>We're Here.</span>
    </h1>
    <p class="page-hero-sub">
        Questions, feedback, or partnership inquiries? Transmit your signal and we'll respond at warp speed.
    </p>
</div>

<!-- Contact Layout -->
<div class="contact-layout">

    <!-- Left: Info -->
    <div class="contact-info-card reveal-left">
        <div class="section-label">Contact Details</div>
        <h2 class="contact-info-title">Reach Mission<br><span>Control.</span></h2>
        <p class="contact-desc">
            If you have any questions, suggestions, or just want to say hello — our team is always monitoring incoming transmissions.
        </p>

        <div class="contact-channels">
            <div class="contact-channel">
                <div class="channel-icon">🌐</div>
                <div>
                    <div class="channel-label">Platform</div>
                    <div class="channel-value">DevSprint · Hackathon Discovery</div>
                </div>
            </div>
            <div class="contact-channel">
                <div class="channel-icon">📍</div>
                <div>
                    <div class="channel-label">Location</div>
                    <div class="channel-value">Pillai College of Engineering, New Panvel</div>
                </div>
            </div>
            <div class="contact-channel">
                <div class="channel-icon">🎓</div>
                <div>
                    <div class="channel-label">Department</div>
                    <div class="channel-value">B.E. Information Technology · Batch 2025</div>
                </div>
            </div>
            <div class="contact-channel">
                <div class="channel-icon">⚡</div>
                <div>
                    <div class="channel-label">Response Time</div>
                    <div class="channel-value">Within 24 hours (warp speed)</div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
            <a href="hackathons.php" class="btn btn-primary btn-sm"><span>Browse Hackathons →</span></a>
            <a href="about.php" class="btn btn-ghost btn-sm"><span>Our Mission</span></a>
        </div>
    </div>

    <!-- Right: Form -->
    <div class="contact-form-card reveal-right">
        <div class="contact-form-title">📡 Send a Transmission</div>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <!-- NOT LOGGED IN: show gate -->
            <div class="login-gate">
                <p>🔐 You must be <strong style="color:var(--plasma-cyan);">logged in</strong> to transmit a message to mission control.<br>Create a free account or sign in to continue.</p>
                <a href="login_view.php?redirect=contact.php">⚡ LOGIN TO TRANSMIT</a>
            </div>
            <!-- Disabled/preview form -->
            <form id="contactForm" style="opacity:0.4;pointer-events:none;">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" placeholder="Your name" disabled>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" placeholder="commander@example.com" disabled>
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" placeholder="e.g. Partnership inquiry, Bug report, Feedback..." disabled>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea rows="5" placeholder="Write your message here..." disabled></textarea>
                </div>
                <button type="button" class="contact-submit" disabled><span>📨 TRANSMIT MESSAGE</span></button>
            </form>

        <?php else:
            // Fetch user email from DB if not fully in session
            require_once 'db_connect.php';
            $u_stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
            $u_stmt->bind_param("i", $_SESSION['user_id']);
            $u_stmt->execute();
            $u_res = $u_stmt->get_result();
            $user_data = $u_res->fetch_assoc();
            $u_stmt->close();
            $user_full_name = htmlspecialchars(($user_data['first_name'] ?? '') . ' ' . ($user_data['last_name'] ?? ''));
            $user_email     = htmlspecialchars($user_data['email'] ?? '');
        ?>

            <!-- SUCCESS TOAST -->
            <div class="toast-success" id="toast-success">
                <?php if (isset($_SESSION['contact_success']) && $_SESSION['contact_success']): ?>
                    ✅ Message transmitted successfully! Mission control will respond soon.
                <?php endif; ?>
            </div>

            <!-- ERROR TOAST -->
            <div class="toast-error" id="toast-error">
                <?php if (isset($_SESSION['contact_error'])): ?>
                    ⚠️ <?= htmlspecialchars($_SESSION['contact_error']) ?>
                <?php endif; ?>
            </div>

            <?php
                // Clear flash messages after reading
                unset($_SESSION['contact_success'], $_SESSION['contact_error']);
            ?>

            <form id="contactForm" action="send_message.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? generate_csrf_token() : '' ?>">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" id="cf-name" value="<?= $user_full_name ?>" placeholder="Your name" required>
                </div>
                <div class="form-group">
                    <label>Email Address <span style="font-family:'JetBrains Mono',monospace;font-size:0.65rem;color:var(--text-dim);letter-spacing:0.1em;">(auto-filled · cannot be changed)</span></label>
                    <input type="email" name="email" id="cf-email"
                           value="<?= $user_email ?>"
                           readonly
                           title="Your registered email address. This cannot be changed.">
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" id="cf-subject" placeholder="e.g. Partnership inquiry, Bug report, Feedback...">
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" id="cf-message" rows="5" placeholder="Write your message here..." required></textarea>
                </div>
                <button type="submit" class="contact-submit"><span>📨 TRANSMIT MESSAGE</span></button>
            </form>

        <?php endif; ?>
    </div>
</div>

<!-- TEAM ROSTER TABLE (from original) -->
<div class="roster-section">
    <div class="section-label reveal-left">Meet the Team</div>
    <h2 class="section-title reveal-left d1" style="margin-bottom:1.5rem;">The Crew Behind DevSprint</h2>
    <div class="roster-table-wrap reveal d2">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Roll No.</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="color:var(--text-bright);font-weight:600;">Rushikesh Vichare</td>
                    <td><a href="mailto:rushikesh25beit@student.mes.ac.in" style="color:var(--plasma-cyan);text-decoration:none;">rushikesh25beit@student.mes.ac.in</a></td>
                    <td style="font-family:'JetBrains Mono',monospace;color:var(--pulsar-violet);font-weight:700;">461</td>
                </tr>
                <tr>
                    <td style="color:var(--text-bright);font-weight:600;">Vedant Garud</td>
                    <td><a href="mailto:vedant25beit@student.mes.ac.in" style="color:var(--plasma-cyan);text-decoration:none;">vedant25beit@student.mes.ac.in</a></td>
                    <td style="font-family:'JetBrains Mono',monospace;color:var(--pulsar-violet);font-weight:700;">462</td>
                </tr>
                <tr>
                    <td style="color:var(--text-bright);font-weight:600;">Aayush Nair</td>
                    <td><a href="mailto:aayush25beit@student.mes.ac.in" style="color:var(--plasma-cyan);text-decoration:none;">aayush25beit@student.mes.ac.in</a></td>
                    <td style="font-family:'JetBrains Mono',monospace;color:var(--pulsar-violet);font-weight:700;">463</td>
                </tr>
                <tr>
                    <td style="color:var(--text-bright);font-weight:600;">Archit Deorukhakar</td>
                    <td><a href="mailto:archit25beit@student.mes.ac.in" style="color:var(--plasma-cyan);text-decoration:none;">archit25beit@student.mes.ac.in</a></td>
                    <td style="font-family:'JetBrains Mono',monospace;color:var(--pulsar-violet);font-weight:700;">464</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- FOOTER -->
<footer>
    <div class="footer-grid">
        <div class="footer-brand-col">
            <span class="nav-brand-text">DevSprint</span>
            <p>India's premier hackathon discovery platform. Navigate the universe of tech competitions.</p>
            <div class="footer-status"><div class="status-dot"></div> All systems operational</div>
        </div>
        <div class="footer-col"><h4>Platform</h4><ul>
            <li><a href="hackathons.php">Hackathons</a></li>
            <li><a href="matchmaking.php">Find Teammates</a></li>
            <li><a href="teams.php">My Teams</a></li>
        </ul></div>
        <div class="footer-col"><h4>Company</h4><ul>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="index.php">Home</a></li>
        </ul></div>
        <div class="footer-col"><h4>Account</h4><ul>
            <li><a href="login_view.php">Login</a></li>
            <li><a href="Registerpage_view.php">Register</a></li>
        </ul></div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 DevSprint · Build faster. Compete smarter. Sprint to success.</p>
        <p>Crafted somewhere in the cosmos 🚀</p>
    </div>
</footer>

<script src="script.js"></script>
<script>
// Show flash toasts if set
document.addEventListener('DOMContentLoaded', function() {
    const successToast = document.getElementById('toast-success');
    const errorToast   = document.getElementById('toast-error');

    if (successToast && successToast.textContent.trim().length > 5) {
        successToast.style.display = 'block';
        setTimeout(() => { successToast.style.display = 'none'; }, 6000);
    }
    if (errorToast && errorToast.textContent.trim().length > 5) {
        errorToast.style.display = 'block';
        setTimeout(() => { errorToast.style.display = 'none'; }, 6000);
    }

    // Prevent the email field from being changed even via JS tricks
    const emailField = document.getElementById('cf-email');
    if (emailField) {
        const lockedValue = emailField.value;
        emailField.addEventListener('input', function() { this.value = lockedValue; });
        emailField.addEventListener('change', function() { this.value = lockedValue; });
    }
});
</script>
</body>
</html>
