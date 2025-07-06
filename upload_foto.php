<?php
session_start();
include 'koneksi.php';

// Cek apakah admin login
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['id'];

// Validasi file upload
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $foto = $_FILES['foto'];
    $namaFile = basename($foto['name']);
    $ext = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($ext, $allowed)) {
        $namaBaru = 'admin_' . $admin_id . '_' . time() . '.' . $ext;
        $tujuan = 'uploads/' . $namaBaru;

        if (move_uploaded_file($foto['tmp_name'], $tujuan)) {
            // Simpan nama file ke DB
            $query = "UPDATE admin SET foto = '$namaBaru' WHERE id = $admin_id";
            mysqli_query($conn, $query);
            header("Location: profil-admin.php?upload=success");
            exit;
        } else {
            echo "Gagal menyimpan file.";
        }
    } else {
        echo "Format file tidak diizinkan. Hanya JPG, PNG, GIF.";
    }
} else {
    echo "Tidak ada file yang diupload atau terjadi kesalahan.";
}
?>
