<?php
require_once 'config.php';

if (isset($_POST['daftar'])) {
    $user = input($_POST['username']);
    $nama = input($_POST['nama_lengkap']);
    $pass_raw = input($_POST['password']);
    $pass_conf = input($_POST['konfirmasi_password']);
    $role = 'dokter';
    if ($pass_raw !== $pass_conf) {
        $error = "Pendaftaran gagal! Password dan Konfirmasi Password tidak cocok.";
    } else {
        $pass = md5($pass_raw);
        $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$user'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Username sudah terdaftar, silakan gunakan NIP/ID yang lain!";
        } else {
            $sql = "INSERT INTO users (username, password, nama_lengkap, role) 
                    VALUES ('$user', '$pass', '$nama', '$role')";
            if (mysqli_query($koneksi, $sql)) {
                echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
            } else {
                $error = "Terjadi kesalahan saat mendaftar database.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Dokter - CURA-LOG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/style.css">
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 card-regis">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h3 class="text-center fw-bold text-primary">Daftar Akun Dokter</h3>
                    <p class="text-center text-muted">Silakan lengkapi data untuk membuat akun baru.</p>
                    <hr>
                    <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: Dr. Daffa Fadhil" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username (NIP/ID)</label>
                            <input type="text" name="username" class="form-control" placeholder="Contoh: 200106" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-control" required>
                                <option value="">Pilih Role</option>
                                <option value="dokter">Dokter</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="regPassword" class="form-control" placeholder="Masukkan password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleRegPassword">
                                    <i class="bi bi-eye-slash" id="iconRegPass"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <div class="input-group">
                                <input type="password" name="konfirmasi_password" id="regConfirmPassword" class="form-control" placeholder="Ulangi password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleRegConfirm">
                                    <i class="bi bi-eye-slash" id="iconRegConf"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" name="daftar" class="btn btn-primary w-100">Daftar Sekarang</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <p class="small">Sudah punya akun? <a href="login.php">Login di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('toggleRegPassword').addEventListener('click', function () {
        const input = document.getElementById('regPassword');
        const icon = document.getElementById('iconRegPass');
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });

    document.getElementById('toggleRegConfirm').addEventListener('click', function () {
        const input = document.getElementById('regConfirmPassword');
        const icon = document.getElementById('iconRegConf');
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });
</script>
</body>
</html>