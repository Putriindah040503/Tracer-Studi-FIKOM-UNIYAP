<?php
session_start();
include 'koneksi.php';

// Cek apakah login sebagai fakultas
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'fakultas') {
    die("Akses ditolak. Silakan login sebagai pengguna fakultas.");
}


$user_id = $_SESSION['id'];

// Ambil parameter notifikasi jika ada
$sukses = $_GET['sukses'] ?? '';
$passwordStatus = $_GET['password'] ?? '';

// Ambil data dari tabel fakultas
$query = "SELECT * FROM fakultas WHERE user_id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Query prepare gagal: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$fakultas = $result->fetch_assoc();

// Jika tidak ditemukan
if (!$fakultas) {
    die("<div style='padding:2rem;color:red'>Data fakultas tidak ditemukan di database.</div>");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard Admin - Tracer Study</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="styless.css" rel="stylesheet" />
  <style>
    
  </style>
</head>
<body>

       <!-- Header -->
    <?php include 'header.php'; ?>

        <!-- Sidebar -->
        <nav class="sidebar">
            <?php include 'sidebar.php'; ?>
        </nav>

 <main>
        <div class="page-header fade-in-up">
            <h2>
                <i class="bi bi-speedometer2"></i>
                Profil fakultas
            </h2>

  <!-- Profile Information Card -->

    <div class="row">
      <div class="col-md-4 text-center">
<img src="images/<?= htmlspecialchars($foto) ?>" alt="Foto Profil" class="profile-pic mb-3" id="currentPhoto" />

        <h4 class="text-primary"><?= htmlspecialchars($fakultas['nama_pengguna']) ?></h4>
        <p class="text-muted mb-3">Administrator</p>
        
        <!-- Action Buttons -->
        <div class="d-flex flex-column gap-2">
          <button type="button" class="btn btn-primary-custom btn-action" onclick="toggleForm('formEdit')">
            <i class="bi bi-pencil-square me-2"></i>Edit Profil
          </button>
          <button type="button" class="btn btn-warning-custom btn-action" onclick="toggleForm('formPassword')">
            <i class="bi bi-shield-lock me-2"></i>Ubah Password
          </button>
        </div>
      </div>
      
      <div class="col-md-8">
  <div class="info-card">
    <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Pengguna</h5>

    <div class="info-item">
      <i class="bi bi-person-fill"></i>
      <div>
        <strong>Nama Lengkap:</strong><br>
        <?= htmlspecialchars($fakultas['nama_pengguna']) ?>
      </div>
    </div>

    <div class="info-item">
      <i class="bi bi-building"></i>
      <div>
        <strong>Fakultas:</strong><br>
        <?= htmlspecialchars($fakultas['nama_fakultas']) ?>
      </div>
    </div>

    <div class="info-item">
      <i class="bi bi-mortarboard-fill"></i>
      <div>
        <strong>Program Studi:</strong><br>
        <?= htmlspecialchars($fakultas['prodi']) ?>
      </div>
    </div>

    <div class="info-item">
      <i class="bi bi-card-text"></i>
      <div>
        <strong>NIP:</strong><br>
        <?= htmlspecialchars($fakultas['nip']) ?>
      </div>
    </div>

    <div class="info-item">
      <i class="bi bi-envelope-fill"></i>
      <div>
        <strong>Email:</strong><br>
        <?= htmlspecialchars($fakultas['email_kontak']) ?>
      </div>
    </div>

    <div class="info-item">
      <i class="bi bi-telephone-fill"></i>
      <div>
        <strong>No. Telepon:</strong><br>
        <?= htmlspecialchars($fakultas['no_telp']) ?>
      </div>
    </div>

    <div class="info-item">
      <i class="bi bi-geo-alt-fill"></i>
      <div>
        <strong>Alamat:</strong><br>
        <?= htmlspecialchars($fakultas['alamat']) ?>
      </div>
    </div>

    <div class="info-item">
      <i class="bi bi-calendar-event"></i>
      <div>
        <strong>Bergabung:</strong><br>
        <?= isset($fakultas['created_at']) ? date('d F Y', strtotime($fakultas['created_at'])) : 'Tidak tersedia' ?>
      </div>
    </div>

    <div class="info-item">
      <i class="bi bi-shield-check"></i>
      <div>
        <strong>Status:</strong><br>
        <span class="badge bg-success">Administrator Aktif</span>
      </div>
    </div>
  </div>
</div>


  <!-- Edit Profile Form -->
<div class="form-section" id="formEdit">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="text-primary mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Profil</h5>
    <button type="button" class="btn-close" onclick="toggleForm('formEdit')"></button>
  </div>
  
  <form action="update-profil-fakultas.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $fakultas['user_id'] ?>" />
    
    <div class="row g-3">
      <div class="col-md-6">
        <label for="nama_pengguna" class="form-label fw-bold">
          <i class="bi bi-person me-2"></i>Nama Lengkap
        </label>
        <input type="text" id="nama_pengguna" name="nama_pengguna" class="form-control form-control-custom" 
               value="<?= htmlspecialchars($fakultas['nama_pengguna']) ?>" required />
      </div>

      <div class="col-md-6">
        <label for="nip" class="form-label fw-bold">
          <i class="bi bi-credit-card me-2"></i>NIP
        </label>
        <input type="text" id="nip" name="nip" class="form-control form-control-custom" 
               value="<?= htmlspecialchars($fakultas['nip']) ?>" />
      </div>

      <div class="col-md-6">
        <label for="nama_fakultas" class="form-label fw-bold">
          <i class="bi bi-building me-2"></i>Nama Fakultas
        </label>
        <input type="text" id="nama_fakultas" name="nama_fakultas" class="form-control form-control-custom" 
               value="<?= htmlspecialchars($fakultas['nama_fakultas']) ?>" required />
      </div>

      <div class="col-md-6">
        <label for="prodi" class="form-label fw-bold">
          <i class="bi bi-mortarboard-fill me-2"></i>Program Studi
        </label>
        <input type="text" id="prodi" name="prodi" class="form-control form-control-custom" 
               value="<?= htmlspecialchars($fakultas['prodi']) ?>" required />
      </div>

      <div class="col-md-6">
        <label for="email_kontak" class="form-label fw-bold">
          <i class="bi bi-envelope me-2"></i>Alamat Email
        </label>
        <input type="email" id="email_kontak" name="email_kontak" class="form-control form-control-custom" 
               value="<?= htmlspecialchars($fakultas['email_kontak']) ?>" required />
      </div>

      <div class="col-md-6">
        <label for="no_telp" class="form-label fw-bold">
          <i class="bi bi-telephone me-2"></i>No. Telepon
        </label>
        <input type="text" id="no_telp" name="no_telp" class="form-control form-control-custom" 
               value="<?= htmlspecialchars($fakultas['no_telp']) ?>" />
      </div>

      <div class="col-md-6">
        <label for="alamat" class="form-label fw-bold">
          <i class="bi bi-geo-alt me-2"></i>Alamat
        </label>
        <input type="text" id="alamat" name="alamat" class="form-control form-control-custom" 
               value="<?= htmlspecialchars($fakultas['alamat']) ?>" />
      </div>
    </div>

   <!-- Input Ganti Foto -->
<div class="mt-4">
  <label for="foto" class="form-label fw-bold">
    <i class="bi bi-camera me-2"></i>Foto Profil
  </label>
  <input type="file" id="foto" name="foto" class="form-control form-control-custom"
         accept="images/*" onchange="previewImage(event)" />

  <!-- Preview Foto Baru -->
  <div class="preview-container" id="imagePreview" style="display: none;">
    <img id="preview" style="max-height: 200px; border-radius: 10px;" alt="Preview Foto" />
    <p class="mt-2 mb-0 text-muted">Preview foto baru</p>
  </div>
</div>

    <div class="mt-4 text-end">
      <button type="button" class="btn btn-outline-secondary btn-action me-2" onclick="toggleForm('formEdit')">
        <i class="bi bi-x-lg me-2"></i>Batal
      </button>

      <button type="submit" class="btn btn-success-custom btn-action">
        <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
      </button>
    </div>
  </form>
</div>

  <!-- Change Password Form -->
  <div class="form-section" id="formPassword">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h5 class="text-warning mb-0"><i class="bi bi-shield-lock me-2"></i>Ubah Password</h5>
      <button type="button" class="btn-close" onclick="toggleForm('formPassword')"></button>
    </div>
    
    <form action="proses-ubah-password-fakultas.php" method="POST">
      <input type="hidden" name="id" value="<?= $fakultas['user_id'] ?>" />
      
      <div class="mb-3">
        <label for="password_lama" class="form-label fw-bold">
          <i class="bi bi-key me-2"></i>Password Lama
        </label>
        <input type="password" id="password_lama" name="password_lama" 
               class="form-control form-control-custom" required />
      </div>
      
      <div class="mb-3">
        <label for="password_baru" class="form-label fw-bold">
          <i class="bi bi-key-fill me-2"></i>Password Baru
        </label>
        <input type="password" id="password_baru" name="password_baru" 
               class="form-control form-control-custom" required />
        <div class="form-text">
          <i class="bi bi-info-circle me-1"></i>
          Password minimal 8 karakter, kombinasi huruf dan angka
        </div>
      </div>
      
      <div class="mb-3">
        <label for="konfirmasi_password" class="form-label fw-bold">
          <i class="bi bi-check2-square me-2"></i>Konfirmasi Password
        </label>
        <input type="password" id="konfirmasi_password" name="konfirmasi_password" 
               class="form-control form-control-custom" required />
      </div>

      <div class="mt-4 text-end">
        <button type="button" class="btn btn-outline-secondary btn-action me-2" onclick="toggleForm('formPassword')">
          <i class="bi bi-x-lg me-2"></i>Batal
        </button>
        <button type="submit" class="btn btn-warning-custom btn-action">
          <i class="bi bi-shield-check me-2"></i>Update Password
        </button>
      </div>
    </form>
  </div>
</main>
<?php if ($sukses === 'update'): ?>
  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div class="toast align-items-center text-white bg-success border-0 show" role="alert">
      <div class="d-flex">
        <div class="toast-body">
          ✅ Data profil berhasil diperbarui.
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if ($passwordStatus === 'berhasil'): ?>
  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div class="toast text-white bg-success show">
      <div class="toast-body">✅ Password berhasil diubah.</div>
    </div>
  </div>
<?php elseif ($passwordStatus === 'salah'): ?>
  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div class="toast text-white bg-danger show">
      <div class="toast-body">❌ Password lama salah.</div>
    </div>
  </div>
<?php elseif ($passwordStatus === 'gagal_konfirmasi'): ?>
  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div class="toast text-white bg-warning show">
      <div class="toast-body">⚠️ Konfirmasi password tidak cocok.</div>
    </div>
  </div>
<?php endif; ?>

<script>

  document.addEventListener("DOMContentLoaded", function () {
    var toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.map(function (toastEl) {
      return new bootstrap.Toast(toastEl).show();
    });
  });


  document.addEventListener('DOMContentLoaded', () => {
    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.forEach(toastEl => new bootstrap.Toast(toastEl).show());
  });

  // === Toggle form ===
  function toggleForm(formId) {
    const form = document.getElementById(formId);
    const otherFormId = formId === 'formEdit' ? 'formPassword' : 'formEdit';
    const otherForm = document.getElementById(otherFormId);

    if (otherForm.classList.contains('show')) otherForm.classList.remove('show');
    form.classList.toggle('show');

    if (form.classList.contains('show')) {
      setTimeout(() => {
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 100);
    }
  }

  // === Image preview ===
  function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        const preview = document.getElementById("preview");
        const container = document.getElementById("imagePreview");

        preview.src = e.target.result;
        container.style.display = "block";
      };
      reader.readAsDataURL(file);
    }
  }

  // === Dark mode toggle ===
  const toggleBtn = document.getElementById('toggleDarkMode');
  const darkModeIcon = document.getElementById('darkModeIcon');

  function updateDarkModeIcon(isDark) {
    darkModeIcon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
  }

  function applyDarkModeSetting() {
    const isDark = localStorage.getItem('darkmode') === 'true';
    document.body.classList.toggle('dark-mode', isDark);
    updateDarkModeIcon(isDark);
  }

  toggleBtn?.addEventListener('click', () => {
    const isDark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkmode', isDark);
    updateDarkModeIcon(isDark);

    // Optional: update chart style in dark mode
    if (window.lineChart) {
      const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
      lineChart.options.scales.y.grid.color = gridColor;
      lineChart.update();
    }
  });

  applyDarkModeSetting();

  // === Password validation ===
  document.getElementById('konfirmasi_password')?.addEventListener('input', function () {
    const password = document.getElementById('password_baru').value;
    this.setCustomValidity(password !== this.value ? 'Password tidak cocok' : '');
  });

  // === Form loading state ===
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function () {
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Menyimpan...';

      setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }, 3000);
    });
  });

  // === Show success alert if redirected with success parameter ===
  if (new URLSearchParams(window.location.search).get('success') === '1') {
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
    alert.style.cssText = 'top: 80px; right: 20px; z-index: 1060; min-width: 300px;';
    alert.innerHTML = `
      <i class="bi bi-check-circle me-2"></i>
      <strong>Berhasil!</strong> Profil telah diperbarui.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
  }

  // === Realtime date and time ===
  function updateDateTime() {
    const dateTimeElem = document.getElementById('realtimeDateTime');
    if (!dateTimeElem) return;
    const now = new Date();
    const options = {
      weekday: 'long', year: 'numeric', month: 'long',
      day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit'
    };
    dateTimeElem.textContent = now.toLocaleDateString('id-ID', options);
  }

  setInterval(updateDateTime, 1000);
  updateDateTime();

  // === Notifikasi dropdown ===
  const notifBtn = document.getElementById('notifBtn');
  const notifDropdown = document.getElementById('notifDropdown');
  const notifList = document.getElementById('notifList');
  const notifCount = document.getElementById('notifCount');

  notifBtn?.addEventListener('click', () => {
    notifDropdown.style.display = notifDropdown.style.display === 'block' ? 'none' : 'block';
  });

  document.addEventListener('click', function (event) {
    if (!notifBtn?.contains(event.target) && !notifDropdown?.contains(event.target)) {
      notifDropdown.style.display = 'none';
    }
  });

  async function loadNotifikasi() {
    try {
      const dummyData = [
        { pesan: "Alumni A baru mengisi kuesioner.", waktu: "2 menit lalu" },
        { pesan: "Alumni B memperbarui datanya.", waktu: "10 menit lalu" }
      ];

      notifList.innerHTML = '';
      dummyData.forEach(notif => {
        const li = document.createElement('li');
        li.className = 'mb-2 border-bottom pb-2';
        li.innerHTML = `<strong>${notif.pesan}</strong><br><small class="text-muted">${notif.waktu}</small>`;
        notifList.appendChild(li);
      });

      notifCount.textContent = dummyData.length;
      notifCount.style.display = dummyData.length ? 'inline-block' : 'none';
    } catch (error) {
      console.error('Gagal memuat notifikasi:', error);
    }
  }

  document.addEventListener('DOMContentLoaded', loadNotifikasi);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>