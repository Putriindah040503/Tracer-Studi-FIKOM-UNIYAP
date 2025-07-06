<?php
require_once 'koneksi.php'; // koneksi database

header('Content-Type: application/json');

$sql = "SELECT id, judul, perusahaan, lokasi, status FROM lowongan ORDER BY tanggal_mulai DESC";
$result = $conn->query($sql);

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['id'],
            'judul' => $row['judul'],
            'perusahaan' => $row['perusahaan'],
            'lokasi' => $row['lokasi'],
            'status' => $row['status']
        ];
    }
}

echo json_encode($data);
