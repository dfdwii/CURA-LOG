<?php
require_once '../config.php';
require_once '../auth_check.php';
global $koneksi;

$id = $_GET['id'];
$ambil = mysqli_query($koneksi, "SELECT * FROM alat WHERE id_alat='$id'");
$data = mysqli_fetch_array($ambil);

if (isset($_POST['update'])) {
    $nama = input($_POST['nama_alat']);
    $merk = input($_POST['merk']);
    $kategori = input($_POST['kategori']);
    $status = input($_POST['status']);
    $ket = input($_POST['keterangan']);

    if ($_FILES['foto']['name'] != "") {
        $foto = $_FILES['foto']['name'];
        $tmp = $_FILES['foto']['tmp_name'];
        move_uploaded_file($tmp, "../assets/img/alat_medis/" . $foto);
        $sql = "UPDATE alat SET nama_alat='$nama', merk='$merk', kategori='$kategori', status='$status', keterangan='$ket', gambar='$foto' WHERE id_alat='$id'";
    } else {
        $sql = "UPDATE alat SET nama_alat='$nama', merk='$merk', kategori='$kategori', status='$status', keterangan='$ket' WHERE id_alat='$id'";
    }

    if (mysqli_query($koneksi, $sql)) {
        echo "<script>alert('Data berhasil diubah!'); window.location='inventory.php';</script>";
    }
}

include '../tampilan/header.php';
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-warning text-dark"><h5>Edit Alat Medis</h5></div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label>Nama Alat</label>
                    <input type="text" name="nama_alat" class="form-control" value="<?php echo $data['nama_alat']; ?>" required>
                </div>
                <div class="mb-3">
                    <label>Merk</label>
                    <input type="text" name="merk" class="form-control" value="<?php echo $data['merk']; ?>" required>
                </div>
                <div class="mb-3">
                    <label>Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value="Alat Diagnostik" <?php if($data['kategori'] == 'Alat Diagnostik') echo 'selected'; ?>>Alat Diagnostik</option>
                        <option value="Alat Terapi" <?php if($data['kategori'] == 'Alat Terapi') echo 'selected'; ?>>Alat Terapi</option>
                        <option value="Alat Bedah" <?php if($data['kategori'] == 'Alat Bedah') echo 'selected'; ?>>Alat Bedah</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select" required>
                        <option value="Tersedia">Tersedia</option>
                        <option value="Rusak">Rusak</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Perlu Kalibrasi">Perlu Kalibrasi</option> </select>
                </div>
                <div class="mb-3">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control"><?php echo $data['keterangan']; ?></textarea>
                </div>
                <div class="mb-3">
                    <label>Ganti Foto (Kosongkan jika tidak diganti)</label>
                    <input type="file" name="foto" class="form-control">
                </div>
                <button type="submit" name="update" class="btn btn-primary">Simpan Perubahan</button>
                <a href="inventory.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<?php include '../tampilan/footer.php'; ?>