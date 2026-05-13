<?php
require_once '../config.php';
require_once '../auth_check.php';

if (isset($_POST['update_status'])) {
    global $koneksi;
    $id_alat = $_POST['id_alat'];
    $status = $_POST['status'];
    $kondisi = $_POST['kondisi'];

    $sql = "UPDATE alat SET status='$status', kondisi='$kondisi' WHERE id_alat='$id_alat'";

    if (mysqli_query($koneksi, $sql)) {
        echo "<script>alert('Status berhasil diperbarui!'); window.location='inventory.php';</script>";
    }
}
?>