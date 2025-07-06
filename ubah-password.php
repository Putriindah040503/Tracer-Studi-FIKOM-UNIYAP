<?php
session_start();
include 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$adminId = $_SESSION['id'];

// Tangkap input dari form
$passLama = $_POST['password_lama'] ?? '';
$passBaru = $_POST['password_baru'] ?? '';
$konfirmasi = $_POST['konfirmasi_password'] ?? '';

// Validasi form
if (empty($passLama) || empty($passBaru) || empty($konfirmasi)) {
    header("Location: profil-admin.php?error=empty");
    exit();
}

if ($passBaru !== $konfirmasi) {
    header("Location: profil-admin.php?error=nomatch");
    exit();
}

// Ambil password lama dari database
$stmt = $conn->prepare("SELECT password FROM user WHERE id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$stmt->bind_result($hashedPassword);
$stmt->fetch();
$stmt->close();

// Verifikasi password lama
if (!password_verify($passLama, $hashedPassword)) {
    header("Location: profil-admin.php?error=wrongold");
    exit();
}

// Hash password baru
$passwordBaruHash = password_hash($passBaru, PASSWORD_DEFAULT);

// Update password di database
$stmt = $conn->prepare("UPDATE user SET password = ? WHERE id = ?");
$stmt->bind_param("si", $passwordBaruHash, $adminId);
$stmt->execute();
$stmt->close();

header("Location: profil-admin.php?update=password_success");
exit();
?>
