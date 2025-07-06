<?php
session_start();
include 'koneksi.php';

// Check admin authentication
if (!isset($_SESSION['id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'fakultas')) {
    header("Location: login.php");
    exit;
}

// Get filter parameters
$tahun_filter = isset($_GET['tahun']) ? $_GET['tahun'] : '';
$prodi_filter = isset($_GET['prodi']) ? $_GET['prodi'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build WHERE conditions for filters
$where_conditions = [];
$having_conditions = [];

if ($tahun_filter) {
    $where_conditions[] = "a.tahun_lulus = '$tahun_filter'";
}

if ($prodi_filter) {
    $where_conditions[] = "a.program_studi = '$prodi_filter'";
}

if ($status_filter) {
    if ($status_filter == 'sudah') {
        $having_conditions[] = "status_pengisian = '✅ Sudah Isi'";
    } elseif ($status_filter == 'belum') {
        $having_conditions[] = "status_pengisian = '❌ Belum Isi'";
    }
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
$having_clause = !empty($having_conditions) ? 'HAVING ' . implode(' AND ', $having_conditions) : '';

// Get available years and program studi for filters
$tahun_query = "SELECT DISTINCT tahun_lulus FROM alumni ORDER BY tahun_lulus DESC";
$tahun_result = mysqli_query($conn, $tahun_query);

$prodi_query = "SELECT DISTINCT program_studi FROM alumni ORDER BY program_studi";
$prodi_result = mysqli_query($conn, $prodi_query);

// Main query to get alumni data
$query = "
    SELECT 
        a.id,
        a.nama_lengkap AS nama_lengkap,
        a.npm AS npm,
        a.program_studi AS program_studi,
        a.tahun_lulus AS tahun_lulus,
        CASE 
            WHEN k.jawaban IS NOT NULL THEN '✅ Sudah Isi' 
            ELSE '❌ Belum Isi' 
        END AS status_pengisian,
        COUNT(DISTINCT k.pertanyaan_id) as total_jawaban,
        MAX(k.waktu_pengisian) as terakhir_isi
    FROM alumni a
    LEFT JOIN kuesioner_jawaban k ON a.id = k.alumni_id AND k.is_latest = 1
    $where_clause
    GROUP BY a.id
    $having_clause
    ORDER BY a.tahun_lulus DESC, a.nama_lengkap ASC
";

$result = mysqli_query($conn, $query);
$alumniData = [];

while ($row = mysqli_fetch_assoc($result)) {
    $alumniData[] = $row;
}

// Get statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_alumni,
        SUM(CASE WHEN k.alumni_id IS NOT NULL THEN 1 ELSE 0 END) as sudah_isi,
        SUM(CASE WHEN k.alumni_id IS NULL THEN 1 ELSE 0 END) as belum_isi
    FROM alumni a
    LEFT JOIN (
        SELECT DISTINCT alumni_id 
        FROM kuesioner_jawaban 
        WHERE is_latest = 1
    ) k ON a.id = k.alumni_id
    $where_clause
";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

$pageTitle = "Laporan Data Alumni & Status Kuesioner";
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
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .status-badge {
            font-size: 0.9em;
            font-weight: bold;
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
        }
        .print-hide {
            display: block;
        }
        @media print {
            .print-hide {
                display: none !important;
            }
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
            <h2><i class="fas fa-users"></i> <?= $pageTitle ?></h2>
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh Data
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h3><?= $stats['total_alumni'] ?></h3>
                        <p class="mb-0">Total Alumni</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <h3><?= $stats['sudah_isi'] ?></h3>
                        <p class="mb-0">Sudah Mengisi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-times-circle fa-3x mb-3"></i>
                        <h3><?= $stats['belum_isi'] ?></h3>
                        <p class="mb-0">Belum Mengisi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-percentage fa-3x mb-3"></i>
                        <h3><?= $stats['total_alumni'] > 0 ? round(($stats['sudah_isi'] / $stats['total_alumni']) * 100, 1) : 0 ?>%</h3>
                        <p class="mb-0">Response Rate</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4 print-hide">
            <div class="card-header">
                <h5><i class="fas fa-filter"></i> Filter Data</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tahun Lulus</label>
                        <select name="tahun" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Tahun</option>
                            <?php while ($tahun = mysqli_fetch_assoc($tahun_result)): ?>
                                <option value="<?= $tahun['tahun_lulus'] ?>" <?= $tahun_filter == $tahun['tahun_lulus'] ? 'selected' : '' ?>>
                                    <?= $tahun['tahun_lulus'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Program Studi</label>
                        <select name="prodi" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Program Studi</option>
                            <?php while ($prodi = mysqli_fetch_assoc($prodi_result)): ?>
                                <option value="<?= $prodi['program_studi'] ?>" <?= $prodi_filter == $prodi['program_studi'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prodi['program_studi']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status Pengisian</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="sudah" <?= $status_filter == 'sudah' ? 'selected' : '' ?>>Sudah Isi</option>
                            <option value="belum" <?= $status_filter == 'belum' ? 'selected' : '' ?>>Belum Isi</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <a href="?" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Reset Filter
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-table"></i> Data Alumni (<?= count($alumniData) ?> dari <?= $stats['total_alumni'] ?> alumni)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="alumniTable" class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama Alumni</th>
                                <th>NPM</th>
                                <th>Program Studi</th>
                                <th>Tahun Lulus</th>
                                <th>Status Pengisian</th>
                                <th>Total Jawaban</th>
                                <th>Terakhir Update</th>
                                <th class="print-hide">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($alumniData)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Tidak ada data yang ditemukan</h5>
                                            <p class="text-muted">Silakan ubah filter pencarian Anda</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($alumniData as $index => $alumni): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($alumni['nama_lengkap']) ?></strong>
                                        </td>
                                        <td>
                                            <code><?= htmlspecialchars($alumni['npm']) ?></code>
                                        </td>
                                        <td><?= htmlspecialchars($alumni['program_studi']) ?></td>
                                        <td>
                                            <span class="badge bg-info"><?= htmlspecialchars($alumni['tahun_lulus']) ?></span>
                                        </td>
                                        <td>
                                            <?php if (strpos($alumni['status_pengisian'], 'Sudah') !== false): ?>
                                                <span class="badge bg-success status-badge"><?= $alumni['status_pengisian'] ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger status-badge"><?= $alumni['status_pengisian'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?= $alumni['total_jawaban'] ?> jawaban</span>
                                        </td>
                                        <td>
                                            <?php if ($alumni['terakhir_isi']): ?>
                                                <small><?= date('d/m/Y H:i', strtotime($alumni['terakhir_isi'])) ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="print-hide">
                                            <?php if (strpos($alumni['status_pengisian'], 'Sudah') !== false): ?>
                                                <a href="lihat_jawaban.php?alumni_id=<?= $alumni['id'] ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> Lihat Jawaban
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    <i class="fas fa-minus-circle"></i> Belum ada jawaban
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#alumniTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                "pageLength": 25,
                "order": [[4, "desc"], [1, "asc"]],
                "columnDefs": [
                    { "orderable": false, "targets": [8] }
                ]
            });
        });

        // Export functions
        function exportToExcel() {
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Laporan Data Alumni & Status Kuesioner\n\n";
            csvContent += "No,Nama Alumni,NPM,Program Studi,Tahun Lulus,Status Pengisian,Total Jawaban,Terakhir Update\n";

            <?php foreach ($alumniData as $index => $alumni): ?>
            csvContent += `<?= $index + 1 ?>,"<?= addslashes($alumni['nama_lengkap']) ?>","<?= $alumni['npm'] ?>","<?= addslashes($alumni['program_studi']) ?>","<?= $alumni['tahun_lulus'] ?>","<?= strip_tags($alumni['status_pengisian']) ?>","<?= $alumni['total_jawaban'] ?>","<?= $alumni['terakhir_isi'] ? date('d/m/Y H:i', strtotime($alumni['terakhir_isi'])) : '-' ?>"\n`;
            <?php endforeach; ?>

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `laporan_alumni_${new Date().getTime()}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function exportToPDF() {
            window.print();
        }
    </script>
</body>
</html>