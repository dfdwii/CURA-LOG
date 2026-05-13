<?php
require_once 'config.php';

if (isset($_POST['daftar'])) {
    $user = input($_POST['username']);
    $nama = input($_POST['nama_lengkap']);
    $pass = md5(input($_POST['password']));
    $role = 'dokter';

    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$user'");
    
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah terdaftar, silakan gunakan yang lain!";
    } else {
        $sql = "INSERT INTO users (username, password, nama_lengkap, role) 
                VALUES ('$user', '$pass', '$nama', '$role')";
        
        if (mysqli_query($koneksi, $sql)) {
            echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
        } else {
            $error = "Terjadi kesalahan saat mendaftar.";
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
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
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
</body>
</html>