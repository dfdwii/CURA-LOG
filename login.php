<?php
session_start();
include 'config.php';

if(isset($_POST['login'])){
    $u = $_POST['username'];
    $p = md5($_POST['password']);

    $cek = mysqli_query($conn,"SELECT * FROM users WHERE username='$u' AND password='$p'");
    if(mysqli_num_rows($cek)>0){
        $_SESSION['login']=true;
        header("Location: index.php");
    } else {
        $error="Login gagal!";
    }
}
?>

<link rel="stylesheet" href="assets/style.css">

<div class="login-box">
<h2>🏥 Login Sistem</h2>
<?php if(isset($error)) echo $error; ?>
<form method="POST">
<input type="text" name="username" placeholder="Username">
<input type="password" name="password" placeholder="Password">
<button class="btn btn-primary" name="login">Login</button>
</form>
</div>