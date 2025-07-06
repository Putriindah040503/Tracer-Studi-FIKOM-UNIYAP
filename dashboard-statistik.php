<?php
require_once 'koneksi.php';
require_once 'function.php';

$statusData = getStatusChartData();
$trendData = getTrendKelulusanData();
$jurusanData = getAlumniPerJurusan();
$tahunList = getTahunLulusList();

$totalAlumni = getTotalAlumni();
$bekerja = getAlumniBekerja();
$wirausaha = getAlumniWirausaha();
$studi = getAlumniStudi();
$menganggur = getAlumniMenganggur();
$responseRate = getResponseRate();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Statistik Alumni - Tracer Study</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            background: #023E8A;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .card {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }
        .card h4 {
            margin-bottom: 5px;
            color: #666;
        }
        .card p {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            color: #007bff;
        }
        .charts-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        .chart-section {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .chart-section h5 {
            margin-bottom: 1rem;
            font-weight: 600;
            color: #444;
        }
        .filter-select {
            margin-bottom: 1rem;
        }
        canvas {
            max-width: 100%;
        }
        @media(min-width: 768px) {
            .charts-container {
                grid-template-columns: 1fr 1fr;
            }
            .charts-container .chart-section:last-child {
                grid-column: span 2;
            }
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

    </style>
</head>
<body>
     <header>
        <div class="header-content">
            <div class="logo">
                <img src="images/logoYapis.png" alt="Logo FIKOM UNIYAP" width="60" height="60">
                <div class="logo-text">
                    <h1>Tracer Study Fakultas Ilmu Komputer</h1>
                    <p>Universitas Yapis Papua</p>
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
                    <li><a href="dashboard-tracer.php" >Dashboard</a></li>
                    <li><a href="profil-fakultas.php">Info Fakultas</a></li>
                    <li><a href="isi_kuesioner.php">Isi Kuesioner</a></li>
                    <li><a href="lowongan.php">Lowongan Pekerjaan</a></li>
                    <li><a href="dashboard-statistik.php"class="active">Dashboard Statistik</a></li>
                </ul>
                <a href="login.php" class="btn-login">Login</a>
            </div>
        </div>
    </nav>


    <section class="container">
        <!-- Kartu Statistik -->
        <div class="stats-cards">
            <div class="card"><h4>Total Alumni</h4><p><?= $totalAlumni ?></p></div>
            <div class="card"><h4>Bekerja</h4><p><?= $bekerja ?></p></div>
            <div class="card"><h4>Wirausaha</h4><p><?= $wirausaha ?></p></div>
            <div class="card"><h4>Studi Lanjut</h4><p><?= $studi ?></p></div>
            <div class="card"><h4>Belum Bekerja</h4><p><?= $menganggur ?></p></div>
            <div class="card"><h4>Response Rate</h4><p><?= $responseRate ?>%</p></div>
        </div>

        <!-- Grafik -->
        <div class="charts-container fade-in-up">
            <div class="chart-section">
                <h5><i class="bi bi-pie-chart"></i> Distribusi Status Pekerjaan</h5>
                <canvas id="statusChart"></canvas>
            </div>

            <div class="chart-section">
                <h5><i class="bi bi-graph-up"></i> Tren Kelulusan per Tahun</h5>
                <select id="tahunFilter" class="form-select filter-select">
                    <option value="all">Semua Tahun</option>
                    <?php foreach ($tahunList as $th): ?>
                        <option value="<?= $th ?>"><?= $th ?></option>
                    <?php endforeach; ?>
                </select>
                <canvas id="trendChart"></canvas>
            </div>

            <div class="chart-section">
                <h5><i class="bi bi-bar-chart"></i> Alumni per Jurusan</h5>
                <canvas id="jurusanChart"></canvas>
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
    const statusData = <?= json_encode($statusData) ?>;
    const trendData = <?= json_encode($trendData) ?>;
    const jurusanData = <?= json_encode($jurusanData) ?>;

    const trendChartCanvas = document.getElementById('trendChart').getContext('2d');
    const trendChart = new Chart(trendChartCanvas, {
        type: 'line',
        data: trendData,
        options: {
            responsive: true,
            tension: 0.4,
            plugins: { legend: { display: true } }
        }
    });

    // Render chart lainnya
    new Chart(document.getElementById('statusChart').getContext('2d'), {
        type: 'pie',
        data: statusData,
        options: { responsive: true }
    });

    new Chart(document.getElementById('jurusanChart').getContext('2d'), {
        type: 'bar',
        data: jurusanData,
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Filter Tahun Kelulusan
    document.getElementById('tahunFilter').addEventListener('change', function () {
        const selectedYear = this.value;
        const originalData = <?= json_encode($trendData) ?>;
        const labels = originalData.labels;
        const data = originalData.datasets[0].data;

        let filteredLabels = labels;
        let filteredData = data;

        if (selectedYear !== 'all') {
            filteredLabels = labels.filter((label, i) => label === selectedYear);
            filteredData = data.filter((_, i) => labels[i] === selectedYear);
        }

        trendChart.data.labels = filteredLabels;
        trendChart.data.datasets[0].data = filteredData;
        trendChart.update();
    });
    </script>
</body>
</html>
