<?php
session_start();
require 'koneksi.php';

if (!isset($_POST['id'])) {
    header("Location: profil-pengguna-fakultas.php");
    exit;
}

$id = $_POST['id'];
$nama = trim($_POST['nama_pengguna']);
$email = trim($_POST['email_kontak']);

// Ambil data admin lama
$query = $conn->prepare("SELECT foto FROM fakultas WHERE user_id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$fakultas = $result->fetch_assoc();

$fotoLama = $fakultas['foto'];
$fotoBaru = $fotoLama;

// Cek apakah ada file foto baru diupload
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
    $file = $_FILES['foto'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array(strtolower($ext), $allowed)) {
        echo "<script>alert('Hanya file JPG, JPEG, PNG yang diizinkan!'); window.history.back();</script>";
        exit;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        echo "<script>alert('Ukuran file maksimal 2MB!'); window.history.back();</script>";
        exit;
    }

    $namaBaru = 'admin_' . time() . '.' . $ext;
    $target = 'images/' . $namaBaru;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        // Hapus foto lama jika bukan default
        if ($fotoLama !== 'admin-profile.png' && file_exists('images/' . $fotoLama)) {
            unlink('images/' . $fotoLama);
        }
        $fotoBaru = $namaBaru;
    }
}

// Update data ke database
$update = $conn->prepare("UPDATE fakultas SET nama_pengguna = ?, email_kontak = ?, foto = ? WHERE user_id = ?");
$update->bind_param("sssi", $nama, $email, $fotoBaru, $id);

if ($update->execute()) {
    $_SESSION['success'] = "Profil berhasil diperbarui!";
    header("Location: profil-pengguna-fakultas.php?status=updated");
    exit;
} else {
    echo "<script>alert('Gagal memperbarui profil.'); window.history.back();</script>";
    exit;
}


?>
