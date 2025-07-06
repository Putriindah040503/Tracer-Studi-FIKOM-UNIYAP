<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['role']) || !isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'];
$id = $_SESSION['id'];

// Handle aksi marking notifikasi sebagai dibaca
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['mark_read'])) {
        $notif_id = $_POST['notif_id'];
        $stmt = $conn->prepare("UPDATE notifikasi SET status = 'dibaca', dibaca_pada = NOW() WHERE id = ? AND penerima_role = ?");
        $stmt->bind_param("is", $notif_id, $role);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['mark_all_read'])) {
        $stmt = $conn->prepare("UPDATE notifikasi SET status = 'dibaca', dibaca_pada = NOW() WHERE penerima_role = ? AND status = 'belum_dibaca'");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete_notif'])) {
        $notif_id = $_POST['notif_id'];
        $stmt = $conn->prepare("DELETE FROM notifikasi WHERE id = ? AND penerima_role = ?");
        $stmt->bind_param("is", $notif_id, $role);
        $stmt->execute();
        $stmt->close();
    }
    
    // Redirect untuk mencegah form resubmission
    header('Location: notifikasi.php');
    exit();
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter status
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'semua';
$where_clause = "WHERE penerima_role = ?";
$params = [$role];
$param_types = "s";

if ($filter_status == 'belum_dibaca') {
    $where_clause .= " AND status = 'belum_dibaca'";
} elseif ($filter_status == 'dibaca') {
    $where_clause .= " AND status = 'dibaca'";
}

// Hitung total notifikasi
$count_query = "SELECT COUNT(*) as total FROM notifikasi $where_clause";
$stmt = $conn->prepare($count_query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$total_result = $stmt->get_result();
$total_notif = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_notif / $limit);
$stmt->close();

// Ambil notifikasi
$query = "SELECT * FROM notifikasi $where_clause ORDER BY dibuat_pada DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$param_types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$notifikasi = $stmt->get_result();

// Hitung notifikasi belum dibaca
$stmt_unread = $conn->prepare("SELECT COUNT(*) as total FROM notifikasi WHERE penerima_role = ? AND status = 'belum_dibaca'");
$stmt_unread->bind_param("s", $role);
$stmt_unread->execute();
$unread_result = $stmt_unread->get_result();
$jumlah_belum_dibaca = $unread_result->fetch_assoc()['total'];
$stmt_unread->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - Sistem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="styless.css" rel="stylesheet" />

    <style>
        .notification-item {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .notification-item.unread {
            background-color: #f8f9fa;
            border-left-color: #0d6efd;
        }
        .notification-item:hover {
            background-color: #e9ecef;
            transform: translateX(5px);
        }
        .notification-meta {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .badge-notification {
            font-size: 0.75rem;
        }
        .filter-tabs {
            border-bottom: 2px solid #e9ecef;
        }
        .filter-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
        }
        .filter-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
            background: none;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>
    <nav class="sidebar">
        <?php include 'sidebar.php'; ?>
    </nav>
    
    <main>
        <div class="container my-4">
            <div class="row">
                <div class="col-12">
                    <!-- Header Notifikasi -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-1">
                                        <i class="bi bi-bell text-primary"></i> Notifikasi
                                    </h4>
                                    <p class="text-muted mb-0">
                                        <?= $jumlah_belum_dibaca ?> notifikasi belum dibaca dari total <?= $total_notif ?> notifikasi
                                    </p>
                                </div>
                                <?php if ($jumlah_belum_dibaca > 0): ?>
                                <form method="POST" class="d-inline">
                                    <button type="submit" name="mark_all_read" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-check-all"></i> Tandai Semua Dibaca
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <ul class="nav nav-tabs filter-tabs card-header-tabs">
                                <li class="nav-item">
                                    <a class="nav-link <?= $filter_status == 'semua' ? 'active' : '' ?>" 
                                       href="?status=semua">
                                        <i class="bi bi-list"></i> Semua
                                        <span class="badge bg-secondary ms-1"><?= $total_notif ?></span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $filter_status == 'belum_dibaca' ? 'active' : '' ?>" 
                                       href="?status=belum_dibaca">
                                        <i class="bi bi-bell"></i> Belum Dibaca
                                        <?php if ($jumlah_belum_dibaca > 0): ?>
                                        <span class="badge bg-danger ms-1"><?= $jumlah_belum_dibaca ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?= $filter_status == 'dibaca' ? 'active' : '' ?>" 
                                       href="?status=dibaca">
                                        <i class="bi bi-check-circle"></i> Sudah Dibaca
                                        <span class="badge bg-success ms-1"><?= $total_notif - $jumlah_belum_dibaca ?></span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body p-0">
                            <?php if ($notifikasi->num_rows > 0): ?>
                                <?php while ($notif = $notifikasi->fetch_assoc()): ?>
                                <div class="notification-item p-3 border-bottom <?= $notif['status'] == 'belum_dibaca' ? 'unread' : '' ?>">
                                    <div class="d-flex justify-content-between">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-start mb-2">
                                                <div class="me-3">
                                                    <?php
                                                    $icon_class = 'bi-info-circle text-primary';
                                                    switch ($notif['jenis']) {
                                                        case 'kuesioner_baru':
                                                            $icon_class = 'bi-clipboard-check text-success';
                                                            break;
                                                        case 'pengingat':
                                                            $icon_class = 'bi-clock text-warning';
                                                            break;
                                                        case 'sistem':
                                                            $icon_class = 'bi-gear text-info';
                                                            break;
                                                        case 'pengumuman':
                                                            $icon_class = 'bi-megaphone text-primary';
                                                            break;
                                                    }
                                                    ?>
                                                    <i class="bi <?= $icon_class ?> fs-4"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-semibold">
                                                        <?= htmlspecialchars($notif['judul']) ?>
                                                        <?php if ($notif['status'] == 'belum_dibaca'): ?>
                                                            <span class="badge bg-primary badge-notification ms-1">Baru</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                    <p class="mb-2 text-dark">
                                                        <?= nl2br(htmlspecialchars($notif['pesan'])) ?>
                                                    </p>
                                                    <div class="notification-meta">
                                                        <i class="bi bi-calendar3"></i>
                                                        <?= date('d F Y, H:i', strtotime($notif['dibuat_pada'])) ?>
                                                        <?php if ($notif['status'] == 'dibaca' && $notif['dibaca_pada']): ?>
                                                            | <i class="bi bi-eye"></i> Dibaca pada <?= date('d F Y, H:i', strtotime($notif['dibaca_pada'])) ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 ms-2">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                        type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <?php if ($notif['status'] == 'belum_dibaca'): ?>
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="notif_id" value="<?= $notif['id'] ?>">
                                                            <button type="submit" name="mark_read" class="dropdown-item">
                                                                <i class="bi bi-check"></i> Tandai Dibaca
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <form method="POST" class="d-inline" 
                                                              onsubmit="return confirm('Yakin ingin menghapus notifikasi ini?')">
                                                            <input type="hidden" name="notif_id" value="<?= $notif['id'] ?>">
                                                            <button type="submit" name="delete_notif" class="dropdown-item text-danger">
                                                                <i class="bi bi-trash"></i> Hapus
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="bi bi-bell-slash"></i>
                                    <h5>Tidak Ada Notifikasi</h5>
                                    <p>
                                        <?php if ($filter_status == 'belum_dibaca'): ?>
                                            Tidak ada notifikasi yang belum dibaca.
                                        <?php elseif ($filter_status == 'dibaca'): ?>
                                            Tidak ada notifikasi yang sudah dibaca.
                                        <?php else: ?>
                                            Belum ada notifikasi untuk Anda.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <div class="card-footer bg-white">
                            <nav aria-label="Navigasi halaman notifikasi">
                                <ul class="pagination pagination-sm justify-content-center mb-0">
                                    <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= $filter_status ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                    <?php endif; ?>

                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);
                                    
                                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&status=<?= $filter_status ?>"><?= $i ?></a>
                                    </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= $filter_status ?>">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                            <div class="text-center mt-2">
                                <small class="text-muted">
                                    Halaman <?= $page ?> dari <?= $total_pages ?> 
                                    (<?= $total_notif ?> total notifikasi)
                                </small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto refresh setiap 5 menit untuk memperbarui notifikasi
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 menit

        // Smooth scroll untuk pagination
        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function() {
                setTimeout(() => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }, 100);
            });
        });
    </script>
</body>
</html>

<?php
$notifikasi->close();
$conn->close();
?>
