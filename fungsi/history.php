<?php
require_once '../config.php';
require_once '../auth_check.php';
global $koneksi;

$role = $_SESSION['role'];
$my_id = $_SESSION['user_id'];

$s_key = isset($_GET['s_key']) ? mysqli_real_escape_string($koneksi, $_GET['s_key']) : '';
$tgl_mulai = isset($_GET['tgl_mulai']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_mulai']) : '';
$tgl_akhir = isset($_GET['tgl_akhir']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_akhir']) : '';

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$url_query = '';
if ($s_key != '') $url_query .= "&s_key=" . urlencode($s_key);
if ($tgl_mulai != '') $url_query .= "&tgl_mulai=" . urlencode($tgl_mulai);
if ($tgl_akhir != '') $url_query .= "&tgl_akhir=" . urlencode($tgl_akhir);

if (isset($_GET['kembali'])) {
    $id_h = $_GET['kembali'];
    $id_a = $_GET['alat'];
    $cek = mysqli_query($koneksi, "SELECT id_user FROM history_peminjaman WHERE id_history='$id_h'");
    $data_h = mysqli_fetch_array($cek);

    if ($role == 'dokter' && $data_h['id_user'] != $my_id) {
        echo "<script>alert('Gagal! Anda hanya bisa mengembalikan alat yang Anda pinjam.'); window.location='history.php';</script>";
    } else {
        mysqli_query($koneksi, "UPDATE history_peminjaman SET status_peminjaman='Dikembalikan', tgl_kembali=NOW() WHERE id_history='$id_h'");
        mysqli_query($koneksi, "UPDATE alat SET status='Tersedia' WHERE id_alat='$id_a'");
        echo "<script>alert('Alat berhasil dikembalikan!'); window.location='history.php';</script>";
    }
}

include '../tampilan/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../tampilan/sidebar.php'; ?>

        <div class="main-content px-4 pt-0">

            <!-- Page Header -->
            <div class="sticky-top pt-4 pb-3 mb-3" style="z-index:10;">
                <div class="mb-3">
                    <h2 class="m-0"><?php echo ($role == 'dokter') ? 'Histori Peminjaman' : 'Laporan Peminjaman'; ?></h2>
                    <p class="text-muted m-0 mt-1" style="font-size:13.5px;">Riwayat seluruh transaksi peminjaman alat medis</p>
                </div>

                <!-- Filter bar -->
                <form method="GET" class="filter-bar">
                    <div class="filter-bar-inner flex-wrap gap-2">
                        <div class="filter-input-wrap" style="flex:1;min-width:180px;">
                            <i class="bi bi-search filter-icon"></i>
                            <input type="text" name="s_key" class="form-control filter-input" placeholder="Cari nama alat atau dokter..." value="<?php echo htmlspecialchars($s_key); ?>">
                        </div>
                        <input type="date" name="tgl_mulai" class="form-control" style="width:160px;" value="<?php echo htmlspecialchars($tgl_mulai); ?>">
                        <input type="date" name="tgl_akhir" class="form-control" style="width:160px;" value="<?php echo htmlspecialchars($tgl_akhir); ?>">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <?php if ($s_key || $tgl_mulai || $tgl_akhir) { ?>
                            <a href="history.php" class="btn btn-secondary">Reset</a>
                        <?php } ?>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="form-section-card" style="max-width:100%;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Nama Alat</th>
                                <th>Peminjam</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                                <th class="text-center pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $cond = "WHERE 1=1";
                            if ($role == 'dokter') $cond .= " AND h.id_user = '$my_id'";
                            if ($s_key != '') $cond .= " AND (a.nama_alat LIKE '%$s_key%' OR u.nama_lengkap LIKE '%$s_key%')";
                            if ($tgl_mulai != '' && $tgl_akhir != '') $cond .= " AND h.tgl_pinjam BETWEEN '$tgl_mulai 00:00:00' AND '$tgl_akhir 23:59:59'";

                            $q_count = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM history_peminjaman h JOIN alat a ON h.id_alat=a.id_alat JOIN users u ON h.id_user=u.id $cond");
                            $row_count = mysqli_fetch_assoc($q_count);
                            $total_data = $row_count['total'];
                            $total_pages = ceil($total_data / $limit);

                            $sql = "SELECT h.*, a.nama_alat, u.nama_lengkap FROM history_peminjaman h 
                                    JOIN alat a ON h.id_alat=a.id_alat 
                                    JOIN users u ON h.id_user=u.id 
                                    $cond ORDER BY h.id_history DESC LIMIT $limit OFFSET $offset";
                            
                            $q = mysqli_query($koneksi, $sql);

                            if (mysqli_num_rows($q) == 0) {
                                echo '<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2"></i>Belum ada riwayat peminjaman saat ini.</td></tr>';
                            } else {
                                while($h = mysqli_fetch_array($q)) {
                                    $badge = ($h['status_peminjaman'] == 'Dipinjam') ? 'badge-status-warning' : 'badge-status-success';
                            ?>
                            <tr>
                                <td class="ps-3"><strong><?php echo $h['nama_alat']; ?></strong></td>
                                <td><?php echo $h['nama_lengkap']; ?></td>
                                <td><small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($h['tgl_pinjam'])); ?></small></td>
                                <td><small class="text-muted"><?php echo ($h['tgl_kembali']) ? date('d/m/Y H:i', strtotime($h['tgl_kembali'])) : '-'; ?></small></td>
                                <td><span class="badge <?php echo $badge; ?>"><?php echo $h['status_peminjaman']; ?></span></td>
                                <td class="text-center pe-3">
                                    <?php if($h['status_peminjaman'] == 'Dipinjam') { ?>
                                        <a href="history.php?kembali=<?php echo $h['id_history']; ?>&alat=<?php echo $h['id_alat']; ?>" 
                                           class="btn btn-sm btn-primary px-3"
                                           onclick="return confirm('Apakah alat ini sudah dikembalikan?')">
                                            <i class="bi bi-box-arrow-in-left me-1"></i>Kembalikan
                                        </a>
                                    <?php } else { ?>
                                        <span class="text-success"><i class="bi bi-check-circle-fill"></i></span>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1) { ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-end">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $url_query; ?>"><i class="bi bi-chevron-left"></i></a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $url_query; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php } ?>
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $url_query; ?>"><i class="bi bi-chevron-right"></i></a>
                    </li>
                </ul>
            </nav>
            <?php } ?>

        </div>
    </div>
</div>

<?php include '../tampilan/footer.php'; ?>
