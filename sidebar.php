<?php
if (session_status() == PHP_SESSION_NONE) session_start();
$role = $_SESSION['role'] ?? 'guest';
$activePage = $activePage ?? '';
?>

<nav class="sidebar">
    <div class="sidebar-header">
        <h5 class="text-light text-center mt-3">
            <?= ucfirst($role) ?> Panel
        </h5>
        <hr class="text-secondary mx-3" />
    </div>

    <?php if ($role === 'admin'): ?>
        <a href="dashboard-admin.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-house"></i> Dashboard
        </a>
        <a href="profil-admin.php" class="<?= $activePage === 'profil-admin' ? 'active' : '' ?>">
            <i class="bi bi-person-circle"></i> Profil Admin
        </a>
        <a href="manajemen-pengguna.php" class="<?= $activePage === 'manajemen-pengguna' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Manajemen Pengguna
        </a>
        <a href="manajemen_kuesioner.php" class="<?= $activePage === 'kuesioner' ? 'active' : '' ?>">
            <i class="bi bi-ui-checks"></i> Kuesioner
        </a>
        <a href="manajemen-alumni.php" class="<?= $activePage === 'alumni' ? 'active' : '' ?>">
            <i class="bi bi-person-lines-fill"></i> Data Alumni
        </a>
        <a href="manajemen-lowongan.php" class="<?= $activePage === 'lowongan' ? 'active' : '' ?>">
            <i class="bi bi-briefcase"></i> Lowongan Pekerjaan
        </a>
        <a href="admin-kirim-notifikasi.php" class="<?= $activePage === 'notifikasi' ? 'active' : '' ?>">
            <i class="bi bi-bell"></i> Notifikasi
        </a>
<!-- Dropdown Laporan -->
<div class="nav-item">
    <a class="nav-link dropdown-toggle <?= in_array($activePage, ['hasil_tracer', 'demografi', 'data_alumni', 'jawaban']) ? 'active' : '' ?>" 
       data-bs-toggle="collapse" href="#menuLaporan" role="button" aria-expanded="false" aria-controls="menuLaporan">
        <i class="bi bi-file-earmark-bar-graph"></i> Laporan
    </a>
    <div class="collapse <?= in_array($activePage, ['hasil_tracer', 'demografi', 'data_alumni', 'jawaban']) ? 'show' : '' ?>" id="menuLaporan">
        <ul class="nav flex-column ms-4">
            <li class="nav-item">
                <a href="laporan-data.php" class="nav-link <?= $activePage === 'hasil_tracer' ? 'active' : '' ?>">
                    <i class="bi bi-graph-up"></i> Hasil Tracer
                </a>
            </li>
            <li class="nav-item">
                <a href="laporan_demografi.php" class="nav-link <?= $activePage === 'demografi' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i> Laporan Demografi
                </a>
            </li>
        </ul>
    </div>
</div>




    <?php elseif ($role === 'fakultas'): ?>
        <a href="dashboard-fakultas.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-house"></i> Dashboard
        </a>
        <a href="profil-pengguna-fakultas.php" class="<?= $activePage === 'profil-fakultas' ? 'active' : '' ?>">
            <i class="bi bi-person-circle"></i> Profil Pengguna
        </a>
        <a href="manajemen_kuesioner.php" class="<?= $activePage === 'kuesioner' ? 'active' : '' ?>">
            <i class="bi bi-ui-checks"></i> Kuesioner
        </a>
        <a href="manajemen-alumni.php" class="<?= $activePage === 'alumni' ? 'active' : '' ?>">
            <i class="bi bi-person-lines-fill"></i> Data Alumni
        </a>
            <a href="admin-kirim-notifikasi.php" class="<?= $activePage === 'notifikasi' ? 'active' : '' ?>">
            <i class="bi bi-bell"></i> Notifikasi
        </a>
        <div class="nav-item">
    <a class="nav-link dropdown-toggle <?= in_array($activePage, ['hasil_tracer', 'demografi', 'data_alumni', 'jawaban']) ? 'active' : '' ?>" 
       data-bs-toggle="collapse" href="#menuLaporan" role="button" aria-expanded="false" aria-controls="menuLaporan">
        <i class="bi bi-file-earmark-bar-graph"></i> Laporan
    </a>
    <div class="collapse <?= in_array($activePage, ['hasil_tracer', 'demografi', 'data_alumni', 'jawaban']) ? 'show' : '' ?>" id="menuLaporan">
        <ul class="nav flex-column ms-4">
            <li class="nav-item">
                <a href="laporan-data.php" class="nav-link <?= $activePage === 'hasil_tracer' ? 'active' : '' ?>">
                    <i class="bi bi-graph-up"></i> Hasil Tracer
                </a>
            </li>
            <li class="nav-item">
                <a href="laporan_demografi.php" class="nav-link <?= $activePage === 'demografi' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i> Laporan Demografi
                </a>
            </li>
        </ul>
    </div>
</div>

    <?php elseif ($role === 'alumni'): ?>
        <a href="dashboard-alumni.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-house"></i> Dashboard
        </a>
        <a href="profil-alumni.php" class="<?= $activePage === 'profil-alumni' ? 'active' : '' ?>">
            <i class="bi bi-person-circle"></i> Profil
        </a>
        <a href="isi_kuesioner.php" class="<?= $activePage === 'kuesioner' ? 'active' : '' ?>">
            <i class="bi bi-ui-checks"></i> Isi Kuesioner
        </a>
        <a href="notifikasi.php" class="<?= $activePage === 'notifikasi' ? 'active' : '' ?>">
            <i class="bi bi-bell"></i> Notifikasi
        </a>
    <?php endif; ?>

    <hr class="text-secondary mx-3" />
    <a href="logout.php">
        <i class="bi bi-box-arrow-right"></i> Keluar
    </a>
</nav>
<script>
document.getElementById('toggleSidebar').addEventListener('click', function () {
    document.querySelector('.sidebar').classList.toggle('show');
});
</script>

