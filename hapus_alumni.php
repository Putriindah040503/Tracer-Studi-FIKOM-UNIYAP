<?php
session_start();
include 'koneksi.php';

if (!isset($_GET['id'])) {
    header("Location: manajemen-alumni.php");
    exit;
}

$id = $_GET['id'];

// Ambil data foto
$query = "SELECT foto FROM alumni WHERE id = '$id'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

// Hapus file foto jika ada
if ($data && !empty($data['foto'])) {
    $filePath = 'uploads/' . $data['foto'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Hapus data alumni
$delete = mysqli_query($conn, "DELETE FROM alumni WHERE id = '$id'");

if ($delete) {
    header("Location: manajemen-alumni.php?success=hapus");
} else {
    echo "Gagal menghapus data.";
}
exit;
