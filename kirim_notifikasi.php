<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once 'koneksi.php';

// Check admin authentication
if (!isset($_SESSION['id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'fakultas')) {
    header("Location: login.php");
    exit;
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'] ?? '';
    $pesan = $_POST['pesan'] ?? '';
    $jenis = $_POST['jenis'] ?? 'pengumuman';
    $penerima_role = $_POST['penerima_role'] ?? 'alumni';
    $link = $_POST['link'] ?? '';
    $prioritas = $_POST['prioritas'] ?? 'normal';
    $dijadwalkan_pada = $_POST['dijadwalkan_pada'] ?? '';

    // (Validation logic goes here)

    // Insert into database logic here

    // Send email notification
    $to = "recipient@example.com"; // Ganti dengan alamat email penerima
    $subject = $judul;
    $message = $pesan . "\n\nLink: " . $link;
    $headers = "From: admin@example.com"; // Ganti dengan alamat email pengirim

    if (mail($to, $subject, $message, $headers)) {
        $success_message = "Notifikasi berhasil dikirim!";
    } else {
        $error_message = "Gagal mengirim notifikasi.";
    }
}

// Get user statistics for info
$stats_sql = "SELECT 
    COUNT(CASE WHEN role = 'admin' THEN 1 END) as admin_count,
    COUNT(CASE WHEN role = 'fakultas' THEN 1 END) as fakultas_count,
    COUNT(CASE WHEN role = 'alumni' THEN 1 END) as alumni_count,
    COUNT(*) as total_user
FROM user WHERE status_akun = 'aktif'";
$user_stats = $conn->query($stats_sql)->fetch_assoc();

// Set default values if form hasn't been submitted
$judul = $_POST['judul'] ?? '';
$pesan = $_POST['pesan'] ?? '';
$jenis = $_POST['jenis'] ?? 'pengumuman';
$penerima_role = $_POST['penerima_role'] ?? 'alumni';
$link = $_POST['link'] ?? '';
$prioritas = $_POST['prioritas'] ?? 'normal';
$dijadwalkan_pada = $_POST['dijadwalkan_pada'] ?? '';

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Notifikasi - Admin Panel</title>
    <link href="styless.css?v=1.0" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <nav class="sidebar">
        <?php include 'sidebar.php'; ?>
    </nav>

    <main>
        <div class="page-header">
            <h2>Kirim Notifikasi</h2>
            <p>Buat dan kirim notifikasi kepada pengguna sistem</p>
        </div>

        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?= $success_message ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" id="notificationForm">
                <div class="mb-3">
                    <label for="judul" class="form-label">Judul Notifikasi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="judul" name="judul" value="<?= htmlspecialchars($judul) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="pesan" class="form-label">Pesan Notifikasi <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="pesan" name="pesan" rows="6" required><?= htmlspecialchars($pesan) ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="jenis" class="form-label">Jenis Notifikasi <span class="text-danger">*</span></label>
                    <select class="form-select" id="jenis" name="jenis" required>
                        <option value="pengumuman" <?= $jenis == 'pengumuman' ? 'selected' : '' ?>>Pengumuman</option>
                        <option value="kuesioner_baru" <?= $jenis == 'kuesioner_baru' ? 'selected' : '' ?>>Kuesioner Baru</option>
                        <option value="pengingat" <?= $jenis == 'pengingat' ? 'selected' : '' ?>>Pengingat</option>
                        <option value="sistem" <?= $jenis == 'sistem' ? 'selected' : '' ?>>Sistem</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="link" class="form-label">Link Terkait (Opsional)</label>
                    <input type="url" class="form-control" id="link" name="link" value="<?= htmlspecialchars($link) ?>" placeholder="https://example.com">
                </div>

                <div class="mb-3">
                    <label for="penerima_role" class="form-label">Target Penerima <span class="text-danger">*</span></label>
                    <select class="form-select" id="penerima_role" name="penerima_role" required>
                        <option value="alumni" <?= $penerima_role == 'alumni' ? 'selected' : '' ?>>Alumni</option>
                        <option value="fakultas" <?= $penerima_role == 'fakultas' ? 'selected' : '' ?>>Fakultas</option>
                        <option value="admin" <?= $penerima_role == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="semua" <?= $penerima_role == 'semua' ? 'selected' : '' ?>>Semua Pengguna</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="prioritas" class="form-label">Prioritas</label>
                    <select class="form-select" id="prioritas" name="prioritas">
                        <option value="rendah" <?= $prioritas == 'rendah' ? 'selected' : '' ?>>Rendah</option>
                        <option value="normal" <?= $prioritas == 'normal' ? 'selected' : '' ?>>Normal</option>
                        <option value="tinggi" <?= $prioritas == 'tinggi' ? 'selected' : '' ?>>Tinggi</option>
                        <option value="urgent" <?= $prioritas == 'urgent' ? 'selected' : '' ?>>Urgent</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="dijadwalkan_pada" class="form-label">Jadwalkan Pengiriman</label>
                    <input type="datetime-local" class="form-control" id="dijadwalkan_pada" name="dijadwalkan_pada" value="<?= $dijadwalkan_pada ?>">
                </div>

                <button type="submit" class="btn btn-primary">Kirim Notifikasi</button>
            </form>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
