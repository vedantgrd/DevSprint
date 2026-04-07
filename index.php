<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevSprint | Hack the Universe</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <style>
        :root {
            --void: #00000a;
            --deep: #02020f;
            --nebula-blue: #0d1b4b;
            --nebula-purple: #1a0533;
            --star-white: #e8f0ff;
            --ion-blue: #4fc3f7;
            --plasma-cyan: #00e5ff;
            --pulsar-violet: #7c4dff;
            --nova-orange: #ff6d00;
            --comet-green: #00e676;
            --text-dim: #7b8eb0;
            --text-mid: #a8b8d8;
            --text-bright: #e8f0ff;
            --glow-blue: rgba(79,195,247,0.4);
            --glow-violet: rgba(124,77,255,0.4);
            --radius-sm: 6px;
            --radius-md: 14px;
            --radius-lg: 24px;
        }
        *, *::before, *::after { margin:0;padding:0;box-sizing:border-box; }
        html { scroll-behavior:smooth; }
        body {
            font-family:'Syne',sans-serif;
            background:var(--void);
            color:var(--text-bright);
            overflow-x:hidden;
            cursor:none;
        }

        /* ── Cursor ── */
        .cursor {
            position:fixed;width:12px;height:12px;
            background:var(--plasma-cyan);border-radius:50%;
            pointer-events:none;z-index:9999;
            transition:transform 0.1s;mix-blend-mode:screen;
        }
        .cursor-ring {
            position:fixed;width:36px;height:36px;
            border:1px solid rgba(0,229,255,0.5);border-radius:50%;
            pointer-events:none;z-index:9998;
            transition:transform 0.15s,width 0.2s,height 0.2s;
        }

        /* ── Canvas & Overlays ── */
        #cosmos-canvas { position:fixed;top:0;left:0;width:100%;height:100%;z-index:-3; }
        .nebula-overlay {
            position:fixed;top:0;left:0;width:100%;height:100%;z-index:-2;
            background:
                radial-gradient(ellipse 80% 60% at 10% 20%,rgba(13,27,75,0.7) 0%,transparent 60%),
                radial-gradient(ellipse 60% 80% at 90% 80%,rgba(26,5,51,0.8) 0%,transparent 60%);
            pointer-events:none;
        }
        .scanlines {
            position:fixed;top:0;left:0;width:100%;height:100%;z-index:-1;
            background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,0.03) 2px,rgba(0,0,0,0.03) 4px);
            pointer-events:none;
        }

        /* ── Nav ── */
        nav {
            position:fixed;top:0;left:0;right:0;z-index:1000;
            padding:0 3rem;height:70px;
            display:flex;align-items:center;
            transition:background 0.4s,border-bottom 0.4s;
        }
        nav.scrolled {
            background:rgba(0,0,10,0.85);
            backdrop-filter:blur(20px);
            border-bottom:1px solid rgba(79,195,247,0.1);
        }
        .nav-container {
            max-width:1400px;margin:0 auto;width:100%;
            display:flex;justify-content:space-between;align-items:center;
        }
        .nav-brand { display:flex;align-items:center;gap:12px;text-decoration:none; }
        .nav-logo svg { width:40px;height:40px;filter:drop-shadow(0 0 8px var(--plasma-cyan)); }
        .nav-brand-text {
            font-family:'Orbitron',monospace;font-weight:900;font-size:1.3rem;
            letter-spacing:0.08em;
            background:linear-gradient(90deg,var(--plasma-cyan),var(--pulsar-violet));
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
        }
        .nav-menu { display:flex;gap:0.25rem;align-items:center;list-style:none; }
        .nav-menu li a {
            color:var(--text-dim);text-decoration:none;
            padding:0.5rem 1rem;border-radius:var(--radius-sm);
            font-size:0.85rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;
            transition:color 0.2s,background 0.2s;
        }
        .nav-menu li a:hover,.nav-menu li a.active {
            color:var(--text-bright);background:rgba(79,195,247,0.08);
        }

        /* ── Nav Button (FIXED) ── */
        .nav-btn {
            background:transparent!important;
            border:1px solid var(--plasma-cyan)!important;
            color:var(--plasma-cyan)!important;
            padding:0.55rem 1.4rem!important;
            border-radius:40px!important;
            position:relative;
            overflow:hidden;
            isolation:isolate;
            transition:color 0.3s!important;
            display:inline-block;
        }
        .nav-btn::before {
            content:'';position:absolute;inset:0;
            background:var(--plasma-cyan);
            border-radius:40px;
            transform:scaleX(0);transform-origin:left;
            transition:transform 0.3s ease;
            z-index:-1;
        }
        .nav-btn:hover::before { transform:scaleX(1); }
        .nav-btn:hover { color:var(--void)!important; }

        .nav-btn-danger {
            border-color:var(--nova-orange)!important;
            color:var(--nova-orange)!important;
            isolation:isolate;
        }
        .nav-btn-danger::before { background:var(--nova-orange); }
        .nav-btn-danger:hover { color:var(--void)!important; }

        .nav-toggle {
            display:none;
            background:none;
            border:1px solid rgba(79,195,247,0.3);
            color:var(--plasma-cyan);
            font-size:1.2rem;
            padding:0.4rem 0.7rem;
            border-radius:var(--radius-sm);
            cursor:pointer;
            transition:all 0.2s;
        }
        .nav-toggle:hover {
            background:rgba(79,195,247,0.08);
            border-color:var(--plasma-cyan);
        }

        /* ── Hero ── */
        .hero {
            min-height:100vh;display:flex;flex-direction:column;
            align-items:center;justify-content:center;
            text-align:center;padding:120px 2rem 4rem;position:relative;
        }
        .hero-orbit {
            position:absolute;top:50%;left:50%;
            transform:translate(-50%,-50%);
            width:700px;height:700px;border-radius:50%;
            border:1px solid rgba(79,195,247,0.06);
            animation:orbitSpin 30s linear infinite;pointer-events:none;
        }
        .hero-orbit::before {
            content:'◆';position:absolute;top:-6px;left:50%;
            color:var(--plasma-cyan);font-size:0.6rem;
            filter:drop-shadow(0 0 6px var(--plasma-cyan));
            animation:orbitSpin 30s linear infinite reverse;
        }
        .hero-orbit-2 { width:900px;height:900px;animation-duration:50s;border-color:rgba(124,77,255,0.05); }
        .hero-orbit-2::before { color:var(--pulsar-violet);filter:drop-shadow(0 0 6px var(--pulsar-violet)); }
        @keyframes orbitSpin {
            from{transform:translate(-50%,-50%) rotate(0deg);}
            to{transform:translate(-50%,-50%) rotate(360deg);}
        }
        .hero-eyebrow {
            display:inline-flex;align-items:center;gap:8px;
            font-family:'JetBrains Mono',monospace;font-size:0.75rem;
            letter-spacing:0.2em;text-transform:uppercase;color:var(--plasma-cyan);
            border:1px solid rgba(0,229,255,0.2);padding:0.4rem 1.2rem;
            border-radius:40px;margin-bottom:2.5rem;
            animation:fadeDown 1s ease both;
        }
        .eyebrow-dot { width:6px;height:6px;background:var(--plasma-cyan);border-radius:50%;animation:eyebrowPulse 1.5s ease-in-out infinite; }
        @keyframes eyebrowPulse { 0%,100%{box-shadow:0 0 0 0 rgba(0,229,255,0.5);}50%{box-shadow:0 0 0 6px transparent;} }
        .hero-title {
            font-family:'Orbitron',monospace;
            font-size:clamp(2.8rem,6vw,6.5rem);font-weight:900;
            line-height:1.05;letter-spacing:-0.02em;margin-bottom:2rem;
            animation:fadeUp 1s ease 0.2s both;
        }
        .hero-title .line-1 { color:var(--text-bright);display:block; }
        .hero-title .line-2 {
            display:block;
            background:linear-gradient(90deg,var(--plasma-cyan) 0%,var(--pulsar-violet) 50%,var(--nova-orange) 100%);
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
            background-size:200%;animation:gradientFlow 4s ease infinite,fadeUp 1s ease 0.4s both;
        }
        @keyframes gradientFlow { 0%,100%{background-position:0%;}50%{background-position:100%;} }
        .hero-sub {
            font-size:1.15rem;color:var(--text-mid);max-width:620px;
            line-height:1.7;margin-bottom:3rem;animation:fadeUp 1s ease 0.6s both;
        }
        .hero-cta { display:flex;gap:1.5rem;flex-wrap:wrap;justify-content:center;animation:fadeUp 1s ease 0.8s both; }
        .scroll-indicator {
            position:absolute;bottom:2.5rem;left:50%;transform:translateX(-50%);
            display:flex;flex-direction:column;align-items:center;gap:8px;
            animation:fadeUp 1s ease 1.2s both;
        }
        .scroll-line {
            width:1px;height:60px;
            background:linear-gradient(to bottom,var(--plasma-cyan),transparent);
            animation:scrollPulse 2s ease-in-out infinite;
        }
        @keyframes scrollPulse {
            0%{transform:scaleY(0);transform-origin:top;}
            50%{transform:scaleY(1);transform-origin:top;}
            100%{transform:scaleY(0);transform-origin:bottom;}
        }
        .scroll-label { font-family:'JetBrains Mono',monospace;font-size:0.65rem;letter-spacing:0.2em;color:var(--text-dim); }

        /* ── Buttons ── */
        .btn {
            display:inline-flex;align-items:center;gap:0.6rem;
            padding:0.9rem 2.2rem;font-family:'Syne',sans-serif;
            font-weight:700;font-size:0.95rem;letter-spacing:0.05em;
            text-decoration:none;border-radius:40px;
            transition:all 0.3s ease;cursor:pointer;border:none;
            position:relative;overflow:hidden;
        }
        .btn-primary {
            background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));
            color:var(--void);
            box-shadow:0 0 30px rgba(0,229,255,0.25),0 0 60px rgba(124,77,255,0.15);
        }
        .btn-primary::before {
            content:'';position:absolute;inset:0;
            background:linear-gradient(135deg,var(--pulsar-violet),var(--plasma-cyan));
            opacity:0;transition:opacity 0.3s;
        }
        .btn-primary:hover::before { opacity:1; }
        .btn-primary:hover { transform:translateY(-3px);box-shadow:0 0 50px rgba(0,229,255,0.4),0 0 100px rgba(124,77,255,0.25); }
        .btn-ghost {
            background:transparent;color:var(--text-bright);
            border:1px solid rgba(232,240,255,0.15);backdrop-filter:blur(10px);
        }
        .btn-ghost:hover { border-color:rgba(79,195,247,0.4);background:rgba(79,195,247,0.06);transform:translateY(-3px); }
        .btn span { position:relative;z-index:1; }
        .btn-arrow { font-size:1.1rem;transition:transform 0.3s;position:relative;z-index:1; }
        .btn:hover .btn-arrow { transform:translateX(4px); }

        /* ── Ticker ── */
        .ticker-section {
            padding:2.5rem 0;
            border-top:1px solid rgba(79,195,247,0.08);
            border-bottom:1px solid rgba(79,195,247,0.08);
            overflow:hidden;
        }
        .ticker-track { display:flex;gap:4rem;animation:tickerScroll 35s linear infinite;white-space:nowrap; }
        .ticker-item { display:inline-flex;align-items:center;gap:1rem;flex-shrink:0;font-family:'JetBrains Mono',monospace;font-size:0.85rem;color:var(--text-dim); }
        .ticker-item .dot { width:6px;height:6px;border-radius:50%;background:var(--plasma-cyan);flex-shrink:0; }
        .ticker-item strong { color:var(--text-mid); }
        @keyframes tickerScroll { from{transform:translateX(0);}to{transform:translateX(-50%);} }

        /* ── Stats ── */
        .stats-section { max-width:1200px;margin:0 auto;padding:6rem 2rem; }
        .stats-grid {
            display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
            gap:1.5px;background:rgba(79,195,247,0.08);
            border:1px solid rgba(79,195,247,0.1);border-radius:var(--radius-lg);overflow:hidden;
        }
        .stat-item { background:var(--void);padding:3rem 2rem;text-align:center;transition:background 0.3s;position:relative; }
        .stat-item:hover { background:rgba(79,195,247,0.04); }
        .stat-number {
            font-family:'Orbitron',monospace;font-size:3.2rem;font-weight:900;
            background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
            margin-bottom:0.5rem;display:block;
        }
        .stat-label { font-size:0.85rem;color:var(--text-dim);letter-spacing:0.1em;text-transform:uppercase;font-family:'JetBrains Mono',monospace; }

        /* ── Section helpers ── */
        .content-section { max-width:1400px;margin:0 auto;padding:6rem 2rem; }
        .section-label {
            display:inline-flex;align-items:center;gap:10px;
            font-family:'JetBrains Mono',monospace;font-size:0.7rem;
            letter-spacing:0.25em;text-transform:uppercase;color:var(--plasma-cyan);margin-bottom:1.5rem;
        }
        .section-label::before { content:'';display:block;width:30px;height:1px;background:var(--plasma-cyan); }
        .section-title {
            font-family:'Orbitron',monospace;font-size:clamp(1.8rem,3.5vw,3rem);
            font-weight:700;color:var(--text-bright);margin-bottom:1rem;line-height:1.2;
        }
        .section-desc { color:var(--text-mid);font-size:1.05rem;line-height:1.7;max-width:550px; }

        /* ── Features ── */
        .features-layout { display:grid;grid-template-columns:1fr 1fr;gap:5rem;align-items:start; }
        .features-header { position:sticky;top:100px; }
        .feature-list { display:flex;flex-direction:column;gap:1.5rem; }
        .feature-item {
            display:flex;gap:1.5rem;padding:2rem;
            border:1px solid rgba(79,195,247,0.08);border-radius:var(--radius-md);
            background:rgba(255,255,255,0.01);transition:all 0.4s ease;
            position:relative;overflow:hidden;
        }
        .feature-item::before {
            content:'';position:absolute;top:0;left:0;width:3px;height:0;
            background:linear-gradient(to bottom,var(--plasma-cyan),var(--pulsar-violet));
            transition:height 0.4s ease;
        }
        .feature-item:hover::before { height:100%; }
        .feature-item:hover { border-color:rgba(79,195,247,0.2);background:rgba(79,195,247,0.03);transform:translateX(8px); }
        .feature-icon-wrap {
            flex-shrink:0;width:52px;height:52px;border-radius:var(--radius-sm);
            border:1px solid rgba(79,195,247,0.2);display:flex;align-items:center;justify-content:center;
            font-size:1.5rem;background:rgba(0,229,255,0.04);transition:all 0.3s;
        }
        .feature-item:hover .feature-icon-wrap { border-color:var(--plasma-cyan);box-shadow:0 0 20px rgba(0,229,255,0.2); }
        .feature-text h3 { font-size:1rem;font-weight:700;color:var(--text-bright);margin-bottom:0.5rem;letter-spacing:0.03em; }
        .feature-text p { color:var(--text-dim);font-size:0.9rem;line-height:1.6; }

        /* ── Event Card ── */
        .event-showcase { padding:6rem 2rem;max-width:1400px;margin:0 auto; }
        .event-card-3d { perspective:1200px; }
        .event-card-inner {
            background:linear-gradient(135deg,rgba(13,27,75,0.9) 0%,rgba(26,5,51,0.9) 50%,rgba(2,2,15,0.9) 100%);
            border:1px solid rgba(79,195,247,0.2);border-radius:30px;padding:4rem;
            display:grid;grid-template-columns:1.1fr 0.9fr;gap:4rem;align-items:center;
            position:relative;overflow:hidden;
            transform-style:preserve-3d;transition:transform 0.6s cubic-bezier(0.23,1,0.32,1);
            backdrop-filter:blur(20px);
        }
        .event-card-inner::before {
            content:'';position:absolute;inset:-1px;
            background:linear-gradient(135deg,rgba(0,229,255,0.3),transparent,rgba(124,77,255,0.3));
            border-radius:30px;z-index:-1;opacity:0;transition:opacity 0.4s;
        }
        .event-card-3d:hover .event-card-inner::before { opacity:1; }
        .event-bg-glow {
            position:absolute;top:-100px;right:-100px;width:400px;height:400px;border-radius:50%;
            background:radial-gradient(circle,rgba(124,77,255,0.15) 0%,transparent 70%);
            animation:glowPulse 6s ease-in-out infinite;
        }
        @keyframes glowPulse { 0%,100%{transform:scale(1);opacity:0.7;}50%{transform:scale(1.2);opacity:1;} }
        .event-badge {
            display:inline-flex;align-items:center;gap:8px;
            background:rgba(255,109,0,0.15);border:1px solid rgba(255,109,0,0.3);
            color:var(--nova-orange);padding:0.4rem 1rem;border-radius:40px;
            font-size:0.75rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:1.5rem;
        }
        .event-title { font-family:'Orbitron',monospace;font-size:2.8rem;font-weight:900;color:var(--text-bright);margin-bottom:1.2rem;line-height:1.1; }
        .event-desc { color:var(--text-mid);font-size:1.05rem;line-height:1.7;margin-bottom:2.5rem; }
        .event-meta-grid { display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:2.5rem; }
        .event-meta-item { background:rgba(255,255,255,0.03);border:1px solid rgba(79,195,247,0.08);border-radius:var(--radius-sm);padding:1rem; }
        .meta-key { font-family:'JetBrains Mono',monospace;font-size:0.65rem;letter-spacing:0.15em;color:var(--text-dim);text-transform:uppercase;margin-bottom:0.3rem; }
        .meta-val { font-size:0.95rem;font-weight:700;color:var(--text-bright); }
        .event-img-wrap { border-radius:20px;overflow:hidden;position:relative;box-shadow:0 0 80px rgba(124,77,255,0.3),0 40px 80px rgba(0,0,0,0.5); }
        .event-img-wrap img { width:100%;display:block;transition:transform 0.6s ease; }
        .event-card-3d:hover .event-img-wrap img { transform:scale(1.04); }
        .event-img-overlay { position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,10,0.6),transparent); }
        .event-img-badge {
            position:absolute;bottom:20px;left:20px;
            background:rgba(0,229,255,0.1);backdrop-filter:blur(10px);
            border:1px solid rgba(0,229,255,0.3);padding:0.8rem 1.2rem;
            border-radius:var(--radius-sm);font-family:'JetBrains Mono',monospace;font-size:0.8rem;color:var(--plasma-cyan);
        }

        /* ── Categories ── */
        .categories-section { padding:6rem 2rem;max-width:1400px;margin:0 auto;overflow:hidden; }
        .categories-header { display:grid;grid-template-columns:1fr auto;align-items:end;gap:2rem;margin-bottom:3rem; }
        .cat-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.5rem; }
        .cat-card {
            background:rgba(255,255,255,0.02);border:1px solid rgba(79,195,247,0.08);
            border-radius:var(--radius-md);padding:2rem 1.5rem;
            transition:all 0.4s ease;position:relative;overflow:hidden;cursor:pointer;
        }
        .cat-card::after { content:'';position:absolute;inset:0;background:linear-gradient(135deg,var(--plasma-cyan),var(--pulsar-violet));opacity:0;transition:opacity 0.4s;z-index:0; }
        .cat-card:hover::after { opacity:0.05; }
        .cat-card:hover { border-color:rgba(79,195,247,0.3);transform:translateY(-6px);box-shadow:0 20px 50px rgba(0,229,255,0.1); }
        .cat-card>* { position:relative;z-index:1; }
        .cat-emoji { font-size:2.5rem;margin-bottom:1rem;display:block; }
        .cat-name { font-weight:700;font-size:1rem;color:var(--text-bright);margin-bottom:0.4rem; }
        .cat-count { font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:var(--plasma-cyan); }

        /* ── Timeline (FIXED) ── */
        .timeline-section { padding:6rem 2rem;max-width:900px;margin:0 auto;text-align:center; }
        .timeline { display:flex;flex-direction:column;gap:0;margin-top:4rem;position:relative; }
        .timeline::before {
            content:'';position:absolute;left:50%;top:0;bottom:0;width:1px;
            background:linear-gradient(to bottom,var(--plasma-cyan),var(--pulsar-violet),transparent);
            transform:translateX(-50%);
            z-index:0;
        }
        .timeline-step {
            display:grid;
            grid-template-columns:1fr 1fr;
            align-items:center;
            gap:0;
            padding:2.5rem 0;
            position:relative;
        }

        /* ODD: node hugs RIGHT side of left column (sits left of line), text in right column */
        .timeline-step:nth-child(odd) .timeline-node {
            grid-column:1;grid-row:1;
            justify-self:flex-end;
            margin-right:2.5rem;
        }
        .timeline-step:nth-child(odd) .step-content {
            grid-column:2;grid-row:1;
            text-align:left;
            padding-left:2.5rem;
        }

        /* EVEN: text in left column, node hugs LEFT side of right column (sits right of line) */
        .timeline-step:nth-child(even) .step-content {
            grid-column:1;grid-row:1;
            text-align:right;
            padding-right:2.5rem;
        }
        .timeline-step:nth-child(even) .timeline-node {
            grid-column:2;grid-row:1;
            justify-self:flex-start;
            margin-left:2.5rem;
        }

        .step-empty { display:none; }

        .timeline-node {
            width:56px;height:56px;border-radius:50%;
            border:2px solid var(--plasma-cyan);
            background:var(--void);
            display:flex;align-items:center;justify-content:center;
            font-family:'Orbitron',monospace;font-size:1rem;font-weight:900;color:var(--plasma-cyan);
            box-shadow:0 0 20px rgba(0,229,255,0.3),inset 0 0 20px rgba(0,229,255,0.05);
            z-index:1;position:relative;transition:all 0.3s;flex-shrink:0;
        }
        .timeline-step:hover .timeline-node {
            background:rgba(0,229,255,0.1);
            box-shadow:0 0 40px rgba(0,229,255,0.6);
        }
        .step-content h3 { font-family:'Orbitron',monospace;font-size:1rem;font-weight:700;color:var(--text-bright);margin-bottom:0.6rem; }
        .step-content p { color:var(--text-dim);font-size:0.88rem;line-height:1.6; }

        /* ── Sponsors ── */
        .sponsors-section { padding:5rem 2rem;max-width:1200px;margin:0 auto;text-align:center; }
        .sponsors-label { font-family:'JetBrains Mono',monospace;font-size:0.7rem;letter-spacing:0.25em;color:var(--text-dim);text-transform:uppercase;margin-bottom:2.5rem; }
        .sponsors-row { display:flex;flex-wrap:wrap;gap:1.5rem;justify-content:center;align-items:center; }
        .sponsor-chip {
            background:rgba(255,255,255,0.03);border:1px solid rgba(79,195,247,0.08);border-radius:8px;
            padding:0.8rem 2rem;font-family:'Orbitron',monospace;font-size:0.8rem;font-weight:600;
            color:var(--text-dim);letter-spacing:0.1em;transition:all 0.3s;
        }
        .sponsor-chip:hover { border-color:rgba(79,195,247,0.2);color:var(--text-mid); }

        /* ── CTA Band ── */
        .cta-band {
            margin:4rem auto;
            max-width:1400px;
            background:linear-gradient(135deg,rgba(0,229,255,0.05) 0%,rgba(124,77,255,0.08) 50%,rgba(0,229,255,0.05) 100%);
            border:1px solid rgba(79,195,247,0.15);border-radius:30px;
            padding:5rem 4rem;text-align:center;position:relative;overflow:hidden;
        }
        .cta-band::before {
            content:'';position:absolute;top:-60%;left:-20%;width:60%;height:200%;
            background:radial-gradient(ellipse,rgba(0,229,255,0.08) 0%,transparent 70%);pointer-events:none;
        }
        .cta-band::after {
            content:'';position:absolute;top:-60%;right:-20%;width:60%;height:200%;
            background:radial-gradient(ellipse,rgba(124,77,255,0.08) 0%,transparent 70%);pointer-events:none;
        }
        .cta-band h2 { font-family:'Orbitron',monospace;font-size:clamp(1.8rem,3vw,2.8rem);font-weight:900;color:var(--text-bright);margin-bottom:1rem;position:relative;z-index:1; }
        .cta-band p { color:var(--text-mid);font-size:1.05rem;margin-bottom:2.5rem;position:relative;z-index:1; }
        .cta-band .btn { position:relative;z-index:1; }

        /* ── Footer ── */
        footer { background:rgba(0,0,5,0.8);border-top:1px solid rgba(79,195,247,0.08);padding:4rem 2rem 2rem; }
        .footer-grid {
            max-width:1400px;margin:0 auto;
            display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:4rem;
            padding-bottom:3rem;border-bottom:1px solid rgba(79,195,247,0.06);
        }
        .footer-brand-col .nav-brand-text { font-size:1.5rem;display:block;margin-bottom:1rem; }
        .footer-brand-col p { color:var(--text-dim);font-size:0.9rem;line-height:1.6;max-width:300px;margin-bottom:1.5rem; }
        .footer-col h4 { font-size:0.75rem;letter-spacing:0.2em;text-transform:uppercase;color:var(--text-dim);font-family:'JetBrains Mono',monospace;margin-bottom:1.5rem; }
        .footer-col ul { list-style:none; }
        .footer-col ul li { margin-bottom:0.7rem; }
        .footer-col ul li a { color:var(--text-dim);text-decoration:none;font-size:0.9rem;transition:color 0.2s; }
        .footer-col ul li a:hover { color:var(--plasma-cyan); }
        .footer-bottom { max-width:1400px;margin:2rem auto 0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem; }
        .footer-bottom p { color:var(--text-dim);font-size:0.8rem;font-family:'JetBrains Mono',monospace; }
        .footer-status { display:flex;align-items:center;gap:8px;font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:var(--comet-green); }
        .status-dot { width:6px;height:6px;border-radius:50%;background:var(--comet-green);animation:statusBlink 2s ease-in-out infinite; }
        @keyframes statusBlink { 0%,100%{opacity:1;}50%{opacity:0.3;} }

        /* ── Animations ── */
        @keyframes fadeDown { from{opacity:0;transform:translateY(-20px);}to{opacity:1;transform:translateY(0);} }
        @keyframes fadeUp   { from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);} }
        .reveal       { opacity:0;transform:translateY(40px);transition:opacity 0.9s ease,transform 0.9s ease; }
        .reveal.visible { opacity:1;transform:translateY(0); }
        .reveal-left  { opacity:0;transform:translateX(-40px);transition:opacity 0.9s ease,transform 0.9s ease; }
        .reveal-left.visible { opacity:1;transform:translateX(0); }
        .reveal-right { opacity:0;transform:translateX(40px);transition:opacity 0.9s ease,transform 0.9s ease; }
        .reveal-right.visible { opacity:1;transform:translateX(0); }
        .d1{transition-delay:0.1s;}.d2{transition-delay:0.2s;}.d3{transition-delay:0.3s;}.d4{transition-delay:0.4s;}.d5{transition-delay:0.5s;}

        /* ── Responsive ── */
        @media(max-width:1024px){
            .features-layout{grid-template-columns:1fr;}
            .features-header{position:static;}
            .event-card-inner{grid-template-columns:1fr;}
            .footer-grid{grid-template-columns:1fr 1fr;}
        }
        @media(max-width:768px){
            nav{padding:0 1.5rem;}
            .nav-menu{display:none;position:absolute;top:70px;left:0;right:0;background:rgba(0,0,10,0.97);flex-direction:column;padding:1rem;border-bottom:1px solid rgba(79,195,247,0.1);}
            .nav-menu.active{display:flex;}
            .nav-toggle{display:block;}
            .nav-container{position:relative;}
            .hero-title{font-size:2.5rem;}
            .hero-orbit,.hero-orbit-2{display:none;}
            .cta-band{padding:3rem 2rem;}
            .categories-header{grid-template-columns:1fr;}
            .footer-grid{grid-template-columns:1fr 1fr;gap:2rem;}

            /* Timeline mobile: single column, node on left */
            .timeline::before { left:27px;transform:none; }
            .timeline-step { grid-template-columns:56px 1fr;gap:1.5rem; }
            .timeline-step:nth-child(odd) .timeline-node,
            .timeline-step:nth-child(even) .timeline-node {
                grid-column:1;grid-row:1;
                justify-self:center;
                margin:0;
            }
            .timeline-step:nth-child(odd) .step-content,
            .timeline-step:nth-child(even) .step-content {
                grid-column:2;grid-row:1;
                text-align:left;
                padding:0;
            }
        }
        @media(max-width:480px){
            .footer-grid{grid-template-columns:1fr;}
            .footer-bottom{flex-direction:column;text-align:center;}
            body{cursor:auto;}
            .cursor,.cursor-ring{display:none;}
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
                    <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="20" cy="20" r="8" fill="none" stroke="#00e5ff" stroke-width="1.5"/>
                        <ellipse cx="20" cy="20" rx="18" ry="7" fill="none" stroke="#7c4dff" stroke-width="1" transform="rotate(30 20 20)"/>
                        <ellipse cx="20" cy="20" rx="18" ry="7" fill="none" stroke="#00e5ff" stroke-width="1" opacity="0.4" transform="rotate(-30 20 20)"/>
                        <circle cx="20" cy="20" r="3" fill="#00e5ff"/>
                    </svg>
                </div>
                <span class="nav-brand-text">DevSprint</span>
            </a>
            <button class="nav-toggle" id="nav-toggle" aria-label="Toggle menu">☰</button>
            <ul class="nav-menu" id="nav-menu">
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="hackathons.php">Hackathons</a></li>
                <li><a href="about.php">About</a></li>
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
    <section class="hero">
        <div class="hero-orbit"></div>
        <div class="hero-orbit hero-orbit-2"></div>
        <div class="hero-eyebrow">
            <span class="eyebrow-dot"></span>
            Mission Control · Active · 50+ Events Live
        </div>
        <h1 class="hero-title">
            <span class="line-1">Hack the</span>
            <span class="line-2">Universe.</span>
        </h1>
        <p class="hero-sub">Your launchpad to India's most exciting hackathons. Navigate the cosmos of code, connect with engineers across galaxies, and launch your ideas into orbit.</p>
        <div class="hero-cta">
            <a href="hackathons.php" class="btn btn-primary">
                <span>Explore Events</span>
                <span class="btn-arrow">↗</span>
            </a>
            <a href="about.php" class="btn btn-ghost">
                <span>Our Mission</span>
            </a>
        </div>
        <div class="scroll-indicator">
            <div class="scroll-line"></div>
            <span class="scroll-label">Scroll</span>
        </div>
    </section>

    <!-- ── LIVE TICKER ── -->
    <div class="ticker-section">
        <div class="ticker-track">
            <span class="ticker-item"><span class="dot"></span><strong>CodeFest 2026</strong> — Bangalore · Mar 15–17 · ₹50K</span>
            <span class="ticker-item"><span class="dot"></span><strong>HackIndia 9.0</strong> — Delhi · Apr 5–6 · ₹1L</span>
            <span class="ticker-item"><span class="dot"></span><strong>Smart India Hackathon</strong> — Nationwide · Apr 20 · ₹1.75L</span>
            <span class="ticker-item"><span class="dot"></span><strong>TechGig Code Gladiators</strong> — Online · May 1 · ₹30K</span>
            <span class="ticker-item"><span class="dot"></span><strong>MLH Prime</strong> — Mumbai · May 22–24 · $10K</span>
            <span class="ticker-item"><span class="dot"></span><strong>ETHIndia</strong> — Bangalore · Jun 7–9 · $25K</span>
            <span class="ticker-item"><span class="dot"></span><strong>CodeFest 2026</strong> — Bangalore · Mar 15–17 · ₹50K</span>
            <span class="ticker-item"><span class="dot"></span><strong>HackIndia 9.0</strong> — Delhi · Apr 5–6 · ₹1L</span>
            <span class="ticker-item"><span class="dot"></span><strong>Smart India Hackathon</strong> — Nationwide · Apr 20 · ₹1.75L</span>
            <span class="ticker-item"><span class="dot"></span><strong>TechGig Code Gladiators</strong> — Online · May 1 · ₹30K</span>
            <span class="ticker-item"><span class="dot"></span><strong>MLH Prime</strong> — Mumbai · May 22–24 · $10K</span>
            <span class="ticker-item"><span class="dot"></span><strong>ETHIndia</strong> — Bangalore · Jun 7–9 · $25K</span>
        </div>
    </div>

    <!-- ── STATS ── -->
    <div class="stats-section reveal">
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-number" data-target="50" data-suffix="+">0</span>
                <span class="stat-label">Active Hackathons</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" data-target="1200" data-suffix="+">0</span>
                <span class="stat-label">Developers Onboard</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" data-target="500" data-prefix="₹" data-suffix="L+">0</span>
                <span class="stat-label">Total Prize Pool</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" data-target="38" data-suffix="+">0</span>
                <span class="stat-label">Partner Companies</span>
            </div>
        </div>
    </div>

    <!-- ── FEATURES ── -->
    <section class="content-section">
        <div class="features-layout">
            <div class="features-header reveal-left">
                <div class="section-label">Why DevSprint</div>
                <h2 class="section-title">Your mission<br>control for<br>hackathons.</h2>
                <p class="section-desc">Everything you need to discover, prepare, and dominate hackathons — engineered for the serious builder.</p>
            </div>
            <div class="feature-list">
                <div class="feature-item reveal d1">
                    <div class="feature-icon-wrap">🛰️</div>
                    <div class="feature-text">
                        <h3>Curated Event Discovery</h3>
                        <p>We scan the cosmos daily to surface the best hackathons matched to your skills, location, and tech stack.</p>
                    </div>
                </div>
                <div class="feature-item reveal d2">
                    <div class="feature-icon-wrap">🔭</div>
                    <div class="feature-text">
                        <h3>AI-Powered Project Ideas</h3>
                        <p>Get intelligent project concepts with resource guides, tech stack suggestions, and implementation roadmaps.</p>
                    </div>
                </div>
                <div class="feature-item reveal d3">
                    <div class="feature-icon-wrap">🏆</div>
                    <div class="feature-text">
                        <h3>Prize Intelligence</h3>
                        <p>Full transparency on prizes, sponsors, judging criteria, and networking opportunities before you commit.</p>
                    </div>
                </div>
                <div class="feature-item reveal d4">
                    <div class="feature-icon-wrap">⚡</div>
                    <div class="feature-text">
                        <h3>One-Click Registration</h3>
                        <p>Pre-fill profiles, instant confirmations, calendar sync. Focus on shipping, not paperwork.</p>
                    </div>
                </div>
                <div class="feature-item reveal d5">
                    <div class="feature-icon-wrap">🌌</div>
                    <div class="feature-text">
                        <h3>Community & Team Finder</h3>
                        <p>Find co-founders, teammates, and mentors from a network of 1,200+ builders across India.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ── FEATURED EVENT ── -->
    <div class="event-showcase reveal">
        <div class="event-card-3d" id="event3d">
            <div class="event-card-inner" id="eventCardInner">
                <div class="event-bg-glow"></div>
                <div>
                    <span class="event-badge">🔥 Featured Mission</span>
                    <h2 class="event-title">CodeFest<br>2026</h2>
                    <p class="event-desc">India's premier 48-hour hackathon — a supernova of innovation, mentorship, and networking. 500+ participants, 15 problem tracks, and live pitches to top VCs.</p>
                    <div class="event-meta-grid">
                        <div class="event-meta-item"><div class="meta-key">Location</div><div class="meta-val">🗺️ Bangalore, India</div></div>
                        <div class="event-meta-item"><div class="meta-key">Date</div><div class="meta-val">📅 Mar 15–17, 2026</div></div>
                        <div class="event-meta-item"><div class="meta-key">Prize Pool</div><div class="meta-val">💰 ₹50,000</div></div>
                        <div class="event-meta-item"><div class="meta-key">Format</div><div class="meta-val">👥 Teams of 2–4</div></div>
                    </div>
                    <a href="apply.html" class="btn btn-primary"><span>Register Now</span><span class="btn-arrow">↗</span></a>
                </div>
                <div>
                    <div class="event-img-wrap">
                        <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=700&h=500&fit=crop" alt="CodeFest 2026">
                        <div class="event-img-overlay"></div>
                        <div class="event-img-badge">📡 Live Registration Open</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── CATEGORIES ── -->
    <section class="categories-section">
        <div class="categories-header">
            <div>
                <div class="section-label reveal-left">Explore by Domain</div>
                <h2 class="section-title reveal-left d1">Find your orbit.</h2>
            </div>
            <a href="hackathons.php" class="btn btn-ghost reveal-right" style="white-space:nowrap">View All →</a>
        </div>
        <div class="cat-grid">
            <div class="cat-card reveal d1"><span class="cat-emoji">🤖</span><div class="cat-name">AI / Machine Learning</div><div class="cat-count">14 events</div></div>
            <div class="cat-card reveal d2"><span class="cat-emoji">🔗</span><div class="cat-name">Web3 / Blockchain</div><div class="cat-count">9 events</div></div>
            <div class="cat-card reveal d3"><span class="cat-emoji">🌐</span><div class="cat-name">Web Development</div><div class="cat-count">18 events</div></div>
            <div class="cat-card reveal d4"><span class="cat-emoji">📱</span><div class="cat-name">Mobile Apps</div><div class="cat-count">11 events</div></div>
            <div class="cat-card reveal d5"><span class="cat-emoji">🔒</span><div class="cat-name">Cybersecurity</div><div class="cat-count">6 events</div></div>
            <div class="cat-card reveal d1"><span class="cat-emoji">☁️</span><div class="cat-name">Cloud / DevOps</div><div class="cat-count">8 events</div></div>
            <div class="cat-card reveal d2"><span class="cat-emoji">🎮</span><div class="cat-name">Game Development</div><div class="cat-count">5 events</div></div>
            <div class="cat-card reveal d3"><span class="cat-emoji">🏥</span><div class="cat-name">HealthTech</div><div class="cat-count">7 events</div></div>
        </div>
    </section>

    <!-- ── HOW IT WORKS (Timeline) ── -->
    <div class="timeline-section">
        <div class="section-label" style="justify-content:center;">Mission Brief</div>
        <h2 class="section-title reveal" style="text-align:center;">Launch in 3 steps.</h2>
        <p style="color:var(--text-dim);font-size:0.95rem;margin-top:0.5rem;" class="reveal d1">Simple. Fast. Built for builders.</p>
        <div class="timeline">

            <!-- Step 01: node LEFT of line -->
            <div class="timeline-step reveal">
                <div class="timeline-node">01</div>
                <div class="step-content">
                    <h3>Ignition — Create Profile</h3>
                    <p>Tell us your stack, interests, and experience. Our system maps you to events where you'll thrive.</p>
                </div>
            </div>

            <!-- Step 02: node RIGHT of line -->
            <div class="timeline-step reveal d2">
                <div class="step-content">
                    <h3>Navigation — Explore & Apply</h3>
                    <p>Browse curated events, compare prize structures, assess difficulty — then apply in two clicks.</p>
                </div>
                <div class="timeline-node">02</div>
            </div>

            <!-- Step 03: node LEFT of line -->
            <div class="timeline-step reveal d3">
                <div class="timeline-node">03</div>
                <div class="step-content">
                    <h3>Liftoff — Build & Win</h3>
                    <p>Ship with our curated resources, collaborate with teammates, and pitch to the stars.</p>
                </div>
            </div>

        </div>
    </div>

    <!-- ── SPONSORS ── -->
    <section class="sponsors-section reveal">
        <p class="sponsors-label">Trusted by developers at</p>
        <div class="sponsors-row">
            <div class="sponsor-chip">GOOGLE</div>
            <div class="sponsor-chip">MICROSOFT</div>
            <div class="sponsor-chip">DEVFOLIO</div>
            <div class="sponsor-chip">FLIPKART</div>
            <div class="sponsor-chip">RAZORPAY</div>
            <div class="sponsor-chip">POLYGON</div>
            <div class="sponsor-chip">NETLIFY</div>
        </div>
    </section>

    <!-- ── CTA BAND ── -->
    <div class="cta-band reveal" style="margin:0 2rem 4rem;">
        <h2>Ready for launch?</h2>
        <p>Join 1,200+ developers already sprinting toward the future.</p>
        <a href="login_view.php" class="btn btn-primary"><span>Start Your Mission</span><span class="btn-arrow">↗</span></a>
    </div>

    <!-- ── FOOTER ── -->
    <footer>
        <div class="footer-grid">
            <div class="footer-brand-col">
                <span class="nav-brand-text">DevSprint</span>
                <p>India's premier hackathon discovery platform. Navigate the universe of tech competitions and launch your ideas into orbit.</p>
                <div class="footer-status"><div class="status-dot"></div> All systems operational</div>
            </div>
            <div class="footer-col">
                <h4>Platform</h4>
                <ul>
                    <li><a href="hackathons.php">Hackathons</a></li>
                    <li><a href="#">Project Ideas</a></li>
                    <li><a href="#">Teams</a></li>
                    <li><a href="#">Resources</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Company</h4>
                <ul>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Careers</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Legal</h4>
                <ul>
                    <li><a href="#">Privacy</a></li>
                    <li><a href="#">Terms</a></li>
                    <li><a href="#">Cookies</a></li>
                    <li><a href="#">Sitemap</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 DevSprint · Build faster. Compete smarter. Sprint to success.</p>
            <p>Crafted somewhere in the cosmos 🚀</p>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
    // ── Cursor ──
    const cursor     = document.getElementById('cursor');
    const cursorRing = document.getElementById('cursorRing');
    let mx = 0, my = 0, rx = 0, ry = 0;
    document.addEventListener('mousemove', e => {
        mx = e.clientX; my = e.clientY;
        cursor.style.left = (mx - 6) + 'px';
        cursor.style.top  = (my - 6) + 'px';
    });
    (function animateRing() {
        rx += (mx - rx) * 0.12;
        ry += (my - ry) * 0.12;
        cursorRing.style.left = (rx - 18) + 'px';
        cursorRing.style.top  = (ry - 18) + 'px';
        requestAnimationFrame(animateRing);
    })();
    document.querySelectorAll('a, button').forEach(el => {
        el.addEventListener('mouseenter', () => { cursorRing.style.width='60px'; cursorRing.style.height='60px'; cursor.style.transform='scale(0.4)'; });
        el.addEventListener('mouseleave', () => { cursorRing.style.width='36px'; cursorRing.style.height='36px'; cursor.style.transform='scale(1)'; });
    });

    // ── Three.js Cosmos ──
    (function initThree() {
        const canvas   = document.getElementById('cosmos-canvas');
        const renderer = new THREE.WebGLRenderer({ canvas, antialias:true, alpha:true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        const scene  = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 3000);
        camera.position.z = 600;

        // Stars
        const starCount = 9000;
        const pos = new Float32Array(starCount * 3);
        const col = new Float32Array(starCount * 3);
        for (let i = 0; i < starCount; i++) {
            pos[i*3]   = (Math.random() - 0.5) * 3500;
            pos[i*3+1] = (Math.random() - 0.5) * 3500;
            pos[i*3+2] = (Math.random() - 0.5) * 2500;
            const t = Math.random();
            if (t < 0.6)      { col[i*3]=0.9;  col[i*3+1]=0.93; col[i*3+2]=1.0; }
            else if (t < 0.8) { col[i*3]=0.3;  col[i*3+1]=0.76; col[i*3+2]=0.97; }
            else               { col[i*3]=0.48; col[i*3+1]=0.30; col[i*3+2]=1.0; }
        }
        const geo = new THREE.BufferGeometry();
        geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
        geo.setAttribute('color',    new THREE.BufferAttribute(col, 3));
        const stars = new THREE.Points(geo, new THREE.PointsMaterial({ size:1.5, vertexColors:true, transparent:true, opacity:0.85, sizeAttenuation:true }));
        scene.add(stars);

        function ring(r, color, rx, ry) {
            const m = new THREE.Mesh(
                new THREE.TorusGeometry(r, 0.6, 16, 200),
                new THREE.MeshBasicMaterial({ color, transparent:true, opacity:0.07 })
            );
            m.rotation.x = rx; m.rotation.y = ry;
            scene.add(m); return m;
        }
        const r1 = ring(320, 0x4fc3f7, 1.2, 0.3);
        const r2 = ring(480, 0x7c4dff, 0.5, 1.0);
        const r3 = ring(180, 0x00e676, 0.8, 0.6);

        const shoots = [];
        function spawnShoot() {
            const g = new THREE.BufferGeometry();
            const x = (Math.random()-0.5)*1200, y = 200+Math.random()*300, z = -100+Math.random()*300;
            g.setFromPoints([new THREE.Vector3(x,y,z), new THREE.Vector3(x-100,y-25,z)]);
            const l = new THREE.Line(g, new THREE.LineBasicMaterial({ color:0x00e5ff, transparent:true, opacity:0.9 }));
            scene.add(l);
            shoots.push({ l, vx:-(3+Math.random()*4), vy:-(0.8+Math.random()) });
            setTimeout(() => { scene.remove(l); const idx=shoots.findIndex(s=>s.l===l); if(idx>-1) shoots.splice(idx,1); }, 1200);
        }
        setInterval(spawnShoot, 3500);

        let mouseX = 0, mouseY = 0, scrollY = 0;
        document.addEventListener('mousemove', e => {
            mouseX = (e.clientX / window.innerWidth  - 0.5) * 2;
            mouseY = (e.clientY / window.innerHeight - 0.5) * 2;
        });
        window.addEventListener('scroll', () => { scrollY = window.scrollY; });

        let t = 0;
        (function animate() {
            requestAnimationFrame(animate);
            t += 0.0008;
            stars.rotation.y = t * 0.025 + mouseX * 0.04;
            stars.rotation.x = t * 0.01  + mouseY * 0.02;
            camera.position.y = -scrollY * 0.12;
            r1.rotation.z += 0.001; r2.rotation.z -= 0.0007; r3.rotation.y += 0.0015;
            shoots.forEach(s => { s.l.position.x += s.vx; s.l.position.y += s.vy; s.l.material.opacity -= 0.01; });
            renderer.render(scene, camera);
        })();

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    })();

    // ── Nav scroll ──
    window.addEventListener('scroll', () => {
        document.getElementById('main-nav').classList.toggle('scrolled', window.scrollY > 40);
    });

    // ── Mobile nav ──
    document.getElementById('nav-toggle').addEventListener('click', () => {
        document.getElementById('nav-menu').classList.toggle('active');
    });

    // ── Intersection Observer (reveal) ──
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold:0.1, rootMargin:'0px 0px -60px 0px' });
    document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => io.observe(el));

    // ── Count-up ──
    const countIO = new IntersectionObserver(entries => {
        entries.forEach(({ isIntersecting, target }) => {
            if (!isIntersecting) return;
            const end    = parseInt(target.dataset.target);
            const prefix = target.dataset.prefix || '';
            const suffix = target.dataset.suffix || '';
            let current = 0;
            const step  = end / 55;
            const iv = setInterval(() => {
                current = Math.min(current + step, end);
                target.textContent = prefix + Math.floor(current) + suffix;
                if (current >= end) clearInterval(iv);
            }, 22);
            countIO.unobserve(target);
        });
    }, { threshold:0.5 });
    document.querySelectorAll('.stat-number[data-target]').forEach(el => countIO.observe(el));

    // ── 3D tilt card ──
    const card3d = document.getElementById('event3d');
    const inner  = document.getElementById('eventCardInner');
    if (card3d && inner) {
        card3d.addEventListener('mousemove', e => {
            const r = card3d.getBoundingClientRect();
            const x = (e.clientX - r.left) / r.width  - 0.5;
            const y = (e.clientY - r.top)  / r.height - 0.5;
            inner.style.transform = `rotateY(${x*8}deg) rotateX(${-y*6}deg)`;
        });
        card3d.addEventListener('mouseleave', () => { inner.style.transform = 'rotateY(0) rotateX(0)'; });
    }
    </script>
</body>
</html>