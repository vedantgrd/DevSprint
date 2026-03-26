<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevSprint | Discover Hackathons</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="gradient-bg"></div>
    <div class="particles"></div>

    <!-- Navigation -->
    <nav>
        <div class="nav-container">
            <a href="index.php" class="nav-brand">
                <img src="logo.png" alt="DevSprint Logo">
                <span class="nav-brand-text">DevSprint</span>
            </a>
            <button class="nav-toggle" aria-label="Toggle menu">☰</button>
            <ul class="nav-menu">
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="hackathons.php">Hackathons</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="contact.html">Contact</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="logout.php" class="nav-btn" style="background: #ef4444;">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.html" class="nav-btn">Get Started</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-badge">🚀 Discover Your Next Coding Adventure</div>
        <h1>Build. Compete.<br>Sprint to Success.</h1>
        <p>Your gateway to the most exciting hackathons and coding events. Connect with innovators, showcase your skills, and turn ideas into reality.</p>
        <div class="hero-cta">
            <a href="hackathons.php" class="btn btn-primary">
                Explore Events
                <span>→</span>
            </a>
            <a href="about.html" class="btn btn-secondary">Learn More</a>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="stat-card">
            <div class="stat-number">50+</div>
            <div class="stat-label">Active Hackathons</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">1K+</div>
            <div class="stat-label">Developers Connected</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">₹5L+</div>
            <div class="stat-label">Total Prizes</div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features scroll-fade">
        <div class="section-header">
            <h2>Why Choose DevSprint?</h2>
            <p>Everything you need to succeed in hackathons, all in one place</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🔍</div>
                <h3>Discover Events</h3>
                <p>Browse curated hackathons tailored to your interests and skill level. Never miss an opportunity to showcase your talent.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💡</div>
                <h3>Project Ideas</h3>
                <p>Access a library of innovative project concepts with detailed resource requirements and implementation guides.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏆</div>
                <h3>Track Rewards</h3>
                <p>View prizes, sponsorships, and networking opportunities. Know what you're competing for before you commit.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>Quick Registration</h3>
                <p>Seamless application process with instant confirmations. Focus on building, not paperwork.</p>
            </div>
        </div>
    </section>

    <!-- Highlighted Event -->
    <section class="highlight scroll-fade">
        <div class="highlight-card">
            <div class="highlight-content">
                <span class="highlight-badge">🔥 Featured Event</span>
                <h2>CodeFest 2026</h2>
                <p>Join India's premier 48-hour hackathon focused on web and mobile innovation. Network with industry leaders, mentors, and fellow developers.</p>
                <div class="highlight-meta">
                    <div class="meta-item">
                        <span>📍</span>
                        <strong>Bangalore, India</strong>
                    </div>
                    <div class="meta-item">
                        <span>📅</span>
                        <strong>March 15-17, 2026</strong>
                    </div>
                    <div class="meta-item">
                        <span>💰</span>
                        <strong>₹50,000 Prize Pool</strong>
                    </div>
                </div>
                <a href="apply.html" class="btn btn-primary">Register Now →</a>
            </div>
            <div class="highlight-image">
                <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=600&fit=crop" alt="CodeFest 2026">
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works scroll-fade">
        <div class="section-header">
            <h2>How It Works</h2>
            <p>Get started in three simple steps</p>
        </div>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Create Your Profile</h3>
                <p>Tell us about your skills, interests, and experience level. We'll match you with the perfect hackathons.</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Explore & Apply</h3>
                <p>Browse events, compare prizes and requirements, then apply with just a few clicks.</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Build & Win</h3>
                <p>Use our curated resources to build innovative projects and compete for amazing prizes.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-brand">DevSprint</div>
        <p>© 2026 DevSprint. All rights reserved.</p>
        <p>Build faster. Compete smarter. Sprint to success.</p>
    </footer>

    <script>
        // Create floating particles
        const particlesContainer = document.querySelector('.particles');
        for (let i = 0; i < 30; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.width = Math.random() * 10 + 5 + 'px';
            particle.style.height = particle.style.width;
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 20 + 's';
            particle.style.animationDuration = (Math.random() * 10 + 15) + 's';
            particlesContainer.appendChild(particle);
        }

        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.scroll-fade').forEach(el => {
            observer.observe(el);
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>