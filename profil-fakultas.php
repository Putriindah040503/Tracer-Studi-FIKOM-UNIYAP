<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Fakultas - Tracer Study FIKOM Universitas Yapis Papua</title>
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
            color: white;
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
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        /* Main Content */
        main {
            padding: 4rem 0;
            background: #f8fafc;
        }

        .page-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .page-header h2 {
            font-size: 3rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
        }

        .page-header p {
            font-size: 1.2rem;
            color: #718096;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* Vision Mission Cards */
        .vision-mission {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .vm-card {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 8px 35px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        .vm-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .vm-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 45px rgba(0,0,0,0.12);
        }

        .vm-card h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .vm-card .icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .vm-card blockquote {
            font-style: italic;
            font-size: 1.1rem;
            color: #4a5568;
            line-height: 1.8;
            border: none;
            padding: 0;
            margin: 0;
        }

        .mission-list {
            list-style: none;
            padding: 0;
        }

        .mission-list li {
            background: #f7fafc;
            margin-bottom: 0.8rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            border-left: 4px solid #667eea;
            color: #4a5568;
            transition: all 0.3s ease;
        }

        .mission-list li:hover {
            background: #edf2f7;
            transform: translateX(5px);
        }

        /* Program Study Section */
        .program-section {
            margin-bottom: 4rem;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h3 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
        }

        .accordion {
            max-width: 1000px;
            margin: 0 auto;
        }

        .accordion-item {
            background: white;
            border: none;
            border-radius: 16px;
            margin-bottom: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 25px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .accordion-item:hover {
            box-shadow: 0 8px 35px rgba(0,0,0,0.12);
        }

        .accordion-header {
            margin: 0;
        }

        .accordion-button {
            background: white;
            border: none;
            padding: 1.5rem 2rem;
            font-size: 1.3rem;
            font-weight: 600;
            color: #2d3748;
            width: 100%;
            text-align: left;
            transition: all 0.3s ease;
            position: relative;
        }

        .accordion-button:not(.collapsed) {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: none;
        }

        .accordion-button::after {
            content: '+';
            position: absolute;
            right: 2rem;
            font-size: 1.5rem;
            font-weight: bold;
            transition: transform 0.3s ease;
        }

        .accordion-button:not(.collapsed)::after {
            content: '−';
            transform: rotate(180deg);
        }

        .accordion-body {
            padding: 2rem;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }

        .accordion-body p {
            margin-bottom: 1.5rem;
            color: #4a5568;
            line-height: 1.7;
        }

        .accordion-body strong {
            color: #2d3748;
            font-weight: 600;
        }

        .accordion-body h6 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .accordion-body ul {
            color: #4a5568;
            padding-left: 1.5rem;
        }

        .accordion-body li {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }

        /* Organization Structure */
        .org-structure {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 8px 35px rgba(0,0,0,0.08);
            text-align: center;
        }

        .org-structure h3 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 2rem;
        }

        .org-structure p {
            color: #718096;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .org-structure img {
            max-width: 100%;
            height: auto;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .org-structure img:hover {
            transform: scale(1.02);
        }

        .org-structure small {
            color: #718096;
        }

        .org-structure small a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .org-structure small a:hover {
            text-decoration: underline;
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

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease forwards;
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

            .page-header h2 {
                font-size: 2.2rem;
            }

            .vision-mission {
                grid-template-columns: 1fr;
            }

            .vm-card {
                padding: 2rem;
            }

            .vm-card h3 {
                font-size: 1.6rem;
            }

            .section-title h3 {
                font-size: 2rem;
            }

            .accordion-button {
                padding: 1rem 1.5rem;
                font-size: 1.1rem;
            }

            .org-structure {
                padding: 2rem 1rem;
            }

            .logo-text h1 {
                font-size: 1.4rem;
            }

            .logo-text p {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .page-header h2 {
                font-size: 1.8rem;
            }

            .vm-card h3 {
                font-size: 1.4rem;
                flex-direction: column;
                text-align: center;
            }

            .accordion-button::after {
                right: 1rem;
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
                    <li><a href="dashboard-tracer.php">Dahboard</a></li>
                    <li><a href="profil-fakultas.php" class="active">Info Fakultas</a></li>
                    <li><a href="isi_kuesioner.php">Isi Kuesioner</a></li>
                    <li><a href="lowongan.php">Lowongan Pekerjaan</a></li>
                    <li><a href="dashboard-statistik.php">Dashboard Statistik</a></li>
                </ul>
                <a href="login.php" class="btn-login">Login</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h2>Profil Fakultas Ilmu Komputer</h2>
                <p>
                    Fakultas Ilmu Komputer Universitas Yapis Papua adalah salah satu pilar penting dalam membentuk generasi unggul di bidang Teknologi Informasi. Kami menaungi dua program studi: <strong>Sistem Informasi</strong> dan <strong>Informatika</strong>, yang siap menjawab tantangan revolusi digital masa kini.
                </p>
            </div>

            <!-- Vision & Mission -->
            <div class="vision-mission">
                <div class="vm-card fade-in-up">
                    <h3>
                        <div class="icon">V</div>
                        Visi
                    </h3>
                    <blockquote>
                        "Menjadikan Fakultas Ilmu Komputer yang Terdepan, Mandiri dan Berkualitas di Bidang Ilmu Komputer yang Profesional dengan Tetap Memegang Teguh Prinsip Keislaman di Kawasan Indonesia Timur pada Tahun 2028."
                    </blockquote>
                </div>

                <div class="vm-card fade-in-up">
                    <h3>
                        <div class="icon">M</div>
                        Misi
                    </h3>
                    <ul class="mission-list">
                        <li>Pendidikan berkualitas di bidang ilmu komputer.</li>
                        <li>Penelitian berorientasi pada pengembangan IPTEK.</li>
                        <li>Pengabdian kepada masyarakat berbasis ilmu komputer.</li>
                        <li>Pendidikan inovatif melalui Prodi SI dan Informatika.</li>
                        <li>Pengembangan penelitian terapan untuk masyarakat Papua.</li>
                        <li>Kemitraan strategis dengan industri dan lembaga.</li>
                        <li>Program pengabdian berbasis teknologi informasi.</li>
                        <li>Peningkatan kompetensi dosen dan staf.</li>
                        <li>Fasilitas pendukung proses belajar dan riset.</li>
                        <li>Evaluasi layanan akademik secara berkala.</li>
                    </ul>
                </div>
            </div>

            <!-- Program Studi -->
            <div class="program-section">
                <div class="section-title">
                    <h3>Program Studi</h3>
                </div>
                <div class="accordion" id="accordionProgramStudi">
                    <!-- Sistem Informasi -->
                    <div class="accordion-item">
                        <div class="accordion-header" id="headingSI">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSI" aria-expanded="true">
                                Program Studi Sistem Informasi
                            </button>
                        </div>
                        <div id="collapseSI" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <p><strong>Visi:</strong> Menuju program studi yang maju dan berkualitas dalam pendidikan, penelitian dan pengabdian masyarakat pada bidang Digital Business Innovation dan System Analyst Developer pada tahun 2028.</p>
                                <h6>Misi:</h6>
                                <ul>
                                    <li>Pendidikan berkualitas dalam bidang digital business dan sistem analyst.</li>
                                    <li>Penelitian dan penerapan dalam bidang sistem informasi dan TI.</li>
                                    <li>Pengabdian kepada masyarakat berbasis informatika.</li>
                                    <li>Kerja sama institusi untuk pengembangan keilmuan.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Informatika -->
                    <div class="accordion-item">
                        <div class="accordion-header" id="headingInformatika">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInformatika">
                                Program Studi Informatika
                            </button>
                        </div>
                        <div id="collapseInformatika" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <p><strong>Visi:</strong> Menuju program studi yang maju dan berkualitas dalam pendidikan, penelitian dan pengabdian masyarakat di bidang informatika, khususnya jaringan komputer, sistem cerdas, dan visi komputer yang inovatif pada tahun 2028.</p>
                                <h6>Misi:</h6>
                                <ul>
                                    <li>Pendidikan bermutu dalam bidang jaringan, AI, dan visi komputer.</li>
                                    <li>Penelitian untuk pengembangan teknologi informatika.</li>
                                    <li>Pengabdian masyarakat berbasis sistem informasi dan informatika.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Struktur Organisasi -->
            <div class="org-structure fade-in-up">
                <h3>Struktur Organisasi</h3>
                <p>Berikut adalah struktur organisasi Fakultas Ilmu Komputer Universitas Yapis Papua:</p>
                <img src="images/Struktur_organisasi.jpg" alt="Struktur Organisasi Fakultas Ilmu Komputer" />
                <br>
                <small>Sumber: <a href="http://fikom.uniyap.ac.id/content/fikom-strukturorganisasi" target="_blank">fikom.uniyap.ac.id</a></small>
            </div>
        </div>
    </main>

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
                        <li><a href="dashboard-tracer.php">Beranda</a></li>
                        <li><a href="profil-fakultas.php">Profil Fakultas</a></li>
                        <li><a href="isi_kuesioner.php">Isi Kuesioner</a></li>
                        <li><a href="lowongan.php">Lowongan Pekerjaan</a></li>
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

        // Custom accordion functionality
        document.addEventListener('DOMContentLoaded', function() {
            const accordionButtons = document.querySelectorAll('.accordion-button');
            
            accordionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const target = document.querySelector(this.getAttribute('data-bs-target'));
                    const isExpanded = !this.classList.contains('collapsed');
                    
                    // Close all other accordions
                    accordionButtons.forEach(otherButton => {
                        if (otherButton !== this) {
                            otherButton.classList.add('collapsed');
                            const otherTarget = document.querySelector(otherButton.getAttribute('data-bs-target'));
                            otherTarget.classList.remove('show');
                        }
                    });
                    
                    // Toggle current accordion
                    if (isExpanded) {
                        this.classList.add('collapsed');
                        target.classList.remove('show');
                    } else {
                        this.classList.remove('collapsed');
                        target.classList.add('show');
                    }
                });
            });
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

        // Animate elements on scroll
        // Animate elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                }
            });
        }, observerOptions);

        // Observe all elements with animation classes
        document.querySelectorAll('.vm-card, .org-structure').forEach(el => {
            observer.observe(el);
        });

        // Add loading animation
        window.addEventListener('load', () => {
            document.body.classList.add('loaded');
        });

        // Image lazy loading fallback
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    </script>
</body>
</html>