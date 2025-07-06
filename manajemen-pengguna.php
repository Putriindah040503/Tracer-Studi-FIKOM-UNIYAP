<?php
session_start();
include 'koneksi.php';

// Cek otentikasi admin
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fungsi untuk mendapatkan semua pengguna
function getAllUsers($conn) {
    $data = [];
    
    // Ambil data admin dan fakultas dari tabel user
    $resUser = mysqli_query($conn, "SELECT id, nama, email, role FROM user WHERE role IN ('admin', 'fakultas')");
    if ($resUser) {
        while ($row = mysqli_fetch_assoc($resUser)) {
            $data[] = $row;
        }
    }
    
    // Ambil data alumni yang aktif (pernah login)
$resAlumni = mysqli_query($conn, "SELECT id, nama_lengkap AS nama, email, 'alumni' AS role, 
                                 program_studi, npm, tahun_masuk, tahun_lulus, last_login
                                 FROM alumni WHERE last_login IS NOT NULL");

    if ($resAlumni) {
        while ($row = mysqli_fetch_assoc($resAlumni)) {
            $data[] = $row;
        }
    }
    
    return $data;
}

// Handle penghapusan pengguna
if (isset($_GET['hapus']) && isset($_GET['role'])) {
    $id = intval($_GET['hapus']);
    $role = mysqli_real_escape_string($conn, $_GET['role']);
    
    // Cegah admin menghapus dirinya sendiri
    if ($role === 'admin' && $id == $_SESSION['id']) {
        $_SESSION['error'] = "Anda tidak dapat menghapus akun Anda sendiri!";
        header("Location: manajemen-pengguna.php");
        exit;
    }
    
    if ($role === 'admin' || $role === 'fakultas') {
        $stmt = $conn->prepare("DELETE FROM user WHERE id = ? AND role = ?");
        $stmt->bind_param("is", $id, $role);
    } elseif ($role === 'alumni') {
        // Untuk alumni, hanya ubah status menjadi nonaktif
        $stmt = $conn->prepare("UPDATE alumni SET status = 'nonaktif' WHERE id = ?");
        $stmt->bind_param("i", $id);
    }

    if ($stmt && $stmt->execute()) {
        $_SESSION['success'] = $role === 'alumni' ? "Alumni berhasil dinonaktifkan." : "Pengguna berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal memproses permintaan.";
    }
    
    if ($stmt) $stmt->close();
    header("Location: manajemen-pengguna.php");
    exit;
}

// Handle penyimpanan/update pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Validasi input
    if (empty($nama) || empty($email) || empty($role)) {
        $_SESSION['error'] = "Semua field harus diisi!";
        header("Location: manajemen-pengguna.php");
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format email tidak valid!";
        header("Location: manajemen-pengguna.php");
        exit;
    }
    
    // Hash password jika diisi
    $hashedPassword = '';
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $_SESSION['error'] = "Password minimal 6 karakter!";
            header("Location: manajemen-pengguna.php");
            exit;
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    }
    
    if ($id > 0) {
        // Update pengguna existing
        if ($role === 'admin' || $role === 'fakultas') {
            if (!empty($password)) {
                $stmt = $conn->prepare("UPDATE user SET nama = ?, email = ?, password = ? WHERE id = ? AND role = ?");
                $stmt->bind_param("sssis", $nama, $email, $hashedPassword, $id, $role);
            } else {
                $stmt = $conn->prepare("UPDATE user SET nama = ?, email = ? WHERE id = ? AND role = ?");
                $stmt->bind_param("ssis", $nama, $email, $id, $role);
            }
        } elseif ($role === 'alumni') {
            if (!empty($password)) {
                $stmt = $conn->prepare("UPDATE alumni SET nama_lengkap = ?, email = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssi", $nama, $email, $hashedPassword, $id);
            } else {
                $stmt = $conn->prepare("UPDATE alumni SET nama_lengkap = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $nama, $email, $id);
            }
        }
        
        if ($stmt && $stmt->execute()) {
            $_SESSION['success'] = "Pengguna berhasil diupdate.";
        } else {
            $_SESSION['error'] = "Gagal mengupdate pengguna.";
        }
    } else {
        // Tambah pengguna baru (hanya admin dan fakultas)
        if ($role === 'alumni') {
            $_SESSION['error'] = "Alumni tidak dapat ditambahkan melalui halaman ini!";
            header("Location: manajemen-pengguna.php");
            exit;
        }
        
        if (empty($password)) {
            $_SESSION['error'] = "Password harus diisi untuk pengguna baru!";
            header("Location: manajemen-pengguna.php");
            exit;
        }
        
        // Cek email duplikat
        $checkStmt = $conn->prepare("SELECT id FROM user WHERE email = ? UNION SELECT id FROM alumni WHERE email = ?");
        $checkStmt->bind_param("ss", $email, $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Email sudah terdaftar!";
            header("Location: manajemen-pengguna.php");
            exit;
        }
        $checkStmt->close();
        
        // Insert pengguna baru ke tabel user
        $stmt = $conn->prepare("INSERT INTO user (nama, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $email, $hashedPassword, $role);
        
        if ($stmt && $stmt->execute()) {
            $_SESSION['success'] = "Pengguna berhasil ditambahkan.";
        } else {
            $_SESSION['error'] = "Gagal menambahkan pengguna.";
        }
    }
    
    if ($stmt) $stmt->close();
    header("Location: manajemen-pengguna.php");
    exit;
}

$users = getAllUsers($conn);

// Hitung jumlah pengguna per role
$jumlahAdmin = count(array_filter($users, function($u) { return $u['role'] === 'admin'; }));
$jumlahFakultas = count(array_filter($users, function($u) { return $u['role'] === 'fakultas'; }));
$jumlahAlumni = count(array_filter($users, function($u) { return $u['role'] === 'alumni'; }));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna</title>
     <link href="styless.css?v=1.0" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1050;
        }
        .modal-content-custom {
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            position: relative;
        }
        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

    <nav class="sidebar">
        <?php include 'sidebar.php'; ?>
    </nav>

    <main>
        <div class="page-header fade-in-up">
            <h2><i class="bi bi-people-fill"></i> Manajemen Pengguna</h2>
            <p class="text-muted">Kelola data pengguna sistem tracer study</p>
        </div>
        
        <button class="btn btn-primary mb-3" onclick="showForm()">
            <i class="bi bi-plus-circle"></i> Tambah Admin/Fakultas
        </button>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> <?= htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Statistik -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5><i class="bi bi-shield-check"></i> Admin</h5>
                        <p class="fs-4"><?= $jumlahAdmin ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5><i class="bi bi-building"></i> Fakultas</h5>
                        <p class="fs-4"><?= $jumlahFakultas ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5><i class="bi bi-mortarboard"></i> Alumni Aktif</h5>
                        <p class="fs-4"><?= $jumlahAlumni ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Admin & Fakultas -->
        <div class="card mb-4">
            <div class="card-header">
                <h4><i class="bi bi-people"></i> Admin & Fakultas</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($users as $u): ?>
                                <?php if ($u['role'] === 'admin' || $u['role'] === 'fakultas'): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($u['nama']) ?></td>
                                        <td><?= htmlspecialchars($u['email']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $u['role'] === 'admin' ? 'primary' : 'success' ?>">
                                                <?= ucfirst($u['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick='editForm(<?= json_encode($u) ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if ($u['role'] !== 'admin' || $u['id'] != $_SESSION['id']): ?>
                                                <button class="btn btn-sm btn-danger" onclick='hapusUser(<?= $u["id"] ?>, "<?= $u["role"] ?>", "<?= htmlspecialchars($u["nama"]) ?>")'>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tabel Alumni Aktif -->
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-mortarboard"></i> Alumni Aktif</h4>
                <small class="text-muted">Alumni yang pernah login ke sistem</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>NPM</th>
                                <th>Program Studi</th>
                                <th>Email</th>
                                <th>Tahun Lulus</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($users as $u): ?>
                                <?php if ($u['role'] === 'alumni'): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($u['nama']) ?></td>
                                        <td><?= htmlspecialchars($u['npm'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($u['program_studi'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($u['email']) ?></td>
                                        <td><?= htmlspecialchars($u['tahun_lulus'] ?? '-') ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick='editForm(<?= json_encode($u) ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick='nonaktifkanAlumni(<?= $u["id"] ?>, "<?= htmlspecialchars($u["nama"]) ?>")'>
                                                <i class="bi bi-x-circle"></i> Nonaktifkan
                                            </button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Form Modal -->
<div id="userForm" class="modal-overlay">
    <div class="modal-content-custom">
        <button onclick="hideForm()" class="close-btn">&times;</button>
        <h4 id="formTitle">Tambah Pengguna</h4>

        <form method="POST" onsubmit="return validateForm()">
            <input type="hidden" name="id" id="id" />
            <input type="hidden" name="role" id="role" />

            <div class="mb-3">
                <label for="nama" class="form-label">Nama *</label>
                <input type="text" class="form-control" name="nama" id="nama" required />
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" name="email" id="email" required />
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password <span id="passwordRequired">*</span></label>
                <input type="password" class="form-control" name="password" id="password" minlength="6" />
                <div class="form-text" id="passwordHelp">Minimal 6 karakter</div>
            </div>

            <div class="mb-3" id="roleSelect">
                <label class="form-label">Role *</label>
                <div>
                    <input type="radio" name="role" value="admin" id="roleAdmin" required />
                    <label for="roleAdmin">Admin</label>
                    <input type="radio" name="role" value="fakultas" id="roleFakultas" class="ms-3" />
                    <label for="roleFakultas">Fakultas</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-success" type="submit">Simpan</button>
                <button class="btn btn-secondary" type="button" onclick="hideForm()">Batal</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showForm() {
    document.getElementById("formTitle").innerText = "Tambah Admin/Fakultas";
    document.getElementById("id").value = '';
    document.getElementById("nama").value = '';
    document.getElementById("email").value = '';
    document.getElementById("password").value = '';
    document.getElementById("roleAdmin").checked = false;
    document.getElementById("roleFakultas").checked = false;
    document.getElementById("passwordRequired").style.display = 'inline';
    document.getElementById("passwordHelp").innerText = 'Minimal 6 karakter';
    document.getElementById("password").required = true;
    document.getElementById("roleSelect").style.display = 'block';
    document.getElementById("userForm").style.display = 'block';
}

function editForm(data) {
    document.getElementById("formTitle").innerText = "Edit " + data.nama;
    document.getElementById("id").value = data.id;
    document.getElementById("nama").value = data.nama;
    document.getElementById("email").value = data.email;
    document.getElementById("password").value = '';
    document.getElementById("role").value = data.role;
    document.getElementById("passwordRequired").style.display = 'none';
    document.getElementById("passwordHelp").innerText = 'Kosongkan jika tidak ingin mengubah';
    document.getElementById("password").required = false;
    
    if (data.role === 'alumni') {
        document.getElementById("roleSelect").style.display = 'none';
    } else {
        document.getElementById("roleSelect").style.display = 'block';
        document.getElementById(data.role === 'admin' ? 'roleAdmin' : 'roleFakultas').checked = true;
    }
    
    document.getElementById("userForm").style.display = 'block';
}

function hideForm() {
    document.getElementById("userForm").style.display = 'none';
}

function hapusUser(id, role, nama) {
    if (confirm(`Yakin ingin menghapus ${role} "${nama}"?`)) {
        window.location.href = `?hapus=${id}&role=${role}`;
    }
}

function nonaktifkanAlumni(id, nama) {
    if (confirm(`Yakin ingin menonaktifkan alumni "${nama}"?`)) {
        window.location.href = `?hapus=${id}&role=alumni`;
    }
}

function validateForm() {
    const nama = document.getElementById("nama").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const id = document.getElementById("id").value;
    const role = document.querySelector('input[name="role"]:checked')?.value || document.getElementById("role").value;

    if (!nama || !email || !role) {
        alert("Semua field harus diisi!");
        return false;
    }

    if (id === '' && password === '') {
        alert("Password harus diisi untuk pengguna baru!");
        return false;
    }

    if (password !== '' && password.length < 6) {
        alert("Password minimal 6 karakter!");
        return false;
    }

    return true;
}

// Tutup modal jika klik di luar
document.getElementById("userForm").onclick = function(event) {
    if (event.target === this) hideForm();
}
</script>

</body>
</html>