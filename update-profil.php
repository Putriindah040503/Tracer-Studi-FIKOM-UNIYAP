<?php
session_start();
include 'koneksi.php';

$id = $_POST['id'];
$nama = $_POST['nama'];
$email = $_POST['email'];
$fotoBaru = $_FILES['foto']['name'] ?? '';

// Ambil data admin lama
$query = $conn->prepare("SELECT foto FROM admin WHERE user_id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$admin = $result->fetch_assoc();
$fotoLama = $admin['foto'] ?? '';

// Proses upload foto jika ada file baru
if (!empty($fotoBaru)) {
    $ext = pathinfo($fotoBaru, PATHINFO_EXTENSION);
    $namaBaru = uniqid('admin_') . '.' . $ext;
    $target = 'images/' . $namaBaru;

    // Pindahkan file ke folder
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
        // Hapus foto lama jika ada
        if (!empty($fotoLama) && file_exists('images/' . $fotoLama)) {
            unlink('images/' . $fotoLama);
        }
        $fotoFinal = $namaBaru;
    } else {
        $fotoFinal = $fotoLama; // gagal upload, pakai yang lama
    }
} else {
    $fotoFinal = $fotoLama; // tidak ada file baru
}

// Update data
$query = $conn->prepare("UPDATE admin SET nama_admin = ?, email = ?, foto = ? WHERE user_id = ?");
$query->bind_param("sssi", $nama, $email, $fotoFinal, $id);
$query->execute();

header("Location: profil-admin.php?update=success");
exit;
