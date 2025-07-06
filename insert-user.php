<?php
require 'koneksi.php'; // koneksi ke database

// Admin
$nama_admin = "admin";
$email_admin = "admin@fikom.uniyap.ac.id";
$password_admin = password_hash("admin123", PASSWORD_DEFAULT);

// Fakultas
$nama_fakultas = "Ketua Prodi";
$email_fakultas = "prodi@fikom.uniyap.ac.id";
$nip_fakultas = "198211112019031001";
$password_fakultas = password_hash("fakultas123", PASSWORD_DEFAULT);

// Insert Admin
$stmt1 = $conn->prepare("INSERT INTO admin (nama, email, password) VALUES (?, ?, ?)");
$stmt1->bind_param("sss", $nama_admin, $email_admin, $password_admin);
$stmt1->execute();

// Insert Fakultas
$stmt2 = $conn->prepare("INSERT INTO fakultas (nama, email, nip, password) VALUES (?, ?, ?, ?)");
$stmt2->bind_param("ssss", $nama_fakultas, $email_fakultas, $nip_fakultas, $password_fakultas);
$stmt2->execute();

echo "Akun admin dan fakultas berhasil ditambahkan.";
?>
