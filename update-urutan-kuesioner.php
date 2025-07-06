<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || !in_array($_SESSION['role'], ['admin', 'fakultas'])) {
    echo json_encode(['status' => 'error', 'message' => 'Akses tidak diizinkan']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['orders']) || !is_array($input['orders'])) {
        echo json_encode(['status' => 'error', 'message' => 'Data urutan tidak valid']);
        exit;
    }

    $orders = $input['orders'];

    // Validasi setiap entri
    foreach ($orders as $order) {
        if (
            !isset($order['id'], $order['urutan'], $order['kategori_id']) ||
            !is_numeric($order['id']) ||
            !is_numeric($order['urutan']) ||
            !is_numeric($order['kategori_id'])
        ) {
            echo json_encode(['status' => 'error', 'message' => 'Format data urutan tidak valid']);
            exit;
        }
    }

    // Kelompokkan per kategori & urutkan
    usort($orders, function ($a, $b) {
        if ($a['kategori_id'] == 1 && $b['kategori_id'] != 1) return -1;
        if ($b['kategori_id'] == 1 && $a['kategori_id'] != 1) return 1;
        return $a['kategori_id'] == $b['kategori_id']
            ? $a['urutan'] - $b['urutan']
            : $a['kategori_id'] - $b['kategori_id'];
    });

    // Hitung ulang urutan per kategori
    $kategoriTracker = [];
    foreach ($orders as &$order) {
        $kat = $order['kategori_id'];
        $kategoriTracker[$kat] = ($kategoriTracker[$kat] ?? 0) + 1;
        $order['urutan'] = $kategoriTracker[$kat];
    }
    unset($order); // Break reference

    // Mulai transaksi
    mysqli_begin_transaction($conn);

    $updateStmt = mysqli_prepare($conn, "UPDATE kuesioner_pertanyaan SET urutan = ? WHERE id = ?");
    if (!$updateStmt) {
        throw new Exception('Gagal menyiapkan query: ' . mysqli_error($conn));
    }

    foreach ($orders as $order) {
        mysqli_stmt_bind_param($updateStmt, "ii", $order['urutan'], $order['id']);
        if (!mysqli_stmt_execute($updateStmt)) {
            throw new Exception('Gagal update ID ' . $order['id']);
        }
    }

    mysqli_stmt_close($updateStmt);
    mysqli_commit($conn);

    // Catat log
    $logStmt = mysqli_prepare($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail, waktu) VALUES (?, 'Update Urutan Kuesioner', ?, NOW())");
    if ($logStmt) {
        $detail = 'Mengubah urutan ' . count($orders) . ' pertanyaan berdasarkan kategori';
        mysqli_stmt_bind_param($logStmt, "is", $_SESSION['id'], $detail);
        mysqli_stmt_execute($logStmt);
        mysqli_stmt_close($logStmt);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Urutan pertanyaan berhasil diperbarui berdasarkan kategori',
        'updated_count' => count($orders)
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

mysqli_close($conn);
?>
