<?php
include 'koneksi.php';
session_start();

// Cek autentikasi
if (!isset($_SESSION['id']) || !in_array($_SESSION['role'], ['admin', 'fakultas'])) {
    header("Location: login.php");
    exit;
}

// Proses aksi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'add':
        case 'edit':
            handleSaveAlumni($conn);
            break;
        case 'delete':
            handleDeleteAlumni($conn);
            break;
        case 'verify':
            handleVerifyAlumni($conn);
            break;
        case 'bulk_verify':
            handleBulkVerify($conn);
            break;
    }
}

// Build filter query
$where_conditions = [];
if (!empty($_GET['search'])) {
    $keyword = mysqli_real_escape_string($conn, $_GET['search']);
    $where_conditions[] = "(nama_lengkap LIKE '%$keyword%' OR npm LIKE '%$keyword%')";
}
if (!empty($_GET['filter_prodi'])) {
    $prodi = mysqli_real_escape_string($conn, $_GET['filter_prodi']);
    $where_conditions[] = "program_studi = '$prodi'";
}
if (isset($_GET['filter_status']) && $_GET['filter_status'] !== '') {
    $status = mysqli_real_escape_string($conn, $_GET['filter_status']);
    $where_conditions[] = "status_utama = '$status'";
}
if (!empty($_GET['filter_tahun'])) {
    $tahun = mysqli_real_escape_string($conn, $_GET['filter_tahun']);
    $where_conditions[] = "tahun_lulus = '$tahun'";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Query data
$query = "SELECT * FROM alumni $where_clause ORDER BY 
    CASE status_utama 
        WHEN 'pending_verifikasi' THEN 1
        WHEN 'active' THEN 2
        WHEN 'terklaim' THEN 3
        ELSE 4
    END, nama_lengkap ASC";
$result = mysqli_query($conn, $query);

// Statistik
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status_utama = 'pending_verifikasi' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status_utama = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status_kuesioner = 'sudah_isi' THEN 1 ELSE 0 END) as kuesioner
FROM alumni";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

// Fungsi helper
function handleSaveAlumni($conn) {
    $id = $_POST['id'] ?? '';
    $npm = validateInput($_POST['npm']);
    $nama = validateInput($_POST['nama_lengkap']);
    $email = validateInput($_POST['email']);
    $prodi = validateInput($_POST['program_studi']);
    $tahun_lulus = (int)$_POST['tahun_lulus'];
    
    // Validasi
    $errors = [];
    if (!$npm) $errors[] = "NPM wajib diisi";
    if (!$nama) $errors[] = "Nama wajib diisi";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email tidak valid";
    if ($tahun_lulus < 2000 || $tahun_lulus > date('Y')+5) $errors[] = "Tahun lulus tidak valid";
    
    // Cek duplikasi
    $check_query = "SELECT id FROM alumni WHERE (npm = '$npm' OR email = '$email')";
    if ($id) $check_query .= " AND id != '$id'";
    if (mysqli_num_rows(mysqli_query($conn, $check_query)) > 0) {
        $errors[] = "NPM atau Email sudah terdaftar";
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode(", ", $errors);
        return;
    }
    
    // Simpan data
    if ($id) {
        // Update
        $query = "UPDATE alumni SET 
            npm = '$npm', nama_lengkap = '$nama', email = '$email',
            program_studi = '$prodi', tahun_lulus = $tahun_lulus,
            no_hp = '" . validateInput($_POST['no_hp']) . "',
            updated_at = NOW()
            WHERE id = '$id'";
        $message = "Data alumni berhasil diupdate";
    } else {
        // Insert
        $query = "INSERT INTO alumni (npm, nama_lengkap, email, program_studi, tahun_lulus, no_hp, status_utama, created_at) 
                  VALUES ('$npm', '$nama', '$email', '$prodi', $tahun_lulus, '" . validateInput($_POST['no_hp']) . "', 'pending_verifikasi', NOW())";
        $message = "Alumni berhasil ditambahkan";
    }
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = $message;
    } else {
        $_SESSION['error'] = "Gagal menyimpan data: " . mysqli_error($conn);
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

function handleDeleteAlumni($conn) {
    $id = (int)$_POST['id'];
    if ($id > 0) {
        $query = "DELETE FROM alumni WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Data alumni berhasil dihapus";
        } else {
            $_SESSION['error'] = "Gagal menghapus data";
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

function handleVerifyAlumni($conn) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'] === 'approve' ? 'active' : 'rejected';
    
    if ($id > 0) {
        $query = "UPDATE alumni SET status_utama = '$status', verified_at = NOW() WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Status alumni berhasil diupdate";
        } else {
            $_SESSION['error'] = "Gagal mengupdate status";
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

function handleBulkVerify($conn) {
    $ids = $_POST['selected_ids'] ?? [];
    $status = $_POST['bulk_status'];
    
    if (!empty($ids) && in_array($status, ['active', 'rejected'])) {
        $id_list = implode(',', array_map('intval', $ids));
        $query = "UPDATE alumni SET status_utama = '$status', verified_at = NOW() WHERE id IN ($id_list)";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = count($ids) . " alumni berhasil diverifikasi";
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

function validateInput($input) {
    return htmlspecialchars(trim($input));
}

function getStatusBadge($status) {
    $badges = [
        'pending_verifikasi' => '<span class="badge bg-warning">Pending</span>',
        'active' => '<span class="badge bg-success">Active</span>',
        'terklaim' => '<span class="badge bg-info">Terklaim</span>',
        'rejected' => '<span class="badge bg-danger">Rejected</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
}

    // Ambil jumlah notifikasi belum dibaca
$stmt_notif = $conn->prepare("SELECT COUNT(*) as total FROM notifikasi WHERE penerima_role = ? AND status = 'belum_dibaca'");

    $stmt_notif->bind_param("s", $role);
    $stmt_notif->execute();
    $result_notif = $stmt_notif->get_result();
    if ($row_notif = $result_notif->fetch_assoc()) {
        $jumlah_notif = $row_notif['total'];
    }
    $stmt_notif->close();
$nama_pengguna = ''; // lebih deskriptif
if (isset($_SESSION['id']) && $_SESSION['role'] === 'alumni') {
    $stmt = $conn->prepare("SELECT nama_lengkap FROM alumni WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $nama_pengguna = $row['nama_lengkap'];
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Alumni</title>
         <link href="styless.css?v=1.0" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<header class="d-flex justify-content-between align-items-center p-3 bg-primary text-white">
    <button class="btn btn-outline-light d-md-none" id="toggleSidebar">
    <i class="bi bi-list"></i>
</button>
    <div class="header-left d-flex align-items-center">
        <img src="images/logoYapis.png" alt="Logo Fakultas" style="height: 50px; margin-right: 15px;">
        <h5 class="mb-0">Tracer Study Fakultas Ilmu Komputer</h5>
    </div>

    <div class="header-right d-flex align-items-center gap-3">
        <!-- Waktu Realtime -->
        <div id="realtimeDateTime" class="me-3 small"></div>

        <!-- Notifikasi -->
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

        <!-- Profil Pengguna -->
        <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($nama_pengguna) ?>
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

          <nav class="sidebar">
        <?php include 'sidebar.php'; ?>
    </nav>
<main>
      <div class="page-header fade-in-up">
            <h2><i class="bi bi-people-fill"></i> Manajemen Alumni</h2>
            <p class="text-muted">Kelola data Alumni sistem tracer study</p>
        </div>
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h4><?= $stats['pending'] ?></h4>
                        <small>Pending Verifikasi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h4><?= $stats['active'] ?></h4>
                        <small>Active</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h4><?= $stats['kuesioner'] ?></h4>
                        <small>Sudah Isi Kuesioner</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h4><?= $stats['total'] ?></h4>
                        <small>Total Alumni</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary" onclick="openModal()">
                        <i class="bi bi-person-plus"></i> Tambah Alumni
                    </button>
                    <button class="btn btn-success" onclick="bulkVerify('active')">
                        <i class="bi bi-check-all"></i> Verifikasi Terpilih
                    </button>
                    <button class="btn btn-danger" onclick="bulkVerify('rejected')">
                        <i class="bi bi-x-circle"></i> Tolak Terpilih
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama/NPM..." 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="filter_prodi" class="form-select">
                            <option value="">Semua Prodi</option>
                            <option value="Sistem Informasi" <?= ($_GET['filter_prodi'] ?? '') == 'Sistem Informasi' ? 'selected' : '' ?>>SI</option>
                            <option value="Informatika" <?= ($_GET['filter_prodi'] ?? '') == 'Informatika' ? 'selected' : '' ?>>IF</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="filter_status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending_verifikasi" <?= ($_GET['filter_status'] ?? '') == 'pending_verifikasi' ? 'selected' : '' ?>>Pending</option>
                            <option value="active" <?= ($_GET['filter_status'] ?? '') == 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="rejected" <?= ($_GET['filter_status'] ?? '') == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="filter_tahun" class="form-control" placeholder="Tahun Lulus" 
                               value="<?= htmlspecialchars($_GET['filter_tahun'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Data Alumni</h6>
                    <div>
                        <input type="checkbox" id="selectAll" onchange="toggleAll(this)">
                        <label for="selectAll" class="ms-1">Pilih Semua</label>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="40"></th>
                            <th>NPM</th>
                            <th>Nama</th>
                            <th>Prodi</th>
                            <th>Tahun</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="select-item" value="<?= $row['id'] ?>">
                            </td>
                            <td><span class="badge bg-light text-dark"><?= htmlspecialchars($row['npm']) ?></span></td>
                            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            <td>
                                <span class="badge <?= $row['program_studi'] == 'Informatika' ? 'bg-info' : 'bg-warning' ?>">
                                    <?= $row['program_studi'] == 'Informatika' ? 'IF' : 'SI' ?>
                                </span>
                            </td>
                            <td><?= $row['tahun_lulus'] ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= getStatusBadge($row['status_utama']) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick='editAlumni(<?= json_encode($row) ?>)' title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php if ($row['status_utama'] == 'pending_verifikasi'): ?>
                                    <button class="btn btn-outline-success" onclick="verifyAlumni(<?= $row['id'] ?>, 'approve')" title="Verifikasi">
                                        <i class="bi bi-check"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="verifyAlumni(<?= $row['id'] ?>, 'reject')" title="Tolak">
                                        <i class="bi bi-x"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-danger" onclick="deleteAlumni(<?= $row['id'] ?>)" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if (mysqli_num_rows($result) === 0): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="alumniModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="alumniForm" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Form Alumni</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="form-action" value="add">
                        <input type="hidden" name="id" id="form-id">
                        
                        <div class="mb-3">
                            <label class="form-label">NPM *</label>
                            <input type="text" name="npm" id="form-npm" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" name="nama_lengkap" id="form-nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" id="form-email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No HP</label>
                            <input type="text" name="no_hp" id="form-hp" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Program Studi *</label>
                                    <select name="program_studi" id="form-prodi" class="form-select" required>
                                        <option value="">Pilih Prodi</option>
                                        <option value="Sistem Informasi">Sistem Informasi</option>
                                        <option value="Informatika">Informatika</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tahun Lulus *</label>
                                    <input type="number" name="tahun_lulus" id="form-lulus" class="form-control" 
                                           min="2000" max="<?= date('Y') + 5 ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden Forms -->
    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="delete-id">
    </form>

    <form id="verifyForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="verify">
        <input type="hidden" name="id" id="verify-id">
        <input type="hidden" name="status" id="verify-status">
    </form>

    <form id="bulkForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="bulk_verify">
        <input type="hidden" name="bulk_status" id="bulk-status">
        <div id="bulk-ids"></div>
    </form>
</main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openModal(data = null) {
            const modal = new bootstrap.Modal(document.getElementById('alumniModal'));
            const form = document.getElementById('alumniForm');
            
            form.reset();
            
            if (data) {
                document.getElementById('form-action').value = 'edit';
                document.getElementById('form-id').value = data.id;
                document.getElementById('form-npm').value = data.npm;
                document.getElementById('form-nama').value = data.nama_lengkap;
                document.getElementById('form-email').value = data.email;
                document.getElementById('form-hp').value = data.no_hp || '';
                document.getElementById('form-prodi').value = data.program_studi;
                document.getElementById('form-lulus').value = data.tahun_lulus;
            } else {
                document.getElementById('form-action').value = 'add';
            }
            
            modal.show();
        }

        function editAlumni(data) {
            openModal(data);
        }

        function deleteAlumni(id) {
            if (confirm('Yakin ingin menghapus data ini?')) {
                document.getElementById('delete-id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        function verifyAlumni(id, action) {
            const message = action === 'approve' ? 'menyetujui' : 'menolak';
            if (confirm(`Yakin ingin ${message} alumni ini?`)) {
                document.getElementById('verify-id').value = id;
                document.getElementById('verify-status').value = action;
                document.getElementById('verifyForm').submit();
            }
        }

        function toggleAll(checkbox) {
            const items = document.querySelectorAll('.select-item');
            items.forEach(item => item.checked = checkbox.checked);
        }

        function bulkVerify(status) {
            const selected = document.querySelectorAll('.select-item:checked');
            if (selected.length === 0) {
                alert('Pilih minimal satu alumni');
                return;
            }
            
            const action = status === 'active' ? 'menyetujui' : 'menolak';
            if (confirm(`Yakin ingin ${action} ${selected.length} alumni terpilih?`)) {
                document.getElementById('bulk-status').value = status;
                
                const bulkIds = document.getElementById('bulk-ids');
                bulkIds.innerHTML = '';
                
                selected.forEach(item => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_ids[]';
                    input.value = item.value;
                    bulkIds.appendChild(input);
                });
                
                document.getElementById('bulkForm').submit();
            }
        }

        // Auto-hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
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