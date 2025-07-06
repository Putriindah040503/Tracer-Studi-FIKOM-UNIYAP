<?php
session_start();
include 'koneksi.php';

// Check authentication
if (!isset($_SESSION['id']) || !in_array($_SESSION['role'], ['admin', 'fakultas'])) {
    header("Location: login.php");
    exit;
}

// Get filter parameters
$tahun_filter = isset($_GET['tahun']) ? $_GET['tahun'] : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$prodi_filter = isset($_GET['prodi']) ? $_GET['prodi'] : '';

// Get available years from responses
$tahun_query = "SELECT DISTINCT YEAR(kj.waktu_pengisian) as tahun 
                FROM kuesioner_jawaban kj 
                ORDER BY tahun DESC";
$tahun_result = mysqli_query($conn, $tahun_query);

// Get categories
$kategori_query = "SELECT id, nama_kategori FROM kuesioner_kategori ORDER BY nama_kategori";
$kategori_result = mysqli_query($conn, $kategori_query);

// Get program studi
$prodi_query = "SELECT DISTINCT program_studi FROM alumni ORDER BY program_studi";
$prodi_result = mysqli_query($conn, $prodi_query);

// Build WHERE conditions
$where_conditions = [];
$params = [];
$param_types = '';

if ($tahun_filter) {
    $where_conditions[] = "YEAR(kj.waktu_pengisian) = ?";
    $params[] = $tahun_filter;
    $param_types .= 'i';
}

if ($kategori_filter) {
    $where_conditions[] = "kk.id = ?";
    $params[] = $kategori_filter;
    $param_types .= 'i';
}

if ($prodi_filter) {
    $where_conditions[] = "a.program_studi = ?";
    $params[] = $prodi_filter;
    $param_types .= 's';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get questionnaire data with responses
$query = "SELECT 
    kp.id,
    kp.pertanyaan,
    kp.tipe_input,
    kk.nama_kategori,
    COUNT(DISTINCT kj.alumni_id) as total_responden
FROM kuesioner_pertanyaan kp
JOIN kuesioner_kategori kk ON kp.kategori_id = kk.id
LEFT JOIN kuesioner_jawaban kj ON kp.id = kj.pertanyaan_id AND kj.is_latest = 1
LEFT JOIN alumni a ON kj.alumni_id = a.id
$where_clause
WHERE kp.aktif = 1
GROUP BY kp.id, kp.pertanyaan, kp.tipe_input, kk.nama_kategori
ORDER BY kk.nama_kategori, kp.urutan";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    mysqli_stmt_execute($stmt);
    $questions_result = mysqli_stmt_get_result($stmt);
} else {
    $questions_result = mysqli_query($conn, $query);
}

$questions = [];
while ($row = mysqli_fetch_assoc($questions_result)) {
    $questions[] = $row;
}

// Function to get response data for a specific question
function getResponseData($conn, $question_id, $where_conditions, $params, $param_types) {
    $response_query = "SELECT 
        kj.jawaban,
        COUNT(*) as jumlah
    FROM kuesioner_jawaban kj
    LEFT JOIN alumni a ON kj.alumni_id = a.id
    LEFT JOIN kuesioner_pertanyaan kp ON kj.pertanyaan_id = kp.id
    LEFT JOIN kuesioner_kategori kk ON kp.kategori_id = kk.id
    WHERE kj.pertanyaan_id = ? AND kj.is_latest = 1";
    
    $response_params = [$question_id];
    $response_param_types = 'i';
    
    if (!empty($where_conditions)) {
        $response_query .= ' AND ' . implode(' AND ', $where_conditions);
        $response_params = array_merge($response_params, $params);
        $response_param_types .= $param_types;
    }
    
    $response_query .= " GROUP BY kj.jawaban ORDER BY jumlah DESC";
    
    $stmt = mysqli_prepare($conn, $response_query);
    mysqli_stmt_bind_param($stmt, $response_param_types, ...$response_params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    return $data;
}

// Get total respondents
$total_query = "SELECT COUNT(DISTINCT kj.alumni_id) as total
                FROM kuesioner_jawaban kj
                LEFT JOIN alumni a ON kj.alumni_id = a.id
                LEFT JOIN kuesioner_pertanyaan kp ON kj.pertanyaan_id = kp.id
                LEFT JOIN kuesioner_kategori kk ON kp.kategori_id = kk.id
                WHERE kj.is_latest = 1
                $where_clause";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $total_query);
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    mysqli_stmt_execute($stmt);
    $total_result = mysqli_stmt_get_result($stmt);
} else {
    $total_result = mysqli_query($conn, $total_query);
}

$total_responden = mysqli_fetch_assoc($total_result)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hasil Kuesioner</title>
    <link href="styless.css?v=1.0" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <style>
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 30px;
        }
        .question-card {
            margin-bottom: 40px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .stats-card {
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
        .print-hide {
            display: block;
        }
        @media print {
            .print-hide {
                display: none !important;
            }
            .chart-container {
                height: 300px;
                page-break-inside: avoid;
            }
            .question-card {
                page-break-inside: avoid;
                margin-bottom: 20px;
            }
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
            <h2><i class="fas fa-chart-bar"></i> Laporan Hasil Kuesioner</h2>
            <button class="btn btn-primary" onclick="refreshData()">
                <i class="fas fa-sync-alt"></i> Refresh Data
            </button>
        </div>

        <!-- Filters -->
        <div class="card mb-4 print-hide">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tahun</label>
                        <select name="tahun" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Tahun</option>
                            <?php while ($tahun = mysqli_fetch_assoc($tahun_result)): ?>
                                <option value="<?= $tahun['tahun'] ?>" <?= $tahun_filter == $tahun['tahun'] ? 'selected' : '' ?>>
                                    <?= $tahun['tahun'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Kategori</option>
                            <?php while ($kat = mysqli_fetch_assoc($kategori_result)): ?>
                                <option value="<?= $kat['id'] ?>" <?= $kategori_filter == $kat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['nama_kategori']) ?>
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

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h3><?= $total_responden ?></h3>
                        <p class="mb-0">Total Responden</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-question-circle fa-3x mb-3"></i>
                        <h3><?= count($questions) ?></h3>
                        <p class="mb-0">Total Pertanyaan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar fa-3x mb-3"></i>
                        <h3><?= $tahun_filter ?: date('Y') ?></h3>
                        <p class="mb-0">Tahun Laporan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-percentage fa-3x mb-3"></i>
                        <h3><?= $total_responden > 0 ? '100%' : '0%' ?></h3>
                        <p class="mb-0">Response Rate</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Title for Print -->
        <div class="text-center mb-4" style="display: none;">
            <h1 class="print-title">Laporan Hasil Kuesioner Alumni</h1>
            <p class="text-muted">
                <?= $tahun_filter ? "Tahun $tahun_filter" : "Semua Tahun" ?>
                <?= $prodi_filter ? " - $prodi_filter" : "" ?>
            </p>
            <p class="text-muted">Total Responden: <?= $total_responden ?> orang</p>
        </div>

        <!-- Charts -->
        <div id="chartsContainer">
            <?php
            $current_category = '';
            foreach ($questions as $index => $question):
                // Display category header
                if ($question['nama_kategori'] !== $current_category):
                    $current_category = $question['nama_kategori'];
                    echo "<div class='category-header'>";
                    echo "<h3><i class='fas fa-folder-open'></i> {$current_category}</h3>";
                    echo "</div>";
                endif;

                // Get response data for this question
                $responseData = getResponseData($conn, $question['id'], $where_conditions, $params, $param_types);
                
                if (empty($responseData)) {
                    continue;
                }
            ?>
                <div class="question-card card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-question-circle text-primary"></i>
                            <?= htmlspecialchars($question['pertanyaan']) ?>
                        </h5>
                        <small class="text-muted">
                            Tipe: <?= strtoupper($question['tipe_input']) ?> | 
                            Total Responden: <?= $question['total_responden'] ?> orang
                        </small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="chart-container">
                                    <canvas id="chart_<?= $question['id'] ?>"></canvas>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6>Detail Jawaban:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Jawaban</th>
                                                <th>Jumlah</th>
                                                <th>%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $total_responses = array_sum(array_column($responseData, 'jumlah'));
                                            foreach ($responseData as $response): 
                                                $percentage = $total_responses > 0 ? round(($response['jumlah'] / $total_responses) * 100, 1) : 0;
                                            ?>
                                                <tr>
                                                    <td><?= htmlspecialchars(substr($response['jawaban'], 0, 30)) ?><?= strlen($response['jawaban']) > 30 ? '...' : '' ?></td>
                                                    <td><?= $response['jumlah'] ?></td>
                                                    <td><?= $percentage ?>%</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-bold">
                                                <td>Total</td>
                                                <td><?= $total_responses ?></td>
                                                <td>100%</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($questions)): ?>
            <div class="text-center py-5">
                <i class="fas fa-chart-bar fa-5x text-muted mb-3"></i>
                <h4 class="text-muted">Tidak ada data untuk ditampilkan</h4>
                <p class="text-muted">Silakan periksa filter yang Anda gunakan atau pastikan ada responden yang telah mengisi kuesioner.</p>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart.js configuration
        Chart.register(ChartDataLabels);
        
        const colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
        ];

        // Chart data from PHP
        const chartData = [
            <?php
            foreach ($questions as $question) {
                $responseData = getResponseData($conn, $question['id'], $where_conditions, $params, $param_types);
                if (!empty($responseData)) {
                    echo "{";
                    echo "id: {$question['id']},";
                    echo "question: '" . addslashes($question['pertanyaan']) . "',";
                    echo "type: '{$question['tipe_input']}',";
                    echo "data: " . json_encode($responseData) . ",";
                    echo "},";
                }
            }
            ?>
        ];

        // Create charts
        chartData.forEach((item, index) => {
            const ctx = document.getElementById(`chart_${item.id}`).getContext('2d');
            const labels = item.data.map(d => d.jawaban.length > 20 ? d.jawaban.substring(0, 20) + '...' : d.jawaban);
            const data = item.data.map(d => d.jumlah);
            
            // Choose chart type based on question type and data
            let chartType = 'bar';
            if (item.type === 'radio' || item.type === 'select') {
                chartType = data.length <= 5 ? 'doughnut' : 'bar';
            } else if (item.type === 'checkbox') {
                chartType = 'bar';
            }

            const config = {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Responden',
                        data: data,
                        backgroundColor: colors.slice(0, data.length),
                        borderColor: colors.slice(0, data.length).map(color => color + '80'),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: chartType === 'doughnut',
                            position: 'right'
                        },
                        datalabels: {
                            display: true,
                            color: 'white',
                            font: {
                                weight: 'bold'
                            },
                            formatter: (value, ctx) => {
                                if (chartType === 'doughnut') {
                                    const sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / sum) * 100).toFixed(1);
                                    return percentage + '%';
                                }
                                return value;
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.raw / total) * 100).toFixed(1);
                                    return `${context.label}: ${context.raw} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    scales: chartType === 'bar' ? {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45
                            }
                        }
                    } : {}
                }
            };

            new Chart(ctx, config);
        });

        // Export functions
        function exportToExcel() {
            // Create CSV content
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Laporan Hasil Kuesioner Alumni\n\n";
            csvContent += "Pertanyaan,Jawaban,Jumlah,Persentase\n";

            chartData.forEach(item => {
                const total = item.data.reduce((sum, d) => sum + d.jumlah, 0);
                item.data.forEach(response => {
                    const percentage = total > 0 ? ((response.jumlah / total) * 100).toFixed(1) : 0;
                    csvContent += `"${item.question}","${response.jawaban}",${response.jumlah},${percentage}%\n`;
                });
                csvContent += "\n";
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `laporan_kuesioner_${new Date().getTime()}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function exportToPDF() {
            // Show print-specific elements
            document.querySelector('.print-title').parentElement.style.display = 'block';
            
            // Hide interactive elements
            document.querySelectorAll('.print-hide').forEach(el => {
                el.style.display = 'none';
            });

            // Print
            window.print();

            // Restore original display
            setTimeout(() => {
                document.querySelector('.print-title').parentElement.style.display = 'none';
                document.querySelectorAll('.print-hide').forEach(el => {
                    el.style.display = 'block';
                });
            }, 1000);
        }

        function refreshData() {
            location.reload();
        }

        // Print styles
        const printStyles = `
            @media print {
                .print-title { display: block !important; }
                body { font-size: 12px; }
                .chart-container { height: 250px !important; }
                .question-card { margin-bottom: 15px !important; }
                .category-header { margin: 15px 0 10px 0 !important; }
            }
        `;

        const styleSheet = document.createElement("style");
        styleSheet.innerText = printStyles;
        document.head.appendChild(styleSheet);
    </script>
</body>
</html>