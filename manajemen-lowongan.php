<?php
include 'koneksi.php';
session_start();

// Cek otentikasi admin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$nama = 'Pengguna';
$link_profil = '#';
$jumlah_notif = 0;

$role = $_SESSION['role'] ?? null;
$id = $_SESSION['id'] ?? null;

if ($role === 'admin' && $id) {
    $stmt = $conn->prepare("SELECT nama_admin FROM admin WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($data = $result->fetch_assoc()) {
            $nama = $data['nama_admin'];
            $link_profil = 'profil-admin.php';
        }
        $stmt->close();
    }
}

// Function to determine status based on dates
function getStatusByDates($tanggal_mulai, $tanggal_berakhir) {
    $today = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime($tanggal_mulai));
    $end_date = date('Y-m-d', strtotime($tanggal_berakhir));
    
    return ($today >= $start_date && $today <= $end_date) ? 'active' : 'inactive';
}

// Simpan / Update data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_lowongan'])) {
    // Ambil data
    $id = $_POST['id'] ?? '';
    $judul = trim($_POST['judul'] ?? '');
    $perusahaan = trim($_POST['perusahaan'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_berakhir = $_POST['tanggal_berakhir'] ?? '';
    $link = trim($_POST['link_pendaftaran'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $syarat = trim($_POST['syarat'] ?? '');
    $status = getStatusByDates($tanggal_mulai, $tanggal_berakhir);
    $created_at = date('Y-m-d H:i:s');
    $foto = ''; // default kosong

    // Handle upload foto jika ada
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $foto = uniqid('low_', true) . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/$foto");
        }
    }

    // Validasi sederhana
    if (empty($judul) || empty($perusahaan) || empty($lokasi)) {
        $_SESSION['error'] = 'Judul, Perusahaan, dan Lokasi wajib diisi.';
        header("Location: manajemen-lowongan.php");
        exit;
    }

    if (!empty($id)) {
        // UPDATE
        $sql = "UPDATE lowongan SET 
                    judul=?, perusahaan=?, lokasi=?, tanggal_mulai=?, tanggal_berakhir=?, 
                    link_pendaftaran=?, deskripsi=?, syarat=?, status=?, " . (!empty($foto) ? "foto=?, " : "") . "updated_at=NOW()
                WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!empty($foto)) {
            mysqli_stmt_bind_param($stmt, 'ssssssssssi', $judul, $perusahaan, $lokasi, $tanggal_mulai, $tanggal_berakhir, $link, $deskripsi, $syarat, $status, $foto, $id);
        } else {
            mysqli_stmt_bind_param($stmt, 'sssssssssi', $judul, $perusahaan, $lokasi, $tanggal_mulai, $tanggal_berakhir, $link, $deskripsi, $syarat, $status, $id);
        }
    } else {
        // INSERT
        $sql = "INSERT INTO lowongan (
                    judul, perusahaan, lokasi, tanggal_mulai, tanggal_berakhir, 
                    link_pendaftaran, deskripsi, syarat, foto, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssssssssss', $judul, $perusahaan, $lokasi, $tanggal_mulai, $tanggal_berakhir, $link, $deskripsi, $syarat, $foto, $status, $created_at);
    }

    // Eksekusi
    if ($stmt && mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = !empty($id) ? 'Data berhasil diperbarui!' : 'Data berhasil ditambahkan!';
    } else {
        $_SESSION['error'] = 'Gagal menyimpan data: ' . ($stmt ? mysqli_stmt_error($stmt) : mysqli_error($conn));
    }

    if ($stmt) {
        mysqli_stmt_close($stmt);
    }
    header("Location: manajemen-lowongan.php");
    exit;
}

// Hapus data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hapus_lowongan'])) {
    $id = intval($_POST['id']);
    
    // Ambil nama file foto sebelum menghapus
    $foto_query = mysqli_query($conn, "SELECT foto FROM lowongan WHERE id = $id");
    $foto_data = mysqli_fetch_assoc($foto_query);
    
    if (mysqli_query($conn, "DELETE FROM lowongan WHERE id = $id")) {
        // Hapus file foto jika ada
        if (!empty($foto_data['foto']) && file_exists("uploads/{$foto_data['foto']}")) {
            unlink("uploads/{$foto_data['foto']}");
        }
        $_SESSION['success'] = 'Data berhasil dihapus!';
    } else {
        $_SESSION['error'] = 'Gagal menghapus data: ' . mysqli_error($conn);
    }
    
    header("Location: manajemen-lowongan.php");
    exit;
}

// Update status semua lowongan berdasarkan tanggal
$update_status_query = "UPDATE lowongan SET 
    status = CASE 
        WHEN CURDATE() BETWEEN tanggal_mulai AND tanggal_berakhir THEN 'active'
        ELSE 'inactive'
    END";
mysqli_query($conn, $update_status_query);

// Query untuk menampilkan data dengan pencarian dan filter
$keyword = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$where = [];
if (!empty($keyword)) {
    $keyword_escaped = mysqli_real_escape_string($conn, $keyword);
    $where[] = "(judul LIKE '%$keyword_escaped%' OR perusahaan LIKE '%$keyword_escaped%' OR lokasi LIKE '%$keyword_escaped%')";
}

$where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
$query = "SELECT * FROM lowongan $where_sql ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Ambil jumlah notifikasi belum dibaca
$stmt_notif = $conn->prepare("SELECT COUNT(*) as total FROM notifikasi WHERE penerima_role = ? AND status = 'belum_dibaca'");
$stmt_notif->bind_param("s", $role);
$stmt_notif->execute();
$result_notif = $stmt_notif->get_result();
if ($row_notif = $result_notif->fetch_assoc()) {
    $jumlah_notif = $row_notif['total'];
}
$stmt_notif->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Lowongan</title>
    <link href="styless.css?v=1.0" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .badge {
            font-size: 0.75rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.775rem;
        }
        .modal-header.bg-danger {
            border-bottom: 1px solid #dc3545;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .status-badge i {
            font-size: 0.75rem;
        }
        .status-info {
            background-color: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 0.375rem;
            padding: 0.75rem;
            margin-bottom: 1rem;
        }
        .status-info small {
            color: #1976d2;
        }
    </style>
</head>
<body>
    <header class="d-flex justify-content-between align-items-center p-3 bg-primary text-white">
        <div class="header-left d-flex align-items-center">
            <img src="images/logoYapis.png" alt="Logo Fakultas" style="height: 50px; margin-right: 15px;">
            <h5 class="mb-0">Tracer Study Fakultas Ilmu Komputer</h5>
        </div>
        <div class="header-right d-flex align-items-center gap-3">
            <div id="realtimeDateTime" class="me-3 small"></div>
            <div class="dropdown">
                <a class="btn btn-outline-light position-relative" href="notifikasi.php">
                    <i class="bi bi-bell"></i>
                    <?php if ($jumlah_notif > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $jumlah_notif ?>
                            <span class="visually-hidden">notifikasi belum dibaca</span>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($nama) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= $link_profil ?>"><i class="bi bi-person"></i> Profil</a></li>
                    <li><a class="dropdown-item" href="pengaturan.php"><i class="bi bi-gear"></i> Pengaturan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <nav class="sidebar">
        <?php include 'sidebar.php'; ?>
    </nav>

    <main>
        <div class="page-header fade-in-up">
            <h2><i class="bi bi-people-fill"></i> Manajemen Lowongan Pekerjaan</h2>
        </div>
        <div class="page-header fade-in-up">
            <div class="card-body">
                <!-- Alert Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <!-- Tombol Tambah -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button class="btn btn-primary" onclick="openLowonganModal()">
                        <i class="bi bi-plus-circle"></i> Tambah Lowongan
                    </button>
                    <span class="text-muted">Total: <?= mysqli_num_rows($result) ?> lowongan</span>
                </div>

                <!-- Form Pencarian dan Filter -->
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Cari judul, perusahaan, atau lokasi..."
                                   value="<?= htmlspecialchars($keyword) ?>">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="manajemen-lowongan.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="page-header fade-in-up">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">No</th>
                            <th>Judul</th>
                            <th>Perusahaan</th>
                            <th>Lokasi</th>
                            <th>Deskripsi</th>
                            <th>Syarat</th>
                            <th>Foto</th>
                            <th>Periode</th>
                            <th width="120">Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): 
                                $today = date('Y-m-d');
                                $status = ($row['tanggal_berakhir'] < $today) ? 'inactive' : 'active';
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($row['judul']) ?></strong></td>
                                <td><?= htmlspecialchars($row['perusahaan']) ?></td>
                                <td><?= htmlspecialchars($row['lokasi']) ?></td>
                                <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                                <td><?= htmlspecialchars($row['syarat']) ?></td>
                                <td>
                                    <?php if (!empty($row['foto']) && file_exists("uploads/{$row['foto']}")): ?>
                                        <img src="uploads/<?= htmlspecialchars($row['foto']) ?>" alt="Foto" width="60" class="img-thumbnail">
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small>
                                        <?= date('d M Y', strtotime($row['tanggal_mulai'])) ?> <br>
                                        s/d <?= date('d M Y', strtotime($row['tanggal_berakhir'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($status === 'active'): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle-fill"></i> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-x-circle-fill"></i> Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-warning btn-sm" 
                                                onclick='openLowonganModal(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" 
                                                onclick="confirmHapusLowongan(<?= $row['id'] ?>, '<?= htmlspecialchars($row['judul'], ENT_QUOTES) ?>')"
                                                title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <p class="mt-2 text-muted">Tidak ada data lowongan yang ditemukan.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Tambah/Edit -->
    <div class="modal fade" id="lowonganModal" tabindex="-1" aria-labelledby="lowonganModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" enctype="multipart/form-data" class="modal-content" id="lowonganForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="lowonganModalLabel">
                        <i class="bi bi-plus-circle"></i> Form Lowongan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                               </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="lowongan_id">
                    <input type="hidden" name="simpan_lowongan" value="1">
                    
                    <!-- Status Information -->
                    <div class="status-info">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle me-2"></i>
                            <small><strong>Informasi Status:</strong> Status lowongan akan otomatis ditentukan berdasarkan tanggal mulai dan berakhir. Jika tanggal hari ini berada dalam rentang periode, maka status akan menjadi "Active", jika tidak maka "Inactive".</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="judul" class="form-label">Judul Lowongan <span class="text-danger">*</span></label>
                            <input type="text" name="judul" id="judul" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="perusahaan" class="form-label">Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" name="perusahaan" id="perusahaan" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="lokasi" class="form-label">Lokasi <span class="text-danger">*</span></label>
                        <input type="text" name="lokasi" id="lokasi" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_berakhir" class="form-label">Tanggal Berakhir <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_berakhir" id="tanggal_berakhir" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="link_pendaftaran" class="form-label">Link Pendaftaran</label>
                        <input type="url" name="link_pendaftaran" id="link_pendaftaran" class="form-control" 
                               placeholder="https://example.com/apply">
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" 
                                  placeholder="Masukkan deskripsi lowongan..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="syarat" class="form-label">Syarat & Ketentuan</label>
                        <textarea name="syarat" id="syarat" class="form-control" rows="3" 
                                  placeholder="Masukkan syarat dan ketentuan..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="foto" class="form-label">Foto/Logo</label>
                        <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
                        <div class="form-text">Format yang diizinkan: JPG, JPEG, PNG, GIF (Max: 2MB)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus -->
    <div class="modal fade" id="hapusModal" tabindex="-1" aria-labelledby="hapusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="hapusModalLabel">
                        <i class="bi bi-exclamation-triangle"></i> Konfirmasi Hapus
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="bi bi-trash display-1 text-danger"></i>
                        <p class="mt-3">Apakah Anda yakin ingin menghapus lowongan:</p>
                        <strong id="hapus_judul" class="text-danger"></strong>
                        <p class="mt-2 text-muted">Data yang dihapus tidak dapat dikembalikan.</p>
                    </div>
                    <input type="hidden" name="id" id="hapus_id">
                    <input type="hidden" name="hapus_lowongan" value="1">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openLowonganModal(data = null) {
            const modal = new bootstrap.Modal(document.getElementById('lowonganModal'));
            const form = document.getElementById('lowonganForm');
            const modalTitle = document.getElementById('lowonganModalLabel');
            
            // Reset form
            form.reset();
            
            if (data) {
                // Mode Edit
                modalTitle.innerHTML = '<i class="bi bi-pencil"></i> Edit Lowongan';
                document.getElementById('lowongan_id').value = data.id || '';
                document.getElementById('judul').value = data.judul || '';
                document.getElementById('perusahaan').value = data.perusahaan || '';
                document.getElementById('lokasi').value = data.lokasi || '';
                document.getElementById('tanggal_mulai').value = data.tanggal_mulai || '';
                document.getElementById('tanggal_berakhir').value = data.tanggal_berakhir || '';
                document.getElementById('link_pendaftaran').value = data.link_pendaftaran || '';
                document.getElementById('deskripsi').value = data.deskripsi || '';
                document.getElementById('syarat').value = data.syarat || '';
            } else {
                // Mode Tambah
                modalTitle.innerHTML = '<i class="bi bi-plus-circle"></i> Tambah Lowongan';
            }
            
            modal.show();
        }

        function confirmHapusLowongan(id, judul) {
            document.getElementById('hapus_id').value = id;
            document.getElementById('hapus_judul').textContent = judul;
            const hapusModal = new bootstrap.Modal(document.getElementById('hapusModal'));
            hapusModal.show();
        }

        // Validasi form
        document.getElementById('lowonganForm').addEventListener('submit', function(e) {
            const tanggalMulai = new Date(document.getElementById('tanggal_mulai').value);
            const tanggalBerakhir = new Date(document.getElementById('tanggal_berakhir').value);
            
            if (tanggalBerakhir <= tanggalMulai) {
                e.preventDefault();
                alert('Tanggal berakhir harus lebih besar dari tanggal mulai!');
                return false;
            }
        });

        // Auto-hide alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    if (alert && alert.parentNode) {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            if (alert.parentNode) {
                                alert.parentNode.removeChild(alert);
                            }
                        }, 500);
                    }
                }, 5000);
            });
        });

        function updateDateTime() {
            const elem = document.getElementById('realtimeDateTime');
            if (!elem) return;
            const now = new Date();
            elem.textContent = now.toLocaleString('id-ID', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);
    </script>

</body>
</html>
