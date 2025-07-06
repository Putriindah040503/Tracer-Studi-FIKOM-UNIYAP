<?php
session_start();
include 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['id'];

// Ambil data admin dari database
$query = "SELECT * FROM admin WHERE user_id = $admin_id";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $admin = mysqli_fetch_assoc($result);
} else {
    // Jika admin tidak ditemukan (harusnya tidak terjadi)
    echo "Data admin tidak ditemukan.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Profil <?= ucfirst($labelRole) ?> - Tracer Study</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
   <link href="styless.css?v=1.0" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

</head>
<body>

<?php include 'header.php'; ?>

  <!-- Sidebar -->
  <nav class="sidebar">
    <?php include 'sidebar.php'; ?>
  </nav>

 <main>
        <div class="page-header fade-in-up">
            <h2>
                <i class="bi bi-speedometer2"></i>
                Profil Admin
            </h2>
            <p class="mb-0 text-muted">Selamat datang, <?= htmlspecialchars($nama) ?>! Terima kasih telah menjaga keberlangsungan tracer study demi kemajuan Fakultas dan para alumni.</p>
          </div>

  <!-- Profile Information Card -->

    <div class="row">
      <div class="col-md-4 text-center">
       <img src="images/<?= htmlspecialchars($admin['foto'] ?? 'default.jpg') ?>" alt="Foto Admin" class="profile-pic mb-3" />

        <h4 class="text-primary"><?= htmlspecialchars($admin['nama_admin']) ?></h4>
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
          <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Informasi Admin</h5>
          
          <div class="info-item">
            <i class="bi bi-person-fill"></i>
            <div>
              <strong>Nama Lengkap:</strong><br>
              <?= htmlspecialchars($admin['nama'] ?? $admin['nama_admin']) ?>
            </div>
          </div>
          
          <div class="info-item">
            <i class="bi bi-envelope-fill"></i>
            <div>
              <strong>Email:</strong><br>
              <?= htmlspecialchars($admin['email']) ?>
            </div>
          </div>
          
          <div class="info-item">
            <i class="bi bi-calendar-event"></i>
            <div>
              <strong>Bergabung:</strong><br>
              <?= isset($admin['created_at']) ? date('d F Y', strtotime($admin['created_at'])) : 'Tidak tersedia' ?>
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
    </div>

  <!-- Edit Profile Form -->
  <div class="form-section" id="formEdit">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h5 class="text-primary mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Profil</h5>
      <button type="button" class="btn-close" onclick="toggleForm('formEdit')"></button>
    </div>
    
    <form action="update-profil.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $admin['user_id'] ?>" />
      
      <div class="row g-3">
        <div class="col-md-6">
          <label for="nama" class="form-label fw-bold">
            <i class="bi bi-person me-2"></i>Nama Lengkap
          </label>
          <input type="text" id="nama" name="nama" class="form-control form-control-custom" 
                 value="<?= htmlspecialchars($admin['nama_admin'] ?? $admin['nama_admin']) ?>" required />
        </div>
        
        <div class="col-md-6">
          <label for="email" class="form-label fw-bold">
            <i class="bi bi-envelope me-2"></i>Alamat Email
          </label>
          <input type="email" id="email" name="email" class="form-control form-control-custom" 
                 value="<?= htmlspecialchars($admin['email']) ?>" required />
        </div>
      </div>

      <div class="mt-4">
        <label for="foto" class="form-label fw-bold">
          <i class="bi bi-camera me-2"></i>Foto Profil
        </label>
        <input type="file" id="foto" name="foto" class="form-control form-control-custom" 
               accept="image/*" onchange="previewImage(event)" />
        
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
    
    <form action="ubah-password.php" method="POST">
      <input type="hidden" name="id" value="<?= $admin['user_id'] ?>" />
      
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

<?php if (isset($_GET['update']) && $_GET['update'] === 'password_success'): ?>
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="toastSuccess" class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          <i class="bi bi-check-circle-fill me-2"></i>
          Password berhasil diubah!
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>
<?php endif; ?>
<?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
  <div class="alert alert-success alert-dismissible fade show mt-3 mx-3" role="alert">
    <strong>Berhasil!</strong> Data profil berhasil diperbarui.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
</main>
<script>
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

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>