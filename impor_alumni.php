<?php
include 'koneksi.php';

if (isset($_POST['upload']) && isset($_FILES['file_csv'])) {
    $file = $_FILES['file_csv']['tmp_name'];
    $mime = mime_content_type($file);

    if (in_array($mime, ['text/csv', 'application/vnd.ms-excel'])) {
        $handle = fopen($file, "r");

        // Lewati baris header
        fgetcsv($handle);

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $npm             = mysqli_real_escape_string($conn, $data[0]);
            $nama            = mysqli_real_escape_string($conn, $data[1]);
            $univ            = mysqli_real_escape_string($conn, $data[2]);
            $fakultas        = mysqli_real_escape_string($conn, $data[3]);
            $prodi           = mysqli_real_escape_string($conn, $data[4]);
            $gender          = mysqli_real_escape_string($conn, $data[5]);
            $tgl_lahir       = mysqli_real_escape_string($conn, $data[6]);
            $tahun_masuk     = mysqli_real_escape_string($conn, $data[7]);
            $tahun_lulus     = mysqli_real_escape_string($conn, $data[8]);
            $email           = mysqli_real_escape_string($conn, $data[9]);
            $no_hp           = mysqli_real_escape_string($conn, $data[10]);
            $nik             = mysqli_real_escape_string($conn, $data[11]);
            $npwp            = mysqli_real_escape_string($conn, $data[12]);
            $ipk             = mysqli_real_escape_string($conn, $data[13]);
            $judul_ta        = mysqli_real_escape_string($conn, $data[14]);
            $status_utama    = mysqli_real_escape_string($conn, $data[15]);
            $status_kuesioner = mysqli_real_escape_string($conn, $data[16]);

            $check = mysqli_query($conn, "SELECT * FROM alumni WHERE npm = '$npm'");
            if (mysqli_num_rows($check) == 0) {
                mysqli_query($conn, "INSERT INTO alumni 
                (npm, nama_lengkap, nama_universitas, fakultas, program_studi, jenis_kelamin, tanggal_lahir, tahun_masuk, tahun_lulus, email, no_hp, nik, npwp, ipk, judul_ta, status_utama, status_kuesioner, created_at) 
                VALUES 
                ('$npm', '$nama', '$univ', '$fakultas', '$prodi', '$gender', '$tgl_lahir', '$tahun_masuk', '$tahun_lulus', '$email', '$no_hp', '$nik', '$npwp', '$ipk', '$judul_ta', '$status_utama', '$status_kuesioner', NOW())");
            }
        }

        fclose($handle);
        header("Location: manajemen-alumni.php?success=import");
        exit;
    } else {
        echo "Format file tidak didukung. Harap unggah file CSV.";
    }
}
?>
