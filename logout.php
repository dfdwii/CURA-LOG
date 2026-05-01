<?php
require_once __DIR__ . '/config.php';
$nama = $_SESSION['nama_lengkap'] ?? 'Pengguna';
session_unset(); session_destroy(); session_start();
setToast('info', "Sampai jumpa, {$nama}! Anda berhasil keluar.");
header('Location: login.php'); exit;
