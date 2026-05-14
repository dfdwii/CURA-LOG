<?php
require_once '../config.php';
require_once '../auth_check.php';

$role = $_SESSION['role'];
$my_id = $_SESSION['user_id'];

$s_key = isset($_GET['s_key']) ? mysqli_real_escape_string($koneksi, $_GET['s_key']) : '';
$tgl_mulai = isset($_GET['tgl_mulai']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_mulai']) : '';
$tgl_akhir = isset($_GET['tgl_akhir']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_akhir']) : '';

if (isset($_GET['kembali'])) {
    $id_h = $_GET['kembali'];
    $id_a = $_GET['alat'];
    $cek = mysqli_query($koneksi, "SELECT id_user FROM history_peminjaman WHERE id_history='$id_h'");
    $data_h = mysqli_fetch_array($cek);

    if ($role == 'dokter' && $data_h['id_user'] != $my_id) {
        echo "<script>alert('Gagal!'); window.location='history.php';</script>";
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

        <div class="col-md-10 offset-md-2 px-4 pt-0" style="padding-bottom: 80px;">
            
            <div class="sticky-top pt-4 pb-3 mb-3" style="background-color: #f8fafc; z-index: 10;">
                <h3 class="m-0"><?php echo ($role == 'dokter') ? 'Histori Peminjaman' : 'Laporan Peminjaman'; ?></h3>
                
                <form method="GET" class="row g-2 mt-3 bg-white p-3 rounded shadow-sm border">
                    <div class="col-md-4">
                        <input type="text" name="s_key" class="form-control" placeholder="Cari nama alat atau dokter..." value="<?php echo $s_key; ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="tgl_mulai" class="form-control" value="<?php echo $tgl_mulai; ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="tgl_akhir" class="form-control" value="<?php echo $tgl_akhir; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter Data</button>
                    </div>
                </form>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Nama Alat</th>
                                <th>Peminjam</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $cond = "WHERE 1=1";
                            if ($role == 'dokter') $cond .= " AND h.id_user = '$my_id'";
                            if ($s_key != '') $cond .= " AND (a.nama_alat LIKE '%$s_key%' OR u.nama_lengkap LIKE '%$s_key%')";
                            if ($tgl_mulai != '' && $tgl_akhir != '') $cond .= " AND h.tgl_pinjam BETWEEN '$tgl_mulai 00:00:00' AND '$tgl_akhir 23:59:59'";

                            $sql = "SELECT h.*, a.nama_alat, u.nama_lengkap FROM history_peminjaman h 
                                    JOIN alat a ON h.id_alat=a.id_alat 
                                    JOIN users u ON h.id_user=u.id 
                                    $cond ORDER BY h.id_history DESC";
                            
                            $q = mysqli_query($koneksi, $sql);
                            while($h = mysqli_fetch_array($q)) {
                                $badge = ($h['status_peminjaman'] == 'Dipinjam') ? 'bg-warning text-dark' : 'bg-success';
                            ?>
                            <tr>
                                <td class="ps-3"><strong><?php echo $h['nama_alat']; ?></strong></td>
                                <td><?php echo $h['nama_lengkap']; ?></td>
                                <td><small><?php echo date('d/m/Y H:i', strtotime($h['tgl_pinjam'])); ?></small></td>
                                <td><small><?php echo ($h['tgl_kembali']) ? date('d/m/Y H:i', strtotime($h['tgl_kembali'])) : '-'; ?></small></td>
                                <td><span class="badge <?php echo $badge; ?>"><?php echo $h['status_peminjaman']; ?></span></td>
                                <td class="text-center">
                                    <?php if($h['status_peminjaman'] == 'Dipinjam') { ?>
                                        <a href="history.php?kembali=<?php echo $h['id_history']; ?>&alat=<?php echo $h['id_alat']; ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           onclick="return confirm('Apakah alat ini sudah dikembalikan?')">Kembalikan</a>
                                    <?php } else { echo '<i class="bi bi-check-circle-fill text-success"></i>'; } ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../tampilan/footer.php'; ?>