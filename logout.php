<?php
session_start();

// Ambil role dari session
$role = $_SESSION['role'] ?? '';

// Ambil nama pengguna berdasarkan role
switch ($role) {
    case 'admin':
        $nama = $_SESSION['nama_admin'] ?? 'Admin';
        $redirect = 'dashboard-admin.php';
        break;
    case 'fakultas':
        $nama = $_SESSION['nama_pengguna'] ?? 'Fakultas';
        $redirect = 'dashboard-fakultas.php';
        break;
    case 'alumni':
        $nama = $_SESSION['nama_lengkap'] ?? 'Alumni';
        $redirect = 'dashboard-alumni.php';
        break;
    default:
        $nama = 'Pengguna';
        $redirect = 'login.php';
        break;
}

// Tangani aksi logout jika sudah dikonfirmasi
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    session_destroy();
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Logout</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sampai jumpa, <?= htmlspecialchars($nama) ?>!',
            text: 'Anda berhasil logout.',
            showConfirmButton: false,
            timer: 2500
        }).then(() => {
            window.location.href = 'login.php';
        });
    </script>
    </body>
    </html>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Logout</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
    Swal.fire({
        title: 'Yakin ingin keluar?',
        text: "Anda akan keluar dari sistem.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php?confirm=yes';
        } else {
            window.location.href = '<?= $redirect ?>';
        }
    });
</script>
</body>
</html>
