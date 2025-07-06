<?php
// Koneksi database (jika belum dibuat di config.php)
require_once 'koneksi.php';

function getTotalAlumni() {
    global $conn;
    return (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM alumni"))['total'];
}

function getAlumniBekerja() {
    global $conn;
    $query = "
        SELECT COUNT(DISTINCT alumni_id) as total
        FROM kuesioner_jawaban
        WHERE pertanyaan_id = 1 AND jawaban LIKE '%Bekerja%'
    ";
    return (int)mysqli_fetch_assoc(mysqli_query($conn, $query))['total'];
}

function getAlumniWirausaha() {
    global $conn;
    $query = "
        SELECT COUNT(DISTINCT alumni_id) as total
        FROM kuesioner_jawaban
        WHERE pertanyaan_id = 1 AND jawaban LIKE '%Wiraswasta%'
    ";
    return (int)mysqli_fetch_assoc(mysqli_query($conn, $query))['total'];
}

function getAlumniStudi() {
    global $conn;
    $query = "
        SELECT COUNT(DISTINCT alumni_id) as total
        FROM kuesioner_jawaban
        WHERE pertanyaan_id = 1 AND jawaban LIKE '%Melanjutkan pendidikan%'
    ";
    return (int)mysqli_fetch_assoc(mysqli_query($conn, $query))['total'];
}

function getAlumniMenganggur() {
    global $conn;
    $query = "
        SELECT COUNT(DISTINCT alumni_id) as total
        FROM kuesioner_jawaban
        WHERE pertanyaan_id = 1 AND (
            jawaban LIKE '%Belum memungkinkan%' OR 
            jawaban LIKE '%Sedang mencari kerja%'
        )
    ";
    return (int)mysqli_fetch_assoc(mysqli_query($conn, $query))['total'];
}

function getResponseRate() {
    global $conn;
    $total = getTotalAlumni();
    $isi = (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT alumni_id) as total FROM kuesioner_jawaban"))['total'];
    return $total > 0 ? round(($isi / $total) * 100, 1) : 0;
}

// Data chart status pekerjaan alumni
function getStatusChartData() {
    return [
        'labels' => ['Bekerja', 'Wirausaha', 'Studi Lanjut', 'Belum Bekerja'],
        'datasets' => [[
            'data' => [
                getAlumniBekerja(),
                getAlumniWirausaha(),
                getAlumniStudi(),
                getAlumniMenganggur()
            ],
            'backgroundColor' => ['#007bff', '#ffc107', '#17a2b8', '#dc3545']
        ]]
    ];
}

// Data tren kelulusan alumni per tahun
function getTrendKelulusanData() {
    global $conn;
    $result = mysqli_query($conn, "SELECT tahun_lulus, COUNT(*) as total FROM alumni GROUP BY tahun_lulus ORDER BY tahun_lulus ASC");
    $labels = [];
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $labels[] = $row['tahun_lulus'];
        $data[] = $row['total'];
    }
    return [
        'labels' => $labels,
        'datasets' => [[
            'label' => 'Jumlah Lulusan',
            'data' => $data,
            'borderColor' => '#28a745',
            'fill' => false
        ]]
    ];
}

function getAlumniPerJurusan() {
    global $conn;
    $query = "
        SELECT program_studi AS jurusan, COUNT(*) as total
        FROM alumni
        GROUP BY program_studi
    ";
    $result = mysqli_query($conn, $query);
    $labels = [];
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $labels[] = $row['jurusan'];
        $data[] = $row['total'];
    }
    return [
        'labels' => $labels,
        'datasets' => [[
            'label' => 'Jumlah Alumni',
            'data' => $data,
            'backgroundColor' => '#6f42c1'
        ]]
    ];
}
function getTahunLulusList() {
    global $conn;
    $result = mysqli_query($conn, "SELECT DISTINCT tahun_lulus FROM alumni ORDER BY tahun_lulus ASC");
    $tahun = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $tahun[] = $row['tahun_lulus'];
    }
    return $tahun;
}


?>
