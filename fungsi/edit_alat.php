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

<div class="container-fluid">
    <div class="row">
        <?php include '../tampilan/sidebar.php'; ?>
        <div class="main-content px-4 pt-0">
            <div class="sticky-top pt-4 pb-3 mb-3" style="z-index:10;">
                <div class="d-flex align-items-center gap-2">
                    <a href="inventory.php" class="btn btn-sm btn-secondary px-3">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                    <div>
                        <h2 class="m-0">Edit Alat Medis</h2>
                        <p class="text-muted m-0 mt-1" style="font-size:13px;">Perbarui informasi alat: <strong><?php echo htmlspecialchars($data['nama_alat']); ?></strong></p>
                    </div>
                </div>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-section-card">
                    <div class="form-section-header">
                        <div class="icon-wrap"><i class="bi bi-pencil-square"></i></div>
                        <div>
                            <h5>Informasi Alat</h5>
                            <p>Ubah nama, merk, kategori, dan status</p>
                        </div>
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Alat <span class="text-danger">*</span></label>
                                <input type="text" name="nama_alat" class="form-control" value="<?php echo htmlspecialchars($data['nama_alat']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Merk <span class="text-danger">*</span></label>
                                <input type="text" name="merk" class="form-control" value="<?php echo htmlspecialchars($data['merk']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori</label>
                                <select name="kategori" class="form-select">
                                    <option value="Alat Diagnostik" <?php if($data['kategori'] == 'Alat Diagnostik') echo 'selected'; ?>>Alat Diagnostik</option>
                                    <option value="Alat Terapi" <?php if($data['kategori'] == 'Alat Terapi') echo 'selected'; ?>>Alat Terapi</option>
                                    <option value="Alat Bedah" <?php if($data['kategori'] == 'Alat Bedah') echo 'selected'; ?>>Alat Bedah</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="Tersedia" <?php if($data['status']=='Tersedia') echo 'selected'; ?>>Tersedia</option>
                                    <option value="Rusak" <?php if($data['status']=='Rusak') echo 'selected'; ?>>Rusak</option>
                                    <option value="Maintenance" <?php if($data['status']=='Maintenance') echo 'selected'; ?>>Maintenance</option>
                                    <option value="Perlu Kalibrasi" <?php if($data['status']=='Perlu Kalibrasi') echo 'selected'; ?>>Perlu Kalibrasi</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan (opsional)"><?php echo htmlspecialchars($data['keterangan']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-section-card">
                    <div class="form-section-header">
                        <div class="icon-wrap"><i class="bi bi-image"></i></div>
                        <div>
                            <h5>Foto Alat</h5>
                            <p>Kosongkan jika tidak ingin mengganti foto</p>
                        </div>
                    </div>
                    <div class="form-section-body">
                        <?php if (!empty($data['gambar'])) { ?>
                        <div class="mb-3 d-flex align-items-center gap-3">
                            <img src="../assets/img/alat_medis/<?php echo htmlspecialchars($data['gambar']); ?>"
                                 style="width:80px;height:80px;object-fit:cover;border-radius:12px;border:2px solid var(--teal-mid);">
                            <div>
                                <div class="fw-600" style="font-size:13px;">Foto saat ini</div>
                                <div class="text-muted" style="font-size:12px;"><?php echo htmlspecialchars($data['gambar']); ?></div>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="upload-zone" id="uploadZone">
                            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" onchange="previewFoto(this)">
                            <span class="upload-icon"><i class="bi bi-cloud-arrow-up"></i></span>
                            <p id="uploadLabel"><strong>Klik untuk pilih foto baru</strong> atau seret & lepas</p>
                            <p class="mt-1" style="font-size:12px;">Format: JPG, PNG, WebP</p>
                        </div>
                        <div id="previewWrap" class="mt-3 text-center" style="display:none;">
                            <img id="previewImg" src="" alt="Preview" style="max-height:160px;border-radius:12px;border:2px solid var(--teal-mid);box-shadow:var(--shadow-sm);">
                            <p class="mt-2 text-muted" style="font-size:13px;" id="previewName"></p>
                        </div>
                    </div>
                    <div class="form-actions">
                        <a href="inventory.php" class="btn btn-secondary px-4">
                            <i class="bi bi-x-lg me-1"></i> Batal
                        </a>
                        <button type="submit" name="update" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewFoto(input) {
    const wrap = document.getElementById('previewWrap');
    const img  = document.getElementById('previewImg');
    const name = document.getElementById('previewName');
    const label= document.getElementById('uploadLabel');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result;
            wrap.style.display = 'block';
            name.textContent = input.files[0].name;
            label.innerHTML = '<strong>File dipilih</strong> — klik untuk ganti';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../tampilan/footer.php'; ?>
