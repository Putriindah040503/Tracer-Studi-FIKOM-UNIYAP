<?php
session_start();
include 'koneksi.php';

// Cek apakah user login sebagai alumni
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'alumni') {
    header("Location: login.php");
    exit;
}

// Ambil ID alumni dari session
$alumni_id = $_SESSION['id'];

// Ambil data dari database
$query = mysqli_query($conn, "SELECT * FROM alumni WHERE id = '$alumni_id'");
$alumni = mysqli_fetch_assoc($query);

if (!$alumni) {
    $_SESSION['error'] = "Data alumni tidak ditemukan.";
    header("Location: logout.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Profil Alumni - Tracer Study</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="styless.css" rel="stylesheet" />
</head>
<body>

<?php include 'header.php'; ?>
<nav class="sidebar">
    <?php include 'sidebar.php'; ?>
</nav>

<main class="content">
    <div class="page-header fade-in-up">
        <h2>
            <i class="bi bi-person-circle"></i>
            Profil Alumni
        </h2>
        <p class="mb-0 text-muted">
            Selamat datang, <?= htmlspecialchars($alumni['nama_lengkap']) ?>! Terima kasih telah berpartisipasi dalam tracer study kami.
        </p>
    </div>

    <div class="row">
        <div class="col-md-4 text-center">
            <img src="uploads/<?= htmlspecialchars($alumni['foto'] ?? 'default.jpg') ?>" alt="Foto Alumni" class="profile-pic mb-3" />
            
            <h4 class="text-primary"><?= htmlspecialchars($alumni['nama_lengkap']) ?></h4>
            <p class="text-muted mb-3"><?= htmlspecialchars($alumni['program_studi']) ?> - <?= htmlspecialchars($alumni['tahun_lulus']) ?></p>

            <!-- Tombol Edit Profil -->
            <div class="d-flex flex-column gap-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editModal">
                    <i class="bi bi-pencil-square me-2"></i>Edit Profil
                </button>
            </div>
        </div>

        <div class="col-md-8">
            <div class="info-card">
                <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Alumni</h5>

                <div class="info-item">
                    <i class="bi bi-person-badge-fill"></i>
                    <div>
                        <strong>NPM:</strong><br>
                        <?= htmlspecialchars($alumni['npm']) ?>
                    </div>
                </div>

                <div class="info-item">
                    <i class="bi bi-mortarboard-fill"></i>
                    <div>
                        <strong>Program Studi:</strong><br>
                        <?= htmlspecialchars($alumni['program_studi']) ?>
                    </div>
                </div>

                <div class="info-item">
                    <i class="bi bi-calendar-check-fill"></i>
                    <div>
                        <strong>Tahun Masuk - Lulus:</strong><br>
                        <?= htmlspecialchars($alumni['tahun_masuk']) ?> - <?= htmlspecialchars($alumni['tahun_lulus']) ?>
                    </div>
                </div>

                <div class="info-item">
                    <i class="bi bi-envelope-fill"></i>
                    <div>
                        <strong>Email:</strong><br>
                        <?= htmlspecialchars($alumni['email']) ?>
                    </div>
                </div>

                <div class="info-item">
                    <i class="bi bi-telephone-fill"></i>
                    <div>
                        <strong>No HP:</strong><br>
                        <?= htmlspecialchars($alumni['no_hp']) ?>
                    </div>
                </div>

                <div class="info-item">
                    <i class="bi bi-person-vcard"></i>
                    <div>
                        <strong>NIK:</strong><br>
                        <?= htmlspecialchars($alumni['nik']) ?>
                    </div>
                </div>

                <div class="info-item">
                    <i class="bi bi-wallet2"></i>
                    <div>
                        <strong>NPWP:</strong><br>
                        <?= htmlspecialchars($alumni['npwp']) ?>
                    </div>
                </div>

                <div class="info-item">
                    <i class="bi bi-calendar-event"></i>
                    <div>
                        <strong>Bergabung:</strong><br>
                        <?= isset($alumni['created_at']) ? date('d F Y', strtotime($alumni['created_at'])) : 'Tidak tersedia' ?>
                    </div>
                </div>

                <div class="info-item">
                    <i class="bi bi-journal-check"></i>
                    <div>
                        <strong>Status Kuesioner:</strong><br>
                        <?= $alumni['status_kuesioner'] ? '<span class="badge bg-primary">Sudah Mengisi</span>' : '<span class="badge bg-secondary">Belum Mengisi</span>' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Profil -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="update-profil-alumni.php" enctype="multipart/form-data" class="modal-content" onsubmit="return validateForm()">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Profil Alumni</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body row g-3">
                    <input type="hidden" name="id" value="<?= $alumni['id'] ?>">

                    <div class="col-md-6">
                        <label class="form-label">NPM</label>
                        <input type="text" name="npm" class="form-control" value="<?= htmlspecialchars($alumni['npm']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nama Universitas</label>
                        <input type="text" name="nama_universitas" class="form-control" value="<?= htmlspecialchars($alumni['nama_universitas']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($alumni['nama_lengkap']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Program Studi</label>
                        <input type="text" name="program_studi" class="form-control" value="<?= htmlspecialchars($alumni['program_studi']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tahun Masuk</label>
                        <input type="number" name="tahun_masuk" class="form-control" value="<?= htmlspecialchars($alumni['tahun_masuk']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tahun Lulus</label>
                        <input type="number" name="tahun_lulus" class="form-control" value="<?= htmlspecialchars($alumni['tahun_lulus']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($alumni['email']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($alumni['no_hp']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">NIK</label>
                        <input type="text" name="nik" class="form-control" value="<?= htmlspecialchars($alumni['nik']) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">NPWP</label>
                        <input type="text" name="npwp" class="form-control" value="<?= htmlspecialchars($alumni['npwp']) ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Upload Foto</label><br>
                        <img src="uploads/<?= htmlspecialchars($alumni['foto'] ?? 'default.jpg') ?>" 
                             style="width: 100px; height: 100px; object-fit: cover;" class="img-thumbnail mb-2">
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-2"></i>Simpan
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function validateForm() {
    const hp = document.querySelector('[name="no_hp"]').value;
    const nik = document.querySelector('[name="nik"]').value;
    const npwp = document.querySelector('[name="npwp"]').value;
    
    const hpRegex = /^[0-9]{10,15}$/;
    const nikRegex = /^[0-9]{16}$/;
    const npwpRegex = /^[0-9]{15}$/;

    if (!hpRegex.test(hp)) {
        Swal.fire({
            icon: 'error',
            title: 'Validasi Error',
            text: 'No HP harus berisi 10-15 digit angka',
            confirmButtonColor: '#d33'
        });
        return false;
    }
    
    if (nik && !nikRegex.test(nik)) {
        Swal.fire({
            icon: 'error',
            title: 'Validasi Error',
            text: 'NIK harus berisi 16 digit angka',
            confirmButtonColor: '#d33'
        });
        return false;
    }
    
    if (npwp && !npwpRegex.test(npwp)) {
        Swal.fire({
            icon: 'error',
            title: 'Validasi Error',
            text: 'NPWP harus berisi 15 digit angka',
            confirmButtonColor: '#d33'
        });
        return false;
    }
    
    return true;
}
</script>

<?php if (isset($_SESSION['success'])): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: '<?= $_SESSION['success']; ?>',
    confirmButtonColor: '#3085d6'
});
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Oops...',
    text: '<?= $_SESSION['error']; ?>',
    confirmButtonColor: '#d33'
});
</script>
<?php unset($_SESSION['error']); endif; ?>

</body>
</html>
