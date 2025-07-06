<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "tracer_study_fakultas");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

$id = $_GET['id'];
$conn->query("DELETE FROM users WHERE id = $id");

header("Location: manajemen-pengguna.php");
exit();
?>
