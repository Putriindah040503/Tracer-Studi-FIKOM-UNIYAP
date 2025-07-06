<?php
session_start();
include 'koneksi.php';

function uploadFoto($file, $namaLama = null) {
    if ($file && $file['error'] === 0) {
        $namaFile = uniqid() . '-' . basename($file['name']);
        $tujuan = 'uploads/' . $namaFile;

        // Validasi tipe file
        $tipe = mime_content_type($file['tmp_name']);
        if (!in_array($tipe, ['image/jpeg', 'image/png', 'image/webp'])) {
            return null;
        }

        // Hapus foto lama
        if ($namaLama && file_exists("uploads/$namaLama")) {
            unlink("uploads/$namaLama");
        }

        move_uploaded_file($file['tmp_name'], $tujuan);
        return $namaFile;
    }
    return $namaLama;
}

// Ambil data POST
$id              = $_POST['id'] ?? '';
$npm             = $_POST['npm'];
$nama_lengkap    = $_POST['nama_lengkap'];
$nama_universitas= $_POST['nama_universitas'] ?? 'Universitas Yapis Papua';
$program_studi   = $_POST['program_studi'];
$tahun_masuk     = $_POST['tahun_masuk'];
$tahun_lulus     = $_POST['tahun_lulus'];
$email           = $_POST['email'];
$no_hp           = $_POST['no_hp'];
$nik             = $_POST['nik'] ?? null;
$npwp            = $_POST['npwp'] ?? null;
$jenis_kelamin   = $_POST['jenis_kelamin'];
$tanggal_lahir   = $_POST['tanggal_lahir'] ?? null;
$ipk             = $_POST['ipk'] ?? null;
$judul_ta        = $_POST['judul_ta'] ?? null;

$fotoBaru        = $_FILES['foto'] ?? null;

if ($id) {
    // Edit
    $result = mysqli_query($conn, "SELECT foto FROM alumni WHERE id = '$id'");
    $data = mysqli_fetch_assoc($result);
    $fotoLama = $data['foto'] ?? null;

    $foto = uploadFoto($fotoBaru, $fotoLama);

    $query = "UPDATE alumni SET 
        npm='$npm',
        nama_lengkap='$nama_lengkap',
        nama_universitas='$nama_universitas',
        program_studi='$program_studi',
        tahun_masuk='$tahun_masuk',
        tahun_lulus='$tahun_lulus',
        email='$email',
        no_hp='$no_hp',
        nik=" . ($nik ? "'$nik'" : "NULL") . ",
        npwp=" . ($npwp ? "'$npwp'" : "NULL") . ",
        jenis_kelamin='$jenis_kelamin',
        tanggal_lahir=" . ($tanggal_lahir ? "'$tanggal_lahir'" : "NULL") . ",
        ipk=" . ($ipk !== '' ? "'$ipk'" : "NULL") . ",
        judul_ta=" . ($judul_ta ? "'$judul_ta'" : "NULL") . ",
        foto='$foto',
        updated_at=NOW()
        WHERE id='$id'";
    $pesan = 'update';
} else {
    // Tambah
    $foto = uploadFoto($fotoBaru);

    $query = "INSERT INTO alumni 
        (npm, nama_lengkap, nama_universitas, program_studi, tahun_masuk, tahun_lulus, email, no_hp, nik, npwp, jenis_kelamin, tanggal_lahir, ipk, judul_ta, foto, created_at)
        VALUES (
            '$npm', '$nama_lengkap', '$nama_universitas', '$program_studi',
            '$tahun_masuk', '$tahun_lulus', '$email', '$no_hp',
            " . ($nik ? "'$nik'" : "NULL") . ", " . ($npwp ? "'$npwp'" : "NULL") . ",
            '$jenis_kelamin', " . ($tanggal_lahir ? "'$tanggal_lahir'" : "NULL") . ",
            " . ($ipk !== '' ? "'$ipk'" : "NULL") . ", " . ($judul_ta ? "'$judul_ta'" : "NULL") . ",
            '$foto', NOW()
        )";
    $pesan = 'tambah';
}

if (mysqli_query($conn, $query)) {
    header("Location: manajemen-alumni.php?success=$pesan");
    exit;
} else {
    echo "Gagal menyimpan data: " . mysqli_error($conn);
}
?>
