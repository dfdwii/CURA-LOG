<?php
require_once 'config.php';

if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['login'])) {
    $user = input($_POST['username']);
    $pass = md5(input($_POST['password']));

    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$user' AND password='$pass'");
    
    if (mysqli_num_rows($cek) > 0) {
        $data = mysqli_fetch_array($cek);
        $_SESSION['user_id'] = $data['id'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['nama'] = $data['nama_lengkap'];
        $_SESSION['role'] = $data['role'];
        
        header("Location: index.php");
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CURA-LOG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/style.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4" style="margin-top: 80px;">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h3 class="text-center fw-bold text-primary">Login CURA-LOG</h3>
                    <p class="text-center text-muted">Silakan masuk ke akun Anda</p>
                    <hr>
                    
                    <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mt-4">
                            <button type="submit" name="login" class="btn btn-primary w-100">Masuk</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p class="small">Belum punya akun? <a href="registrasi.php">Daftar Dokter</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>