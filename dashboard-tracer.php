<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracer Study FIKOM Universitas Yapis Papua</title>
    <style>
        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8fafc;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background: #023E8A;
            color: #fff;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo img {
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(255,255,255,0.2);
        }

        .logo-text h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .logo-text p {
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 300;
        }

        /* Navigation Styles */
        nav {
            background: white;
            padding: 0.8rem 0;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 4px;
        }

        .menu-toggle span {
            width: 25px;
            height: 3px;
            background: #333;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .menu {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .menu a {
            text-decoration: none;
            color: #4a5568;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
        }

        .menu a:hover, .menu a.active {
            color: #667eea;
            background: #f7fafc;
        }

        .btn-login {
            background: #023E8A;
            color: white !important;
            padding: 0.6rem 1.5rem !important;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-login:hover {
            background: #023E8A;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        /* Hero Section */
        .hero {
            background:#ffffff;
            color: #023E8A;
            padding: 4rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><radialGradient id="b" cx="50" cy="50" r="50"><stop offset="0" stop-color="rgba(255,255,255,.1)"/><stop offset="1" stop-color="rgba(255,255,255,0)"/></radialGradient></defs><circle cx="50" cy="10" r="10" fill="url(%23b)"/></svg>') repeat;
            opacity: 0.1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero h2 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2.5rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0.95;
            line-height: 1.8;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.8rem 2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #023E8A;
            color: white;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .btn-primary:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .btn-secondary {
            background: #023E8A;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        /* Section Styles */
        section {
            padding: 4rem 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
        }

        .section-title p {
            font-size: 1.1rem;
            color: #718096;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* Features Section */
        .features {
            background: white;
        }

        .feature-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 35px rgba(0,0,0,0.12);
        }

        .icon-container {
            width: 70px;
            height: 70px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .icon-container svg {
            width: 35px;
            height: 35px;
            stroke: white;
            fill: none;
            stroke-width: 2;
        }

        .feature-card h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: #718096;
            line-height: 1.7;
        }

        /* Stats Section */
        .stats {
            background: #f7fafc;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 25px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 35px rgba(0,0,0,0.12);
        }

        .stat-card h3 {
            font-size: 3rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .stat-card p {
            color: #4a5568;
            font-weight: 500;
            font-size: 1rem;
        }

        /* Footer */
        footer {
            background: #2d3748;
            color: white;
            padding: 3rem 0 1rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-about h3 {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: white;
        }

        .footer-about p {
            color: #cbd5e0;
            line-height: 1.7;
        }

        .footer-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: white;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: #cbd5e0;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
        }

        .contact-info {
            list-style: none;
        }

        .contact-info li {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1rem;
            color: #cbd5e0;
        }

        .contact-info svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .footer-bottom {
            border-top: 1px solid #4a5568;
            padding-top: 1rem;
            text-align: center;
            color: #cbd5e0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }

            .menu-toggle {
                display: flex;
            }

            .menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                flex-direction: column;
                padding: 1rem 0;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                gap: 0;
            }

            .menu.active {
                display: flex;
            }

            .menu li {
                width: 100%;
            }

            .menu a {
                display: block;
                padding: 1rem 2rem;
                border-radius: 0;
            }

            .hero h2 {
                font-size: 2.2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .section-title h2 {
                font-size: 2rem;
            }

            .feature-cards {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .news-cards {
                grid-template-columns: 1fr;
            }

            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .logo-text h1 {
                font-size: 1.4rem;
            }

            .logo-text p {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stat-card h3 {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
   <header>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <img src="images/logoYapis.png" alt="Logo FIKOM UNIYAP" width="60" height="60">
                <div class="logo-text">
                    <h1>Tracer Study Fakultas Ilmu Komputer</h1>
                    <p>Universitas Yapis Papua</p>
                </div>
            </div>
        </div>
    </div>
</header>

    <!-- Navigation -->
    <nav>
        <div class="container">
            <div class="navbar">
                <div class="menu-toggle" id="menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <ul class="menu" id="menu">
                    <li><a href="dashboard-tracer" class="active">Dashboard</a></li>
                    <li><a href="profil-fakultas.php">Info Fakultas</a></li>
                    <li><a href="isi_kuesioner.php">Isi Kuesioner</a></li>
                    <li><a href="lowongan.php">Lowongan Pekerjaan</a></li>
                    <li><a href="dashboard-statistik.php">Dashboard Statistik</a></li>
                </ul>
                <a href="login.php" class="btn-login">Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>
  <span>Tracer Study Fakultas Ilmu Komputer</span><br>
  <span>Universitas Yapis Papua</span>
</h2>
                <p>Sistem penelusuran alumni untuk mengetahui perkembangan karir dan kontribusi lulusan Fakultas Ilmu Komputer Universitas Yapis Papua di dunia kerja.</p>
                <div class="hero-buttons">
                    <a href="isi_kuesioner.php" class="btn btn-primary">Isi Kuesioner</a>
                    <a href="dashboard-statistik.php" class="btn btn-secondary">Lihat Statistik</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>Fitur Tracer Study</h2>
                <p>Sistem tracer study kami menyediakan berbagai fitur untuk membantu alumni dan institusi dalam mengevaluasi dan mengembangkan kurikulum pendidikan.</p>
            </div>
            <div class="feature-cards">
                <div class="feature-card">
                    <div class="icon-container">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <h3>Kuesioner Alumni</h3>
                    <p>Isi kuesioner tracer study untuk memberikan informasi terkait perkembangan karir dan kebutuhan kompetensi di industri kerja.</p>
                </div>
                <div class="feature-card">
                    <div class="icon-container">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3>Statistik & Visualisasi</h3>
                    <p>Akses dashboard interaktif yang menampilkan data statistik mengenai distribusi alumni berdasarkan pekerjaan, gaji, dan lainnya.</p>
                </div>
                <div class="feature-card">
                    <div class="icon-container">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3>Lowongan Pekerjaan</h3>
                    <p>Temukan informasi lowongan pekerjaan terbaru dari berbagai perusahaan dan instansi yang bermitra dengan FIKOM UNIYAP.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="section-title">
                <h2>Statistik Alumni</h2>
                <p>Data statistik hasil tracer study alumni FIKOM Universitas Yapis Papua</p>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>85%</h3>
                    <p>Tingkat Penyerapan di Dunia Kerja</p>
                </div>
                <div class="stat-card">
                    <h3>6</h3>
                    <p>Bulan Rata-rata Waktu Tunggu Kerja</p>
                </div>
                <div class="stat-card">
                    <h3>70%</h3>
                    <p>Bekerja Sesuai Bidang</p>
                </div>
                <div class="stat-card">
                    <h3>1000+</h3>
                    <p>Alumni Terdata</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-about">
                    <h3>Tracer Study FIKOM UNIYAP</h3>
                    <p>Sistem penelusuran alumni untuk mengetahui perkembangan karir dan kontribusi lulusan Fakultas Ilmu Komputer Universitas Yapis Papua di dunia kerja.</p>
                </div>
                <div class="footer-links-container">
                    <h4 class="footer-title">Menu Utama</h4>
                    <ul class="footer-links">
                        <li><a href="#">Beranda</a></li>
                        <li><a href="profil-fakultas.php">Profil Fakultas</a></li>
                        <li><a href="#">Isi Kuesioner</a></li>
                        <li><a href="#">Lowongan Pekerjaan</a></li>
                        <li><a href="#">Dashboard Statistik</a></li>
                    </ul>
                </div>
                <div class="footer-links-container">
                    <h4 class="footer-title">Link Terkait</h4>
                    <ul class="footer-links">
                        <li><a href="#">Website UNIYAP</a></li>
                        <li><a href="#">FIKOM UNIYAP</a></li>
                        <li><a href="#">Kemahasiswaan</a></li>
                        <li><a href="#">E-Learning</a></li>
                        <li><a href="#">Perpustakaan</a></li>
                    </ul>
                </div>
                <div class="footer-links-container">
                    <h4 class="footer-title">Kontak Kami</h4>
                    <ul class="contact-info">
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                            Jl. Sam Ratulangi No. 11, Jayapura
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                            </svg>
                            (0967) 123456
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M14.243 5.757a6 6 0 10-.986 9.284 1 1 0 111.087 1.678A8 8 0 1118 10a3 3 0 01-4.8 2.401A4 4 0 1114 10a1 1 0 102 0c0-1.537-.586-3.07-1.757-4.243zM12 10a2 2 0 10-4 0 2 2 0 004 0z" clip-rule="evenodd" />
                            </svg>
                            fikom@uniyap.ac.id
                        </li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Tracer Study FIKOM Universitas Yapis Papua. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menu-toggle');
        const menu = document.getElementById('menu');

        menuToggle.addEventListener('click', () => {
            menu.classList.toggle('active');
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Add scroll effect to navigation
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav');
            if (window.scrollY > 100) {
                nav.style.boxShadow = '0 2px 20px rgba(0,0,0,0.15)';
            } else {
                nav.style.boxShadow = '0 2px 15px rgba(0,0,0,0.08)';
            }
        });

        // Animate cards on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all cards for animation
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.feature-card, .stat-card, .news-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                observer.observe(card);
            });
        });
    </script>
</body>
</html>