<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once 'koneksi.php';

// Check admin authentication
if (!isset($_SESSION['id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'fakultas')) {
    header("Location: login.php");
    exit;
}

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter settings
$filter_jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$filter_role = isset($_GET['role']) ? $_GET['role'] : '';
$filter_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build WHERE clause
$where_conditions = [];
$params = [];
$types = '';

if (!empty($filter_jenis)) {
    $where_conditions[] = "jenis = ?";
    $params[] = $filter_jenis;
    $types .= 's';
}

if (!empty($filter_role)) {
    $where_conditions[] = "penerima_role = ?";
    $params[] = $filter_role;
    $types .= 's';
}

if (!empty($filter_tanggal)) {
    $where_conditions[] = "DATE(dibuat_pada) = ?";
    $params[] = $filter_tanggal;
    $types .= 's';
}

if (!empty($search)) {
    $where_conditions[] = "(judul LIKE ? OR pesan LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM notifikasi $where_clause";
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();
} else {
    $total_records = $conn->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total_records / $limit);

// Get notifications
$sql = "SELECT * FROM notifikasi $where_clause ORDER BY dibuat_pada DESC LIMIT ? OFFSET ?";
$final_params = array_merge($params, [$limit, $offset]);
$final_types = $types . 'ii';

$stmt = $conn->prepare($sql);
if (!empty($final_params)) {
    $stmt->bind_param($final_types, ...$final_params);
}
$stmt->execute();
$notifications = $stmt->get_result();
$stmt->close();

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total_notifikasi,
    COUNT(CASE WHEN DATE(dibuat_pada) = CURDATE() THEN 1 END) as hari_ini,
    COUNT(CASE WHEN MONTH(dibuat_pada) = MONTH(CURDATE()) AND YEAR(dibuat_pada) = YEAR(CURDATE()) THEN 1 END) as bulan_ini,
    COUNT(CASE WHEN jenis = 'pengumuman' THEN 1 END) as pengumuman,
    COUNT(CASE WHEN jenis = 'kuesioner_baru' THEN 1 END) as kuesioner,
    COUNT(CASE WHEN jenis = 'pengingat' THEN 1 END) as pengingat,
    COUNT(CASE WHEN jenis = 'sistem' THEN 1 END) as sistem
FROM notifikasi";
$stats = $conn->query($stats_sql)->fetch_assoc();

// Handle delete notification
if (isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    $delete_stmt = $conn->prepare("DELETE FROM notifikasi WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    
    if ($delete_stmt->execute()) {
        $success_message = "Notifikasi berhasil dihapus.";
    } else {
        $error_message = "Gagal menghapus notifikasi.";
    }
    $delete_stmt->close();
    
    // Redirect to avoid resubmission
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . http_build_query($_GET));
    exit;
}

// Handle bulk delete
if (isset($_POST['bulk_delete']) && !empty($_POST['selected_notifications'])) {
    $selected_ids = $_POST['selected_notifications'];
    $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
    
    $bulk_delete_stmt = $conn->prepare("DELETE FROM notifikasi WHERE id IN ($placeholders)");
    $bulk_delete_stmt->bind_param(str_repeat('i', count($selected_ids)), ...$selected_ids);
    
    if ($bulk_delete_stmt->execute()) {
        $success_message = "Berhasil menghapus " . $bulk_delete_stmt->affected_rows . " notifikasi.";
    } else {
        $error_message = "Gagal menghapus notifikasi terpilih.";
    }
    $bulk_delete_stmt->close();
    
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . http_build_query($_GET));
    exit;
}

function getJenisIcon($jenis) {
    switch($jenis) {
        case 'pengumuman': return 'bi-megaphone text-primary';
        case 'kuesioner_baru': return 'bi-clipboard-check text-success';
        case 'pengingat': return 'bi-clock text-warning';
        case 'sistem': return 'bi-gear text-info';
        default: return 'bi-info-circle text-secondary';
    }
}

function getRoleText($role) {
    switch($role) {
        case 'admin': return 'Admin';
        case 'fakultas': return 'Fakultas';
        case 'alumni': return 'Alumni';
        case 'semua': return 'Semua Pengguna';
        default: return ucfirst($role);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Notifikasi - Admin Panel</title>
    <link href="styless.css?v=1.0" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        /* Header */
        header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: linear-gradient(135deg, #00509D, #003B70);
            color: #fff;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            height: 70px;
            box-sizing: border-box;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        header img {
            height: 45px;
            border-radius: 4px;
        }

        header h1 {
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
        }

        #realtimeDateTime {
            font-weight: 600;
            font-size: 0.9rem;
            background: rgba(255,255,255,0.1);
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            backdrop-filter: blur(10px);
        }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(180deg, #003B70, #00509D);
            color: white;
            min-height: calc(100vh - 70px);
            width: 250px;
            position: fixed;
            top: 70px;
            left: 0;
            overflow-y: auto;
            padding-top: 1rem;
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
            z-index: 999;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar a:hover {
            background-color: rgba(255,255,255,0.15);
            border-left-color: #fff;
            transform: translateX(5px);
        }

        .sidebar a.active {
            background-color: rgba(255,255,255,0.2);
            border-left-color: #ffc107;
        }

        /* Main Content */
        main {
            margin-top: 70px;
            margin-left: 250px;
            padding: 2rem;
            min-height: calc(100vh - 70px);
            background-color: #f4f6f9;
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-left: 4px solid;
            transition: transform 0.2s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
        }

        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .notification-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 15px;
            transition: transform 0.2s ease;
        }

        .notification-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }

        .notification-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .notification-content {
            color: #6c757d;
            line-height: 1.6;
        }

        .notification-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
            font-size: 0.875rem;
            color: #6c757d;
        }

        .badge-jenis {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
        }

        .table-responsive {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }

        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .page-header {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .notification-actions {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .bulk-actions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            display: none;
        }

        .bulk-actions.show {
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <nav class="sidebar">
        <?php include 'sidebar.php'; ?>
    </nav>

    <main>
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-list-ul text-primary"></i>
                        Daftar Notifikasi
                    </h2>
                    <p class="text-muted mb-0">Kelola dan pantau semua notifikasi yang telah dikirim</p>
                </div>
                <div>
                    <a href="kirim_notifikasi.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Kirim Notifikasi Baru
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= $success_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?= $error_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card" style="border-left-color: #007bff;">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-bell-fill text-primary fs-1"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total Notifikasi</h6>
                            <h3 class="mb-0 text-primary"><?= number_format($stats['total_notifikasi']) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card" style="border-left-color: #28a745;">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-calendar-day text-success fs-1"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Hari Ini</h6>
                            <h3 class="mb-0 text-success"><?= number_format($stats['hari_ini']) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card" style="border-left-color: #ffc107;">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-calendar-month text-warning fs-1"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Bulan Ini</h6>
                            <h3 class="mb-0 text-warning"><?= number_format($stats['bulan_ini']) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card" style="border-left-color: #17a2b8;">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-megaphone text-info fs-1"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Pengumuman</h6>
                            <h3 class="mb-0 text-info"><?= number_format($stats['pengumuman']) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Cari Notifikasi</label>
                    <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari judul atau isi...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jenis</label>
                    <select class="form-select" name="jenis">
                        <option value="">Semua Jenis</option>
                        <option value="pengumuman" <?= $filter_jenis == 'pengumuman' ? 'selected' : '' ?>>Pengumuman</option>
                        <option value="kuesioner_baru" <?= $filter_jenis == 'kuesioner_baru' ? 'selected' : '' ?>>Kuesioner Baru</option>
                        <option value="pengingat" <?= $filter_jenis == 'pengingat' ? 'selected' : '' ?>>Pengingat</option>
                        <option value="sistem" <?= $filter_jenis == 'sistem' ? 'selected' : '' ?>>Sistem</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Penerima</label>
                    <select class="form-select" name="role">
                        <option value="">Semua Role</option>
                        <option value="admin" <?= $filter_role == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="fakultas" <?= $filter_role == 'fakultas' ? 'selected' : '' ?>>Fakultas</option>
                        <option value="alumni" <?= $filter_role == 'alumni' ? 'selected' : '' ?>>Alumni</option>
                        <option value="semua" <?= $filter_role == 'semua' ? 'selected' : '' ?>>Semua Pengguna</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal</label>
                    <input type="date" class="form-control" name="tanggal" value="<?= htmlspecialchars($filter_tanggal) ?>">
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                        <button type="button" class="btn btn-outline-danger" onclick="toggleBulkActions()">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Bulk Actions -->
        <div class="bulk-actions" id="bulkActions">
            <form method="POST" onsubmit="return confirmBulkDelete()">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Mode Hapus Massal:</strong> 
                        <span id="selectedCount">0</span> notifikasi dipilih
                    </div>
                    <div>
                        <button type="submit" name="bulk_delete" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash"></i> Hapus Terpilih
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="toggleBulkActions()">
                            Batal
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Notifications List -->
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" class="form-check-input" id="selectAll" style="display: none;">
                            <span class="bulk-header" style="display: none;">#</span>
                            <span class="normal-header">#</span>
                        </th>
                        <th>Notifikasi</th>
                        <th>Jenis</th>
                        <th>Penerima</th>
                        <th>Tanggal</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($notifications->num_rows > 0): ?>
                        <?php $no = $offset + 1; ?>
                        <?php while ($notif = $notifications->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input notification-checkbox" 
                                       name="selected_notifications[]" 
                                       value="<?= $notif['id'] ?>" 
                                       style="display: none;">
                                <span class="bulk-number" style="display: none;"><?= $no ?></span>
                                <span class="normal-number"><?= $no ?></span>
                            </td>
                            <td>
                                <div class="notification-info">
                                    <h6 class="mb-1 fw-bold"><?= htmlspecialchars($notif['judul']) ?></h6>
                                    <p class="mb-0 text-muted small">
                                        <?= strlen($notif['pesan']) > 100 ? htmlspecialchars(substr($notif['pesan'], 0, 100)) . '...' : htmlspecialchars($notif['pesan']) ?>
                                    </p>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-jenis bg-<?= 
                                    $notif['jenis'] == 'pengumuman' ? 'primary' : 
                                    ($notif['jenis'] == 'kuesioner_baru' ? 'success' : 
                                    ($notif['jenis'] == 'pengingat' ? 'warning' : 'info')) ?>">
                                    <i class="bi <?= 
                                        $notif['jenis'] == 'pengumuman' ? 'bi-megaphone' : 
                                        ($notif['jenis'] == 'kuesioner_baru' ? 'bi-clipboard-check' : 
                                        ($notif['jenis'] == 'pengingat' ? 'bi-clock' : 'bi-gear')) ?>"></i>
                                    <?= ucfirst(str_replace('_', ' ', $notif['jenis'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-people"></i>
                                    <?= getRoleText($notif['penerima_role']) ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3"></i>
                                    <?= date('d/m/Y H:i', strtotime($notif['dibuat_pada'])) ?>
                                </small>
                            </td>
                            <td>
                                <div class="notification-actions">
                                    <button type="button" class="btn btn-outline-primary btn-action" 
                                            onclick="viewNotification(<?= $notif['id'] ?>)"
                                            title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-action" 
                                            onclick="deleteNotification(<?= $notif['id'] ?>)"
                                            title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php $no++; ?>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    <h5>Tidak ada notifikasi</h5>
                                    <p>Belum ada notifikasi yang sesuai dengan filter yang dipilih.</p>
                                    <a href="kirim_notifikasi.php" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Kirim Notifikasi Pertama
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Menampilkan <?= $offset + 1 ?> - <?= min($offset + $limit, $total_records) ?> dari <?= $total_records ?> notifikasi
            </div>
            <nav>
                <ul class="pagination mb-0">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </main>

    <!-- Modal for Notification Detail -->
    <div class="modal fade" id="notificationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Notifikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="notificationDetail">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden forms for actions -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="delete_id" id="deleteId">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle bulk actions mode
        function toggleBulkActions() {
            const bulkActions = document.getElementById('bulkActions');
            const checkboxes = document.querySelectorAll('.notification-checkbox');
            const selectAll = document.getElementById('selectAll');
            const bulkHeaders = document.querySelectorAll('.bulk-header');
            const normalHeaders = document.querySelectorAll('.normal-header');
            const bulkNumbers = document.querySelectorAll('.bulk-number');
            const normalNumbers = document.querySelectorAll('.normal-number');
            
            if (bulkActions.classList.contains('show')) {
                // Hide bulk actions
                bulkActions.classList.remove('show');
                checkboxes.forEach(cb => cb.style.display = 'none');
                selectAll.style.display = 'none';
                bulkHeaders.forEach(bh => bh.style.display = 'none');
                normalHeaders.forEach(nh => nh.style.display = 'inline');
                bulkNumbers.forEach(bn => bn.style.display = 'none');
                normalNumbers.forEach(nn => nn.style.display = 'inline');
                
                // Uncheck all
                checkboxes.forEach(cb => cb.checked = false);
                selectAll.checked = false;
                updateSelectedCount();
            } else {
                // Show bulk actions
                bulkActions.classList.add('show');
                checkboxes.forEach(cb => cb.style.display = 'inline-block');
                selectAll.style.display = 'inline-block';
                bulkHeaders.forEach(bh => bh.style.display = 'inline');
                normalHeaders.forEach(nh => nh.style.display = 'none');
                bulkNumbers.forEach(bn => bn.style.display = 'inline');
                normalNumbers.forEach(nn => nn.style.display = 'none');
            }
        }

        // Select all checkboxes
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.notification-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateSelectedCount();
        });

        // Update selected count
        function updateSelectedCount() {
            const checkedBoxes = document.querySelectorAll('.notification-checkbox:checked');
            document.getElementById('selectedCount').textContent = checkedBoxes.length;
            
            // Add selected notifications to bulk delete form
            const bulkForm = document.querySelector('#bulkActions form');
            // Remove existing hidden inputs
            bulkForm.querySelectorAll('input[name="selected_notifications[]"]').forEach(input => input.remove());
            
            // Add new hidden inputs for selected notifications
            checkedBoxes.forEach(cb => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'selected_notifications[]';
                hiddenInput.value = cb.value;
                bulkForm.appendChild(hiddenInput);
            });
        }

        // Add event listeners to individual checkboxes
        document.querySelectorAll('.notification-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount();
                
                // Update select all checkbox state
                const allCheckboxes = document.querySelectorAll('.notification-checkbox');
                const checkedCheckboxes = document.querySelectorAll('.notification-checkbox:checked');
                const selectAll = document.getElementById('selectAll');
                
                if (checkedCheckboxes.length === 0) {
                    selectAll.indeterminate = false;
                    selectAll.checked = false;
                } else if (checkedCheckboxes.length === allCheckboxes.length) {
                    selectAll.indeterminate = false;
                    selectAll.checked = true;
                } else {
                    selectAll.indeterminate = true;
                    selectAll.checked = false;
                }
            });
        });

        // Delete single notification
        function deleteNotification(id) {
            if (confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        // Confirm bulk delete
        function confirmBulkDelete() {
            const checkedBoxes = document.querySelectorAll('.notification-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Pilih setidaknya satu notifikasi untuk dihapus.');
                return false;
            }
            return confirm(`Apakah Anda yakin ingin menghapus ${checkedBoxes.length} notifikasi yang dipilih?`);
        }

        // View notification detail
        function viewNotification(id) {
            // Show loading in modal
            const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
            const modalBody = document.getElementById('notificationDetail');
            
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat detail notifikasi...</p>
                </div>
            `;
            
            modal.show();
            
            // Fetch notification details (you would need to create an endpoint for this)
            fetch(`get_notification_detail.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const notif = data.notification;
                        modalBody.innerHTML = `
                            <div class="notification-detail">
                                <div class="row mb-3">
                                    <div class="col-sm-3"><strong>Judul:</strong></div>
                                    <div class="col-sm-9">${notif.judul}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3"><strong>Jenis:</strong></div>
                                    <div class="col-sm-9">
                                        <span class="badge bg-${getJenisBadgeClass(notif.jenis)}">
                                            <i class="bi ${getJenisIcon(notif.jenis)}"></i>
                                            ${getJenisText(notif.jenis)}
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3"><strong>Penerima:</strong></div>
                                    <div class="col-sm-9">
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-people"></i>
                                            ${getRoleText(notif.penerima_role)}
                                        </span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3"><strong>Tanggal:</strong></div>
                                    <div class="col-sm-9">
                                        <i class="bi bi-calendar3"></i>
                                        ${formatDate(notif.dibuat_pada)}
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-3"><strong>Pesan:</strong></div>
                                    <div class="col-sm-9">
                                        <div class="border rounded p-3 bg-light">
                                            ${notif.pesan.replace(/\n/g, '<br>')}
                                        </div>
                                    </div>
                                </div>
                                ${notif.link ? `
                                <div class="row mb-3">
                                    <div class="col-sm-3"><strong>Link:</strong></div>
                                    <div class="col-sm-9">
                                        <a href="${notif.link}" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-link-45deg"></i> Buka Link
                                        </a>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        `;
                    } else {
                        modalBody.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i>
                                Gagal memuat detail notifikasi.
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalBody.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            Terjadi kesalahan saat memuat data.
                        </div>
                    `;
                });
        }

        // Helper functions for modal display
        function getJenisBadgeClass(jenis) {
            switch(jenis) {
                case 'pengumuman': return 'primary';
                case 'kuesioner_baru': return 'success';
                case 'pengingat': return 'warning';
                case 'sistem': return 'info';
                default: return 'secondary';
            }
        }

        function getJenisIcon(jenis) {
            switch(jenis) {
                case 'pengumuman': return 'bi-megaphone';
                case 'kuesioner_baru': return 'bi-clipboard-check';
                case 'pengingat': return 'bi-clock';
                case 'sistem': return 'bi-gear';
                default: return 'bi-info-circle';
            }
        }

        function getJenisText(jenis) {
            switch(jenis) {
                case 'pengumuman': return 'Pengumuman';
                case 'kuesioner_baru': return 'Kuesioner Baru';
                case 'pengingat': return 'Pengingat';
                case 'sistem': return 'Sistem';
                default: return jenis.charAt(0).toUpperCase() + jenis.slice(1);
            }
        }

        function getRoleText(role) {
            switch(role) {
                case 'admin': return 'Admin';
                case 'fakultas': return 'Fakultas';
                case 'alumni': return 'Alumni';
                case 'semua': return 'Semua Pengguna';
                default: return role.charAt(0).toUpperCase() + role.slice(1);
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit',
                timeZone: 'Asia/Jakarta'
            };
            return date.toLocaleDateString('id-ID', options);
        }

        // Real-time datetime update
        function updateDateTime() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'Asia/Jakarta'
            };
            
            const dateTimeElement = document.getElementById('realtimeDateTime');
            if (dateTimeElement) {
                dateTimeElement.textContent = now.toLocaleDateString('id-ID', options);
            }
        }

        // Update datetime every second
        setInterval(updateDateTime, 1000);
        updateDateTime(); // Initial call

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Smooth scrolling for internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + N for new notification
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                window.location.href = 'kirim_notifikasi.php';
            }
            
            // Ctrl + R for refresh/reset filters
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                window.location.href = window.location.pathname;
            }
            
            // Delete key when in bulk mode
            if (e.key === 'Delete' && document.getElementById('bulkActions').classList.contains('show')) {
                const checkedBoxes = document.querySelectorAll('.notification-checkbox:checked');
                if (checkedBoxes.length > 0) {
                    if (confirmBulkDelete()) {
                        document.querySelector('#bulkActions form').submit();
                    }
                }
            }
        });

        // Table row hover effect enhancement
        document.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f9fa';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });

        // Search input auto-focus with Ctrl+F
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                const searchInput = document.querySelector('input[name="search"]');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
        });

        // Enhanced form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="search"]');
            if (searchInput && searchInput.value.trim().length > 0 && searchInput.value.trim().length < 3) {
                e.preventDefault();
                alert('Pencarian harus minimal 3 karakter.');
                searchInput.focus();
            }
        });
    </script>
</body>
</html>