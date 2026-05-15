<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "inventaris";
$koneksi = mysqli_connect($host, $user, $pass, $db);
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
$base_url = "http://localhost/CURALOG/";
function input(string $data) {
    global $koneksi;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($koneksi, $data);
}
?>