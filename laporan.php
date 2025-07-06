<?php
session_start();
include 'koneksi.php';

// Check admin authentication
if (!isset($_SESSION['id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'fakultas')) {
    header("Location: login.php");
    exit;
}

// Hitung total responden
$totalResponden = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT alumni_id) as total FROM kuesioner_jawaban"))['total'];

// Set page title for header
$pageTitle = "Hasil Kuesioner Tracer Study";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="styless.css?v=1.0" rel="stylesheet" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'header.php'; ?>
</head>
<body>
<div>
    <?php include 'sidebar.php'; ?>
</div>
<main>
    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm">
                        <div>
                            <h3 class="mb-1"><i class="fas fa-chart-bar me-2"></i>Hasil Kuesioner Tracer Study</h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="<?= $_SESSION['role'] === 'admin' ? 'dashboard_admin.php' : 'dashboard_fakultas.php' ?>">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item active">Hasil Kuesioner</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-widget">
                                <div class="stat-icon bg-primary"><i class="fas fa-users"></i></div>
                                <div>
                                    <div class="stat-digit"><?= $totalResponden ?></div>
                                    <div class="stat-text">Total Responden</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-widget">
                                <div class="stat-icon bg-success"><i class="fas fa-question-circle"></i></div>
                                <div>
                                    <div class="stat-digit">0</div> <!-- Placeholder for total questions -->
                                    <div class="stat-text">Total Pertanyaan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-widget">
                                <div class="stat-icon bg-warning"><i class="fas fa-chart-pie"></i></div>
                                <div>
                                    <div class="stat-digit">0</div> <!-- Placeholder for total answers -->
                                    <div class="stat-text">Total Jawaban</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-widget">
                                <div class="stat-icon bg-info"><i class="fas fa-percentage"></i></div>
                                <div>
                                    <div class="stat-digit">0%</div> <!-- Placeholder for participation rate -->
                                    <div class="stat-text">Tingkat Partisipasi</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <strong>Informasi Umum:</strong> Hasil kuesioner lebih lanjut dapat dilihat di submenu.
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
