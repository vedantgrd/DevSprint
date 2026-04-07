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
/* ── Hero ── */
.about-hero {
    min-height:60vh;display:flex;flex-direction:column;align-items:center;justify-content:center;
    text-align:center;padding:9rem 2rem 5rem;position:relative;overflow:hidden;
}
.about-orbit {
    position:absolute;top:50%;left:50%;
    transform:translate(-50%,-50%);
    width:520px;height:520px;border-radius:50%;
    border:1px solid rgba(79,195,247,0.06);
    animation:orbitSpin 40s linear infinite;pointer-events:none;
}
.about-orbit::before {
    content:'◆';position:absolute;top:-6px;left:50%;
    color:var(--plasma-cyan);font-size:0.5rem;
    filter:drop-shadow(0 0 6px var(--plasma-cyan));
    animation:orbitSpin 40s linear infinite reverse;
}
.about-orbit-2 {
    position:absolute;top:50%;left:50%;
    transform:translate(-50%,-50%);
    width:340px;height:340px;border-radius:50%;
    border:1px solid rgba(124,77,255,0.06);
    animation:orbitSpin 25s linear infinite reverse;pointer-events:none;
}
.about-orbit-2::before {
    content:'●';position:absolute;bottom:-5px;right:30%;
    color:var(--pulsar-violet);font-size:0.4rem;
    filter:drop-shadow(0 0 5px var(--pulsar-violet));
    animation:orbitSpin 25s linear infinite;
}
@keyframes orbitSpin {
    from{transform:translate(-50%,-50%) rotate(0deg);}
    to{transform:translate(-50%,-50%) rotate(360deg);}
}

/* ── About sections ── */
.about-section { max-width:1100px;margin:0 auto;padding:4rem 2rem; }

/* ── Feature grid ── */
.about-feature-grid {
    display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:1.5rem;margin-top:3rem;
}
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
.about-feature-card:hover {
    border-color:rgba(79,195,247,0.25);transform:translateY(-5px);
    box-shadow:0 20px 40px rgba(0,229,255,0.08);
}
.feature-emoji { font-size:2.2rem;margin-bottom:1rem;display:block; }
.feature-title { font-family:'Orbitron',monospace;font-size:0.95rem;font-weight:700;color:var(--text-bright);margin-bottom:0.6rem; }
.feature-desc { color:var(--text-dim);font-size:0.88rem;line-height:1.65; }

/* ── Stats row ── */
.about-stats {
    display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:1px;background:rgba(79,195,247,0.08);
    border:1px solid rgba(79,195,247,0.1);border-radius:var(--radius-lg);overflow:hidden;margin:3rem 0;
}
.about-stat { background:var(--void);padding:2.5rem 1.5rem;text-align:center; }
.about-stat-num {
    font-family:'Orbitron',monospace;font-size:2.8rem;font-weight:900;
    display:block;margin-bottom:0.4rem;
    background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.about-stat-label {
    font-family:'JetBrains Mono',monospace;font-size:0.72rem;
    letter-spacing:0.15em;text-transform:uppercase;color:var(--text-dim);
}

/* ── Hackathon showcase table ── */
.team-table-wrap {
    overflow-x:auto;border-radius:var(--radius-md);
    border:1px solid rgba(79,195,247,0.1);margin-top:2rem;
}
.about-table { width:100%;border-collapse:collapse; }
.about-table th {
    padding:1rem 1.2rem;font-family:'JetBrains Mono',monospace;
    font-size:0.7rem;letter-spacing:0.15em;text-transform:uppercase;
    color:var(--plasma-cyan);border-bottom:1px solid rgba(79,195,247,0.12);
    text-align:left;background:rgba(79,195,247,0.03);
}
.about-table td {
    padding:1rem 1.2rem;border-bottom:1px solid rgba(255,255,255,0.04);
    color:var(--text-mid);font-size:0.9rem;vertical-align:middle;
}
.about-table tr:last-child td { border-bottom:none; }
.about-table tr:hover td { background:rgba(79,195,247,0.02); }
.hack-table-cell-title { font-weight:700;color:var(--text-bright); }
.hack-table-cell-prize { color:var(--comet-green);font-weight:700;font-family:'JetBrains Mono',monospace; }
.hack-table-cell-cost { color:var(--nova-orange);font-family:'JetBrains Mono',monospace; }

/* ── Quote pull ── */
.about-quote {
    border-left:3px solid var(--plasma-cyan);padding:1rem 1.5rem;
    background:rgba(0,229,255,0.04);border-radius:0 var(--radius-sm) var(--radius-sm) 0;
    font-size:1.05rem;color:var(--text-mid);line-height:1.7;font-style:italic;
    margin:2rem 0;
}

/* ── Platform flow (How It Works) ── */
.how-it-works-grid {
    display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:0;margin-top:3rem;position:relative;
}
.how-it-works-grid::before {
    content:'';position:absolute;top:3rem;left:0;right:0;height:1px;
    background:linear-gradient(90deg,transparent,rgba(79,195,247,0.25),transparent);
    pointer-events:none;
}
.how-step {
    padding:2rem 1.5rem;text-align:center;position:relative;
}
.how-step-num {
    width:3rem;height:3rem;border-radius:50%;
    background:linear-gradient(135deg,rgba(0,229,255,0.12),rgba(124,77,255,0.12));
    border:1px solid rgba(79,195,247,0.25);
    display:flex;align-items:center;justify-content:center;
    font-family:'Orbitron',monospace;font-size:0.85rem;font-weight:900;
    color:var(--plasma-cyan);margin:0 auto 1.2rem;
    position:relative;z-index:1;
}
.how-step-title {
    font-family:'Orbitron',monospace;font-size:0.82rem;font-weight:700;
    color:var(--text-bright);margin-bottom:0.6rem;letter-spacing:0.05em;
}
.how-step-desc { color:var(--text-dim);font-size:0.82rem;line-height:1.6; }
.how-step-icon { font-size:1.4rem;margin-bottom:0.8rem;display:block; }

/* ── Tech stack badges ── */
.tech-stack-row {
    display:flex;flex-wrap:wrap;gap:0.75rem;margin-top:2rem;
}
.tech-badge {
    display:inline-flex;align-items:center;gap:8px;
    padding:0.5rem 1rem;
    border:1px solid rgba(79,195,247,0.15);
    border-radius:40px;
    font-family:'JetBrains Mono',monospace;font-size:0.78rem;
    color:var(--text-mid);letter-spacing:0.05em;
    background:rgba(79,195,247,0.03);
    transition:all 0.3s;
}
.tech-badge:hover {
    border-color:rgba(79,195,247,0.35);color:var(--plasma-cyan);
    background:rgba(79,195,247,0.07);
    transform:translateY(-2px);
}
.tech-badge-dot {
    width:6px;height:6px;border-radius:50%;
    background:var(--plasma-cyan);opacity:0.7;
}
.tech-badge-dot.violet { background:var(--pulsar-violet); }
.tech-badge-dot.green { background:var(--comet-green); }
.tech-badge-dot.orange { background:var(--nova-orange); }

/* ── Timeline / roadmap ── */
.roadmap-list { list-style:none; padding:0; margin-top:2rem; position:relative; }
.roadmap-list::before {
    content:'';
    position:absolute;
    top:0; bottom:0;
    left:0.68rem;           /* ← centres the line behind the 1.4rem dot */
    width:1px;
    background:linear-gradient(to bottom, var(--plasma-cyan), transparent);
}
.roadmap-item {
    display:flex;
    gap:1.5rem;
    padding:0 0 2.2rem 0;
    position:relative;
    align-items:flex-start;  /* ← keeps dot top-aligned with content */
}
.roadmap-dot {
    flex-shrink:0;
    width:1.4rem; height:1.4rem;
    border-radius:50%;
    background:rgba(0,229,255,0.1);
    border:1px solid var(--plasma-cyan);
    display:flex; align-items:center; justify-content:center;
    position:relative;
    z-index:1;               /* ← sits above the ::before line */
    margin-top:0.1rem;
}
.roadmap-dot::before {
    content:'';
    width:6px; height:6px;
    border-radius:50%;
    background:var(--plasma-cyan);
}
.roadmap-dot.done::before { background:var(--comet-green); }
.roadmap-dot.done { border-color:var(--comet-green); background:rgba(0,230,118,0.1); }
.roadmap-dot.upcoming::before { background:var(--pulsar-violet); }
.roadmap-dot.upcoming { border-color:var(--pulsar-violet); background:rgba(124,77,255,0.1); }
.roadmap-content h4 {
    font-family:'Orbitron',monospace; font-size:0.85rem; font-weight:700;
    color:var(--text-bright); margin-bottom:0.3rem;
}
.roadmap-content p { color:var(--text-dim); font-size:0.85rem; line-height:1.6; }
.roadmap-tag {
    display:inline-block; padding:0.15rem 0.6rem; border-radius:4px;
    font-family:'JetBrains Mono',monospace; font-size:0.68rem;
    font-weight:700; letter-spacing:0.08em; text-transform:uppercase;
    margin-bottom:0.5rem;
}
.roadmap-tag.live  { background:rgba(0,230,118,0.12); color:var(--comet-green); }
.roadmap-tag.beta  { background:rgba(79,195,247,0.12); color:var(--plasma-cyan); }
.roadmap-tag.soon  { background:rgba(124,77,255,0.12); color:var(--pulsar-violet); }
/* ── Capability list ── */
.box-legend-list { list-style:none;padding:0; }
.box-legend-list li {
    padding:0.5rem 0;font-size:0.9rem;color:var(--text-mid);
    border-bottom:1px solid rgba(79,195,247,0.06);
    padding-left:1.2rem;position:relative;
}
.box-legend-list li::before {
    content:'◆';position:absolute;left:0;color:var(--plasma-cyan);
    font-size:0.6rem;top:0.65rem;
}

/* ── Two-col layout ── */
.two-col {
    display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:start;
}
@media(max-width:768px){
    .two-col { grid-template-columns:1fr; }
    .how-it-works-grid::before { display:none; }
}
</style>
</head>
<body>
<div class="cursor" id="cursor"></div>
<div class="cursor-ring" id="cursorRing"></div>
<canvas id="cosmos-canvas"></canvas>
<div class="nebula-overlay"></div>
<div class="scanlines"></div>

<!-- ── NAV ── -->
<nav id="main-nav">
    <div class="nav-container">
        <a href="index.php" class="nav-brand">
            <div class="nav-logo">
                <svg viewBox="0 0 40 40" fill="none">
                    <circle cx="20" cy="20" r="8" fill="none" stroke="#00e5ff" stroke-width="1.5"/>
                    <ellipse cx="20" cy="20" rx="18" ry="7" fill="none" stroke="#7c4dff" stroke-width="1" transform="rotate(30 20 20)"/>
                    <ellipse cx="20" cy="20" rx="18" ry="7" fill="none" stroke="#00e5ff" stroke-width="1" opacity="0.4" transform="rotate(-30 20 20)"/>
                    <circle cx="20" cy="20" r="3" fill="#00e5ff"/>
                </svg>
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

<!-- ── HERO ── -->
<section class="about-hero">
    <div class="about-orbit"></div>
    <div class="about-orbit-2"></div>
    <div class="section-label">Our Story</div>
    <h1 class="page-hero-title" style="max-width:700px;">
        Built for<br><span>builders.</span>
    </h1>
    <p class="page-hero-sub">
        <strong style="color:var(--text-bright);">DevSprint</strong> is your launchpad to India's most exciting hackathons — a platform
        that unifies event discovery, team formation, and resource planning in one mission control.
    </p>
    <div style="display:flex;gap:1rem;margin-top:2.5rem;flex-wrap:wrap;justify-content:center;">
        <a href="hackathons.php" class="btn btn-primary"><span>Explore Missions</span><span class="btn-arrow">↗</span></a>
        <a href="login_view.php" class="btn btn-ghost"><span>Join DevSprint</span></a>
    </div>
</section>

<!-- ── STATS ── -->
<div class="about-section" style="padding-top:0;">
    <div class="about-stats reveal">
        <div class="about-stat">
            <span class="about-stat-num" id="stat-hackathons">0</span>
            <span class="about-stat-label">Active Hackathons</span>
        </div>
        <div class="about-stat">
            <span class="about-stat-num" id="stat-devs">0</span>
            <span class="about-stat-label">Developers Onboard</span>
        </div>
        <div class="about-stat">
            <span class="about-stat-num" id="stat-partners">0</span>
            <span class="about-stat-label">Partner Companies</span>
        </div>
        <div class="about-stat">
            <span class="about-stat-num" id="stat-prize">0</span>
            <span class="about-stat-label">Prize Pool Tracked</span>
        </div>
    </div>
</div>

<!-- ── MISSION ── -->
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

<!-- ── WHY DEVSPRINT ── -->
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

<!-- ── HACKATHON SHOWCASE TABLE ── -->
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

<!-- ── HOW IT WORKS ── -->
<div class="about-section" style="padding-top:0;">
    <div class="section-label reveal-left">Platform Flow</div>
    <h2 class="section-title reveal-left d1">From zero to launch in 4 steps.</h2>
    <p style="color:var(--text-mid);font-size:0.95rem;" class="reveal d2">DevSprint is engineered to get you from discovery to submission without friction.</p>
    <div class="how-it-works-grid reveal d3">
        <div class="how-step">
            <span class="how-step-icon">🔭</span>
            <div class="how-step-num">01</div>
            <div class="how-step-title">Discover</div>
            <p class="how-step-desc">Browse curated hackathons filtered by location, tech stack, prize range, and deadline.</p>
        </div>
        <div class="how-step">
            <span class="how-step-icon">👥</span>
            <div class="how-step-num">02</div>
            <div class="how-step-title">Team Up</div>
            <p class="how-step-desc">Our AI matchmaker pairs you with complementary teammates using Jaccard Similarity scoring.</p>
        </div>
        <div class="how-step">
            <span class="how-step-icon">📐</span>
            <div class="how-step-num">03</div>
            <div class="how-step-title">Plan</div>
            <p class="how-step-desc">Access project blueprints, cost estimates, and resource guides tailored to your chosen hackathon.</p>
        </div>
        <div class="how-step">
            <span class="how-step-icon">🚀</span>
            <div class="how-step-num">04</div>
            <div class="how-step-title">Ship</div>
            <p class="how-step-desc">Register, coordinate with real-time team chat, and submit — all from one mission control.</p>
        </div>
    </div>
</div>

<!-- ── TECH STACK + ROADMAP ── -->
<div class="about-section" style="padding-top:0;">
    <div class="section-label reveal-left">Under the Hood</div>
    <h2 class="section-title reveal-left d1">Built with battle-tested tech.</h2>
    <div class="two-col reveal d2" style="margin-top:2rem;">
        <!-- Tech Stack -->
        <div>
            <p style="color:var(--text-mid);font-size:0.95rem;line-height:1.7;margin-bottom:1.5rem;">
                DevSprint's stack is chosen for speed, reliability, and scale — because a platform for builders should be built right.
            </p>
            <div class="tech-stack-row">
                <span class="tech-badge"><span class="tech-badge-dot"></span>PHP 8.2</span>
                <span class="tech-badge"><span class="tech-badge-dot violet"></span>MySQL</span>
                <span class="tech-badge"><span class="tech-badge-dot"></span>Three.js</span>
                <span class="tech-badge"><span class="tech-badge-dot green"></span>REST API</span>
                <span class="tech-badge"><span class="tech-badge-dot orange"></span>WebSockets</span>
                <span class="tech-badge"><span class="tech-badge-dot violet"></span>Jaccard AI</span>
                <span class="tech-badge"><span class="tech-badge-dot"></span>Session Auth</span>
                <span class="tech-badge"><span class="tech-badge-dot green"></span>Responsive CSS</span>
            </div>
        </div>
        <!-- Roadmap -->
        <div>
            <ul class="roadmap-list">
                <li class="roadmap-item">
                    <div class="roadmap-dot done"></div>
                    <div class="roadmap-content">
                        <span class="roadmap-tag live">Live</span>
                        <h4>Core Platform</h4>
                        <p>Hackathon discovery, user profiles, and event browsing fully launched.</p>
                    </div>
                </li>
                <li class="roadmap-item">
                    <div class="roadmap-dot done"></div>
                    <div class="roadmap-content">
                        <span class="roadmap-tag live">Live</span>
                        <h4>AI Matchmaking</h4>
                        <p>Jaccard Similarity-based teammate finder across skill sets.</p>
                    </div>
                </li>
                <li class="roadmap-item">
                    <div class="roadmap-dot"></div>
                    <div class="roadmap-content">
                        <span class="roadmap-tag beta">Beta</span>
                        <h4>Real-Time Team Chat</h4>
                        <p>WebSocket-powered messaging with file sharing and pinned resources.</p>
                    </div>
                </li>
                <li class="roadmap-item">
                    <div class="roadmap-dot upcoming"></div>
                    <div class="roadmap-content">
                        <span class="roadmap-tag soon">Coming Soon</span>
                        <h4>Organiser Dashboard</h4>
                        <p>Event creation tools, applicant management, and analytics for hackathon organisers.</p>
                    </div>
                </li>
                <li class="roadmap-item" style="padding-bottom:0;">
                    <div class="roadmap-dot upcoming"></div>
                    <div class="roadmap-content">
                        <span class="roadmap-tag soon">Coming Soon</span>
                        <h4>Mobile App</h4>
                        <p>Native iOS & Android experience for on-the-go sprint management.</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- ── WHAT YOU CAN DO ── -->
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

<!-- ── FOOTER ── -->
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
/* ── About Page Enhancements ── */
(function () {

    /* ── Randomised + Count-up for stats ── */
    const statsConfig = [
        {
            id: 'stat-hackathons',
            min: 48,  max: 74,
            format: function(n) { return n + '+'; }
        },
        {
            id: 'stat-devs',
            min: 1100, max: 1600,
            format: function(n) { return n.toLocaleString('en-IN') + '+'; }
        },
        {
            id: 'stat-partners',
            min: 32, max: 52,
            format: function(n) { return n + '+'; }
        },
        {
            id: 'stat-prize',
            min: 420, max: 680,
            format: function(n) { return '₹' + n + 'L+'; }
        }
    ];

    function randomBetween(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

    function animateCount(el, target, formatFn, duration) {
        duration = duration || 1800;
        var start = 0;
        var startTime = null;
        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            // Ease-out cubic
            var ease = 1 - Math.pow(1 - progress, 3);
            var current = Math.floor(ease * target);
            el.textContent = formatFn(current);
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = formatFn(target);
            }
        }
        requestAnimationFrame(step);
    }

    var statsTriggered = false;
    var statsSection = document.querySelector('.about-stats');

    var statsObserver = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting && !statsTriggered) {
                statsTriggered = true;
                statsConfig.forEach(function(cfg, i) {
                    var el = document.getElementById(cfg.id);
                    if (!el) return;
                    var target = randomBetween(cfg.min, cfg.max);
                    setTimeout(function() {
                        animateCount(el, target, cfg.format, 1800);
                    }, i * 120);
                });
            }
        });
    }, { threshold: 0.4 });

    if (statsSection) statsObserver.observe(statsSection);

    /* ── How-it-works step hover pulse ── */
    document.querySelectorAll('.how-step').forEach(function(step) {
        step.addEventListener('mouseenter', function() {
            var num = step.querySelector('.how-step-num');
            if (num) {
                num.style.boxShadow = '0 0 20px rgba(0,229,255,0.35)';
                num.style.borderColor = 'var(--plasma-cyan)';
            }
        });
        step.addEventListener('mouseleave', function() {
            var num = step.querySelector('.how-step-num');
            if (num) {
                num.style.boxShadow = '';
                num.style.borderColor = '';
            }
        });
    });

    /* ── Tech badge stagger on reveal ── */
    var badgeObserver = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                var badges = entry.target.querySelectorAll('.tech-badge');
                badges.forEach(function(badge, i) {
                    badge.style.opacity = '0';
                    badge.style.transform = 'translateY(12px)';
                    badge.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    setTimeout(function() {
                        badge.style.opacity = '1';
                        badge.style.transform = 'translateY(0)';
                    }, i * 70 + 200);
                });
                badgeObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });

    var techRow = document.querySelector('.tech-stack-row');
    if (techRow) badgeObserver.observe(techRow);

})();
</script>
</body>
</html>