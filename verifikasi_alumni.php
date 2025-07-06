<?php
include 'koneksi.php';
session_start();

$id = $_POST['id'] ?? null;
if (!$id) {
    header("Location: manajemen-alumni.php?error=invalid_id");
    exit;
}

// Ambil data alumni berdasarkan ID
$cek = mysqli_query($conn, "SELECT status_utama FROM alumni WHERE id = '$id'");
$data = mysqli_fetch_assoc($cek);

// Jika data tidak ditemukan
if (!$data) {
    header("Location: manajemen-alumni.php?error=not_found");
    exit;
}

// Cek status dan lakukan verifikasi jika masih pending
if ($data['status_utama'] === 'pending_verifikasi') {
    $update = mysqli_query($conn, "UPDATE alumni SET status_utama = 'active', updated_at = NOW() WHERE id = '$id'");
    $_SESSION['notif'] = $update ? "Verifikasi berhasil dilakukan." : "Gagal melakukan verifikasi.";
} else {
    $_SESSION['notif'] = "Alumni ini sudah diverifikasi sebelumnya atau tidak dalam status 'pending_verifikasi'.";
}

header("Location: manajemen-alumni.php");
exit;
