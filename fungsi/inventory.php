<?php
require_once '../config.php';
require_once '../auth_check.php';

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

        <div class="col-md-10 offset-md-2 px-4 pt-0" style="padding-bottom: 80px;">
            
            <div class="sticky-top pt-4 pb-3 mb-3" style="background-color: #f8fafc; z-index: 10;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="m-0">Inventaris Alat Medis</h2>
                    <?php if ($role != 'dokter') { ?>
                        <a href="tambah_alat.php" class="btn btn-primary"> + Tambah Alat</a>
                    <?php } ?>
                </div>

                <form method="GET" class="row g-2 bg-white p-3 rounded shadow-sm border">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama alat atau merk..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="f_status" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="Tersedia" <?php echo ($f_status == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                            <option value="Dipinjam" <?php echo ($f_status == 'Dipinjam') ? 'selected' : ''; ?>>Dipinjam</option>
                            <option value="Rusak" <?php echo ($f_status == 'Rusak') ? 'selected' : ''; ?>>Rusak</option>
                            <option value="Maintenance" <?php echo ($f_status == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="Perlu Kalibrasi" <?php echo ($f_status == 'Perlu Kalibrasi') ? 'selected' : ''; ?>>Perlu Kalibrasi</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-secondary w-100">Cari & Filter</button>
                    </div>
                </form>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Foto</th>
                                <th>Nama Alat</th>
                                <th>Merk</th>
                                <th>Tgl Masuk</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
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
                                echo "<tr><td colspan='6' class='text-center py-5 text-muted'><i class='bi bi-inbox fs-2 d-block mb-2 text-secondary'></i>Belum ada data alat medis.</td></tr>";
                            } else {
                                while($data = mysqli_fetch_array($query)) {
                                    $warna = 'bg-secondary';
                                    if ($data['status'] == 'Tersedia') $warna = 'bg-success';
                                    if ($data['status'] == 'Dipinjam') $warna = 'bg-warning text-dark';
                                    if ($data['status'] == 'Rusak') $warna = 'bg-danger';
                                    if ($data['status'] == 'Perlu Kalibrasi') $warna = 'bg-info text-white';
                            ?>
                            <tr>
                                <td class="ps-3"><img src="../assets/img/alat_medis/<?php echo $data['gambar']; ?>" style="width:40px;height:40px;object-fit:cover;border-radius:5px;"></td>
                                <td><strong><?php echo $data['nama_alat']; ?></strong></td>
                                <td><?php echo $data['merk']; ?></td>
                                <td><small class="text-muted"><?php echo date('d M Y', strtotime($data['tgl_masuk'])); ?></small></td>
                                <td><span class="badge <?php echo $warna; ?>"><?php echo $data['status']; ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-sm text-white" onclick="lihatDetail('<?php echo $data['nama_alat']; ?>', '<?php echo $data['merk']; ?>', '<?php echo htmlspecialchars($data['keterangan']); ?>')">Detail</button>
                                    <?php if ($role != 'dokter') { ?>
                                        <button class="btn btn-secondary btn-sm" onclick="bukaStatus('<?php echo $data['id_alat']; ?>', '<?php echo htmlspecialchars($data['nama_alat']); ?>', '<?php echo $data['status']; ?>')">Status</button>
                                        <a href="edit_alat.php?id=<?php echo $data['id_alat']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="hapus_alat.php?id=<?php echo $data['id_alat']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Hapus</a>
                                    <?php } else if ($data['status'] == 'Tersedia') { ?>
                                        <button class="btn btn-success btn-sm" onclick="bukaPinjam('<?php echo $data['id_alat']; ?>', '<?php echo htmlspecialchars($data['nama_alat']); ?>')">Pinjam</button>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($total_pages > 1) { ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-end">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $url_query; ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $url_query; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php } ?>
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $url_query; ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
            <?php } ?>

        </div>
    </div>
</div>

<?php include '../tampilan/footer.php'; ?>