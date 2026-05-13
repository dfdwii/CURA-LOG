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

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <h3 class="text-center">Login CURA-LOG</h3>
                    <hr>
                    <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">Masuk</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>