<?php
require_once '../config.php';
require_once '../auth_check.php';

if (isset($_POST['ganti_pass'])) {
    $id_user = $_SESSION['user_id'];
    $pass_lama = md5(input($_POST['pass_lama']));
    $pass_baru = input($_POST['pass_baru']);
    $pass_konf = input($_POST['pass_konf']);

    $cek = mysqli_query($koneksi, "SELECT password FROM users WHERE id='$id_user'");
    $data = mysqli_fetch_array($cek);

    if ($data['password'] != $pass_lama) {
        echo "<script>alert('Gagal! Password lama yang Anda masukkan salah.'); window.history.back();</script>";
    } else if ($pass_baru != $pass_konf) {
        echo "<script>alert('Gagal! Konfirmasi password baru tidak cocok.'); window.history.back();</script>";
    } else {
        $pass_hash = md5($pass_baru);
        mysqli_query($koneksi, "UPDATE users SET password='$pass_hash' WHERE id='$id_user'");
        
        echo "<script>alert('Password berhasil diubah! Silakan login kembali dengan password baru.'); window.location='../logout.php';</script>";
    }
} else {
    header("Location: ../index.php");
}
?>