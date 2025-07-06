<?php
session_start();

// Cek apakah user adalah alumni
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'alumni') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Terima Kasih</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container text-center py-5">
        <div class="alert alert-success shadow-sm p-5">
            <h3 class="mb-4">🎉 Terima kasih!</h3>
            <p class="lead">Jawaban kuesioner Anda telah berhasil disimpan.</p>
            <p>Kami sangat menghargai partisipasi Anda dalam Tracer Study ini.</p>
            <a href="dashboard-alumni.php" class="btn btn-primary mt-3">Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>
