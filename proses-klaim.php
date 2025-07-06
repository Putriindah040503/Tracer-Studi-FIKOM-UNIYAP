<?php
include 'koneksi.php';
session_start();

function swal($tipe, $judul, $pesan, $redirect = 'login.php') {
    echo "
    <html><head>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head><body>
    <script>
        Swal.fire({
            icon: '$tipe',
            title: '$judul',
            html: '$pesan',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = '$redirect';
        });
    </script>
    </body></html>";
    exit;
}

// === AJAX CHECK ===
if (isset($_POST['ajax_check'])) {
    $npm = mysqli_real_escape_string($conn, $_POST['npm']);
    $tahun = mysqli_real_escape_string($conn, $_POST['tahun_lulus']);

    $query = mysqli_query($conn, "SELECT status_utama FROM alumni WHERE npm='$npm' AND tahun_lulus='$tahun'");
    if (mysqli_num_rows($query) > 0) {
        $row = mysqli_fetch_assoc($query);
        if ($row['status_utama'] === 'terklaim') {
            $res = ['status' => 'error', 'message' => 'Akun sudah diklaim. Silakan login.'];
        } else {
            $res = ['status' => 'ok', 'message' => 'Data ditemukan. Anda dapat klaim akun.'];
        }
    } else {
        $res = ['status' => 'ok', 'message' => 'Data belum ada, akan diajukan ke admin.'];
    }

    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}

// === PROSES KLAIM ===

$npm            = mysqli_real_escape_string($conn, $_POST['npm']);
$nama_lengkap   = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
$tahun_lulus    = mysqli_real_escape_string($conn, $_POST['tahun_lulus']);
$program_studi  = mysqli_real_escape_string($conn, $_POST['program_studi']);
$email          = mysqli_real_escape_string($conn, $_POST['email']);
$no_hp          = mysqli_real_escape_string($conn, $_POST['no_hp']);
$nik            = mysqli_real_escape_string($conn, $_POST['nik']);
$npwp           = mysqli_real_escape_string($conn, $_POST['npwp']);
$jenis_kelamin  = mysqli_real_escape_string($conn, $_POST['jenis_kelamin'] ?? '');
$tanggal_lahir  = mysqli_real_escape_string($conn, $_POST['tanggal_lahir'] ?? null);
$password       = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Cek alumni berdasarkan NPM + Tahun Lulus
$cek = mysqli_query($conn, "SELECT * FROM alumni WHERE npm='$npm' AND tahun_lulus='$tahun_lulus'");
$data = mysqli_fetch_assoc($cek);

if ($data) {
    if ($data['status_utama'] === 'terklaim') {
        swal('error', 'Klaim Gagal', 'Akun sudah diklaim sebelumnya. Silakan login.');
    }

    // Cek email dan HP duplikat (dengan NPM berbeda)
    $cekEmail = mysqli_query($conn, "SELECT * FROM alumni WHERE email='$email' AND npm != '$npm'");
    if (mysqli_num_rows($cekEmail) > 0) {
        swal('error', 'Email Terpakai', 'Email sudah digunakan alumni lain. Gunakan email lain.', 'klaim_akun.php');
    }

    $cekHP = mysqli_query($conn, "SELECT * FROM alumni WHERE no_hp='$no_hp' AND npm != '$npm'");
    if (mysqli_num_rows($cekHP) > 0) {
        swal('error', 'Nomor HP Terpakai', 'No HP sudah digunakan alumni lain. Gunakan nomor lain.', 'klaim_akun.php');
    }

    // Update data alumni yang sudah ada
    $update = mysqli_query($conn, "UPDATE alumni SET
        nama_lengkap = '$nama_lengkap',
        program_studi = '$program_studi',
        email = '$email',
        no_hp = '$no_hp',
        nik = '$nik',
        npwp = '$npwp',
        jenis_kelamin = '$jenis_kelamin',
        tanggal_lahir = " . ($tanggal_lahir ? "'$tanggal_lahir'" : "NULL") . ",
        password = '$password',
        status_utama = 'terklaim',
        updated_at = NOW()
        WHERE npm='$npm' AND tahun_lulus='$tahun_lulus'
    ");

    if ($update) {
        swal('success', 'Klaim Berhasil', 'Klaim akun berhasil. Silakan login.');
    } else {
        swal('error', 'Klaim Gagal', 'Gagal menyimpan klaim akun.', 'klaim_akun.php');
    }

} else {
    // Pengajuan klaim baru
    if (!isset($_POST['submit_ajuan'])) {
        swal('warning', 'Akses Ditolak', 'Silakan konfirmasi terlebih dahulu sebelum mengajukan data.', 'klaim_akun.php');
    }

    // Cek NPM sudah ada di sistem dengan tahun lain?
    $cekNPM = mysqli_query($conn, "SELECT * FROM alumni WHERE npm='$npm'");
    if (mysqli_num_rows($cekNPM) > 0) {
        swal('error', 'NPM Duplikat', 'NPM sudah digunakan. Periksa kembali data Anda.', 'klaim_akun.php');
    }

   // Simpan data alumni baru
$insert = mysqli_query($conn, "INSERT INTO alumni (
    npm, nama_lengkap, program_studi, tahun_lulus,
    email, no_hp, nik, npwp, jenis_kelamin, tanggal_lahir,
    password, status_utama, status_kuesioner, created_at
) VALUES (
    '$npm', '$nama_lengkap', '$program_studi', '$tahun_lulus',
    '$email', '$no_hp', '$nik', '$npwp', '$jenis_kelamin', " . ($tanggal_lahir ? "'$tanggal_lahir'" : "NULL") . ",
    '$password', 'pending_verifikasi', 'Sudah Klaim', NOW()
)");


    if ($insert) {
        swal('success', 'Data Diajukan', 'Data alumni Anda telah diajukan dan menunggu verifikasi admin.');
    } else {
        swal('error', 'Gagal Menyimpan', 'Terjadi kesalahan saat menyimpan data.', 'klaim_akun.php');
    }
}
?>
