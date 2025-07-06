<?php
session_start();
include 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'fakultas')) {
    header("Location: login.php");
    exit;
}

// Fungsi untuk upload foto
function uploadFoto($file) {
    $target_dir = "uploads/alumni/";
    
    // Buat direktori jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Validasi file
    if ($file["size"] > 2097152) { // 2MB
        return array('success' => false, 'message' => 'Ukuran file terlalu besar (max 2MB)');
    }
    
    $allowed_types = array("jpg", "jpeg", "png", "gif");
    if (!in_array($file_extension, $allowed_types)) {
        return array('success' => false, 'message' => 'Format file tidak diizinkan');
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return array('success' => true, 'filename' => $new_filename);
    } else {
        return array('success' => false, 'message' => 'Gagal upload file');
    }
}

// Fungsi untuk menghapus foto lama
function deleteFoto($filename) {
    if ($filename && file_exists("uploads/alumni/" . $filename)) {
        unlink("uploads/alumni/" . $filename);
    }
}

// Proses berdasarkan action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'tambah':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validasi input
                $npm = mysqli_real_escape_string($conn, trim($_POST['npm']));
                $nama_lengkap = mysqli_real_escape_string($conn, trim($_POST['nama_lengkap']));
                $nama_universitas = mysqli_real_escape_string($conn, trim($_POST['nama_universitas']));
                $program_studi = mysqli_real_escape_string($conn, trim($_POST['program_studi']));
                $tahun_masuk = (int)$_POST['tahun_masuk'];
                $tahun_lulus = (int)$_POST['tahun_lulus'];
                $email = mysqli_real_escape_string($conn, trim($_POST['email']));
                $no_hp = mysqli_real_escape_string($conn, trim($_POST['no_hp']));
                $nik = mysqli_real_escape_string($conn, trim($_POST['nik'] ?? ''));
                $npwp = mysqli_real_escape_string($conn, trim($_POST['npwp'] ?? ''));
                $jenis_kelamin = mysqli_real_escape_string($conn, trim($_POST['jenis_kelamin'] ?? ''));
                $tanggal_lahir = $_POST['tanggal_lahir'] ?? null;
                $ipk = $_POST['ipk'] ? (float)$_POST['ipk'] : null;
                $judul_ta = mysqli_real_escape_string($conn, trim($_POST['judul_ta'] ?? ''));
                
                // Validasi tahun
                if ($tahun_lulus <= $tahun_masuk) {
                    throw new Exception("Tahun lulus harus lebih besar dari tahun masuk");
                }
                
                // Cek NPM duplikat
                $check_npm = mysqli_query($conn, "SELECT id FROM alumni WHERE npm = '$npm'");
                if (mysqli_num_rows($check_npm) > 0) {
                    throw new Exception("NPM sudah terdaftar");
                }
                
                // Cek email duplikat
                $check_email = mysqli_query($conn, "SELECT id FROM alumni WHERE email = '$email'");
                if (mysqli_num_rows($check_email) > 0) {
                    throw new Exception("Email sudah terdaftar");
                }
                
                // Handle upload foto
                $foto_filename = null;
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = uploadFoto($_FILES['foto']);
                    if (!$upload_result['success']) {
                        throw new Exception($upload_result['message']);
                    }
                    $foto_filename = $upload_result['filename'];
                }
                
                // Insert data
                $tanggal_lahir_sql = $tanggal_lahir ? "'$tanggal_lahir'" : "NULL";
                $ipk_sql = $ipk ? $ipk : "NULL";
                $foto_sql = $foto_filename ? "'$foto_filename'" : "NULL";
                
                $query = "INSERT INTO alumni (
                    npm, nama_lengkap, nama_universitas, program_studi, 
                    tahun_masuk, tahun_lulus, email, no_hp, nik, npwp,
                    jenis_kelamin, tanggal_lahir, ipk, judul_ta, foto,
                    status_utama, status_klaim, status_verifikasi, status_kuesioner,
                    created_at
                ) VALUES (
                    '$npm', '$nama_lengkap', '$nama_universitas', '$program_studi',
                    $tahun_masuk, $tahun_lulus, '$email', '$no_hp', '$nik', '$npwp',
                    '$jenis_kelamin', $tanggal_lahir_sql, $ipk_sql, '$judul_ta', $foto_sql,
                    'inactive', 0, 0, 'belum_isi',
                    NOW()
                )";
                
                if (mysqli_query($conn, $query)) {
                    $_SESSION['notif'] = "Data alumni berhasil ditambahkan";
                    header("Location: manajemen_alumni.php?status=success");
                } else {
                    throw new Exception("Database error: " . mysqli_error($conn));
                }
                
            } catch (Exception $e) {
                $_SESSION['notif'] = "Error: " . $e->getMessage();
                header("Location: manajemen_alumni.php?status=error&message=" . urlencode($e->getMessage()));
            }
        }
        break;
        
    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = (int)$_POST['id'];
                
                // Validasi input
                $npm = mysqli_real_escape_string($conn, trim($_POST['npm']));
                $nama_lengkap = mysqli_real_escape_string($conn, trim($_POST['nama_lengkap']));
                $nama_universitas = mysqli_real_escape_string($conn, trim($_POST['nama_universitas']));
                $program_studi = mysqli_real_escape_string($conn, trim($_POST['program_studi']));
                $tahun_masuk = (int)$_POST['tahun_masuk'];
                $tahun_lulus = (int)$_POST['tahun_lulus'];
                $email = mysqli_real_escape_string($conn, trim($_POST['email']));
                $no_hp = mysqli_real_escape_string($conn, trim($_POST['no_hp']));
                $nik = mysqli_real_escape_string($conn, trim($_POST['nik'] ?? ''));
                $npwp = mysqli_real_escape_string($conn, trim($_POST['npwp'] ?? ''));
                $jenis_kelamin = mysqli_real_escape_string($conn, trim($_POST['jenis_kelamin'] ?? ''));
                $tanggal_lahir = $_POST['tanggal_lahir'] ?? null;
                $ipk = $_POST['ipk'] ? (float)$_POST['ipk'] : null;
                $judul_ta = mysqli_real_escape_string($conn, trim($_POST['judul_ta'] ?? ''));
                
                // Validasi tahun
                if ($tahun_lulus <= $tahun_masuk) {
                    throw new Exception("Tahun lulus harus lebih besar dari tahun masuk");
                }
                
                // Cek NPM duplikat (kecuali record yang sedang diedit)
                $check_npm = mysqli_query($conn, "SELECT id FROM alumni WHERE npm = '$npm' AND id != $id");
                if (mysqli_num_rows($check_npm) > 0) {
                    throw new Exception("NPM sudah terdaftar");
                }
                
                // Cek email duplikat (kecuali record yang sedang diedit)
                $check_email = mysqli_query($conn, "SELECT id FROM alumni WHERE email = '$email' AND id != $id");
                if (mysqli_num_rows($check_email) > 0) {
                    throw new Exception("Email sudah terdaftar");
                }
                
                // Ambil data lama untuk foto
                $old_data = mysqli_query($conn, "SELECT foto FROM alumni WHERE id = $id");
                $old_row = mysqli_fetch_assoc($old_data);
                $old_foto = $old_row['foto'];
                
                // Handle upload foto baru
                $foto_filename = $old_foto; // default tetap foto lama
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = uploadFoto($_FILES['foto']);
                    if (!$upload_result['success']) {
                        throw new Exception($upload_result['message']);
                    }
                    
                    // Hapus foto lama jika ada
                    deleteFoto($old_foto);
                    $foto_filename = $upload_result['filename'];
                }
                
                // Update data
                $tanggal_lahir_sql = $tanggal_lahir ? "'$tanggal_lahir'" : "NULL";
                $ipk_sql = $ipk ? $ipk : "NULL";
                $foto_sql = $foto_filename ? "'$foto_filename'" : "NULL";
                
                $query = "UPDATE alumni SET 
                    npm = '$npm',
                    nama_lengkap = '$nama_lengkap',
                    nama_universitas = '$nama_universitas',
                    program_studi = '$program_studi',
                    tahun_masuk = $tahun_masuk,
                    tahun_lulus = $tahun_lulus,
                    email = '$email',
                    no_hp = '$no_hp',
                    nik = '$nik',
                    npwp = '$npwp',
                    jenis_kelamin = '$jenis_kelamin',
                    tanggal_lahir = $tanggal_lahir_sql,
                    ipk = $ipk_sql,
                    judul_ta = '$judul_ta',
                    foto = $foto_sql,
                    updated_at = NOW()
                WHERE id = $id";
                
                if (mysqli_query($conn, $query)) {
                    $_SESSION['notif'] = "Data alumni berhasil diperbarui";
                    header("Location: manajemen_alumni.php?status=updated");
                } else {
                    throw new Exception("Database error: " . mysqli_error($conn));
                }
                
            } catch (Exception $e) {
                $_SESSION['notif'] = "Error: " . $e->getMessage();
                header("Location: manajemen_alumni.php?status=error&message=" . urlencode($e->getMessage()));
            }
        }
        break;
        
    case 'delete':
        if (isset($_GET['id'])) {
            try {
                $id = (int)$_GET['id'];
                
                // Ambil data foto sebelum dihapus
                $foto_query = mysqli_query($conn, "SELECT foto FROM alumni WHERE id = $id");
                $foto_data = mysqli_fetch_assoc($foto_query);
                
                // Hapus data dari database
                $query = "DELETE FROM alumni WHERE id = $id";
                if (mysqli_query($conn, $query)) {
                    // Hapus foto jika ada
                    if ($foto_data && $foto_data['foto']) {
                        deleteFoto($foto_data['foto']);
                    }
                    
                    $_SESSION['notif'] = "Data alumni berhasil dihapus";
                    header("Location: manajemen_alumni.php?status=deleted");
                } else {
                    throw new Exception("Gagal menghapus data: " . mysqli_error($conn));
                }
                
            } catch (Exception $e) {
                $_SESSION['notif'] = "Error: " . $e->getMessage();
                header("Location: manajemen_alumni.php?status=error&message=" . urlencode($e->getMessage()));
            }
        }
        break;
        
    case 'update_status':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = (int)$_POST['id'];
                $status = mysqli_real_escape_string($conn, $_POST['status']);
                
                // Validasi status
                $valid_status = ['pending_verifikasi', 'active', 'terklaim', 'inactive', 'rejected'];
                if (!in_array($status, $valid_status)) {
                    throw new Exception("Status tidak valid");
                }
                
                $query = "UPDATE alumni SET status_utama = '$status', updated_at = NOW() WHERE id = $id";
                if (mysqli_query($conn, $query)) {
                    $_SESSION['notif'] = "Status alumni berhasil diperbarui";
                    header("Location: manajemen_alumni.php?status=updated");
                } else {
                    throw new Exception("Gagal memperbarui status: " . mysqli_error($conn));
                }
                
            } catch (Exception $e) {
                $_SESSION['notif'] = "Error: " . $e->getMessage();
                header("Location: manajemen_alumni.php?status=error&message=" . urlencode($e->getMessage()));
            }
        }
        break;
        
    case 'verifikasi':
        if (isset($_GET['id'])) {
            try {
                $id = (int)$_GET['id'];
                
                $query = "UPDATE alumni SET 
                    status_verifikasi = 1, 
                    status_utama = 'active',
                    verified_at = NOW(),
                    verified_by = " . $_SESSION['id'] . "
                WHERE id = $id";
                
                if (mysqli_query($conn, $query)) {
                    $_SESSION['notif'] = "Alumni berhasil diverifikasi";
                    header("Location: manajemen_alumni.php?status=verified");
                } else {
                    throw new Exception("Gagal verifikasi: " . mysqli_error($conn));
                }
                
            } catch (Exception $e) {
                $_SESSION['notif'] = "Error: " . $e->getMessage();
                header("Location: manajemen_alumni.php?status=error&message=" . urlencode($e->getMessage()));
            }
        }
        break;
        
    case 'reject':
        if (isset($_GET['id'])) {
            try {
                $id = (int)$_GET['id'];
                
                $query = "UPDATE alumni SET 
                    status_utama = 'rejected',
                    status_verifikasi = 0,
                    rejected_at = NOW(),
                    rejected_by = " . $_SESSION['id'] . "
                WHERE id = $id";
                
                if (mysqli_query($conn, $query)) {
                    $_SESSION['notif'] = "Alumni berhasil ditolak";
                    header("Location: manajemen_alumni.php?status=rejected");
                } else {
                    throw new Exception("Gagal menolak: " . mysqli_error($conn));
                }
                
            } catch (Exception $e) {
                $_SESSION['notif'] = "Error: " . $e->getMessage();
                header("Location: manajemen_alumni.php?status=error&message=" . urlencode($e->getMessage()));
            }
        }
        break;
        
    default:
        header("Location: manajemen_alumni.php");
        break;
}

mysqli_close($conn);
exit;
?>