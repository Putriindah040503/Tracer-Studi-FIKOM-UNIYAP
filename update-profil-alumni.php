<?php
session_start();
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $id               = $_POST['id'];
    $npm              = $_POST['npm'];
    $nama_universitas = $_POST['nama_universitas'];
    $nama_lengkap     = $_POST['nama_lengkap'];
    $program_studi    = $_POST['program_studi'];
    $tahun_masuk      = $_POST['tahun_masuk'];
    $tahun_lulus      = $_POST['tahun_lulus'];
    $email            = $_POST['email'];
    $no_hp            = $_POST['no_hp'];
    $nik              = $_POST['nik'];
    $npwp             = $_POST['npwp'];
    $status_klaim     = $_POST['status_klaim'] ?? 0;
    $status_verifikasi= $_POST['status_verifikasi'] ?? 0;
    $status_kuesioner = $_POST['status_kuesioner'] ?? 0;
    $password         = $_POST['password'];
    $updated_at       = date('Y-m-d H:i:s');

    $foto_baru = null;

    // Handle upload foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ekstensi_diperbolehkan = ['jpg', 'jpeg', 'png'];
        $nama_file = $_FILES['foto']['name'];
        $tmp_file = $_FILES['foto']['tmp_name'];
        $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $ukuran   = $_FILES['foto']['size'];

        if (in_array($ekstensi, $ekstensi_diperbolehkan)) {
            if ($ukuran <= 2 * 1024 * 1024) { // maksimal 2MB
                $nama_baru = 'foto_' . time() . '.' . $ekstensi;
                $path_simpan = 'images/' . $nama_baru;

                if (move_uploaded_file($tmp_file, $path_simpan)) {
                    // Hapus foto lama
                    $cek = mysqli_query($conn, "SELECT foto FROM alumni WHERE id = '$id'");
                    if ($cek && $d = mysqli_fetch_assoc($cek)) {
                        if (!empty($d['foto']) && file_exists('images/' . $d['foto'])) {
                            unlink('images/' . $d['foto']);
                        }
                    }
                    $foto_baru = $nama_baru;
                } else {
                    $_SESSION['error'] = "Gagal mengunggah foto.";
                    header("Location: profil-alumni.php");
                    exit;
                }
            } else {
                $_SESSION['error'] = "Ukuran foto maksimal 2MB.";
                header("Location: profil-alumni.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Format foto tidak valid. Gunakan JPG atau PNG.";
            header("Location: profil-alumni.php");
            exit;
        }
    }

    // Bangun query
    $sql = "UPDATE alumni SET 
        npm = ?, 
        nama_universitas = ?, 
        nama_lengkap = ?, 
        program_studi = ?, 
        tahun_masuk = ?, 
        tahun_lulus = ?, 
        email = ?, 
        no_hp = ?, 
        nik = ?, 
        npwp = ?, 
        status_utama = ?, 
        status_kuesioner = ?, 
        updated_at = ?";

    $params = [
        $npm, $nama_universitas, $nama_lengkap, $program_studi,
        $tahun_masuk, $tahun_lulus, $email, $no_hp, $nik, $npwp,
        $status_utama, $status_kuesioner, $updated_at
    ];

    $types = "ssssssssssiii";

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", password = ?";
        $params[] = $hashed;
        $types .= "s";
    }

    if (!empty($foto_baru)) {
        $sql .= ", foto = ?";
        $params[] = $foto_baru;
        $types .= "s";
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;
    $types .= "s";

    // Eksekusi
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Profil berhasil diperbarui.";
        } else {
            $_SESSION['error'] = "Gagal memperbarui profil.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Terjadi kesalahan dalam query.";
    }

    mysqli_close($conn);
    header("Location: profil-alumni.php");
    exit;
} else {
    $_SESSION['error'] = "Permintaan tidak valid.";
    header("Location: profil-alumni.php");
    exit;
}
