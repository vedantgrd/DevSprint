<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db_connect.php';

// Fetch hackathons
$result = $conn->query("SELECT * FROM hackathons ORDER BY date_start ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Browse all available hackathons on DevSprint — India's premier hackathon discovery platform.">
<title>Hackathons | DevSprint · Hack the Universe</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Syne:wght@400;500;600;700&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<!-- Leaflet.js Maps -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<style>
/* ── Hackathon Grid ── */
.hack-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(340px,1fr)); gap:1.5rem; margin-top:2rem; }
.hack-card {
    background:rgba(255,255,255,0.02);
    border:1px solid rgba(79,195,247,0.12);
    border-radius:var(--radius-lg);
    padding:2rem;
    transition:all 0.4s ease;
    position:relative;overflow:hidden;
    display:flex;flex-direction:column;gap:1rem;
}
.hack-card::before {
    content:'';position:absolute;top:0;left:0;right:0;height:2px;
    background:linear-gradient(90deg,var(--plasma-cyan),var(--pulsar-violet));
    transform:scaleX(0);transform-origin:left;transition:transform 0.4s ease;
}
.hack-card:hover::before { transform:scaleX(1); }
.hack-card:hover {
    border-color:rgba(79,195,247,0.3);
    background:rgba(79,195,247,0.03);
    transform:translateY(-6px);
    box-shadow:0 24px 60px rgba(0,229,255,0.1);
}
.hack-card-header { display:flex;align-items:flex-start;justify-content:space-between;gap:1rem; }
.hack-title {
    font-family:'Orbitron',monospace;font-size:1.2rem;font-weight:700;
    color:var(--text-bright);line-height:1.3;
}
.hack-type-badge {
    flex-shrink:0;
    font-family:'JetBrains Mono',monospace;font-size:0.65rem;
    letter-spacing:0.1em;text-transform:uppercase;
    padding:0.25rem 0.75rem;border-radius:40px;
    background:rgba(124,77,255,0.15);border:1px solid rgba(124,77,255,0.3);
    color:var(--pulsar-violet);white-space:nowrap;
}
.hack-meta { display:grid;grid-template-columns:1fr 1fr;gap:0.75rem; }
.hack-meta-item {
    background:rgba(255,255,255,0.02);border:1px solid rgba(79,195,247,0.08);
    border-radius:var(--radius-sm);padding:0.75rem;
}
.hack-meta-key {
    font-family:'JetBrains Mono',monospace;font-size:0.6rem;
    letter-spacing:0.15em;color:var(--text-dim);text-transform:uppercase;margin-bottom:0.25rem;
}
.hack-meta-val { font-size:0.88rem;font-weight:700;color:var(--text-bright); }
.hack-desc {
    color:var(--text-dim);font-size:0.88rem;line-height:1.6;
    display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
}
.hack-actions { margin-top:auto; }

/* Page hero accent */
.hero-badge {
    display:inline-flex;align-items:center;gap:8px;
    font-family:'JetBrains Mono',monospace;font-size:0.72rem;
    letter-spacing:0.18em;text-transform:uppercase;color:var(--plasma-cyan);
    border:1px solid rgba(0,229,255,0.2);padding:0.35rem 1.1rem;
    border-radius:40px;margin-bottom:1.5rem;
}
.hero-badge .dot { width:6px;height:6px;background:var(--plasma-cyan);border-radius:50%;animation:statusBlink 1.5s ease-in-out infinite; }

/* Empty state */
.empty-state {
    text-align:center;padding:5rem 2rem;grid-column:1/-1;
}
.empty-state .empty-icon { font-size:4rem;margin-bottom:1.5rem;opacity:0.5; }
.empty-state h3 { font-family:'Orbitron',monospace;font-size:1.5rem;color:var(--text-mid);margin-bottom:0.75rem; }
.empty-state p { color:var(--text-dim);font-size:0.95rem; }

/* Filter bar */
.filter-bar {
    display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;
    margin-bottom:1.5rem;padding:1.5rem;
    background:rgba(255,255,255,0.01);border:1px solid rgba(79,195,247,0.08);
    border-radius:var(--radius-md);
}
.filter-bar span {
    font-family:'JetBrains Mono',monospace;font-size:0.7rem;
    letter-spacing:0.15em;text-transform:uppercase;color:var(--text-dim);
}
.filter-chip {
    padding:0.4rem 1rem;border-radius:40px;
    font-size:0.8rem;font-weight:600;cursor:pointer;
    border:1px solid rgba(79,195,247,0.15);
    background:transparent;color:var(--text-dim);
    transition:all 0.2s;font-family:'Syne',sans-serif;
}
.filter-chip:hover,.filter-chip.active {
    border-color:var(--plasma-cyan);color:var(--plasma-cyan);
    background:rgba(0,229,255,0.06);
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
            <li><a href="index.php">Home</a></li>
            <li><a href="hackathons.php" class="active">Hackathons</a></li>
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

<div class="page-wrapper">
    <!-- Page Hero -->
    <div class="page-hero" style="padding-top:5rem;">
        <div class="hero-badge"><span class="dot"></span> Live Missions Discovered</div>
        <h1 class="page-hero-title">
            Available <span>Hackathons.</span>
        </h1>
        <p class="page-hero-sub">
            Browse curated hackathons matched to your stack. Filter by domain, location, and prize pool. Apply in seconds.
        </p>
    </div>

    <!-- Hackathons Grid -->
    <section class="content-section" style="padding-top:1rem;">
        <div class="filter-bar reveal">
            <span>Filter:</span>
            <button class="filter-chip active" onclick="filterCards('all',this)">All Events</button>
            <button class="filter-chip" onclick="filterCards('individual',this)">Individual</button>
            <button class="filter-chip" onclick="filterCards('team',this)">Team Only</button>
            <button class="filter-chip" onclick="filterCards('both',this)">Open Format</button>
            <button class="filter-chip" onclick="findNearMe()" id="btnNearMe" style="color:var(--plasma-cyan); border-color:var(--plasma-cyan);">📍 Near Me</button>
            
            <span style="margin-left:auto;">View:</span>
            <button class="filter-chip active" id="btnGridView" onclick="toggleView('grid')">Grid</button>
            <button class="filter-chip" id="btnMapView" onclick="toggleView('map')">Map</button>
        </div>

        <div id="mapContainer" style="display:none; height:600px; width:100%; border-radius: var(--radius-lg); border: 1px solid rgba(79,195,247,0.2); overflow:hidden; margin-top:20px; z-index:10;">
            <div id="hackMap" style="height:100%; width:100%;"></div>
        </div>

        <div class="hack-grid" id="hackGrid">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <div class="hack-card reveal" data-type="<?= strtolower(htmlspecialchars($row['application_type'])) ?>" data-lat="<?= $row['latitude'] ?? '' ?>" data-lng="<?= $row['longitude'] ?? '' ?>">
                    <div class="hack-card-header">
                        <h3 class="hack-title"><?= htmlspecialchars($row['title']) ?></h3>
                        <span class="hack-type-badge"><?= htmlspecialchars($row['application_type']) ?></span>
                    </div>
                    <div class="hack-meta">
                        <div class="hack-meta-item">
                            <div class="hack-meta-key">📅 Dates</div>
                            <div class="hack-meta-val"><?= date('M d', strtotime($row['date_start'])) ?> – <?= date('M d, Y', strtotime($row['date_end'])) ?></div>
                        </div>
                        <div class="hack-meta-item">
                            <div class="hack-meta-key">📍 Location</div>
                            <div class="hack-meta-val">
                                <?= htmlspecialchars($row['location']) ?> 
                                <span class="dist-label" style="display:none; color:#00e5ff; font-size:0.75rem; margin-left:5px; background:rgba(0,229,255,0.1); padding:2px 6px; border-radius:4px; font-weight:bold; letter-spacing:0.05em; border:1px solid rgba(0,229,255,0.2);"></span>
                            </div>
                        </div>
                        <div class="hack-meta-item" style="grid-column:span 2;">
                            <div class="hack-meta-key">💰 Prize Pool</div>
                            <div class="hack-meta-val" style="color:var(--comet-green);font-size:1.05rem;"><?= htmlspecialchars($row['prize_pool']) ?></div>
                        </div>
                    </div>
                    <p class="hack-desc"><?= htmlspecialchars($row['description']) ?></p>
                    
                    <?php
                    // Collect map data
                    if ($row['latitude'] !== null && $row['longitude'] !== null) {
                        $mapData[] = [
                            'id' => $row['id'],
                            'title' => htmlspecialchars($row['title']),
                            'lat' => $row['latitude'],
                            'lng' => $row['longitude'],
                            'prize' => htmlspecialchars($row['prize_pool']),
                            'dates' => date('M d', strtotime($row['date_start'])) . ' - ' . date('M d', strtotime($row['date_end']))
                        ];
                    }
                    ?>
                    <div class="hack-actions" style="display:flex; gap:10px;">
                        <a href="apply_gateway.php?id=<?= $row['id'] ?>" class="btn btn-primary" style="flex:1; justify-content:center;">
                            <span>Apply Now</span><span class="btn-arrow">↗</span>
                        </a>
                        <button class="btn btn-ghost" onclick="downloadICS('<?= htmlspecialchars(addslashes($row['title'])) ?>', '<?= htmlspecialchars(addslashes($row['date_start'])) ?>', '<?= htmlspecialchars(addslashes($row['date_end'])) ?>', '<?= htmlspecialchars(addslashes($row['location'])) ?>', '<?= htmlspecialchars(addslashes($row['description'])) ?>')" style="padding: 0 15px; border-color: rgba(79,195,247,0.3); color: var(--plasma-cyan);" title="Add to Calendar">
                            📅
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">🚀</div>
                    <h3>No missions listed yet</h3>
                    <p>More hackathons are loading into orbit. Check back soon.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA Band -->
    <div class="content-section" style="padding-top:1rem;padding-bottom:4rem;">
        <div style="background:linear-gradient(135deg,rgba(0,229,255,0.05),rgba(124,77,255,0.08),rgba(0,229,255,0.05));border:1px solid rgba(79,195,247,0.15);border-radius:30px;padding:3.5rem 2rem;text-align:center;position:relative;overflow:hidden;" class="reveal">
            <h2 style="font-family:'Orbitron',monospace;font-size:clamp(1.5rem,3vw,2.2rem);font-weight:900;color:var(--text-bright);margin-bottom:0.75rem;">Organize your own mission?</h2>
            <p style="color:var(--text-mid);font-size:1rem;margin-bottom:2rem;">Admins can add and manage hackathons from the dashboard.</p>
            <a href="admin_login.php" class="btn btn-ghost"><span>Admin Portal →</span></a>
        </div>
    </div>
</div>

<!-- FOOTER -->
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
                <li><a href="matchmaking.php">Find Teammates</a></li>
                <li><a href="teams.php">My Teams</a></li>
                <li><a href="profile.php">Profile</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Company</h4>
            <ul>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="index.php">Home</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Account</h4>
            <ul>
                <li><a href="login_view.php">Login</a></li>
                <li><a href="Registerpage_view.php">Register</a></li>
                <li><a href="admin_login.php">Admin</a></li>
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
// Filter hackathon cards
function filterCards(type, btn) {
    document.querySelectorAll('.filter-bar button').forEach(c=> {
        if(c.id !== 'btnGridView' && c.id !== 'btnMapView') c.classList.remove('active');
    });
    btn.classList.add('active');
    document.querySelectorAll('.hack-card').forEach(card => {
        if (type === 'all' || card.dataset.type === type) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

// Map logic
let globalHackMap = null;
const hackData = <?= isset($mapData) ? json_encode($mapData) : '[]' ?>;
let userLocCache = null;

function initMap() {
    if(globalHackMap) {
        if(userLocCache) {
            globalHackMap.flyTo([userLocCache.lat, userLocCache.lng], 9);
        }
        return;
    }
    
    globalHackMap = L.map('hackMap').setView([20.5937, 78.9629], 3);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap &copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(globalHackMap);

    let markersArray = [];
    hackData.forEach(h => {
        let popupContent = `
            <div style="font-family:'Syne', sans-serif; color:#000;">
                <h4 style="margin:0 0 5px 0;">${h.title}</h4>
                <p style="margin:0 0 5px 0; font-size:0.8rem;">📅 ${h.dates}<br>💰 ${h.prize}</p>
                <a href="apply_gateway.php?id=${h.id}" style="color:#7c4dff; font-weight:bold; text-decoration:none;">Apply Now ✨</a>
            </div>
        `;
        let m = L.marker([h.lat, h.lng]).bindPopup(popupContent);
        markersArray.push(m);
    });
    
    if(markersArray.length > 0) {
        let group = L.featureGroup(markersArray).addTo(globalHackMap);
        globalHackMap.fitBounds(group.getBounds().pad(0.1));
    }

    if(userLocCache) {
        globalHackMap.setView([userLocCache.lat, userLocCache.lng], 9);
    } else {
        navigator.geolocation.getCurrentPosition(pos => {
            userLocCache = {lat: pos.coords.latitude, lng: pos.coords.longitude};
            globalHackMap.flyTo([userLocCache.lat, userLocCache.lng], 9);
        }, err => console.log('Location access denied or failed'));
    }
}

function toggleView(view) {
    const grid = document.getElementById('hackGrid');
    const map = document.getElementById('mapContainer');
    const btnG = document.getElementById('btnGridView');
    const btnM = document.getElementById('btnMapView');

    if(view === 'grid') {
        grid.style.display = 'grid';
        map.style.display = 'none';
        btnG.classList.add('active');
        btnM.classList.remove('active');
    } else {
        grid.style.display = 'none';
        map.style.display = 'block';
        btnM.classList.add('active');
        btnG.classList.remove('active');
        initMap();
        setTimeout(() => globalHackMap.invalidateSize(), 100);
    }
}

// Distance Sorting Logic (Haversine)
function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
    var R = 6371; // Radius of the earth in km
    var dLat = deg2rad(lat2-lat1);
    var dLon = deg2rad(lon2-lon1); 
    var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
            Math.sin(dLon/2) * Math.sin(dLon/2); 
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
    return R * c; 
}
function deg2rad(deg) { return deg * (Math.PI/180); }

function findNearMe() {
    if (!navigator.geolocation) {
        alert("Geolocation is not supported by your browser.");
        return;
    }
    const btn = document.getElementById('btnNearMe');
    btn.innerHTML = '⏳ Locating...';
    
    navigator.geolocation.getCurrentPosition(pos => {
        const userLat = pos.coords.latitude;
        const userLng = pos.coords.longitude;
        userLocCache = {lat: userLat, lng: userLng};
        
        let grid = document.getElementById('hackGrid');
        let cards = Array.from(grid.querySelectorAll('.hack-card'));
        
        cards.forEach(card => {
            let lat = parseFloat(card.dataset.lat);
            let lng = parseFloat(card.dataset.lng);
            if(!isNaN(lat) && !isNaN(lng)) {
                let dist = getDistanceFromLatLonInKm(userLat, userLng, lat, lng);
                card.dataset.dist = dist;
                let lbl = card.querySelector('.dist-label');
                lbl.style.display = 'inline-block';
                lbl.innerHTML = `📍 ${dist.toFixed(1)} km away`;
            } else {
                card.dataset.dist = 999999; 
            }
        });
        
        cards.sort((a,b) => parseFloat(a.dataset.dist) - parseFloat(b.dataset.dist));
        cards.forEach(card => grid.appendChild(card));
        
        btn.innerHTML = '📍 Near Me Active';
        toggleView('grid'); // switch back to grid to see results
    }, err => {
        alert("Unable to retrieve location.");
        btn.innerHTML = '📍 Near Me';
    });
}

// Calendar ICS Generation
function downloadICS(title, start, end, location, description) {
    const formatICSDate = (dateStr) => {
        const d = new Date(dateStr);
        return d.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
    };

    let startFormatted = formatICSDate(start);
    // Extend end date by 1 day because ICS standard is exclusive for full day events
    let endDate = new Date(end);
    endDate.setDate(endDate.getDate() + 1);
    let endFormatted = formatICSDate(endDate.toISOString());

    const icsContent = `BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//DevSprint//Hackathon Platform//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTSTART:${startFormatted}
DTEND:${endFormatted}
SUMMARY:${title}
LOCATION:${location}
DESCRIPTION:${description}
BEGIN:VALARM
ACTION:DISPLAY
DESCRIPTION:Hackathon Starting
TRIGGER:-P1D
END:VALARM
END:VEVENT
END:VCALENDAR`;

    const blob = new Blob([icsContent], { type: 'text/calendar;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${title.replace(/\s+/g, '_')}_Date.ics`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>
</body>
</html>
<?php $conn->close(); ?>
