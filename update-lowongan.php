<?php
require 'koneksi.php';

try {
    $id = $_POST['id'];
    $judul = $_POST['judul'];
    $perusahaan = $_POST['perusahaan'];
    $lokasi = $_POST['lokasi'];
    $link = $_POST['link_pendaftaran'] ?? '';
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_berakhir = $_POST['tanggal_berakhir'];
    $deskripsi = $_POST['deskripsi'];
    $syarat = $_POST['syarat'];
    $status = $_POST['status'];

    // Ambil data lama (untuk hapus gambar jika perlu)
    $stmt = $pdo->prepare("SELECT foto FROM lowongan WHERE id = ?");
    $stmt->execute([$id]);
    $lama = $stmt->fetch();

    $foto = $lama['foto'];
    if (!empty($_FILES['foto']['name'])) {
        if ($foto && file_exists("uploads/lowongan/$foto")) {
            unlink("uploads/lowongan/$foto");
        }

        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $namaBaru = uniqid() . '.' . $ext;
        $tujuan = 'uploads/lowongan/' . $namaBaru;
        move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan);
        $foto = $namaBaru;
    }

    $stmt = $pdo->prepare("UPDATE lowongan SET judul=?, perusahaan=?, lokasi=?, link_pendaftaran=?, tanggal_mulai=?, tanggal_berakhir=?, deskripsi=?, syarat=?, status=?, foto=? WHERE id=?");
    $stmt->execute([$judul, $perusahaan, $lokasi, $link, $tanggal_mulai, $tanggal_berakhir, $deskripsi, $syarat, $status, $foto, $id]);

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
