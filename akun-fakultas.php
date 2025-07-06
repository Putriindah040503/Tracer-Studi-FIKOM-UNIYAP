<?php
include 'koneksi.php';

// Daftar fakultas
$fakultasList = [
    [
        'nama_fakultas' => 'Sistem Informasi',
        'email' => 'sif@fikom.uniyap.ac.id',
        'password' => 'sif12345',
        'no_hp' => '081234567890',
        'foto' => 'default.jpg'
    ],
    [
        'nama_fakultas' => 'Informatika',
        'email' => 'if@fikom.uniyap.ac.id',
        'password' => 'if12345',
        'no_hp' => '081298765432',
        'foto' => 'default.jpg'
    ]
];

foreach ($fakultasList as $fakultas) {
    $nama = $fakultas['nama_fakultas'];
    $email = $fakultas['email'];
    $password = password_hash($fakultas['password'], PASSWORD_DEFAULT);
    $no_hp = $fakultas['no_hp'];
    $foto = $fakultas['foto'];

    // Cek apakah user dengan email ini sudah ada
    $cek = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $cek->bind_param("s", $email);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {
        echo "Akun fakultas dengan email <strong>$email</strong> sudah ada.<br>";
        continue;
    }

    // 1. Insert ke user (sebagai akun login)
    $stmt_user = $conn->prepare("INSERT INTO user (nama, email, password, role, status_akun) VALUES (?, ?, ?, 'fakultas', 'active')");
    $stmt_user->bind_param("sss", $nama, $email, $password);
    $stmt_user->execute();

    if ($stmt_user->affected_rows > 0) {
        $user_id = $stmt_user->insert_id;

        // 2. Insert ke fakultas (dengan user_id)
        $stmt_fakultas = $conn->prepare("INSERT INTO fakultas (user_id, nama_fakultas, email, password, no_hp, foto) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_fakultas->bind_param("isssss", $user_id, $nama, $email, $password, $no_hp, $foto);
        $stmt_fakultas->execute();

        if ($stmt_fakultas->affected_rows > 0) {
            echo "✅ Akun fakultas <strong>$nama</strong> berhasil dibuat.<br>";
        } else {
            echo "❌ Gagal menyimpan data ke tabel fakultas untuk <strong>$nama</strong>.<br>";
        }

        $stmt_fakultas->close();
    } else {
        echo "❌ Gagal membuat akun user untuk <strong>$nama</strong>.<br>";
    }

    $stmt_user->close();
    $cek->close();
}

$conn->close();
?>
