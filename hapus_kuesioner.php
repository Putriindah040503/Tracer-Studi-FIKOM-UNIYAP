<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'fakultas')) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Akses tidak diizinkan'
    ]);
    exit;
}

try {
    // Validasi input
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('ID pertanyaan tidak valid');
    }

    $id = (int)$_POST['id'];

    // Ambil data pertanyaan (sebelum dihapus)
    $checkQuery = "SELECT kp.id, kp.pertanyaan, kk.nama_kategori, kp.kategori_id
                   FROM kuesioner_pertanyaan kp 
                   JOIN kuesioner_kategori kk ON kp.kategori_id = kk.id 
                   WHERE kp.id = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        throw new Exception('Pertanyaan tidak ditemukan');
    }

    $questionData = mysqli_fetch_assoc($result);
    $kategori_id = $questionData['kategori_id'];

    mysqli_stmt_close($stmt);

    // Mulai transaksi
    mysqli_autocommit($conn, false);

    // Hapus jawaban terkait
    if (!mysqli_query($conn, "DELETE FROM kuesioner_jawaban WHERE pertanyaan_id = $id")) {
        throw new Exception('Gagal menghapus jawaban: ' . mysqli_error($conn));
    }

    // Hapus opsi terkait
    if (!mysqli_query($conn, "DELETE FROM kuesioner_opsi_jawaban WHERE pertanyaan_id = $id")) {
        throw new Exception('Gagal menghapus opsi jawaban: ' . mysqli_error($conn));
    }

    // Hapus pertanyaan
    if (!mysqli_query($conn, "DELETE FROM kuesioner_pertanyaan WHERE id = $id")) {
        throw new Exception('Gagal menghapus pertanyaan: ' . mysqli_error($conn));
    }

    if (mysqli_affected_rows($conn) == 0) {
        throw new Exception('Pertanyaan tidak dapat dihapus');
    }

    // Reset urutan untuk kategori yang sama
    if (!mysqli_query($conn, "SET @rownum = 0")) {
        throw new Exception('Gagal set rownum: ' . mysqli_error($conn));
    }

    if (!mysqli_query($conn, "
        CREATE TEMPORARY TABLE tmp_urut AS
        SELECT id, (@rownum := @rownum + 1) AS new_urutan
        FROM kuesioner_pertanyaan
        WHERE kategori_id = $kategori_id
        ORDER BY urutan, id
    ")) {
        throw new Exception('Gagal membuat temp table: ' . mysqli_error($conn));
    }

    if (!mysqli_query($conn, "
        UPDATE kuesioner_pertanyaan q
        JOIN tmp_urut t ON q.id = t.id
        SET q.urutan = t.new_urutan
    ")) {
        throw new Exception('Gagal update urutan: ' . mysqli_error($conn));
    }

    mysqli_query($conn, "DROP TEMPORARY TABLE tmp_urut");

    // Commit transaksi
    mysqli_commit($conn);
    mysqli_autocommit($conn, true);

    echo json_encode([
        'status' => 'success',
        'message' => 'Pertanyaan "' . htmlspecialchars($questionData['pertanyaan']) . '" berhasil dihapus',
        'data' => [
            'deleted_id' => $id,
            'deleted_question' => $questionData['pertanyaan'],
            'category' => $questionData['nama_kategori']
        ]
    ]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    mysqli_autocommit($conn, true);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);

    error_log("Hapus Kuesioner Error: " . $e->getMessage());
}
?>
