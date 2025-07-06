<?php
require 'koneksi.php';

$result = mysqli_query($conn, "SELECT * FROM lowongan ORDER BY id DESC");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lowongan Pekerjaan - Tracer Study FIKOM Universitas Yapis Papua</title>
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

        /* Job Cards Grid */
        .jobs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .job-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 35px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            position: relative;
        }

        .job-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .job-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }

        .job-header {
            padding: 2rem 2rem 1rem;
            text-align: center;
            background: linear-gradient(135deg, #f8fafc, #edf2f7);
        }

        .job-logo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin: 0 auto 1rem;
        }

        .job-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .job-company {
            font-size: 1.1rem;
            font-weight: 600;
            color: #667eea;
            margin-bottom: 0.3rem;
        }

        .job-location {
            color: #718096;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .job-body {
            padding: 1.5rem 2rem;
        }

        .job-section {
            margin-bottom: 1.5rem;
        }

        .job-section h6 {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .job-section p {
            color: #718096;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .job-dates {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .date-item {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
            border-left: 3px solid #667eea;
        }

        .date-label {
            font-size: 0.8rem;
            color: #718096;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .date-value {
            font-size: 0.95rem;
            color: #2d3748;
            font-weight: 600;
            margin-top: 0.2rem;
        }

        .job-footer {
            padding: 1.5rem 2rem 2rem;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .apply-btn {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
            margin-bottom: 1rem;
        }

        .apply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.4);
            text-decoration: none;
            color: white;
        }

        .job-meta {
            text-align: center;
            font-size: 0.85rem;
            color: #a0aec0;
        }

        /* Status Badge */
        .status-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: #10b981;
            color: white;
        }

        .status-expired {
            background: #ef4444;
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #718096;
        }

        .empty-state svg {
            width: 120px;
            height: 120px;
            margin-bottom: 2rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #4a5568;
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

            .jobs-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .job-dates {
                grid-template-columns: 1fr;
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

            .job-header,
            .job-body,
            .job-footer {
                padding: 1.5rem;
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
                    <li><a href="dashboard-tracer.php">Dashboard</a></li>
                    <li><a href="profil-fakultas.php">Info Fakultas</a></li>
                    <li><a href="isi_kuesioner.php">Isi Kuesioner</a></li>
                    <li><a href="lowongan.php" class="active">Lowongan Pekerjaan</a></li>
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
                <h2>Lowongan Pekerjaan</h2>
                <p>
                    Temukan peluang karir terbaru yang sesuai dengan bidang keahlian Anda. Kami menyediakan informasi lowongan pekerjaan terkini dari berbagai perusahaan untuk lulusan <strong>Sistem Informasi</strong> dan <strong>Informatika</strong>.
                </p>
            </div>

            <!-- Jobs Grid -->
            <div class="jobs-grid">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <?php
                        $today = date('Y-m-d');
                        $isExpired = $today > $row['tanggal_berakhir'];
                        ?>
                        <div class="job-card fade-in-up">
                            <div class="status-badge <?= $isExpired ? 'status-expired' : 'status-active' ?>">
                                <?= $isExpired ? 'Berakhir' : 'Aktif' ?>
                            </div>
                            
                            <div class="job-header">
                                
                                <img src="uploads/<?= htmlspecialchars($row['foto']) ?>" alt="Logo <?= htmlspecialchars($row['perusahaan']) ?>" class="job-logo">
                                <h3 class="job-title"><?= htmlspecialchars($row['judul']) ?></h3>
                                <div class="job-company"><?= htmlspecialchars($row['perusahaan']) ?></div>
                                <div class="job-location">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                    </svg>
                                    <?= htmlspecialchars($row['lokasi']) ?>
                                </div>
                            </div>

                            <div class="job-body">
                                <div class="job-section">
                                    <h6>Deskripsi Pekerjaan</h6>
                                    <p><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></p>
                                </div>

                                <div class="job-section">
                                    <h6>Persyaratan</h6>
                                    <p><?= nl2br(htmlspecialchars($row['syarat'])) ?></p>
                                </div>

                                <div class="job-dates">
                                    <div class="date-item">
                                        <div class="date-label">Mulai</div>
                                        <div class="date-value"><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></div>
                                    </div>
                                    <div class="date-item">
                                        <div class="date-label">Berakhir</div>
                                        <div class="date-value"><?= date('d M Y', strtotime($row['tanggal_berakhir'])) ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="job-footer">
                                <?php if (!empty($row['link_pendaftaran']) && !$isExpired) : ?>
                                    <a href="<?= htmlspecialchars($row['link_pendaftaran']) ?>" class="apply-btn" target="_blank">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16" style="display: inline; margin-right: 8px;">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9.5 7.293 6.207a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l4-4a1 1 0 00-1.414-1.414L13.414 7.5l1.293-1.293z" clip-rule="evenodd" />
                                        </svg>
                                        Lamar Sekarang
                                    </a>
                                <?php elseif ($isExpired): ?>
                                    <div class="apply-btn" style="background: #e2e8f0; color: #718096; cursor: not-allowed;">
                                        Lowongan Berakhir
                                    </div>
                                <?php else: ?>
                                    <div class="apply-btn" style="background: #e2e8f0; color: #718096; cursor: not-allowed;">
                                        Link Tidak Tersedia
                                    </div>
                                <?php endif; ?>
                                
                                <div class="job-meta">
                                    Diposting pada: <?= date('d M Y, H:i', strtotime($row['created_at'])) ?> WIT
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6z" />
                        </svg>
                        <h3>Belum Ada Lowongan Tersedia</h3>
                        <p>Saat ini belum ada lowongan pekerjaan yang tersedia. Silakan cek kembali nanti untuk informasi lowongan terbaru.</p>
                    </div>
                <?php endif; ?>
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

        // Observe all job cards
        document.querySelectorAll('.job-card').forEach(el => {
            observer.observe(el);
        });

        // Add loading animation
        window.addEventListener('load', () => {
            document.body.classList.add('loaded');
        });
    </script>
</body>
</html>