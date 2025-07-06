<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klaim Akun - Tracer Study FIKOM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(to right, #1f2d5c, #3c4e9e);
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .klaim-box {
            background-color: #fff;
            padding: 35px 40px;
            border-radius: 10px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }

        .klaim-box h3 {
            text-align: center;
            margin-bottom: 25px;
            color: #1f2d5c;
        }

        .btn-klaim {
            background-color: #1f2d5c;
            color: white;
            font-weight: bold;
        }

        .btn-klaim:hover {
            background-color: #162147;
        }

        .form-label {
            font-weight: 500;
        }

        small.feedback {
            font-size: 13px;
        }

        .valid-feedback {
            color: green;
        }

        .invalid-feedback {
            color: red;
        }
    </style>
</head>
<body>

<div class="klaim-box">
    <h3>Klaim Akun Alumni</h3>

    <form action="proses-klaim.php" method="POST" onsubmit="return validateForm();">
        <div class="mb-3">
            <label for="npm" class="form-label">NPM</label>
            <input type="text" class="form-control" id="npm" name="npm" required pattern="^\d{8}$" onkeyup="checkNPM()">
            <small id="npm-feedback" class="feedback"></small>
        </div>

        <div class="mb-3">
            <label for="tahun_lulus" class="form-label">Tahun Lulus</label>
            <input type="number" class="form-control" id="tahun_lulus" name="tahun_lulus" required min="2000" max="2099" onkeyup="checkNPM()">
        </div>

        <div class="mb-3">
            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
        </div>

        <div class="mb-3">
            <label for="program_studi" class="form-label">Program Studi</label>
            <select class="form-select" id="program_studi" name="program_studi" required>
                <option value="">-- Pilih Program Studi --</option>
                <option value="Sistem Informasi">Sistem Informasi</option>
                <option value="Informatika">Informatika</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email Aktif</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
            <label for="no_hp" class="form-label">No. HP</label>
            <input type="text" class="form-control" id="no_hp" name="no_hp" required>
        </div>

        <div class="mb-3">
            <label for="nik" class="form-label">NIK (opsional)</label>
            <input type="text" class="form-control" id="nik" name="nik">
        </div>

        <div class="mb-3">
            <label for="npwp" class="form-label">NPWP (opsional)</label>
            <input type="text" class="form-control" id="npwp" name="npwp">
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required minlength="6">
        </div>

        <button type="submit" class="btn btn-klaim w-100" id="submitBtn">Klaim Akun</button>
    </form>

    <div class="text-center mt-3">
        <a href="login.php" class="text-decoration-none">← Kembali ke Login</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Validasi Form Client-side
function validateForm() {
    const npm = document.getElementById('npm').value;
    const tahun = document.getElementById('tahun_lulus').value;
    if (npm.length !== 8 || isNaN(npm)) {
        Swal.fire("NPM tidak valid", "NPM harus 8 digit angka.", "warning");
        return false;
    }
    if (tahun < 2000 || tahun > 2099) {
        Swal.fire("Tahun Lulus tidak valid", "Harap masukkan tahun yang benar.", "warning");
        return false;
    }
    return true;
}

document.querySelector("form").addEventListener("submit", function(e) {
    e.preventDefault(); // Cegah submit langsung

    const form = this;
    const npm = form.npm.value.trim();
    const tahun = form.tahun_lulus.value.trim();

    if (!validateForm()) return;

    // Kirim AJAX ke proses-klaim.php untuk cek data
    fetch("proses-klaim.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `ajax_check=1&npm=${encodeURIComponent(npm)}&tahun_lulus=${encodeURIComponent(tahun)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'error') {
            Swal.fire('Gagal', data.message, 'error');
        } else if (data.message.includes('diajukan')) {
            // Data tidak ada → konfirmasi sebelum kirim
            Swal.fire({
                title: 'Data belum ada di sistem',
                text: 'Apakah Anda ingin mengajukan data ke sistem untuk diverifikasi?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Ajukan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tambah input hidden untuk menandai pengajuan
                    const hidden = document.createElement("input");
                    hidden.type = "hidden";
                    hidden.name = "submit_ajuan";
                    hidden.value = "1";
                    form.appendChild(hidden);

                    form.submit();
                }
            });
        } else {
            // Data ditemukan & belum diklaim → lanjut
            form.submit();
        }
    })
    .catch(() => {
        Swal.fire('Error', 'Terjadi kesalahan saat memeriksa data.', 'error');
    });
});
</script>

</body>
</html>
