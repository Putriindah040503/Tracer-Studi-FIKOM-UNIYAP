<?php
session_start();
include 'koneksi.php';

header('Content-Type: application/json');

// Check alumni authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'alumni') {
    header("Location: login.php");
    exit;
}
$alumni_id = $_SESSION['id'];

if (!isset($_POST['jawaban']) || !is_array($_POST['jawaban'])) {
    echo json_encode(["status" => "error", "message" => "Data jawaban tidak dikirim."]);
    exit;
}

// Loop jawaban
foreach ($_POST['jawaban'] as $pertanyaan_id => $jawaban) {
    // Simpan ke DB
}

try {
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    // Hapus jawaban sebelumnya jika ada
    $deleteQuery = "DELETE FROM kuesioner_jawaban WHERE alumni_id = ?";
    $deleteStmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($deleteStmt, 'i', $alumni_id);
    mysqli_stmt_execute($deleteStmt);
    
    // Insert jawaban baru
    $insertQuery = "INSERT INTO kuesioner_jawaban (alumni_id, pertanyaan_id, jawaban, opsi_id, waktu_pengisian) VALUES (?, ?, ?, ?, NOW())";
    $insertStmt = mysqli_prepare($conn, $insertQuery);
    
    foreach ($_POST['jawaban'] as $pertanyaan_id => $jawaban) {
        $jawaban = '';
        $opsi_id = null;
        
        if (is_array($jawaban)) {
            // Untuk checkbox - gabungkan semua opsi yang dipilih
            $jawaban = implode(',', $jawaban);
            $opsi_id = null; // Untuk checkbox, simpan di jawaban_text
        } else {
            // Cek apakah jawaban berupa opsi_id (untuk radio, select) atau text
            if (is_numeric($jawaban)) {
                // Cek apakah ini opsi_id
                $checkOpsi = mysqli_query($conn, "SELECT id FROM kuesioner_opsi_jawaban WHERE id = $jawaban");
                if (mysqli_num_rows($checkOpsi) > 0) {
                    $opsi_id = $jawaban;
                    $jawaban_text = '';
                } else {
                    // Ini adalah rating atau text biasa
                    $jawaban = $jawaban;
                    $opsi_id = null;
                }
            } else {
                // Text jawaban
                $jawaban = $jawaban;
                $opsi_id = null;
            }
        }
        
        mysqli_stmt_bind_param($insertStmt, 'iisi', $alumni_id, $pertanyaan_id, $jawaban, $opsi_id);
        
        if (!mysqli_stmt_execute($insertStmt)) {
            throw new Exception('Gagal menyimpan jawaban pertanyaan ID: ' . $pertanyaan_id);
        }
    }
    
    // Update status kuesioner alumni
    $updateAlumni = "UPDATE alumni SET status_kuesioner = 'sudah_isi', waktu_pengisian = NOW() WHERE id = ?";
    $updateStmt = mysqli_prepare($conn, $updateAlumni);
    mysqli_stmt_bind_param($updateStmt, 'i', $alumni_id);
    mysqli_stmt_execute($updateStmt);
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Jawaban kuesioner berhasil disimpan!'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    mysqli_rollback($conn);
    
    echo json_encode([
        'status' => 'error', 
        'message' => 'Gagal menyimpan jawaban: ' . $e->getMessage()
    ]);
}
?>