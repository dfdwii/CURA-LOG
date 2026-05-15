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
<?php include '../tampilan/header.php'; ?>

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
                        <h2 class="m-0">Tambah Alat Baru</h2>
                        <p class="text-muted m-0 mt-1" style="font-size:13px;">Lengkapi data alat medis yang akan ditambahkan ke inventaris.</p>
                    </div>
                </div>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-section-card">
                    <div class="form-section-header">
                        <div class="icon-wrap"><i class="bi bi-clipboard-pulse"></i></div>
                        <div>
                            <h5>Informasi Alat Medis</h5>
                            <p>Isi nama, merk, dan kategori alat</p>
                        </div>
                    </div>
                    <div class="form-section-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Alat <span class="text-danger">*</span></label>
                                <input type="text" name="nama_alat" class="form-control" placeholder="Contoh: Nebulizer" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Merk <span class="text-danger">*</span></label>
                                <input type="text" name="merk" class="form-control" placeholder="Contoh: Omron, Philips" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori</label>
                                <select name="kategori" class="form-select">
                                    <option value="Alat Diagnostik">Alat Diagnostik</option>
                                    <option value="Alat Terapi">Alat Terapi</option>
                                    <option value="Alat Bedah">Alat Bedah</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="1" placeholder="Catatan tambahan (opsional)"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-section-card">
                    <div class="form-section-header">
                        <div class="icon-wrap"><i class="bi bi-image"></i></div>
                        <div>
                            <h5>Foto Alat</h5>
                            <p>Upload gambar alat medis (JPG / PNG / WebP)</p>
                        </div>
                    </div>
                    <div class="form-section-body">
                        <div class="upload-zone" id="uploadZone">
                            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" required
                                   onchange="previewFoto(this)">
                            <span class="upload-icon"><i class="bi bi-cloud-arrow-up"></i></span>
                            <p id="uploadLabel"><strong>Klik untuk pilih file</strong> atau seret & lepas di sini</p>
                            <p class="mt-1" style="font-size:12px;">Format: JPG, PNG, WebP &bull; Maks. 5 MB</p>
                        </div>
                        <div id="previewWrap" class="mt-3 text-center" style="display:none;">
                            <img id="previewImg" src="" alt="Preview" style="max-height:180px; border-radius:12px; border:2px solid var(--teal-mid); box-shadow:var(--shadow-sm);">
                            <p class="mt-2 text-muted" style="font-size:13px;" id="previewName"></p>
                        </div>
                    </div>
                    <div class="form-actions">
                        <a href="inventory.php" class="btn btn-secondary px-4">
                            <i class="bi bi-x-lg me-1"></i> Batal
                        </a>
                        <button type="submit" name="simpan" class="btn btn-success px-4">
                            <i class="bi bi-check-lg me-1"></i> Simpan Alat
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
