<?php
require_once '../config.php';
require_once '../auth_check.php';

if (isset($_POST['pinjam'])) {
    global $koneksi;
    $id_alat = $_POST['id_alat'];
    $id_user = $_SESSION['user_id'];
    $keperluan = input($_POST['keperluan']);
    $tujuan = input($_POST['ruangan_tujuan']);
    $sql_histori = "INSERT INTO history_peminjaman (id_alat, id_user, tgl_pinjam, keperluan, ruangan_tujuan, status_peminjaman) 
                    VALUES ('$id_alat', '$id_user', NOW(), '$keperluan', '$tujuan', 'Dipinjam')";
    
    $sql_alat = "UPDATE alat SET status='Dipinjam' WHERE id_alat='$id_alat'";
    if (mysqli_query($koneksi, $sql_histori) && mysqli_query($koneksi, $sql_alat)) {
        echo "<script>alert('Peminjaman berhasil!'); window.location='history.php';</script>";
    } else {
        echo "<script>alert('Gagal memproses!'); window.location='inventory.php';</script>";
    }
}
?>