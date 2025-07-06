<?php
include 'koneksi.php';

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=daftar_alumni.csv");

$output = fopen("php://output", "w");

// Header kolom CSV
fputcsv($output, [
    'NPM', 'Nama Lengkap', 'Universitas', 'Fakultas', 'Program Studi',
    'Jenis Kelamin', 'Tanggal Lahir', 'Tahun Masuk', 'Tahun Lulus',
    'Email', 'No HP', 'NIK', 'NPWP', 'IPK', 'Judul Tugas Akhir',
    'Status Utama', 'Status Kuesioner'
]);

// Ambil data dari database
$query = mysqli_query($conn, "SELECT * FROM alumni ORDER BY nama_lengkap ASC");

while ($row = mysqli_fetch_assoc($query)) {
    fputcsv($output, [
        $row['npm'],
        $row['nama_lengkap'],
        $row['nama_universitas'],
        $row['fakultas'],
        $row['program_studi'],
        $row['jenis_kelamin'],
        $row['tanggal_lahir'],
        $row['tahun_masuk'],
        $row['tahun_lulus'],
        $row['email'],
        $row['no_hp'],
        $row['nik'],
        $row['npwp'],
        $row['ipk'],
        $row['judul_ta'],
        $row['status_utama'],
        $row['status_kuesioner']
    ]);
}

fclose($output);
exit;
?>
