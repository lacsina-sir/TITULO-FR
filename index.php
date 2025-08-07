<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compass North Land Surveying Services</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
            min-height: 100vh;
            color: #ecf0f1;
            line-height: 1.6;
        }

        /* Header - matching index.php style */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.3);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1000;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 24px;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
        }

        .company-name {
            color: #ecf0f1;
            font-size: 18px;
            font-weight: 300;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .nav-pills {
            display: flex;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
        }

        .nav-pill {
            padding: 10px 18px;
            color: #bdc3c7;
            text-decoration: none;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            background: none;
        }

        .nav-pill:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ecf0f1;
        }

        .nav-pill.active {
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            color: white;
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), 
                        linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
            display: flex;
            align-items: center;
            position: relative;
            padding-top: 80px;
        }

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 40px;
            text-align: center;
            color: #ecf0f1;
        }

        .hero-title {
            font-size: 4.5rem;
            font-weight: 300;
            margin-bottom: 30px;
            line-height: 1.2;
            color: #ecf0f1;
        }

        .hero-title .highlight {
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 500;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 40px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            color: #bdc3c7;
            line-height: 1.6;
        }

        .cta-button {
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            color: white;
            padding: 18px 40px;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 8px 25px rgba(0, 255, 136, 0.3);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 255, 136, 0.4);
        }

        /* Projects Gallery */
        .projects-section {
            padding: 100px 40px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .projects-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 60px;
            color: #ecf0f1;
            font-weight: 300;
        }

        .section-title .highlight {
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .project-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .project-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 255, 136, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .project-card:hover::before {
            left: 100%;
        }

        .project-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
            border-color: rgba(0, 255, 136, 0.3);
        }

        .project-placeholder {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .project-placeholder::after {
            content: '🏔️';
            font-size: 3rem;
            opacity: 0.6;
        }

        .project-text {
            text-align: center;
            color: #bdc3c7;
            font-size: 14px;
            margin-top: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Services Section */
        .services-section {
            padding: 100px 40px;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .services-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .services-list {
            display: grid;
            gap: 20px;
            margin-top: 50px;
        }

        .service-item {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            padding: 25px 30px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: 4px solid #00ff88;
            transition: all 0.3s ease;
            cursor: pointer;
            color: #ecf0f1;
        }

        .service-item:hover {
            background: rgba(0, 255, 136, 0.1);
            transform: translateX(10px);
            border-left-color: #00cc6a;
        }

        .service-number {
            color: #00ff88;
            font-weight: bold;
            margin-right: 15px;
            font-size: 18px;
        }

        /* Tips Section */
        .tips-section {
            padding: 100px 40px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .tips-container {
            max-width: 1000px;
            margin: 0 auto;
            text-align: center;
        }

        .tips-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            padding: 60px 40px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .tips-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(45deg, #00ff88, #00cc6a);
        }

        .tips-title {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #ecf0f1;
            font-weight: 300;
        }

        .tips-subtitle {
            font-size: 1.5rem;
            color: #bdc3c7;
            margin-bottom: 40px;
            font-weight: 400;
        }

        .tips-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            text-align: left;
            margin-top: 30px;
        }

        .tip-item {
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: 3px solid #00ff88;
            color: #ecf0f1;
            transition: all 0.3s ease;
        }

        .tip-item:hover {
            background: rgba(0, 255, 136, 0.1);
            transform: translateY(-2px);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            font-size: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(0, 0, 0, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .header {
                flex-direction: column;
                gap: 20px;
                padding: 20px;
            }
            
            .nav-pills {
                order: -1;
            }
            
            .hero-title {
                font-size: 3.5rem;
            }
            
            .company-name {
                font-size: 16px;
            }
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.8rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .projects-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .tips-list {
                grid-template-columns: 1fr;
            }

            .nav-pills {
                flex-wrap: wrap;
                gap: 5px;
            }

            .nav-pill {
                padding: 8px 14px;
                font-size: 12px;
            }

            .contact-form, .tips-card {
                padding: 40px 30px;
            }
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Animation classes */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo-section">
            <div class="logo">C</div>
            <div class="company-name">Compass North Land Surveying Services</div>
        </div>
        <nav class="nav-pills">
            <a href="#home" class="nav-pill active">Home</a>
            <a href="#about" class="nav-pill">About</a>
            <a href="#projects" class="nav-pill">Projects</a>
            <a href="#services" class="nav-pill">Services</a>
            <a href="#tips" class="nav-pill">Tips</a>
            <a href="login_register.php" class="nav-pill">Log In</a>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1 class="hero-title fade-in">
                Your Trusted Partner<br>
                in <span class="highlight">Land Surveying</span><br>
                and Property Solutions
            </h1>
            <p class="hero-subtitle fade-in">
                At Compass North, we specialize in providing accurate land surveys, clear boundary identification, and secure documentation through cutting-edge technology.
            </p>
            <a href="#services" class="cta-button fade-in">Read More</a>
        </div>
    </section>

    <!-- Projects Gallery -->
    <section id="projects" class="projects-section">
        <div class="projects-container">
            <h2 class="section-title fade-in">Our <span class="highlight">Projects</span></h2>
            <div class="projects-grid fade-in">
                <div class="project-card">
                    <div class="project-placeholder"></div>
                    <div class="project-text">Project Gallery</div>
                </div>
                <div class="project-card">
                    <div class="project-placeholder"></div>
                    <div class="project-text">Survey Documentation</div>
                </div>
                <div class="project-card">
                    <div class="project-placeholder"></div>
                    <div class="project-text">Boundary Mapping</div>
                </div>
                <div class="project-card">
                    <div class="project-placeholder"></div>
                    <div class="project-text">Property Assessment</div>
                </div>
                <div class="project-card">
                    <div class="project-placeholder"></div>
                    <div class="project-text">Land Development</div>
                </div>
                <div class="project-card">
                    <div class="project-placeholder"></div>
                    <div class="project-text">Technical Surveys</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="services-container">
            <h2 class="section-title fade-in">Services <span class="highlight">Offered</span></h2>
            <div class="services-list fade-in">
                <div class="service-item">
                    <span class="service-number">1.</span> Relocation Survey
                </div>
                <div class="service-item">
                    <span class="service-number">2.</span> Subdivision Survey
                </div>
                <div class="service-item">
                    <span class="service-number">3.</span> Topography Survey
                </div>
                <div class="service-item">
                    <span class="service-number">4.</span> Segregation Survey
                </div>
                <div class="service-item">
                    <span class="service-number">5.</span> Consolidation Survey
                </div>
                <div class="service-item">
                    <span class="service-number">6.</span> Sketch Plan, Vicinity Plan
                </div>
                <div class="service-item">
                    <span class="service-number">7.</span> Title Transfer / Titling
                </div>
            </div>
        </div>
    </section>

    <!-- Tips Section -->
    <section id="tips" class="tips-section">
        <div class="tips-container">
            <div class="tips-card fade-in">
                <h2 class="tips-title">Professional <span class="highlight">Tips</span></h2>
                <h3 class="tips-subtitle">Before Buying a Land Property</h3>
                <div class="tips-list">
                    <div class="tip-item">1. Check property title and ownership documents</div>
                    <div class="tip-item">2. Verify property boundaries with a survey</div>
                    <div class="tip-item">3. Research zoning laws and restrictions</div>
                    <div class="tip-item">4. Inspect for easements and right-of-ways</div>
                    <div class="tip-item">5. Confirm access to utilities and roads</div>
                    <div class="tip-item">6. Review environmental and flood zone data</div>
                    <div class="tip-item">7. Check for liens and encumbrances</div>
                    <div class="tip-item">8. Consider future development potential</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    

    <footer class="footer">
        Copyright 2025 TITULO.
    </footer>

    <script>
        // Smooth scrolling for navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Active navigation highlighting
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('section');
            const navItems = document.querySelectorAll('.nav-pill');
            
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (scrollY >= sectionTop - 200) {
                    current = section.getAttribute('id');
                }
            });

            navItems.forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('href') === '#' + current) {
                    item.classList.add('active');
                }
            });
        });

        // Fade in animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Form submission
        document.querySelector('.contact-form').addEventListener('submit', (e) => {
            e.preventDefault();
            alert('Thank you for your message! We will get back to you soon.');
        });

        // Initialize animations
        setTimeout(() => {
            document.querySelectorAll('.hero .fade-in').forEach((el, index) => {
                setTimeout(() => {
                    el.classList.add('visible');
                }, index * 200);
            });
        }, 300);
    </script>
    <!-- Botpress Chatbot -->
    <script src="https://cdn.botpress.cloud/webchat/v3.2/inject.js" defer></script>
    <script src="https://files.bpcontent.cloud/2025/01/13/15/20250113152019-KCRF9KEX.js" defer></script>

</body>
</html>