
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tracer Study FIKOM</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ebebeb;
        }

        header {
            background-color: #023E8A;
            color: #fff;
            padding: 20px 0;
        }

        .header-content {
            display: flex;
            align-items: center;
            max-width: 1100px;
            margin: auto;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            margin-right: 15px;
        }

        .logo-text h1 {
            margin: 0;
            font-size: 20px;
        }

        .logo-text p {
            margin: 0;
            font-size: 14px;
        }

        .login-container {
            max-width: 420px;
            margin: 60px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #1f2d5c;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 500;
        }

        select, input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .btn-login {
            width: 100%;
            padding: 10px;
            background-color: #023E8A;
            color: #fff;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .btn-login:hover {
            background-color: #162147;
        }

        .login-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
        }

        .login-footer a {
            color: #007BFF;
            text-decoration: none;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>

</head>
<body>

    <!-- Header -->
    <header>
        <div class="header-content">
            <div class="logo">
                <img src="images/logoYapis.png" alt="Logo FIKOM UNIYAP" width="60" height="60">
                <div class="logo-text">
                    <h1>Tracer Study Fakultas Ilmu Komputer</h1>
                    <p>Universitas Yapis Papua</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Login Form -->
    <div class="login-container">
        <h2>Login</h2>
        <form action="proses_login.php" method="POST">
            <label for="role">Login Sebagai</label>
            <select name="role" id="role" onchange="updatePlaceholder()">
                <option value="alumni">Alumni</option>
                <option value="admin">Admin</option>
                <option value="fakultas">Fakultas / Prodi</option>
            </select>

            <label for="username" id="username-label">NPM</label>
            <input type="text" name="username" id="username" placeholder="Masukkan NPM" required>

            <label for="password">Password</label>
            <input type="password" name="password" placeholder="Masukkan Password" required>

            <button type="submit" class="btn-login">Login</button>
        </form>

             <div class="login-footer" id="alumni-info">
                 <p>Alumni belum pernah klaim akun? <a href="klaim_akun.php">Klaim Akun</a></p>
            </div>


    <!-- Script untuk mengubah placeholder sesuai role -->
   <script>
    function updatePlaceholder() {
        const role = document.getElementById("role").value;
        const usernameInput = document.getElementById("username");
        const label = document.getElementById("username-label");
        const alumniInfo = document.getElementById("alumni-info");

        if (role === "admin") {
            usernameInput.placeholder = "Masukkan Email";
            label.textContent = "Email";
            alumniInfo.style.display = "none"; // sembunyikan
        } else if (role === "fakultas") {
            usernameInput.placeholder = "Masukkan Email";
            label.textContent = "Email";
            alumniInfo.style.display = "none"; // sembunyikan
        } else {
            usernameInput.placeholder = "Masukkan NPM";
            label.textContent = "NPM";
            alumniInfo.style.display = "block"; // tampilkan
        }
    }

    // Panggil sekali saat halaman selesai load untuk set awal
    window.onload = updatePlaceholder;
</script>

<?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil Logout',
        text: 'Anda telah keluar dari sistem.',
        timer: 2500,
        showConfirmButton: false
    });
</script>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: '<?= htmlspecialchars($_GET['error']) ?>',
        timer: 3000,
        showConfirmButton: false
    });
</script>
<?php endif; ?>


</body>
</html>
