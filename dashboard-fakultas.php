<?php
session_start();
include 'koneksi.php';

// Check alumni authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'fakultas') {
    header("Location: login.php");
    exit;
}

// Sample data for dropdowns
$tahunList = [2020, 2021, 2022, 2023, 2024];
$jurusanList = ['Informatika', 'Sistem Informasi'];

$nama = $_SESSION['fakultas_nama_pengguna'] ?? 'fakultas';
// Get basic statistics (sample queries - adjust according to your database)
$totalAlumni = 0;
$alumniVerifikasi = 0;
$kuesionerSelesai = 0;

try {
    // Total alumni
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM alumni");
    if ($result) {
        $totalAlumni = mysqli_fetch_assoc($result)['total'];
    }
    
    // Alumni yang sudah diverifikasi
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM alumni WHERE status_verifikasi = 'verified'");
    if ($result) {
        $alumniVerifikasi = mysqli_fetch_assoc($result)['total'];
    }
    
    // Kuesioner yang sudah selesai
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM alumni WHERE status_kuesioner = 'sudah'");
    if ($result) {
        $kuesionerSelesai = mysqli_fetch_assoc($result)['total'];
    }
    
} catch (Exception $e) {
    // Handle database errors silently
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard Admin - Tracer Study</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="styless.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
       
        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            border-left: 4px solid #00509D;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }

        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #00509D;
            margin: 0;
        }

 
        .stat-card .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }


        /* Chart Section */
        .chart-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .chart-title {
            color: #00509D;
            margin: 0;
            font-weight: 600;
        }


        .chart-controls {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-select {
            max-width: 200px;
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .filter-select:focus {
            border-color: #00509D;
            box-shadow: 0 0 0 0.2rem rgba(0, 80, 157, 0.25);
        }

  
        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00509D, #003B70);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #003B70, #002952);
            transform: translateY(-1px);
        }

        .btn-outline-light {
            border: 1px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
        }

        .btn-outline-light:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.5);
        }

    

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .quick-action-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .quick-action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
            text-decoration: none;
            color: inherit;
        }



        .quick-action-icon {
            font-size: 2rem;
            color: #00509D;
            margin-bottom: 0.5rem;
        }

    </style>
</head>
<body>
     <!-- Header -->
    <?php include 'header.php'; ?>

        <!-- Sidebar -->
        <nav class="sidebar">
            <?php include 'sidebar.php'; ?>
        </nav>


    <main>
        <div class="page-header fade-in-up">
            <h2>
                <i class="bi bi-speedometer2"></i>
                Dashboard Fakultas
            </h2>
            <p class="mb-0 text-muted">Selamat datang, <?= htmlspecialchars($nama) ?>! Kelola sistem tracer study dengan mudah.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid fade-in-up">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #28a745, #20c997);">
                    <i class="bi bi-people"></i>
                </div>
                <h3 class="stat-number"><?= number_format($totalAlumni) ?></h3>
                <p class="stat-label">Total Alumni</p>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #007bff, #0056b3);">
                    <i class="bi bi-check-circle"></i>
                </div>
                <h3 class="stat-number"><?= number_format($alumniVerifikasi) ?></h3>
                <p class="stat-label">Alumni Terverifikasi</p>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #ffc107, #e0a800);">
                    <i class="bi bi-clipboard-check"></i>
                </div>
                <h3 class="stat-number"><?= number_format($kuesionerSelesai) ?></h3>
                <p class="stat-label">Kuesioner Selesai</p>
            </div>

        </div>

        <!-- Quick Actions -->
        <div class="quick-actions fade-in-up">
            <a href="manajemen-alumni.php" class="quick-action-card">
                <div class="quick-action-icon">
                    <i class="bi bi-person-plus"></i>
                </div>
                <h6>Tambah Alumni</h6>
            </a>
            <a href="manajemen_kuesioner.php" class="quick-action-card">
                <div class="quick-action-icon">
                    <i class="bi bi-plus-circle"></i>
                </div>
                <h6>Buat Kuesioner</h6>
            </a>
            <a href="laporan.php" class="quick-action-card">
                <div class="quick-action-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
                <h6>Lihat Laporan</h6>
            </a>
        </div>

        <!-- Chart Section -->
        <div class="chart-section fade-in-up">
            <div class="chart-header">
                <h5 class="chart-title">
                    <i class="bi bi-graph-up"></i>
                    Pelacakan Waktu Alumni Mengisi Kuesioner
                </h5>
                
                <div class="chart-controls">
                    <select id="filterTahun" class="form-select filter-select" aria-label="Filter tahun">
                        <option value="">-- Semua Tahun --</option>
                        <?php foreach ($tahunList as $t): ?>
                            <option value="<?= $t ?>"><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select id="filterJurusan" class="form-select filter-select" aria-label="Filter jurusan">
                        <option value="">-- Semua Jurusan --</option>
                        <?php foreach ($jurusanList as $j): ?>
                            <option value="<?= htmlspecialchars($j) ?>"><?= htmlspecialchars($j) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button class="btn btn-danger" onclick="exportChartPDF()" aria-label="Export chart ke PDF">
                        <i class="bi bi-file-earmark-pdf"></i> Export PDF
                    </button>
                </div>
            </div>
            
            <div style="position: relative; height: 400px;">
                <canvas id="lineChart" aria-label="Grafik pengisian kuesioner alumni"></canvas>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const lineChartCtx = document.getElementById('lineChart').getContext('2d');
        let lineChart;

        // Chart configuration
        const chartConfig = {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Pengisian Kuesioner',
                    data: [],
                    borderColor: '#00509D',
                    backgroundColor: 'rgba(0, 80, 157, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: '#00509D',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#00509D',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        };

        async function loadLineChart(tahun = '', jurusan = '') {
            try {
                // Show loading state
                if (lineChart) {
                    lineChart.data.datasets[0].data = [];
                    lineChart.update();
                }

                const res = await fetch(`get_chart_data.php?tahun=${tahun}&jurusan=${jurusan}`);
                if (!res.ok) throw new Error('Gagal memuat data dari server');

                const data = await res.json();
                const labels = data.map(item => item.bulan);
                const jumlah = data.map(item => Number(item.jumlah));

                if (lineChart) lineChart.destroy();

                chartConfig.data.labels = labels;
                chartConfig.data.datasets[0].data = jumlah;

                lineChart = new Chart(lineChartCtx, chartConfig);
            } catch (error) {
                console.error('Error loading chart:', error);
                // Show error message on chart
                if (lineChart) lineChart.destroy();
                
                const errorCtx = lineChartCtx;
                errorCtx.clearRect(0, 0, errorCtx.canvas.width, errorCtx.canvas.height);
                errorCtx.fillStyle = '#dc3545';
                errorCtx.font = '16px Arial';
                errorCtx.textAlign = 'center';
                errorCtx.fillText('Gagal memuat data grafik', 
                    errorCtx.canvas.width / 2, errorCtx.canvas.height / 2);
            }
        }

        // Event listeners for filters
        document.getElementById('filterTahun').addEventListener('change', () => {
            const tahun = document.getElementById('filterTahun').value;
            const jurusan = document.getElementById('filterJurusan').value;
            loadLineChart(tahun, jurusan);
        });

        document.getElementById('filterJurusan').addEventListener('change', () => {
            const tahun = document.getElementById('filterTahun').value;
            const jurusan = document.getElementById('filterJurusan').value;
            loadLineChart(tahun, jurusan);
        });

        // Export chart to PDF
        function exportChartPDF() {
            try {
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF();
                const canvas = document.getElementById('lineChart');
                const imgData = canvas.toDataURL('image/png');

                pdf.setFontSize(18);
                pdf.text("Dashboard Tracer Study", 20, 20);
                pdf.setFontSize(14);
                pdf.text("Pelacakan Waktu Alumni Mengisi Kuesioner", 20, 35);
                
                pdf.addImage(imgData, 'PNG', 20, 45, 170, 85);
                
                pdf.setFontSize(10);
                pdf.text(`Generated on: ${new Date().toLocaleDateString('id-ID')}`, 20, 140);
                
                pdf.save("grafik-tracer-study.pdf");
            } catch (error) {
                alert('Gagal mengekspor PDF: ' + error.message);
            }
        }

        // Dark mode functionality
        const toggleBtn = document.getElementById('toggleDarkMode');
        const darkModeIcon = document.getElementById('darkModeIcon');

        function updateDarkModeIcon(isDark) {
            darkModeIcon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }

        toggleBtn.addEventListener('click', () => {
            const isDark = document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkmode', isDark);
            updateDarkModeIcon(isDark);
            
            // Update chart colors for dark mode
            if (lineChart && isDark) {
                lineChart.options.scales.y.grid.color = 'rgba(255, 255, 255, 0.1)';
                lineChart.update();
            } else if (lineChart) {
                lineChart.options.scales.y.grid.color = 'rgba(0, 0, 0, 0.1)';
                lineChart.update();
            }
        });

        // Load dark mode preference
        if (localStorage.getItem('darkmode') === 'true') {
            document.body.classList.add('dark-mode');
            updateDarkModeIcon(true);
        }

        // Real-time date and time
        function updateDateTime() {
            const dateTimeElem = document.getElementById('realtimeDateTime');
            const now = new Date();

            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };

            const formatted = now.toLocaleDateString('id-ID', options);
            dateTimeElem.textContent = formatted;
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();

        // Notification functionality

                // Notification functionality
        const notifBtn = document.getElementById('notifBtn');
        const notifDropdown = document.getElementById('notifDropdown');
        const notifList = document.getElementById('notifList');
        const notifCount = document.getElementById('notifCount');

        // Tampilkan/sembunyikan dropdown notifikasi saat tombol diklik
        notifBtn.addEventListener('click', () => {
            const isVisible = notifDropdown.style.display === 'block';
            notifDropdown.style.display = isVisible ? 'none' : 'block';
        });

        // Tutup dropdown jika klik di luar area notifikasi
        document.addEventListener('click', function(event) {
            if (!notifBtn.contains(event.target) && !notifDropdown.contains(event.target)) {
                notifDropdown.style.display = 'none';
            }
        });

        // Fungsi memuat notifikasi dari server (simulasi dummy data, sesuaikan dengan endpoint backend kamu)
        async function loadNotifikasi() {
            try {
                // Ganti ini dengan `fetch('get_notifikasi.php')` jika kamu punya backend
                const dummyData = [
                    { pesan: "Alumni A baru mengisi kuesioner.", waktu: "2 menit lalu" },
                    { pesan: "Alumni B memperbarui datanya.", waktu: "10 menit lalu" }
                ];

                notifList.innerHTML = '';

                dummyData.forEach(notif => {
                    const li = document.createElement('li');
                    li.classList.add('mb-2', 'border-bottom', 'pb-2');
                    li.innerHTML = `
                        <strong>${notif.pesan}</strong><br>
                        <small class="text-muted">${notif.waktu}</small>
                    `;
                    notifList.appendChild(li);
                });

                // Tampilkan badge jumlah notifikasi
                if (dummyData.length > 0) {
                    notifCount.textContent = dummyData.length;
                    notifCount.style.display = 'inline-block';
                } else {
                    notifCount.style.display = 'none';
                }

            } catch (error) {
                console.error('Gagal memuat notifikasi:', error);
            }
        }

        // Panggil fungsi saat halaman selesai dimuat
        document.addEventListener('DOMContentLoaded', loadNotifikasi);

            </script>

</body>
</html>