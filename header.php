<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once 'koneksi.php';

$nama = 'Pengguna';
$link_profil = '#';
$jumlah_notif = 0;
$role = $_SESSION['role'] ?? null;
$id = $_SESSION['id'] ?? null;



if ($role && $id) {
    // Ambil nama pengguna berdasarkan role
    switch ($role) {
        case 'admin':
            $stmt = $conn->prepare("SELECT nama_admin AS nama FROM admin WHERE user_id = ?");
            $link_profil = 'profil-admin.php';
            break;
        case 'fakultas':
            $stmt = $conn->prepare("SELECT nama_pengguna AS nama FROM fakultas WHERE user_id = ?");
            $link_profil = 'profil-pengguna-fakultas.php';
            break;
        case 'alumni':
            $stmt = $conn->prepare("SELECT nama_lengkap AS nama FROM alumni WHERE id = ?");
            $link_profil = 'profil-alumni.php';
            break;
        default:
            $stmt = null;
    }

    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($data = $result->fetch_assoc()) {
            $nama = $data['nama'];
        }
        $stmt->close();
    }

    // Ambil jumlah notifikasi belum dibaca
$stmt_notif = $conn->prepare("SELECT COUNT(*) as total FROM notifikasi WHERE penerima_role = ? AND status = 'belum_dibaca'");

    $stmt_notif->bind_param("s", $role);
    $stmt_notif->execute();
    $result_notif = $stmt_notif->get_result();
    if ($row_notif = $result_notif->fetch_assoc()) {
        $jumlah_notif = $row_notif['total'];
    }
    $stmt_notif->close();
}
?>

<header class="d-flex justify-content-between align-items-center p-3 bg-primary text-white">
    <button class="btn btn-outline-light d-md-none" id="toggleSidebar">
    <i class="bi bi-list"></i>
</button>
    <div class="header-left d-flex align-items-center">
        <img src="images/logoYapis.png" alt="Logo Fakultas" style="height: 50px; margin-right: 15px;">
        <h5 class="mb-0">Tracer Study Fakultas Ilmu Komputer</h5>
    </div>

    <div class="header-right d-flex align-items-center gap-3">
        <!-- Waktu Realtime -->
        <div id="realtimeDateTime" class="me-3 small"></div>

        <!-- Notifikasi -->
        <div class="dropdown">
            <a class="btn btn-outline-light position-relative" href="notifikasi.php">
                <i class="bi bi-bell"></i>
                <?php if ($jumlah_notif > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $jumlah_notif ?>
                        <span class="visually-hidden">notifikasi belum dibaca</span>
                    </span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Profil Pengguna -->
        <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($nama) ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= $link_profil ?>"><i class="bi bi-person"></i> Profil</a></li>
                <li><a class="dropdown-item" href="pengaturan.php"><i class="bi bi-gear"></i> Pengaturan</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
            </ul>
        </div>
    </div>

</header>

<script>
function updateDateTime() {
    const elem = document.getElementById('realtimeDateTime');
    if (!elem) return;
    const now = new Date();
    elem.textContent = now.toLocaleString('id-ID', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    });
}
updateDateTime();
setInterval(updateDateTime, 1000);
// Notification functionality
        const notifBtn = document.getElementById('notifBtn');
        const notifDropdown = document.getElementById('notifDropdown');
        const notifList = document.getElementById('notifList');
        const notifCount = document.getElementById('notifCount');

        // Tampilkan/sembunyikan dropdown notifikasi saat tombol diklik
        notifBtn.addEventListener('click', () => {
            const isVisible = notifDropdown.style.display === 'block';
            notifDropdown.style.display = isVisible ? 'none' : 'block';
        });

        // Tutup dropdown jika klik di luar area notifikasi
        document.addEventListener('click', function(event) {
            if (!notifBtn.contains(event.target) && !notifDropdown.contains(event.target)) {
                notifDropdown.style.display = 'none';
            }
        });

        // Fungsi memuat notifikasi dari server (simulasi dummy data, sesuaikan dengan endpoint backend kamu)
        async function loadNotifikasi() {
            try {
                // Ganti ini dengan `fetch('get_notifikasi.php')` jika kamu punya backend
                const dummyData = [
                    { pesan: "Alumni A baru mengisi kuesioner.", waktu: "2 menit lalu" },
                    { pesan: "Alumni B memperbarui datanya.", waktu: "10 menit lalu" }
                ];

                notifList.innerHTML = '';

                dummyData.forEach(notif => {
                    const li = document.createElement('li');
                    li.classList.add('mb-2', 'border-bottom', 'pb-2');
                    li.innerHTML = `
                        <strong>${notif.pesan}</strong><br>
                        <small class="text-muted">${notif.waktu}</small>
                    `;
                    notifList.appendChild(li);
                });

                // Tampilkan badge jumlah notifikasi
                if (dummyData.length > 0) {
                    notifCount.textContent = dummyData.length;
                    notifCount.style.display = 'inline-block';
                } else {
                    notifCount.style.display = 'none';
                }

            } catch (error) {
                console.error('Gagal memuat notifikasi:', error);
            }
        }

        // Panggil fungsi saat halaman selesai dimuat
        document.addEventListener('DOMContentLoaded', loadNotifikasi);

</script>
