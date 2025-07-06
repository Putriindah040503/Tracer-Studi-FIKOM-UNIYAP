<?php
session_start();
include 'koneksi.php';

// Set header for JSON response
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['id']) || !in_array($_SESSION['role'], ['admin', 'fakultas'])) {
    echo json_encode(['status' => 'error', 'message' => 'Akses tidak diizinkan']);
    exit;
}

try {
    // Get form data
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $pertanyaan = trim($_POST['pertanyaan'] ?? '');
    $kategori_id = (int)($_POST['kategori'] ?? 0);
    $tipe_input = trim($_POST['tipe'] ?? '');
    $wajib = (int)($_POST['wajib'] ?? 0);
    $aktif = (int)($_POST['aktif'] ?? 0);
    $urutan = (int)($_POST['urutan'] ?? 0);
    $opsi = $_POST['opsi'] ?? [];

    // Validation
    if (empty($pertanyaan)) {
        throw new Exception('Pertanyaan tidak boleh kosong');
    }
    
    if ($kategori_id <= 0) {
        throw new Exception('Kategori harus dipilih');
    }
    
    if (empty($tipe_input)) {
        throw new Exception('Tipe input harus dipilih');
    }
    
    // Validate options for radio, checkbox, select
    if (in_array($tipe_input, ['radio', 'checkbox', 'select'])) {
        $opsi = array_filter($opsi, function($item) {
            return !empty(trim($item));
        });
        
        if (count($opsi) < 2) {
            throw new Exception('Minimal 2 opsi diperlukan untuk tipe ' . $tipe_input);
        }
    }
    
    // Validate category exists
    $categoryCheck = mysqli_prepare($conn, "SELECT id FROM kuesioner_kategori WHERE id = ?");
    mysqli_stmt_bind_param($categoryCheck, "i", $kategori_id);
    mysqli_stmt_execute($categoryCheck);
    $categoryResult = mysqli_stmt_get_result($categoryCheck);
    
    if (mysqli_num_rows($categoryResult) === 0) {
        throw new Exception('Kategori tidak valid');
    }
    mysqli_stmt_close($categoryCheck);
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    if ($id > 0) {
        // UPDATE existing question
        
        // Check if question exists and get current order
        $checkStmt = mysqli_prepare($conn, "SELECT urutan FROM kuesioner_pertanyaan WHERE id = ?");
        mysqli_stmt_bind_param($checkStmt, "i", $id);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($checkResult) === 0) {
            throw new Exception('Pertanyaan tidak ditemukan');
        }
        
        $currentData = mysqli_fetch_assoc($checkResult);
        $currentUrutan = $currentData['urutan'];
        mysqli_stmt_close($checkStmt);
        
        // Update question (keep current order unless explicitly changed through reorder)
        $updateStmt = mysqli_prepare($conn, 
            "UPDATE kuesioner_pertanyaan 
             SET pertanyaan = ?, kategori_id = ?, tipe_input = ?, wajib = ?, aktif = ?, updated_at = NOW() 
             WHERE id = ?");
        
        mysqli_stmt_bind_param($updateStmt, "sssiii", $pertanyaan, $kategori_id, $tipe_input, $wajib, $aktif, $id);
        
        if (!mysqli_stmt_execute($updateStmt)) {
            throw new Exception('Gagal mengupdate pertanyaan: ' . mysqli_error($conn));
        }
        mysqli_stmt_close($updateStmt);
        
        // Delete existing options
        $deleteOpsiStmt = mysqli_prepare($conn, "DELETE FROM kuesioner_opsi_jawaban WHERE pertanyaan_id = ?");
        mysqli_stmt_bind_param($deleteOpsiStmt, "i", $id);
        mysqli_stmt_execute($deleteOpsiStmt);
        mysqli_stmt_close($deleteOpsiStmt);
        
        $message = 'Pertanyaan berhasil diperbarui';
        
    } else {
        // INSERT new question
        
        // Get next available order number
        $maxOrderQuery = "SELECT COALESCE(MAX(urutan), 0) + 1 as next_order FROM kuesioner_pertanyaan";
        $maxOrderResult = mysqli_query($conn, $maxOrderQuery);
        $nextOrder = mysqli_fetch_assoc($maxOrderResult)['next_order'];
        
        // Insert new question with auto-generated order
        $insertStmt = mysqli_prepare($conn, 
            "INSERT INTO kuesioner_pertanyaan (pertanyaan, kategori_id, tipe_input, wajib, aktif, urutan, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        
        mysqli_stmt_bind_param($insertStmt, "sssiii", $pertanyaan, $kategori_id, $tipe_input, $wajib, $aktif, $nextOrder);
        
        if (!mysqli_stmt_execute($insertStmt)) {
            throw new Exception('Gagal menyimpan pertanyaan: ' . mysqli_error($conn));
        }
        
        $id = mysqli_insert_id($conn);
        mysqli_stmt_close($insertStmt);
        
        $message = 'Pertanyaan berhasil ditambahkan';
    }
    
    // Insert options if applicable
    if (in_array($tipe_input, ['radio', 'checkbox', 'select']) && !empty($opsi)) {
        $insertOpsiStmt = mysqli_prepare($conn, 
            "INSERT INTO kuesioner_opsi_jawaban (pertanyaan_id, opsi_text, opsi_value) VALUES (?, ?, ?)");
        
        foreach ($opsi as $index => $opsiText) {
            $opsiText = trim($opsiText);
            if (!empty($opsiText)) {
                $opsiValue = $index + 1; // Simple numeric value
                mysqli_stmt_bind_param($insertOpsiStmt, "iss", $id, $opsiText, $opsiValue);
                
                if (!mysqli_stmt_execute($insertOpsiStmt)) {
                    throw new Exception('Gagal menyimpan opsi: ' . mysqli_error($conn));
                }
            }
        }
        mysqli_stmt_close($insertOpsiStmt);
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    // Log activity (optional)
    $logStmt = mysqli_prepare($conn, "INSERT INTO log_aktivitas (user_id, aktivitas, detail, waktu) VALUES (?, ?, ?, NOW())");
    if ($logStmt) {
        $activity = $id > 0 ? 'Update Pertanyaan Kuesioner' : 'Tambah Pertanyaan Kuesioner';
        $detail = 'Pertanyaan: ' . substr($pertanyaan, 0, 100) . (strlen($pertanyaan) > 100 ? '...' : '');
        mysqli_stmt_bind_param($logStmt, "iss", $_SESSION['id'], $activity, $detail);
        mysqli_stmt_execute($logStmt);
        mysqli_stmt_close($logStmt);
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'data' => [
            'id' => $id,
            'pertanyaan' => $pertanyaan,
            'kategori_id' => $kategori_id,
            'tipe_input' => $tipe_input,
            'wajib' => $wajib,
            'aktif' => $aktif,
            'opsi_count' => count($opsi)
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    
    error_log('Error saving question: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

mysqli_close($conn);
?>