<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'fakultas') {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['id'];
$password_lama = $_POST['password_lama'] ?? '';
$password_baru = $_POST['password_baru'] ?? '';
$konfirmasi = $_POST['konfirmasi_password'] ?? '';

if ($password_baru !== $konfirmasi) {
    header("Location: profil-pengguna-fakultas.php?password=gagal_konfirmasi");
    exit;
}

// Ambil password lama dari DB
$stmt = $conn->prepare("SELECT password FROM fakultas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data || !password_verify($password_lama, $data['password'])) {
    header("Location: profil-pengguna-fakultas.php?password=salah");
    exit;
}

// Update password baru
$passwordHash = password_hash($password_baru, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE fakultas SET password = ? WHERE id = ?");
$stmt->bind_param("si", $passwordHash, $id);
$stmt->execute();

header("Location: profil-pengguna-fakultas.php?password=berhasil");
exit;
?>
