<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manajemen-alumni.php");
    exit();
}

$id = intval($_GET['id']);

// Ambil data alumni
$query = $conn->prepare("SELECT id, nama_lengkap, status_verifikasi FROM alumni WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo "Data alumni tidak ditemukan.";
    exit();
}

$alumni = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statusBaru = $_POST['status_verifikasi'] ?? '';

    // Validasi input status verifikasi
    $validStatus = ['pending', 'verified', 'rejected'];
    if (!in_array($statusBaru, $validStatus)) {
        $error = "Status verifikasi tidak valid.";
    } else {
        // Update status verifikasi
        $update = $conn->prepare("UPDATE alumni SET status_verifikasi = ? WHERE id = ?");
        $update->bind_param("si", $statusBaru, $id);
        if ($update->execute()) {
            header("Location: manajemen-alumni.php?pesan=update_berhasil");
            exit();
        } else {
            $error = "Gagal mengupdate status verifikasi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Ubah Status Verifikasi Alumni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light p-3">

<div class="container">
    <h2>Ubah Status Verifikasi Alumni</h2>
    <a href="manajemen-alumni.php" class="btn btn-secondary mb-3">&larr; Kembali</a>

    <?php if (!empty($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Nama Alumni</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($alumni['nama_lengkap']) ?>" readonly>
        </div>

        <div class="mb-3">
            <label>Status Verifikasi</label>
            <select name="status_verifikasi" class="form-select" required>
                <option value="pending" <?= $alumni['status_verifikasi'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="verified" <?= $alumni['status_verifikasi'] === 'verified' ? 'selected' : '' ?>>Verified (Approve)</option>
                <option value="rejected" <?= $alumni['status_verifikasi'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>

</body>
</html>
