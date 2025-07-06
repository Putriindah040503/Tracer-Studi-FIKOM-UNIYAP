<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

$filterProdi = $_GET['filter_prodi'] ?? '';
$search = trim($_GET['search'] ?? '');

$where = [];
$params = [];
$types = '';

if ($filterProdi !== '') {
    $where[] = "program_studi = ?";
    $params[] = $filterProdi;
    $types .= 's';
}
if ($search !== '') {
    $where[] = "(nama_lengkap LIKE ? OR npm LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

$whereSQL = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT * FROM alumni $whereSQL ORDER BY nama_lengkap ASC";
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=alumni_export_" . date('Ymd') . ".xls");

echo "<table border='1'>";
echo "<tr><th>No</th><th>NPM</th><th>Nama Lengkap</th><th>Program Studi</th><th>Tahun Masuk</th><th>Tahun Lulus</th><th>Email</th><th>No HP</th><th>Status Akun</th><th>Status Kuesioner</th></tr>";

$no = 1;
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".$no++."</td>";
    echo "<td>".htmlspecialchars($row['npm'])."</td>";
    echo "<td>".htmlspecialchars($row['nama_lengkap'])."</td>";
    echo "<td>".htmlspecialchars($row['program_studi'])."</td>";
    echo "<td>".htmlspecialchars($row['tahun_masuk'])."</td>";
    echo "<td>".htmlspecialchars($row['tahun_lulus'])."</td>";
    echo "<td>".htmlspecialchars($row['email'])."</td>";
    echo "<td>".htmlspecialchars($row['no_hp'])."</td>";
    echo "<td>".htmlspecialchars($row['status_akun'])."</td>";
    echo "<td>".htmlspecialchars($row['status_kuesioner'])."</td>";
    echo "</tr>";
}
echo "</table>";
exit;
?>
