<?php
session_start();
include "koneksi.php";
require('fpdf/fpdf.php');

// Cek login admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit();
}

// Ambil filter dari GET
$filterProdi = isset($_GET['filter_prodi']) ? trim($_GET['filter_prodi']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusAkun = isset($_GET['status_akun']) ? trim($_GET['status_akun']) : '';

// Siapkan query filter
$whereClauses = [];
if ($filterProdi !== '') {
    $whereClauses[] = "program_studi = '" . mysqli_real_escape_string($conn, $filterProdi) . "'";
}
if ($search !== '') {
    $escaped = mysqli_real_escape_string($conn, $search);
    $whereClauses[] = "(nama_lengkap LIKE '%$escaped%' OR npm LIKE '%$escaped%')";
}
if ($statusAkun !== '') {
    $whereClauses[] = "status_akun = '" . mysqli_real_escape_string($conn, $statusAkun) . "'";
}

$whereSQL = count($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : '';
$query = "SELECT * FROM alumni $whereSQL ORDER BY nama_lengkap ASC";
$result = mysqli_query($conn, $query);

// Cek error query
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

class PDF extends FPDF {
    function Header() {
        // Logo
        $this->Image('images/logoYapis.png', 10, 6, 25); // Sesuaikan path/logo
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'Laporan Data Alumni - Tracer Study FIKOM',0,1,'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF('L','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','B',10);

// Header tabel
$pdf->Cell(10,10,'No',1);
$pdf->Cell(30,10,'NPM',1);
$pdf->Cell(50,10,'Nama Lengkap',1);
$pdf->Cell(30,10,'Program Studi',1);
$pdf->Cell(25,10,'Thn Masuk',1);
$pdf->Cell(25,10,'Thn Lulus',1);
$pdf->Cell(40,10,'Email',1);
$pdf->Cell(30,10,'No. HP',1);
$pdf->Cell(25,10,'Status',1);
$pdf->Ln();

// Isi data
$pdf->SetFont('Arial','',10);
$no = 1;
$total = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $pdf->Cell(10,10,$no++,1);
    $pdf->Cell(30,10,$row['npm'],1);
    $pdf->Cell(50,10,substr($row['nama_lengkap'], 0, 30),1);
    $pdf->Cell(30,10,$row['program_studi'],1);
    $pdf->Cell(25,10,$row['tahun_masuk'],1);
    $pdf->Cell(25,10,$row['tahun_lulus'],1);
    $pdf->Cell(40,10,$row['email'],1);
    $pdf->Cell(30,10,$row['no_hp'],1);
    $pdf->Cell(25,10,ucfirst($row['status_akun']),1);
    $pdf->Ln();
    $total++;
}

// Total alumni
$pdf->Ln(5);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,10,"Total Alumni: $total",0,1,'R');

$pdf->Output('I', 'Laporan_Data_Alumni.pdf');
