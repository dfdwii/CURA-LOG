<?php
require_once '../config.php';
global $koneksi;
if (!isset($_SESSION['username'])) { header("Location: ../login.php"); exit; }

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM alat WHERE id_alat='$id'");
    echo "<script>alert('Alat dihapus!'); window.location='inventory.php';</script>";
}
?>