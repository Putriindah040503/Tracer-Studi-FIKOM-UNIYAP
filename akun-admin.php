<?php
include 'koneksi.php';

// Data akun admin
$email_admin = 'admin@fikom.uniyap.ac.id';
$password_admin = 'admin123';
$nama_lengkap = 'Admin Fakultas Ilmu Komputer';
$username = 'adminfikom';
$no_hp = '081212345678';
$foto = 'default.jpg'; // nama file default foto admin

// Hash password
$hashed_password_admin = password_hash($password_admin, PASSWORD_DEFAULT);

// Cek apakah email sudah ada di tabel user
$query_check = "SELECT * FROM user WHERE email = ?";
$stmt_check = $conn->prepare($query_check);
$stmt_check->bind_param("s", $email_admin);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo "Akun admin dengan email tersebut sudah ada.";
} else {
    // Tambahkan ke tabel `user`
    $query_user = "INSERT INTO user (nama, password, role, email, status_akun) VALUES (?, ?, 'admin', ?, 'active')";
    $stmt_user = $conn->prepare($query_user);
    $stmt_user->bind_param("sss", $nama_lengkap, $hashed_password_admin, $email_admin);
    $stmt_user->execute();

    if ($stmt_user->affected_rows > 0) {
        // Ambil id user yang baru saja dibuat
        $user_id = $stmt_user->insert_id;

        // Tambahkan ke tabel `admin`
        $query_admin = "INSERT INTO admin (user_id, nama_admin, username, email, no_hp, foto) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_admin = $conn->prepare($query_admin);
        $stmt_admin->bind_param("isssss", $user_id, $nama_lengkap, $username, $email_admin, $no_hp, $foto);
        $stmt_admin->execute();

        if ($stmt_admin->affected_rows > 0) {
            echo "Akun admin berhasil dibuat dan disimpan di tabel admin.";
        } else {
            echo "Akun user berhasil, tapi gagal menyimpan ke tabel admin.";
        }

        $stmt_admin->close();
    } else {
        echo "Gagal membuat akun user.";
    }

    $stmt_user->close();
}

$stmt_check->close();
$conn->close();
?>
