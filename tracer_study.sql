-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 29 Jun 2025 pada 15.24
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tracer_study`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `user_id` int(11) NOT NULL,
  `nama_admin` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`user_id`, `nama_admin`, `email`, `no_hp`, `foto`, `created_at`, `updated_at`) VALUES
(3, 'Admin Fakultas Ilmu Komputer01', 'admin@fikom.uniyap.ac.id', '081212345678', 'admin_68538318268a0.jpeg', '2025-06-18 04:52:14', '2025-06-23 16:09:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `alumni`
--

CREATE TABLE `alumni` (
  `id` int(11) NOT NULL,
  `npm` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nama_universitas` varchar(100) DEFAULT 'Universitas Yapis Papua',
  `jenis_kelamin` enum('Laki-Laki','Perempuan') NOT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `fakultas` varchar(255) DEFAULT 'Fakultas Ilmu Komputer',
  `program_studi` enum('Sistem Informasi','Informatika') DEFAULT NULL,
  `judul_ta` text DEFAULT NULL,
  `tahun_masuk` year(4) NOT NULL,
  `tahun_lulus` year(4) NOT NULL,
  `ipk` decimal(3,2) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `nik` char(16) DEFAULT NULL,
  `npwp` char(15) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status_utama` enum('pending_verifikasi','active','inactive','rejected','terklaim') DEFAULT 'pending_verifikasi',
  `status_kuesioner` enum('belum_isi','sudah_isi') DEFAULT 'belum_isi',
  `last_login` datetime DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `rejected_reason` text DEFAULT NULL,
  `klaim_at` datetime DEFAULT NULL,
  `waktu_pengisian_kuesioner` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `alumni`
--

INSERT INTO `alumni` (`id`, `npm`, `nama_lengkap`, `nama_universitas`, `jenis_kelamin`, `tanggal_lahir`, `fakultas`, `program_studi`, `judul_ta`, `tahun_masuk`, `tahun_lulus`, `ipk`, `email`, `no_hp`, `nik`, `npwp`, `password`, `foto`, `status_utama`, `status_kuesioner`, `last_login`, `verified_at`, `verified_by`, `rejected_reason`, `klaim_at`, `waktu_pengisian_kuesioner`, `created_at`, `updated_at`) VALUES
(1, '18621001', 'Ahmad Rizki Pratama', 'Universitas Yapis Papua', 'Laki-Laki', '1999-03-15', 'Fakultas Ilmu Komputer', 'Sistem Informasi', 'Sistem Informasi Manajemen Inventory Berbasis Web pada PT. Berkah Jaya', '2018', '2022', 3.45, 'ahmad.rizki@email.com', '081234567890', '9171031503990001', '123456789012345', NULL, NULL, 'pending_verifikasi', 'belum_isi', NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-27 10:57:12', '2025-06-27 10:57:12'),
(2, '19622015', 'Siti Nurhaliza Putri', 'Universitas Yapis Papua', 'Perempuan', '2000-07-22', 'Fakultas Ilmu Komputer', 'Informatika', 'Aplikasi Mobile Learning untuk Pembelajaran Algoritma dan Pemrograman', '2019', '2023', 3.78, 'siti.nurhaliza@email.com', '082345678901', '9171072207000002', '234567890123456', NULL, NULL, 'pending_verifikasi', 'belum_isi', NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-27 10:57:12', '2025-06-27 10:57:12'),
(3, '20621042', 'Budi Santoso', 'Universitas Yapis Papua', 'Laki-Laki', '2001-11-08', 'Fakultas Ilmu Komputer', 'Sistem Informasi', 'Sistem Pendukung Keputusan Pemilihan Karyawan Terbaik Menggunakan Metode TOPSIS', '2020', '2024', 3.62, 'budi.santoso@email.com', '083456789012', '9171110801010003', '345678901234567', NULL, NULL, 'pending_verifikasi', 'belum_isi', NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-27 10:57:12', '2025-06-27 10:57:12'),
(4, '21622073', 'Maria Christina Sari', 'Universitas Yapis Papua', 'Perempuan', '2002-04-12', 'Fakultas Ilmu Komputer', 'Informatika', 'Implementasi Artificial Intelligence untuk Deteksi Penyakit Tanaman Padi', '2021', '2025', 3.89, 'maria.christina@email.com', '084567890123', '9171041204020004', '456789012345678', NULL, NULL, 'pending_verifikasi', 'belum_isi', NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-27 10:57:12', '2025-06-27 10:57:12'),
(5, '19621087', 'Denny Kurniawan', 'Universitas Yapis Papua', 'Laki-Laki', '2000-09-30', 'Fakultas Ilmu Komputer', 'Sistem Informasi', 'E-Commerce Produk UMKM Papua Berbasis Website dengan Payment Gateway', '2019', '2023', 3.56, 'denny.kurniawan@email.com', '085678901234', '9171093009000005', '567890123456789', NULL, NULL, 'pending_verifikasi', 'belum_isi', NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-27 10:57:12', '2025-06-27 10:57:12'),
(6, '21621010', 'Putri Indah Melani', '', 'Perempuan', NULL, 'Fakultas Ilmu Komputer', 'Sistem Informasi', NULL, '2021', '2025', NULL, 'putriindahmey217@gmail.com', '081354193046', '', '', NULL, '', 'pending_verifikasi', 'belum_isi', NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-27 11:12:21', '2025-06-27 11:27:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `fakultas`
--

CREATE TABLE `fakultas` (
  `user_id` int(11) NOT NULL,
  `nama_fakultas` varchar(100) NOT NULL,
  `prodi` enum('sistem informasi','informatika') NOT NULL,
  `nama_pengguna` varchar(255) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `email_kontak` varchar(100) DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `foto` text DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `fakultas`
--

INSERT INTO `fakultas` (`user_id`, `nama_fakultas`, `prodi`, `nama_pengguna`, `nip`, `email_kontak`, `no_telp`, `foto`, `alamat`, `created_at`) VALUES
(10, 'Belum diisi', 'sistem informasi', 'FIKOM', '000000000', 'informatika@gmail.com', '-', 'admin_1750696838.png', '-', '2025-06-19 03:19:14'),
(11, 'Belum diisi', 'sistem informasi', 'Sistem Informasi', '000000000', 'sisteminformasi01@gmail.com', '-', 'default.png', '-', '2025-06-20 07:31:09');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kuesioner_jawaban`
--

CREATE TABLE `kuesioner_jawaban` (
  `id` int(11) NOT NULL,
  `alumni_id` int(11) NOT NULL,
  `pertanyaan_id` int(11) NOT NULL,
  `opsi_id` int(11) DEFAULT NULL,
  `jawaban` text NOT NULL,
  `waktu_pengisian` datetime DEFAULT current_timestamp(),
  `is_latest` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kuesioner_jawaban`
--

INSERT INTO `kuesioner_jawaban` (`id`, `alumni_id`, `pertanyaan_id`, `opsi_id`, `jawaban`, `waktu_pengisian`, `is_latest`) VALUES
(109, 1, 15, NULL, '', '2025-06-24 21:31:09', 1),
(110, 1, 16, NULL, '', '2025-06-24 21:31:09', 1),
(111, 1, 17, NULL, '', '2025-06-24 21:31:09', 1),
(112, 1, 18, NULL, '', '2025-06-24 21:31:09', 1),
(113, 1, 19, NULL, '', '2025-06-24 21:31:09', 1),
(114, 1, 20, NULL, '', '2025-06-24 21:31:09', 1),
(115, 1, 21, NULL, '', '2025-06-24 21:31:09', 1),
(116, 1, 22, NULL, '', '2025-06-24 21:31:09', 1),
(117, 1, 23, NULL, '', '2025-06-24 21:31:09', 1),
(118, 1, 24, NULL, '', '2025-06-24 21:31:09', 1),
(119, 1, 25, NULL, '', '2025-06-24 21:31:09', 1),
(122, 1, 27, NULL, '', '2025-06-24 21:31:09', 1),
(123, 1, 28, NULL, '', '2025-06-24 21:31:09', 1),
(124, 1, 29, NULL, '', '2025-06-24 21:31:09', 1),
(125, 1, 30, NULL, '', '2025-06-24 21:31:09', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kuesioner_kategori`
--

CREATE TABLE `kuesioner_kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kuesioner_kategori`
--

INSERT INTO `kuesioner_kategori` (`id`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Identitas Diri', 'Data identitas pribadi alumni'),
(2, 'Status Pekerjaan', 'Status saat ini alumni'),
(3, 'Sedang Mencari Kerja / Bekerja / Wiraswasta', 'Khusus untuk alumni bekerja, wiraswasta, atau mencari kerja'),
(4, 'Studi Lanjut', 'Bagi alumni yang melanjutkan studi'),
(5, 'Perkuliahan / Pembelajaran', 'Bagi alumni yang tidak bekerja karena sedang kuliah'),
(6, 'Saran dan Masukkan', 'Masukkan alumni terhadap kampus');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kuesioner_opsi_jawaban`
--

CREATE TABLE `kuesioner_opsi_jawaban` (
  `id` int(11) NOT NULL,
  `pertanyaan_id` int(11) DEFAULT NULL,
  `opsi_text` varchar(255) DEFAULT NULL,
  `opsi_value` varchar(50) DEFAULT NULL,
  `urutan` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kuesioner_opsi_jawaban`
--

INSERT INTO `kuesioner_opsi_jawaban` (`id`, `pertanyaan_id`, `opsi_text`, `opsi_value`, `urutan`) VALUES
(21, 25, 'Bekerja (full time / part time)', 'bekerja_(full_time_/_part_time)', 1),
(22, 25, 'Belum memungkinkan bekerja', 'belum_memungkinkan_bekerja', 2),
(23, 25, 'Wiraswasta', 'wiraswasta', 3),
(24, 25, 'Melanjutkan pendidikan', 'melanjutkan_pendidikan', 4),
(25, 25, 'Tidak kerja tetapi sedang mencari kerja', 'tidak_kerja_tetapi_sedang_mencari_kerja', 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kuesioner_pertanyaan`
--

CREATE TABLE `kuesioner_pertanyaan` (
  `id` int(11) NOT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `pertanyaan` text NOT NULL,
  `tipe_input` enum('text','textarea','select','radio','checkbox','datetime') NOT NULL,
  `wajib` tinyint(1) DEFAULT 1,
  `urutan` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `aktif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kuesioner_pertanyaan`
--

INSERT INTO `kuesioner_pertanyaan` (`id`, `kategori_id`, `pertanyaan`, `tipe_input`, `wajib`, `urutan`, `created_at`, `updated_at`, `aktif`) VALUES
(15, 1, 'NPM', 'text', 1, 1, '2025-06-24 07:48:09', '2025-06-24 07:48:09', 1),
(16, 1, 'Nama Universitas', 'text', 1, 2, '2025-06-24 07:48:09', '2025-06-24 07:48:09', 1),
(17, 1, 'Tahun Masuk Kuliah', 'text', 1, 3, '2025-06-24 07:48:09', '2025-06-24 07:48:09', 1),
(18, 1, 'Tahun Lulus', 'text', 1, 4, '2025-06-24 07:48:09', '2025-06-24 07:48:09', 1),
(19, 1, 'Program Studi', 'text', 1, 5, '2025-06-24 07:48:09', '2025-06-24 07:48:09', 1),
(20, 1, 'Email', 'text', 1, 6, '2025-06-24 07:48:09', '2025-06-24 07:48:09', 1),
(21, 1, 'Nama Lengkap', 'text', 1, 7, '2025-06-24 07:48:09', '2025-06-24 07:48:09', 1),
(22, 1, 'Nomor HP', 'text', 1, 8, '2025-06-24 07:48:09', '2025-06-24 07:48:09', 1),
(23, 1, 'NIK', 'text', 1, 9, '2025-06-24 07:48:09', '2025-06-24 07:48:09', 1),
(24, 1, 'NPWP', 'text', 0, 10, '2025-06-24 07:48:09', '2025-06-24 07:48:09', 1),
(25, 2, 'Jelaskan Status Anda Saat ini?', 'radio', 1, 15, '2025-06-24 07:51:16', '2025-06-24 07:51:42', 1),
(27, 1, 'alamat', 'textarea', 0, 11, '2025-06-24 08:22:05', '2025-06-28 12:36:28', 1),
(28, 1, 'alamat', 'textarea', 0, 12, '2025-06-24 08:22:05', '2025-06-28 12:36:05', 1),
(29, 1, 'status', 'text', 0, 13, '2025-06-24 08:22:05', '2025-06-28 12:35:30', 1),
(30, 1, 'npm anda', 'text', 0, 14, '2025-06-24 08:22:05', '2025-06-28 12:34:52', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `lowongan`
--

CREATE TABLE `lowongan` (
  `id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `perusahaan` varchar(100) NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  `link_pendaftaran` text DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_berakhir` date NOT NULL,
  `deskripsi` text NOT NULL,
  `syarat` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `lowongan`
--

INSERT INTO `lowongan` (`id`, `judul`, `perusahaan`, `lokasi`, `link_pendaftaran`, `tanggal_mulai`, `tanggal_berakhir`, `deskripsi`, `syarat`, `foto`, `created_at`, `updated_at`, `status`) VALUES
(2, 'Data Analyst', 'CV Data Cerdas', 'Bandung', 'https://datacerdas.id/karir/data-analyst', '2025-05-18', '2025-07-11', 'Menganalisis data bisnis dan membuat laporan.', 'Lulusan Statistika/Informatika, mampu menggunakan Excel, SQL, dan Python.', 'low_68597e762daf53.74013077.jpg', '2025-05-22 14:43:23', '2025-06-24 01:19:02', 'active'),
(5, 'IT Support', 'CV Data Cerdas', 'Bandung', '', '2025-06-16', '2025-07-03', 'ASRTYUI', 'QWERTYUIOP', 'low_68597e6682ab48.00839730.jpg', '2025-06-15 22:56:27', '2025-06-24 01:18:46', 'active'),
(6, 'Data Analyst', 'Universitas ABC', 'Jayapura', 'https://datacerdas.id/karir/data-analyst', '2025-06-13', '2025-07-11', 'asdfgh', 'ghjkl', 'low_68597e4591ceb4.87117936.jpeg', '2025-06-16 01:12:27', '2025-06-24 01:18:13', 'active');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `jenis` enum('kuesioner_baru','pengingat','sistem','pengumuman') DEFAULT 'sistem',
  `penerima_role` enum('admin','fakultas','alumni') NOT NULL,
  `status` enum('belum_dibaca','dibaca') DEFAULT 'belum_dibaca',
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `dibaca_pada` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','fakultas','alumni') NOT NULL,
  `status_akun` enum('active','inactive') DEFAULT 'inactive',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `nama`, `email`, `password`, `role`, `status_akun`, `last_login`, `created_at`) VALUES
(3, 'Admin Fakultas Ilmu Komputer', 'admin@fikom.uniyap.ac.id', '$2y$10$1Nifybs/3kSSdWlXzQof8.zOjgCjRi.ZXI8frCkKenlkEwN9L1dhS', 'admin', 'active', NULL, '2025-06-18 04:52:14'),
(10, 'Informatika', 'informatika@gmail.com', '$2y$10$A04YWdl21W2YHDtE4wyNR.zkyaG5GDq2Ph.rpy1r7.bXBiDyG3hwq', 'fakultas', 'inactive', NULL, '2025-06-19 03:19:14'),
(11, 'Sistem Informasi', 'sisteminformasi01@gmail.com', '$2y$10$HF3gOnqk6IoAWOTCoL3yneHlYor3BNa/tr7uKd3GVUfj/o1D0tWc.', 'fakultas', 'inactive', NULL, '2025-06-20 07:31:09');

--
-- Trigger `user`
--
DELIMITER $$
CREATE TRIGGER `after_insert_user_role` AFTER INSERT ON `user` FOR EACH ROW BEGIN
    -- Jika role fakultas, masukkan ke tabel fakultas
    IF NEW.role = 'fakultas' THEN
        INSERT INTO fakultas (
            user_id,
            nama_pengguna,
            email_kontak
        ) VALUES (
            NEW.id,
            NEW.nama,
            NEW.email
        );
        
    -- Jika role admin, masukkan ke tabel admin
    ELSEIF NEW.role = 'admin' THEN
        INSERT INTO admin (
            user_id,
            nama_admin,
            email_admin
        ) VALUES (
            NEW.id,
            NEW.nama,
            NEW.email
        );
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_insert_user_roles` AFTER INSERT ON `user` FOR EACH ROW BEGIN
    -- Jika role fakultas, masukkan ke tabel fakultas
    IF NEW.role = 'fakultas' THEN
        INSERT INTO fakultas (
            user_id,
            nama_fakultas,
            nama_pengguna,
            prodi,
            nip,
            email_kontak,
            no_telp,
            foto,
            alamat,
            created_at
        ) VALUES (
            NEW.id,
            'Belum diisi',
            NEW.nama,
            'sistem informasi',
            '000000000',
            NEW.email,
            '-',
            'default.png',
            '-',
            NOW()
        );

    -- Jika role admin, masukkan ke tabel admin
    ELSEIF NEW.role = 'admin' THEN
        INSERT INTO admin (
            user_id,
            nama_admin,
            email_admin
        ) VALUES (
            NEW.id,
            NEW.nama,
            NEW.email
        );
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`user_id`);

--
-- Indeks untuk tabel `alumni`
--
ALTER TABLE `alumni`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `npm` (`npm`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD UNIQUE KEY `npwp` (`npwp`),
  ADD KEY `idx_npm` (`npm`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status_utama`),
  ADD KEY `idx_tahun_lulus` (`tahun_lulus`),
  ADD KEY `idx_program_studi` (`program_studi`),
  ADD KEY `idx_fakultas` (`fakultas`);

--
-- Indeks untuk tabel `fakultas`
--
ALTER TABLE `fakultas`
  ADD PRIMARY KEY (`user_id`);

--
-- Indeks untuk tabel `kuesioner_jawaban`
--
ALTER TABLE `kuesioner_jawaban`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alumni_id` (`alumni_id`),
  ADD KEY `pertanyaan_id` (`pertanyaan_id`);

--
-- Indeks untuk tabel `kuesioner_kategori`
--
ALTER TABLE `kuesioner_kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kuesioner_opsi_jawaban`
--
ALTER TABLE `kuesioner_opsi_jawaban`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pertanyaan_id` (`pertanyaan_id`);

--
-- Indeks untuk tabel `kuesioner_pertanyaan`
--
ALTER TABLE `kuesioner_pertanyaan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indeks untuk tabel `lowongan`
--
ALTER TABLE `lowongan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `alumni`
--
ALTER TABLE `alumni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `kuesioner_jawaban`
--
ALTER TABLE `kuesioner_jawaban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT untuk tabel `kuesioner_kategori`
--
ALTER TABLE `kuesioner_kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `kuesioner_opsi_jawaban`
--
ALTER TABLE `kuesioner_opsi_jawaban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT untuk tabel `kuesioner_pertanyaan`
--
ALTER TABLE `kuesioner_pertanyaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT untuk tabel `lowongan`
--
ALTER TABLE `lowongan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `fakultas`
--
ALTER TABLE `fakultas`
  ADD CONSTRAINT `fakultas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `kuesioner_jawaban`
--
ALTER TABLE `kuesioner_jawaban`
  ADD CONSTRAINT `kuesioner_jawaban_ibfk_1` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`id`),
  ADD CONSTRAINT `kuesioner_jawaban_ibfk_2` FOREIGN KEY (`pertanyaan_id`) REFERENCES `kuesioner_pertanyaan` (`id`);

--
-- Ketidakleluasaan untuk tabel `kuesioner_opsi_jawaban`
--
ALTER TABLE `kuesioner_opsi_jawaban`
  ADD CONSTRAINT `kuesioner_opsi_jawaban_ibfk_1` FOREIGN KEY (`pertanyaan_id`) REFERENCES `kuesioner_pertanyaan` (`id`);

--
-- Ketidakleluasaan untuk tabel `kuesioner_pertanyaan`
--
ALTER TABLE `kuesioner_pertanyaan`
  ADD CONSTRAINT `kuesioner_pertanyaan_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kuesioner_kategori` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
