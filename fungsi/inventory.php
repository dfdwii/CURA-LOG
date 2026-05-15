<?php
require_once '../config.php';
require_once '../auth_check.php';
global $koneksi;

$role = $_SESSION['role']; 

$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$f_status = isset($_GET['f_status']) ? mysqli_real_escape_string($koneksi, $_GET['f_status']) : '';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$url_query = '';
if ($search != '') $url_query .= "&search=" . urlencode($search);
if ($f_status != '') $url_query .= "&f_status=" . urlencode($f_status);

if (isset($_POST['simpan_status'])) {
    $id_alat = $_POST['id_alat_status'];
    $status_baru = $_POST['status_baru'];
    
    mysqli_query($koneksi, "UPDATE alat SET status='$status_baru' WHERE id_alat='$id_alat'");
    
    if ($status_baru == 'Tersedia') {
        mysqli_query($koneksi, "UPDATE history_peminjaman SET status_peminjaman='Dikembalikan', tgl_kembali=NOW() 
                                WHERE id_alat='$id_alat' AND status_peminjaman='Dipinjam'");
    }
    echo "<script>alert('Status alat berhasil diperbarui!'); window.location='inventory.php';</script>";
}

include '../tampilan/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../tampilan/sidebar.php'; ?>

        <div class="main-content px-4 pt-0">

            <div class="sticky-top pt-4 pb-3 mb-3" style="z-index:10;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="m-0">Inventaris Alat Medis</h2>
                        <p class="text-muted m-0 mt-1" style="font-size:13.5px;">Kelola seluruh alat medis rumah sakit</p>
                    </div>
                    <?php if ($role != 'dokter') { ?>
                        <a href="tambah_alat.php" class="btn btn-primary px-4">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Alat
                        </a>
                    <?php } ?>
                </div>
                <form method="GET" class="filter-bar">
                    <div class="filter-bar-inner">
                        <div class="filter-input-wrap">
                            <i class="bi bi-search filter-icon"></i>
                            <input type="text" name="search" class="form-control filter-input" placeholder="Cari nama alat atau merk..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="filter-select-wrap">
                            <i class="bi bi-search filter-icon"></i>
                            <select name="f_status" class="form-select filter-select">
                                <option value="">Semua Status</option>
                                <option value="Tersedia" <?php echo ($f_status == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                <option value="Dipinjam" <?php echo ($f_status == 'Dipinjam') ? 'selected' : ''; ?>>Dipinjam</option>
                                <option value="Rusak" <?php echo ($f_status == 'Rusak') ? 'selected' : ''; ?>>Rusak</option>
                                <option value="Maintenance" <?php echo ($f_status == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                <option value="Perlu Kalibrasi" <?php echo ($f_status == 'Perlu Kalibrasi') ? 'selected' : ''; ?>>Perlu Kalibrasi</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <?php if ($search || $f_status) { ?>
                            <a href="inventory.php" class="btn btn-secondary">Reset</a>
                        <?php } ?>
                    </div>
                </form>
            </div>

            <div class="form-section-card" style="max-width:100%;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Foto</th>
                                <th>Nama Alat</th>
                                <th>Merk</th>
                                 <th>Tgl Masuk</th>
                                <th>Status</th>
                                <th class="text-center pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $where = "WHERE 1=1";
                            if ($search != '') $where .= " AND (nama_alat LIKE '%$search%' OR merk LIKE '%$search%')";
                            if ($f_status != '') $where .= " AND status = '$f_status'";
                            $q_count = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM alat $where");
                            $row_count = mysqli_fetch_assoc($q_count);
                            $total_data = $row_count['total'];
                            $total_pages = ceil($total_data / $limit);
                            $sql = "SELECT * FROM alat $where ORDER BY id_alat DESC LIMIT $limit OFFSET $offset";
                            $query = mysqli_query($koneksi, $sql);
                            
                           if (mysqli_num_rows($query) == 0) {
                                echo "<tr><td colspan='6' class='text-center py-5 text-muted'><i class='bi bi-inbox fs-2 d-block mb-2'></i>Data alat tidak ditemukan.</td></tr>";
                            } else {
                                while($data = mysqli_fetch_array($query)) {
                                    $warna = 'badge-status-secondary';
                                    if ($data['status'] == 'Tersedia') $warna = 'badge-status-success';
                                    if ($data['status'] == 'Dipinjam') $warna = 'badge-status-warning';
                                    if ($data['status'] == 'Rusak') $warna = 'badge-status-danger';
                                    if ($data['status'] == 'Perlu Kalibrasi') $warna = 'badge-status-info';
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <img src="../assets/img/alat_medis/<?php echo htmlspecialchars($data['gambar']); ?>" class="img-alat">
                                </td>
                                <td><strong><?php echo htmlspecialchars($data['nama_alat']); ?></strong></td>
                                <td class="text-muted"><?php echo htmlspecialchars($data['merk']); ?></td>
                                <td><small class="text-muted"><?php echo date('d M Y', strtotime($data['tgl_masuk'])); ?></small></td>
                                <td><span class="badge <?php echo $warna; ?>"><?php echo htmlspecialchars($data['status']); ?></span></td>
                                <td class="text-center pe-3">
                                    <div class="d-flex gap-1 justify-content-center flex-wrap">
                                        <button class="btn btn-sm btn-outline-teal" onclick="lihatDetail('<?php echo htmlspecialchars($data['nama_alat']); ?>', '<?php echo htmlspecialchars($data['merk']); ?>', '<?php echo htmlspecialchars($data['keterangan']); ?>')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($role != 'dokter') { ?>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="bukaStatus('<?php echo $data['id_alat']; ?>', '<?php echo htmlspecialchars($data['nama_alat']); ?>', '<?php echo htmlspecialchars($data['status']); ?>')">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <a href="edit_alat.php?id=<?php echo $data['id_alat']; ?>" class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="hapus_alat.php?id=<?php echo $data['id_alat']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus alat ini?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php } else if ($data['status'] == 'Tersedia') { ?>
                                            <button class="btn btn-sm btn-success" onclick="bukaPinjam('<?php echo $data['id_alat']; ?>', '<?php echo htmlspecialchars($data['nama_alat']); ?>')">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>Pinjam
                                            </button>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                            <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($total_pages > 1) { ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-end">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $url_query; ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $url_query; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php } ?>
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $url_query; ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php } ?>
       </div>
    </div>
</div>

<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--teal-dark),var(--teal));color:#fff;">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-wrap" style="width:32px;height:32px;font-size:14px;background:rgba(255,255,255,.18);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <h5 class="modal-title mb-0" style="color:#fff;">Ubah Status Alat</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_alat_status" id="statusId">
                    <div class="mb-3">
                        <label class="form-label">Nama Alat</label>
                        <input type="text" id="statusNama" class="form-control" style="background:var(--teal-light);font-weight:600;" readonly>
                    </div>
                    <div>
                        <label class="form-label">Status Baru</label>
                        <select name="status_baru" id="statusPilihan" class="form-select" required>
                            <option value="Tersedia"> Tersedia (Kembali)</option>
                            <option value="Rusak"> Rusak</option>
                           <option value="Maintenance"> Maintenance</option>
                            <option value="Perlu Kalibrasi"> Perlu Kalibrasi</option>
                            <option value="Dipinjam" id="opsiDipinjam" hidden> Dipinjam</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_status" class="btn btn-primary px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--teal-dark),var(--teal));color:#fff;">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-wrap" style="width:32px;height:32px;font-size:14px;background:rgba(255,255,255,.18);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-clipboard-pulse"></i>
                    </div>
                    <h5 class="modal-title mb-0" style="color:#fff;">Detail Alat</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="detail-row"><span class="detail-label">Nama Alat</span><span id="detNama" class="detail-value"></span></div>
                <div class="detail-row"><span class="detail-label">Merk</span><span id="detMerk" class="detail-value"></span></div>
                <div class="detail-row border-0"><span class="detail-label">Keterangan</span><span id="detKet" class="detail-value"></span></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalPinjam" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-wrap" style="width:32px;height:32px;font-size:14px;background:rgba(255,255,255,.18);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </div>
                    <h5 class="modal-title mb-0" style="color:#fff;">Pinjam Alat</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
            <form action="proses_pinjam.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="id_alat" id="pinjamId">
                    <div class="mb-3">
                        <label class="form-label">Nama Alat</label>
                        <input type="text" id="pinjamNama" class="form-control" style="background:var(--teal-light);font-weight:600;" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keperluan <span class="text-danger">*</span></label>
                        <input type="text" name="keperluan" class="form-control" placeholder="Contoh: Pemeriksaan pasien" required>
                     </div>
                    <div>
                        <label class="form-label">Ruangan Tujuan <span class="text-danger">*</span></label>
                        <input type="text" name="ruangan_tujuan" class="form-control" placeholder="Contoh: IGD, Poli Umum" required>
                    </div>
                </div>
                <div class="form-actions pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="pinjam" class="btn btn-success px-4">
                        <i class="bi bi-check-lg me-1"></i> Konfirmasi Pinjam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function bukaStatus(id, nama, statusSekarang) {
    document.getElementById('statusId').value = id;
    document.getElementById('statusNama').value = nama;
    const select = document.getElementById('statusPilihan');
    const opsiDipinjam = document.getElementById('opsiDipinjam');
    opsiDipinjam.hidden = statusSekarang !== 'Dipinjam';
    select.value = statusSekarang;
    new bootstrap.Modal(document.getElementById('modalStatus')).show();
}

function lihatDetail(nama, merk, ket) {
    document.getElementById('detNama').innerText = nama;
    document.getElementById('detMerk').innerText = merk;
    document.getElementById('detKet').innerText = ket || '-';
    new bootstrap.Modal(document.getElementById('modalDetail')).show();
}

function bukaPinjam(id, nama) {
    document.getElementById('pinjamId').value = id;
    document.getElementById('pinjamNama').value = nama;
    new bootstrap.Modal(document.getElementById('modalPinjam')).show();
}
</script>

<?php include '../tampilan/footer.php'; ?>
