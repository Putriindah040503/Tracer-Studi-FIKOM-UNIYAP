<?php
require 'koneksi.php';

try {
    $id = $_POST['id'];
    $status = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE lowongan SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
