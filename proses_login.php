<?php
session_start();
include 'koneksi.php';

// Tampilkan error untuk debug sementara (hapus saat produksi)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ambil input login
$role = $_POST['role'] ?? '';
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Escape input untuk mencegah SQL injection
$username = mysqli_real_escape_string($conn, $username);

function redirectToLogin($msg = '') {
    header("Location: login.php?error=" . urlencode($msg));
    exit;
}

// ADMIN LOGIN
if ($role === 'admin') {
    $query = "SELECT * FROM user WHERE email = '$username'";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = 'admin';
            $_SESSION['login_success'] = true;

            header("Location: dashboard-admin.php");
            exit;
        } else {
            redirectToLogin("Password admin salah");
        }
    } else {
        redirectToLogin("Admin tidak ditemukan");
    }

} elseif ($role === 'fakultas') {
    $query = "SELECT * FROM user WHERE email  = '$username'";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['id'] = $row['id']; // ← penting!
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = 'fakultas';
            $_SESSION['login_success'] = true;
            header("Location: dashboard-fakultas.php");
            exit;
        } else {
            redirectToLogin("Password fakultas salah");
        }
    } else {
        redirectToLogin("Fakultas tidak ditemukan");
    }

} elseif ($role === 'alumni') {
    $query = "SELECT * FROM alumni WHERE npm = '$username'";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['id'] = $row['id'];
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
            $_SESSION['role'] = 'alumni';
            $_SESSION['login_success'] = true;

            // ✅ Update last_login sebelum redirect
            $update = $conn->prepare("UPDATE alumni SET last_login = NOW() WHERE id = ?");
            $update->bind_param("i", $row['id']);
            $update->execute();

            // ⏬ Setelah update, baru redirect
            header("Location: dashboard-alumni.php");
            exit;
        } else {
            redirectToLogin("Password alumni salah");
        }
    } else {
        redirectToLogin("Alumni tidak ditemukan");
    }
}


// ROLE TIDAK VALID
 else {
    redirectToLogin("Role tidak dikenali");
}
?>
