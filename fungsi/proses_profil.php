<?php
require_once '../config.php';
require_once '../auth_check.php';
global $koneksi;

if (isset($_FILES['foto_user'])) {
    $id_user = $_SESSION['user_id'];
    $nama_file = $_FILES['foto_user']['name'];
    $ukuran_file = $_FILES['foto_user']['size'];
    $tmp_file = $_FILES['foto_user']['tmp_name'];
    $ext_boleh = ['jpg', 'jpeg', 'png'];
    $ext_file = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    if (!in_array($ext_file, $ext_boleh)) {
        echo "<script>alert('Format file harus JPG atau PNG!'); window.history.back();</script>";
        exit;
    }
    if ($ukuran_file > 2000000) {
        echo "<script>alert('Ukuran file maksimal 2MB!'); window.history.back();</script>";
        exit;
    }
    $nama_baru = "user_" . $id_user . "_" . time() . "." . $ext_file;
    $tujuan = "../assets/img/users/" . $nama_baru;
    $cek_lama = mysqli_query($koneksi, "SELECT foto FROM users WHERE id='$id_user'");
    $data_lama = mysqli_fetch_array($cek_lama);
    if (!empty($data_lama['foto'])) {
        if (file_exists("../assets/img/users/" . $data_lama['foto'])) {
            unlink("../assets/img/users/" . $data_lama['foto']);
        }
    }
    if (move_uploaded_file($tmp_file, $tujuan)) {
        mysqli_query($koneksi, "UPDATE users SET foto='$nama_baru' WHERE id='$id_user'");
        $_SESSION['foto'] = $nama_baru;
        echo "<script>alert('Foto profil berhasil diperbarui!'); window.location='../index.php';</script>";
    } else {
        echo "<script>alert('Gagal mengunggah file!'); window.history.back();</script>";
    }
}
?>