<?php
session_start();
include 'koneksi.php';

// Check admin authentication
if (!isset($_SESSION['id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'fakultas')) {
    header("Location: login.php");
    exit;
}

// Get alumni_id from URL
$alumni_id = isset($_GET['alumni_id']) ? intval($_GET['alumni_id']) : 0;

if (!$alumni_id) {
    echo "<script>alert('ID Alumni tidak valid!'); window.location.href='hasil_kuesioner.php';</script>";
    exit;
}

// Get alumni information
$alumni_query = "SELECT * FROM alumni WHERE id = ?";
$stmt = mysqli_prepare($conn, $alumni_query);
mysqli_stmt_bind_param($stmt, 'i', $alumni_id);
mysqli_stmt_execute($stmt);
$alumni_result = mysqli_stmt_get_result($stmt);
$alumni_data = mysqli_fetch_assoc($alumni_result);

if (!$alumni_data) {
    echo "<script>alert('Data alumni tidak ditemukan!'); window.location.href='hasil_kuesioner.php';</script>";
    exit;
}

// Get all answers from this alumni with question details
$jawaban_query = "
    SELECT 
        kj.id,
        kj.jawaban,
        kj.waktu_pengisian,
        kp.pertanyaan,
        kp.tipe_input,
        kk.nama_kategori,
        kp.urutan,
        ko.opsi_text
    FROM kuesioner_jawaban kj
    JOIN kuesioner_pertanyaan kp ON kj.pertanyaan_id = kp.id
    JOIN kuesioner_kategori kk ON kp.kategori_id = kk.id
    LEFT JOIN kuesioner_opsi ko ON kj.opsi_id = ko.id
    WHERE kj.alumni_id = ? AND kj.is_latest = 1
    ORDER BY kk.id, kp.urutan
";

$stmt = mysqli_prepare($conn, $jawaban_query);
mysqli_stmt_bind_param($stmt, 'i', $alumni_id);
mysqli_stmt_execute($stmt);
$jawaban_result = mysqli_stmt_get_result($stmt);

$jawaban_data = [];
$kategori_data = [];

while ($row = mysqli_fetch_assoc($jawaban_result)) {
    $kategori = $row['nama_kategori'];
    if (!isset($kategori_data[$kategori])) {
        $kategori_data[$kategori] = [];
    }
    $kategori_data[$kategori][] = $row;
    $jawaban_data[] = $row;
}

// Get summary statistics
$total_pertanyaan_query = "SELECT COUNT(*) as total FROM kuesioner_pertanyaan WHERE aktif = 1";
$total_pertanyaan_result = mysqli_query($conn, $total_pertanyaan_query);
$total_pertanyaan = mysqli_fetch_assoc($total_pertanyaan_result)['total'];

$total_dijawab = count($jawaban_data);
$persentase_kelengkapan = $total_pertanyaan > 0 ? round(($total_dijawab / $total_pertanyaan) * 100, 1) : 0;

// Get the earliest completion date
$tanggal_pengisian = !empty($jawaban_data) ? date('d/m/Y', strtotime($jawaban_data[0]['waktu_pengisian'])) : '-';

$pageTitle = "Jawaban Kuesioner - " . $alumni_data['nama_lengkap'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="styless.css?v=1.0" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .alumni-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .category-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            margin: 30px 0 20px 0;
            padding: 15px;
            border-radius: 10px;
        }
        .question-card {
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #007bff;
            transition: transform 0.2s;
        }
        .question-card:hover {
            transform: translateY(-2px);
        }
        .answer-text {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
            word-wrap: break-word;
        }
        .question-type-badge {
            font-size: 0.8em;
        }
        .timestamp {
            font-size: 0.85em;
            color: #6c757d;
        }
        .export-buttons {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1000;
        }
        .btn-float {
            margin-bottom: 10px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transition: transform 0.2s;
        }
        .btn-float:hover {
            transform: scale(1.1);
        }
        .print-hide {
            display: block;
        }
        @media print {
            .print-hide {
                display: none !important;
            }
            .question-card {
                page-break-inside: avoid;
                margin-bottom: 15px;
                box-shadow: none;
                border: 1px solid #ddd;
            }
            .alumni-info-card {
                background: #f8f9fa !important;
                color: #333 !important;
                border: 1px solid #ddd;
            }
            .category-header {
                background: #e9ecef !important;
                color: #333 !important;
                border: 1px solid #ddd;
            }
        }
        .progress-card {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        .empty-answers {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <nav class="sidebar print-hide"><?php include 'sidebar.php'; ?></nav>

    <!-- Export Buttons -->
    <div class="export-buttons print-hide">
        <button class="btn btn-success btn-float" onclick="exportToExcel()" title="Export ke Excel">
            <i class="fas fa-file-excel"></i>
        </button>
        <button class="btn btn-info btn-float" onclick="exportToPDF()" title="Export ke PDF">
            <i class="fas fa-file-pdf"></i>
        </button>
        <button class="btn btn-warning btn-float" onclick="window.print()" title="Print">
            <i class="fas fa-print"></i>
        </button>
    </div>

    <main>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 print-hide">
            <div>
                <h2><i class="fas fa-user-graduate"></i> Detail Jawaban Kuesioner</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="hasil_kuesioner.php">Laporan Alumni</a></li>
                        <li class="breadcrumb-item active">Detail Jawaban</li>
                    </ol>
                </nav>
            </div>
            <a href="hasil_kuesioner.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Alumni Information -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card alumni-info-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4><i class="fas fa-user"></i> <?= htmlspecialchars($alumni_data['nama_lengkap']) ?></h4>
                                <div class="row mt-3">
                                    <div class="col-sm-6">
                                        <p class="mb-2"><strong>NPM:</strong> <?= htmlspecialchars($alumni_data['npm']) ?></p>
                                        <p class="mb-2"><strong>Program Studi:</strong> <?= htmlspecialchars($alumni_data['program_studi']) ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p class="mb-2"><strong>Tahun Lulus:</strong> <?= htmlspecialchars($alumni_data['tahun_lulus']) ?></p>
                                        <p class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($alumni_data['email'] ?? '-') ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="fas fa-user-circle fa-5x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card progress-card">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-pie fa-3x mb-3"></i>
                        <h3><?= $persentase_kelengkapan ?>%</h3>
                        <p class="mb-2">Kelengkapan Jawaban</p>
                        <small><?= $total_dijawab ?> dari <?= $total_pertanyaan ?> pertanyaan</small>
                        <div class="progress mt-3" style="height: 8px;">
                            <div class="progress-bar bg-light" role="progressbar" style="width: <?= $persentase_kelengkapan ?>%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-question-circle fa-2x mb-2"></i>
                        <h4><?= $total_dijawab ?></h4>
                        <small>Total Jawaban</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-list fa-2x mb-2"></i>
                        <h4><?= count($kategori_data) ?></h4>
                        <small>Kategori Dijawab</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar fa-2x mb-2"></i>
                        <h4><?= $tanggal_pengisian ?></h4>
                        <small>Tanggal Pengisian</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-percentage fa-2x mb-2"></i>
                        <h4><?= $persentase_kelengkapan ?>%</h4>
                        <small>Persentase Lengkap</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Answers by Category -->
        <?php if (empty($jawaban_data)): ?>
            <div class="card">
                <div class="card-body empty-answers">
                    <i class="fas fa-inbox fa-5x mb-3"></i>
                    <h4>Tidak Ada Jawaban</h4>
                    <p class="text-muted">Alumni ini belum mengisi kuesioner.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($kategori_data as $kategori => $questions): ?>
                <div class="category-header">
                    <h5 class="mb-0">
                        <i class="fas fa-folder-open"></i> <?= htmlspecialchars($kategori) ?>
                        <span class="badge bg-light text-dark float-end"><?= count($questions) ?> pertanyaan</span>
                    </h5>
                </div>

                <?php foreach ($questions as $question): ?>
                    <div class="card question-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h6 class="card-title mb-0 flex-grow-1">
                                    <span class="badge bg-secondary question-type-badge me-2">
                                        <?= ucfirst($question['tipe_input']) ?>
                                    </span>
                                    <?= htmlspecialchars($question['pertanyaan']) ?>
                                </h6>
                                <small class="timestamp text-nowrap ms-3">
                                    <i class="fas fa-clock"></i>
                                    <?= date('d/m/Y H:i', strtotime($question['waktu_pengisian'])) ?>
                                </small>
                            </div>

                            <div class="answer-text">
                                <?php if ($question['tipe_input'] === 'radio' || $question['tipe_input'] === 'select'): ?>
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <?= htmlspecialchars($question['opsi_text'] ?? $question['jawaban']) ?>
                                <?php elseif ($question['tipe_input'] === 'checkbox'): ?>
                                    <?php 
                                    $jawaban_array = json_decode($question['jawaban'], true);
                                    if (is_array($jawaban_array)):
                                    ?>
                                        <?php foreach ($jawaban_array as $jawaban_item): ?>
                                            <div><i class="fas fa-check text-success me-2"></i><?= htmlspecialchars($jawaban_item) ?></div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <?= htmlspecialchars($question['jawaban']) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div style="white-space: pre-wrap;"><?= htmlspecialchars($question['jawaban']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Footer Info -->
        <div class="card mt-4 print-hide">
            <div class="card-body bg-light">
                <div class="row text-center">
                    <div class="col-md-4">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Data terakhir diperbarui: <?= date('d/m/Y H:i') ?>
                        </small>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">
                            <i class="fas fa-database"></i>
                            Total data tersimpan: <?= $total_dijawab ?> jawaban
                        </small>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt"></i>
                            Data pribadi dilindungi sistem
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    
    <script>
        // Export to Excel function
        function exportToExcel() {
            const alumniName = '<?= addslashes($alumni_data['nama_lengkap']) ?>';
            const npm = '<?= addslashes($alumni_data['npm']) ?>';
            
            // Prepare data for Excel
            const data = [
                ['Detail Jawaban Kuesioner Alumni'],
                [''],
                ['Nama Lengkap', alumniName],
                ['NPM', npm],
                ['Program Studi', '<?= addslashes($alumni_data['program_studi']) ?>'],
                ['Tahun Lulus', '<?= addslashes($alumni_data['tahun_lulus']) ?>'],
                ['Email', '<?= addslashes($alumni_data['email'] ?? '-') ?>'],
                [''],
                ['Statistik'],
                ['Total Pertanyaan', '<?= $total_pertanyaan ?>'],
                ['Total Dijawab', '<?= $total_dijawab ?>'],
                ['Persentase Kelengkapan', '<?= $persentase_kelengkapan ?>%'],
                [''],
                ['Kategori', 'Pertanyaan', 'Tipe Input', 'Jawaban', 'Waktu Pengisian']
            ];

            <?php foreach ($jawaban_data as $jawaban): ?>
            data.push([
                '<?= addslashes($jawaban['nama_kategori']) ?>',
                '<?= addslashes($jawaban['pertanyaan']) ?>',
                '<?= addslashes($jawaban['tipe_input']) ?>',
                '<?= addslashes($jawaban['opsi_text'] ?? $jawaban['jawaban']) ?>',
                '<?= date('d/m/Y H:i', strtotime($jawaban['waktu_pengisian'])) ?>'
            ]);
            <?php endforeach; ?>

            // Create workbook and worksheet
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(data);
            
            // Set column widths
            ws['!cols'] = [
                { width: 20 },
                { width: 50 },
                { width: 15 },
                { width: 40 },
                { width: 20 }
            ];

            XLSX.utils.book_append_sheet(wb, ws, 'Jawaban Kuesioner');
            XLSX.writeFile(wb, `Jawaban_Kuesioner_${npm}_${alumniName}.xlsx`);
        }

        // Export to PDF function
        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            const alumniName = '<?= addslashes($alumni_data['nama_lengkap']) ?>';
            const npm = '<?= addslashes($alumni_data['npm']) ?>';
            
            // Add title
            doc.setFontSize(16);
            doc.text('Detail Jawaban Kuesioner Alumni', 20, 20);
            
            // Add alumni info
            doc.setFontSize(12);
            let yPos = 40;
            doc.text(`Nama: ${alumniName}`, 20, yPos);
            doc.text(`NPM: ${npm}`, 20, yPos + 10);
            doc.text(`Program Studi: <?= addslashes($alumni_data['program_studi']) ?>`, 20, yPos + 20);
            doc.text(`Tahun Lulus: <?= addslashes($alumni_data['tahun_lulus']) ?>`, 20, yPos + 30);
            
            yPos += 50;
            doc.text(`Kelengkapan: <?= $persentase_kelengkapan ?>% (<?= $total_dijawab ?>/<?= $total_pertanyaan ?>)`, 20, yPos);
            
            doc.save(`Jawaban_Kuesioner_${npm}_${alumniName}.pdf`);
        }

        // Smooth scroll for long content
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading animation
            const cards = document.querySelectorAll('.question-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Auto-hide export buttons on scroll
        let lastScrollTop = 0;
        window.addEventListener('scroll', function() {
            const st = window.pageYOffset || document.documentElement.scrollTop;
            const exportButtons = document.querySelector('.export-buttons');
            
            if (st > lastScrollTop && st > 200) {
                exportButtons.style.transform = 'translateX(70px)';
            } else {
                exportButtons.style.transform = 'translateX(0)';
            }
            lastScrollTop = st <= 0 ? 0 : st;
        });
    </script>
</body>
</html>