<?php
require_once '../config.php';
require_once '../auth_check.php';
global $koneksi;
if (!isset($_SESSION['username'])) { header("Location: ../login.php"); exit; }

if (isset($_POST['simpan'])) {
    $nama     = input($_POST['nama_alat']);
    $merk     = input($_POST['merk']);
    $kategori = input($_POST['kategori']);
    $ket      = input($_POST['keterangan']);
    
    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];
    $path = "../assets/img/alat_medis/" . $foto;

    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['foto']['tmp_name']);

    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $nama_file = uniqid() . '.' . $ext;

    if (!in_array($mime, $allowed)) { 
        die("Tipe file tidak diizinkan!"); 
    }

    if (move_uploaded_file($tmp, $path)) {
        $sql = "INSERT INTO alat (nama_alat, merk, kategori, keterangan, gambar, status) 
                VALUES ('$nama', '$merk', '$kategori', '$ket', '$foto', 'Tersedia')";
        
        if (mysqli_query($koneksi, $sql)) {
            echo "<script>alert('Alat berhasil ditambah!'); window.location='inventory.php';</script>";
        }
    }
}
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white"><h5>Tambah Alat Baru</h5></div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label>Nama Alat</label>
                    <input type="text" name="nama_alat" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Merk</label>
                    <input type="text" name="merk" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value="Alat Diagnostik">Alat Diagnostik</option>
                        <option value="Alat Terapi">Alat Terapi</option>
                        <option value="Alat Bedah">Alat Bedah</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label>Foto Alat</label>
                    <input type="file" name="foto" class="form-control" required>
                </div>
                <button type="submit" name="simpan" class="btn btn-success">Simpan Alat</button>
                <a href="inventory.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>