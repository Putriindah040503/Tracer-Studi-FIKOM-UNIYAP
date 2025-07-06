<?php
header('Content-Type: application/json');
include 'koneksi.php';

$tahun = $_GET['tahun'] ?? '';
$jurusan = $_GET['jurusan'] ?? '';

// Query grafik berdasarkan jawaban kuesioner, dihubungkan ke tabel alumni
$sql = "SELECT MONTHNAME(kj.created_at) AS bulan, COUNT(*) AS jumlah 
        FROM kuesioner_jawaban kj
        JOIN alumni a ON kj.alumni_id = a.id
        WHERE 1=1";

// Filter berdasarkan tahun lulus (atau kamu bisa ubah ke tahun_masuk jika perlu)
if ($tahun !== '') {
    $tahun = mysqli_real_escape_string($conn, $tahun);
    $sql .= " AND YEAR(a.tahun_lulus) = '$tahun'";
}

// Filter berdasarkan jurusan (program_studi)
if ($jurusan !== '') {
    $jurusan = mysqli_real_escape_string($conn, $jurusan);
    $sql .= " AND a.program_studi = '$jurusan'";
}

// Grouping data berdasarkan bulan pengisian
$sql .= " GROUP BY MONTH(kj.created_at) ORDER BY MONTH(kj.created_at)";

$result = mysqli_query($conn, $sql);

// Format hasil ke JSON
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        'bulan' => $row['bulan'],
        'jumlah' => $row['jumlah']
    ];
}

echo json_encode($data);
?>
