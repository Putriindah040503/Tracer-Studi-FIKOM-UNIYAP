<?php
session_start();

// Cek apakah user sudah login dan memiliki role admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // Redirect ke halaman login atau tampilkan pesan
    header("Location: login.php");
    exit;
}
?>
