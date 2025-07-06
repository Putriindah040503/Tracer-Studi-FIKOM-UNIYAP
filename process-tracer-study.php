<?php
// Database connection configuration
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "tracer_study_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// Function to sanitize input data
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    
    // Identitas
    $nomor_mahasiswa = sanitize($_POST['nomor_mahasiswa']);
    $kode_pt = sanitize($_POST['kode_pt'] ?? '131013');
    $kode_prodi = sanitize($_POST['kode_prodi']);
    $nama = sanitize($_POST['nama']);
    $nomor_telepon = sanitize($_POST['nomor_telepon']);
    $email = sanitize($_POST['email']);
    $nik = sanitize($_POST['nik']);
    $npwp = sanitize($_POST['npwp'] ?? '');
    
    // Status
    $status = sanitize($_POST['status']);
    $mendapatkan_pekerjaan = sanitize($_POST['mendapatkan_pekerjaan'] ?? '');
    $masa_tunggu = intval($_POST['masa_tunggu'] ?? 0);
    $pendapatan = sanitize($_POST['pendapatan'] ?? '');
    
    // Data pekerjaan
    $provinsi = sanitize($_POST['provinsi'] ?? '');
    $kabupaten = sanitize($_POST['kabupaten'] ?? '');
    $jenis_perusahaan = sanitize($_POST['jenis_perusahaan'] ?? '');
    $nama_perusahaan = sanitize($_POST['nama_perusahaan'] ?? '');
    $posisi_wiraswasta = sanitize($_POST['posisi_wiraswasta'] ?? '');
    $tingkat_tempat_kerja = sanitize($_POST['tingkat_tempat_kerja'] ?? '');
    
    // Studi lanjut
    $sumber_biaya_studi = sanitize($_POST['sumber_biaya_studi'] ?? '');
    $perguruan_tinggi = sanitize($_POST['perguruan_tinggi'] ?? '');
    $program_studi = sanitize($_POST['program_studi_lanjut'] ?? '');
    $tanggal_masuk = sanitize($_POST['tanggal_masuk'] ?? '');
    
    // Pembiayaan kuliah
    $sumber_dana = isset($_POST['sumber_dana']) ? implode(',', $_POST['sumber_dana']) : '';
    $sumber_dana_lainnya = sanitize($_POST['sumber_dana_lainnya'] ?? '');
    
    // Kesesuaian bidang
    $kesesuaian = sanitize($_POST['kesesuaian'] ?? '');
    $tingkat_pendidikan = sanitize($_POST['tingkat_pendidikan'] ?? '');
    
    // Kompetensi
    $etika_a = intval($_POST['etika_a'] ?? 0);
    $etika_b = intval($_POST['etika_b'] ?? 0);
    $keahlian_a = intval($_POST['keahlian_a'] ?? 0);
    $keahlian_b = intval($_POST['keahlian_b'] ?? 0);
    $bahasa_inggris_a = intval($_POST['bahasa_inggris_a'] ?? 0);
    $bahasa_inggris_b = intval($_POST['bahasa_inggris_b'] ?? 0);
    $teknologi_informasi_a = intval($_POST['teknologi_informasi_a'] ?? 0);
    $teknologi_informasi_b = intval($_POST['teknologi_informasi_b'] ?? 0);
    $komunikasi_a = intval($_POST['komunikasi_a'] ?? 0);
    $komunikasi_b = intval($_POST['komunikasi_b'] ?? 0);
    $kerjasama_tim_a = intval($_POST['kerjasama_tim_a'] ?? 0);
    $kerjasama_tim_b = intval($_POST['kerjasama_tim_b'] ?? 0);
    $pengembangan_diri_a = intval($_POST['pengembangan_diri_a'] ?? 0);
    $pengembangan_diri_b = intval($_POST['pengembangan_diri_b'] ?? 0);
    
    // Metode pembelajaran
    $perkuliahan = intval($_POST['perkuliahan'] ?? 0);
    $demonstrasi = intval($_POST['demonstrasi'] ?? 0);
    $partisipasi_riset = intval($_POST['partisipasi_riset'] ?? 0);
    $magang = intval($_POST['magang'] ?? 0);
    $praktikum = intval($_POST['praktikum'] ?? 0);
    $kerja_lapangan = intval($_POST['kerja_lapangan'] ?? 0);
    $diskusi = intval($_POST['diskusi'] ?? 0);
    
    // Informasi pekerjaan
    $cara_mencari = isset($_POST['cara_mencari']) ? implode(',', $_POST['cara_mencari']) : '';
    $jumlah_perusahaan_dilamar = intval($_POST['jumlah_perusahaan_dilamar'] ?? 0);
    $jumlah_wawancara = intval($_POST['jumlah_wawancara'] ?? 0);
    $aktif_mencari = sanitize($_POST['aktif_mencari'] ?? '');
    $alasan_tidak_sesuai = isset($_POST['alasan_tidak_sesuai']) ? implode(',', $_POST['alasan_tidak_sesuai']) : '';
    $alasan_lainnya = sanitize($_POST['alasan_lainnya'] ?? '');
    
    // Prepare SQL statement
    $sql = "INSERT INTO tracer_study (
                nomor_mahasiswa, kode_pt, kode_prodi, nama, nomor_telepon, email, nik, npwp,
                status, mendapatkan_pekerjaan, masa_tunggu, pendapatan,
                provinsi, kabupaten, jenis_perusahaan, nama_perusahaan, posisi_wiraswasta, tingkat_tempat_kerja,
                sumber_biaya_studi, perguruan_tinggi, program_studi, tanggal_masuk,
                sumber_dana, sumber_dana_lainnya, kesesuaian, tingkat_pendidikan,
                etika_a, etika_b, keahlian_a, keahlian_b, bahasa_inggris_a, bahasa_inggris_b,
                teknologi_informasi_a, teknologi_informasi_b, komunikasi_a, komunikasi_b, 
                kerjasama_tim_a, kerjasama_tim_b, pengembangan_diri_a, pengembangan_diri_b,
                perkuliahan, demonstrasi, partisipasi_riset, magang, praktikum, kerja_lapangan, diskusi,
                cara_mencari, jumlah_perusahaan_dilamar, jumlah_wawancara, aktif_mencari,
                alasan_tidak_sesuai, alasan_lainnya,
                submission_date
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?,
                NOW()
            )";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // Bind parameters
        $stmt->bind_param(
            "ssssssssssdssssssssssssssiiiiiiiiiiiiiiiiiiiisiiiss",
            $nomor_mahasiswa, $kode_pt, $kode_prodi, $nama, $nomor_telepon, $email, $nik, $npwp,
            $status, $mendapatkan_pekerjaan, $masa_tunggu, $pendapatan,
            $provinsi, $kabupaten, $jenis_perusahaan, $nama_perusahaan, $posisi_wiraswasta, $tingkat_tempat_kerja,
            $sumber_biaya_studi, $perguruan_tinggi, $program_studi, $tanggal_masuk,
            $sumber_dana, $sumber_dana_lainnya, $kesesuaian, $tingkat_pendidikan,
            $etika_a, $etika_b, $keahlian_a, $keahlian_b, $bahasa_inggris_a, $bahasa_inggris_b,
            $teknologi_informasi_a, $teknologi_informasi_b, $komunikasi_a, $komunikasi_b,
            $kerjasama_tim_a, $kerjasama_tim_b, $pengembangan_diri_a, $pengembangan_diri_b,
            $perkuliahan, $demonstrasi, $partisipasi_riset, $magang, $praktikum, $kerja_lapangan, $diskusi,
            $cara_mencari, $jumlah_perusahaan_dilamar, $jumlah_wawancara, $aktif_mencari,
            $alasan_tidak_sesuai, $alasan_lainnya
        );
        
        // Execute statement
        if ($stmt->execute()) {
            // Success response
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Data berhasil disimpan']);
        } else {
            // Error response
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data: ' . $stmt->error]);
        }
        
        // Close statement
        $stmt->close();
    } else {
        // Error in preparing statement
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Gagal mempersiapkan pernyataan: ' . $conn->error]);
    }
    
    // Close connection
    $conn->close();
} else {
    // Not a POST request
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Metode permintaan tidak valid']);
}
?>
