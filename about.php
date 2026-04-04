<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="About DevSprint — India's premier hackathon discovery platform built by developers, for developers.">
<title>About Us | DevSprint · Our Mission</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<style>
/* Hero orbit */
.about-hero {
    min-height:55vh;display:flex;flex-direction:column;align-items:center;justify-content:center;
    text-align:center;padding:8rem 2rem 4rem;position:relative;
}
.about-orbit {
    position:absolute;top:50%;left:50%;
    transform:translate(-50%,-50%);
    width:500px;height:500px;border-radius:50%;
    border:1px solid rgba(79,195,247,0.05);
    animation:orbitSpin 40s linear infinite;pointer-events:none;
}
.about-orbit::before {
    content:'◆';position:absolute;top:-6px;left:50%;
    color:var(--plasma-cyan);font-size:0.5rem;
    filter:drop-shadow(0 0 6px var(--plasma-cyan));
    animation:orbitSpin 40s linear infinite reverse;
}
@keyframes orbitSpin {
    from{transform:translate(-50%,-50%) rotate(0deg);}
    to{transform:translate(-50%,-50%) rotate(360deg);}
}

/* About sections */
.about-section { max-width:1100px;margin:0 auto;padding:4rem 2rem; }
.about-feature-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;margin-top:3rem; }
.about-feature-card {
    background:rgba(255,255,255,0.02);border:1px solid rgba(79,195,247,0.1);
    border-radius:var(--radius-md);padding:2rem;
    transition:all 0.4s;position:relative;overflow:hidden;
}
.about-feature-card::before {
    content:'';position:absolute;top:0;left:0;width:3px;height:0;
    background:linear-gradient(to bottom,var(--plasma-cyan),var(--pulsar-violet));
    transition:height 0.4s;
}
.about-feature-card:hover::before { height:100%; }
.about-feature-card:hover { border-color:rgba(79,195,247,0.25);transform:translateY(-5px);box-shadow:0 20px 40px rgba(0,229,255,0.08); }
.feature-emoji { font-size:2.2rem;margin-bottom:1rem;display:block; }
.feature-title { font-family:'Orbitron',monospace;font-size:0.95rem;font-weight:700;color:var(--text-bright);margin-bottom:0.6rem; }
.feature-desc { color:var(--text-dim);font-size:0.88rem;line-height:1.65; }

/* Stats row */
.about-stats { display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1px;background:rgba(79,195,247,0.08);border:1px solid rgba(79,195,247,0.1);border-radius:var(--radius-lg);overflow:hidden;margin:3rem 0; }
.about-stat { background:var(--void);padding:2.5rem 1.5rem;text-align:center; }
.about-stat-num { font-family:'Orbitron',monospace;font-size:2.8rem;font-weight:900;display:block;margin-bottom:0.4rem;background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text; }
.about-stat-label { font-family:'JetBrains Mono',monospace;font-size:0.72rem;letter-spacing:0.15em;text-transform:uppercase;color:var(--text-dim); }

/* Team table (kept from original) */
.team-table-wrap { overflow-x:auto;border-radius:var(--radius-md);border:1px solid rgba(79,195,247,0.1);margin-top:2rem; }
.about-table { width:100%;border-collapse:collapse; }
.about-table th { padding:1rem 1.2rem;font-family:'JetBrains Mono',monospace;font-size:0.7rem;letter-spacing:0.15em;text-transform:uppercase;color:var(--plasma-cyan);border-bottom:1px solid rgba(79,195,247,0.12);text-align:left;background:rgba(79,195,247,0.03); }
.about-table td { padding:1rem 1.2rem;border-bottom:1px solid rgba(255,255,255,0.04);color:var(--text-mid);font-size:0.9rem;vertical-align:middle; }
.about-table tr:last-child td { border-bottom:none; }
.about-table tr:hover td { background:rgba(79,195,247,0.02); }

/* Hackathon showcase table */
.hack-table-cell-title { font-weight:700;color:var(--text-bright); }
.hack-table-cell-prize { color:var(--comet-green);font-weight:700;font-family:'JetBrains Mono',monospace; }
.hack-table-cell-cost { color:var(--nova-orange);font-family:'JetBrains Mono',monospace; }

/* Box model section (kept from original, restyled) */
.box-model-demo { display:flex;gap:3rem;align-items:center;flex-wrap:wrap;margin-top:2rem; }
.box-demo-visual {
    outline:3px solid rgba(124,77,255,0.5);
    outline-offset:20px;
    padding:20px;
    background:rgba(0,229,255,0.04);
    border:2px solid var(--plasma-cyan);
    border-radius:var(--radius-sm);
    min-width:200px;
}
.box-demo-content {
    background:rgba(124,77,255,0.08);border:1px solid rgba(124,77,255,0.2);
    padding:1rem;border-radius:4px;text-align:center;
    font-family:'JetBrains Mono',monospace;font-size:0.82rem;color:var(--plasma-cyan);
}
.box-legend-list { list-style:none;padding:0; }
.box-legend-list li { padding:0.5rem 0;font-size:0.9rem;color:var(--text-mid);border-bottom:1px solid rgba(79,195,247,0.06);padding-left:1.2rem;position:relative; }
.box-legend-list li::before { content:'◆';position:absolute;left:0;color:var(--plasma-cyan);font-size:0.6rem;top:0.65rem; }

/* Quote pull */
.about-quote {
    border-left:3px solid var(--plasma-cyan);padding:1rem 1.5rem;
    background:rgba(0,229,255,0.04);border-radius:0 var(--radius-sm) var(--radius-sm) 0;
    font-size:1.05rem;color:var(--text-mid);line-height:1.7;font-style:italic;
    margin:2rem 0;
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
            <li><a href="about.php" class="active">About</a></li>
            <li><a href="contact.php">Contact</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="profile.php">My Profile</a></li>
                <li><a href="logout.php" class="nav-btn nav-btn-danger">Logout</a></li>
            <?php else: ?>
                <li><a href="login_view.php" class="nav-btn">Launch →</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- HERO -->
<section class="about-hero">
    <div class="about-orbit"></div>
    <div class="section-label">Our Story</div>
    <h1 class="page-hero-title" style="max-width:700px;">
        Built for<br><span>builders.</span>
    </h1>
    <p class="page-hero-sub">
        <strong style="color:var(--text-bright);">DevSprint</strong> is your launchpad to India's most exciting hackathons — a platform
        that unifies event discovery, team formation, and resource planning in one mission control.
    </p>
</section>

<!-- STATS -->
<div class="about-section" style="padding-top:0;">
    <div class="about-stats reveal">
        <div class="about-stat"><span class="about-stat-num" data-target="50" data-suffix="+">0</span><span class="about-stat-label">Active Hackathons</span></div>
        <div class="about-stat"><span class="about-stat-num" data-target="1200" data-suffix="+">0</span><span class="about-stat-label">Developers Onboard</span></div>
        <div class="about-stat"><span class="about-stat-num" data-target="38" data-suffix="+">0</span><span class="about-stat-label">Partner Companies</span></div>
        <div class="about-stat"><span class="about-stat-num" data-target="500" data-prefix="₹" data-suffix="L+">0</span><span class="about-stat-label">Prize Pool Tracked</span></div>
    </div>
</div>

<!-- MISSION -->
<div class="about-section">
    <div class="section-label reveal-left">Our Mission</div>
    <h2 class="section-title reveal-left d1">Simplify the hackathon experience.</h2>
    <div class="about-quote reveal d2">
        To give every developer — from first-year students to senior engineers — complete, clear, and reliable
        hackathon information in a single, beautifully designed platform.
    </div>
    <p style="color:var(--text-mid);font-size:1rem;line-height:1.75;" class="reveal d3">
        Our platform allows users to browse available project ideas, understand required resources,
        estimate project costs, and view winning prizes before participating in any event.
        No more scattered tabs, no more missing deadlines.
    </p>
</div>

<!-- WHY DEVSPRINT -->
<div class="about-section" style="padding-top:0;">
    <div class="section-label reveal-left">Why DevSprint?</div>
    <h2 class="section-title reveal-left d1">Everything you need, in one orbit.</h2>
    <div class="about-feature-grid">
        <div class="about-feature-card reveal d1">
            <span class="feature-emoji">🛰️</span>
            <div class="feature-title">Curated Event Discovery</div>
            <p class="feature-desc">We scan the cosmos daily to surface the best hackathons matched to your skills, location, and tech stack.</p>
        </div>
        <div class="about-feature-card reveal d2">
            <span class="feature-emoji">🤖</span>
            <div class="feature-title">AI-Powered Matchmaking</div>
            <p class="feature-desc">Our Jaccard Similarity engine finds teammates who complement your skills — not just copy them.</p>
        </div>
        <div class="about-feature-card reveal d3">
            <span class="feature-emoji">🏆</span>
            <div class="feature-title">Full Prize Transparency</div>
            <p class="feature-desc">See prize pools, judging criteria, sponsors, and networking opportunities before you commit.</p>
        </div>
        <div class="about-feature-card reveal d4">
            <span class="feature-emoji">⚡</span>
            <div class="feature-title">One-Click Registration</div>
            <p class="feature-desc">Pre-filled profiles, instant confirmations, team management. Focus on shipping, not paperwork.</p>
        </div>
        <div class="about-feature-card reveal d5">
            <span class="feature-emoji">💬</span>
            <div class="feature-title">Real-Time Team Chat</div>
            <p class="feature-desc">Built-in team messaging keeps your squad coordinated and mission-ready from day one.</p>
        </div>
        <div class="about-feature-card reveal d1">
            <span class="feature-emoji">📊</span>
            <div class="feature-title">Project Intelligence</div>
            <p class="feature-desc">Resource guides, estimated costs, and implementation roadmaps that help you plan before the clock starts.</p>
        </div>
    </div>
</div>

<!-- HACKATHON SHOWCASE TABLE (from original) -->
<div class="about-section" style="padding-top:0;">
    <div class="section-label reveal-left">Sample Data</div>
    <h2 class="section-title reveal-left d1">Hackathon Intelligence Preview</h2>
    <p style="color:var(--text-mid);font-size:0.95rem;margin-bottom:1rem;" class="reveal d2">A snapshot of the kind of information DevSprint surfaces for every event.</p>
    <div class="team-table-wrap reveal d3">
        <table class="about-table">
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Project Idea</th>
                    <th>Required Resources</th>
                    <th>Est. Cost</th>
                    <th>Prize Pool</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="hack-table-cell-title">CodeSprint 2026</td>
                    <td>Smart Traffic Management</td>
                    <td>IoT Sensors, Cloud Server</td>
                    <td class="hack-table-cell-cost">₹2,500</td>
                    <td class="hack-table-cell-prize">₹50,000</td>
                </tr>
                <tr>
                    <td class="hack-table-cell-title">HackVerse</td>
                    <td>AI Resume Analyzer</td>
                    <td>Python, ML Models</td>
                    <td class="hack-table-cell-cost">₹1,500</td>
                    <td class="hack-table-cell-prize">₹30,000</td>
                </tr>
                <tr>
                    <td class="hack-table-cell-title">DevFest</td>
                    <td>Online Code Judge</td>
                    <td>Node.js, Database</td>
                    <td class="hack-table-cell-cost">₹2,000</td>
                    <td class="hack-table-cell-prize">₹40,000</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- CSS BOX MODEL (retained educational content, restyled) -->
<div class="about-section" style="padding-top:0;">
    <div class="section-label reveal-left">Tech Insight</div>
    <h2 class="section-title reveal-left d1">How the CSS Box Model Works</h2>
    <div class="box-model-demo reveal d2">
        <div>
            <p style="color:var(--text-mid);font-size:0.95rem;line-height:1.7;margin-bottom:1.5rem;">
                The box model describes how <strong style="color:var(--text-bright);">content</strong>,
                <strong style="color:var(--text-bright);">padding</strong>,
                <strong style="color:var(--text-bright);">border</strong>, and
                <strong style="color:var(--text-bright);">margin</strong> build every element on a web page.
            </p>
            <ul class="box-legend-list">
                <li><strong style="color:var(--text-bright);">Content</strong> — the actual text or image.</li>
                <li><strong style="color:var(--text-bright);">Padding</strong> — transparent space inside the border.</li>
                <li><strong style="color:var(--text-bright);">Border</strong> — the visible line surrounding padding.</li>
                <li><strong style="color:var(--text-bright);">Margin</strong> — space outside the border.</li>
            </ul>
        </div>
        <div>
            <div class="box-demo-visual">
                <div class="box-demo-content">This is the <strong>content</strong> area.</div>
            </div>
            <p style="color:var(--text-dim);font-size:0.78rem;margin-top:0.75rem;font-family:'JetBrains Mono',monospace;">
                Styled using margin (outline), border (cyan), padding (spacing), and background.
            </p>
        </div>
    </div>
</div>

<!-- WHAT YOU CAN DO -->
<div class="about-section" style="padding-top:0;padding-bottom:5rem;">
    <div class="section-label reveal-left">Platform Capabilities</div>
    <h2 class="section-title reveal-left d1">What You Can Do on DevSprint</h2>
    <ul class="box-legend-list reveal d2" style="margin-top:1.5rem;">
        <li style="font-size:1rem;color:var(--text-mid);">Find nearby hackathons and coding events</li>
        <li style="font-size:1rem;color:var(--text-mid);">Browse project ideas for different skill levels</li>
        <li style="font-size:1rem;color:var(--text-mid);">View required tools, technologies, and estimated costs</li>
        <li style="font-size:1rem;color:var(--text-mid);">Explore winning prizes and sponsorship opportunities</li>
        <li style="font-size:1rem;color:var(--text-mid);">Build or join teams with AI-powered matchmaking</li>
        <li style="font-size:1rem;color:var(--text-mid);">Collaborate with real-time team chat</li>
    </ul>
    <div class="about-quote reveal d3" style="margin-top:2.5rem;">
        Build faster. Compete smarter. Sprint with DevSprint. 🚀
    </div>
    <div style="margin-top:2rem;" class="reveal d4">
        <a href="hackathons.php" class="btn btn-primary"><span>Explore Missions</span><span class="btn-arrow">↗</span></a>
        &nbsp;&nbsp;
        <a href="login_view.php" class="btn btn-ghost"><span>Join DevSprint</span></a>
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
</body>
</html>
