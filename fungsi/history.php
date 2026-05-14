<?php
require_once '../config.php';
require_once '../auth_check.php';

$role = $_SESSION['role'];
$my_id = $_SESSION['user_id'];

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
        echo "<script>alert('Berhasil!'); window.location='history.php';</script>";
    }
}

include '../tampilan/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../tampilan/sidebar.php'; ?>

        <div class="col-md-10 offset-md-2 px-4 pt-0" style="padding-bottom: 80px;">
            
            <div class="sticky-top pt-4 pb-3 mb-3" style="background-color: #f8fafc; z-index: 10;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="m-0"><?php echo ($role == 'dokter') ? 'Histori Peminjaman' : 'Laporan Peminjaman'; ?></h3>
                        <p class="text-muted m-0 small"><?php echo ($role == 'dokter') ? "Catatan peminjaman pribadi Anda." : "Seluruh riwayat transaksi alat medis."; ?></p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-3">Nama Alat</th><th>Peminjam</th><th>Tgl Pinjam</th><th>Status</th><th class="text-center">Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = ($role == 'dokter') ? "SELECT h.*, a.nama_alat, u.nama_lengkap FROM history_peminjaman h JOIN alat a ON h.id_alat=a.id_alat JOIN users u ON h.id_user=u.id WHERE h.id_user = '$my_id' ORDER BY h.id_history DESC" : "SELECT h.*, a.nama_alat, u.nama_lengkap FROM history_peminjaman h JOIN alat a ON h.id_alat=a.id_alat JOIN users u ON h.id_user=u.id ORDER BY h.id_history DESC";
                        $q = mysqli_query($koneksi, $sql);
                        while($h = mysqli_fetch_array($q)) {
                            $badge = ($h['status_peminjaman'] == 'Dipinjam') ? 'bg-warning text-dark' : 'bg-success';
                        ?>
                        <tr>
                            <td class="ps-3"><strong><?php echo $h['nama_alat']; ?></strong></td>
                            <td><?php echo $h['nama_lengkap']; ?></td>
                            <td><small><?php echo $h['tgl_pinjam']; ?></small></td>
                            <td><span class="badge <?php echo $badge; ?>"><?php echo $h['status_peminjaman']; ?></span></td>
                            <td class="text-center">
                                <?php if($h['status_peminjaman'] == 'Dipinjam') { ?>
                                    <a href="history.php?kembali=<?php echo $h['id_history']; ?>&alat=<?php echo $h['id_alat']; ?>" class="btn btn-sm btn-primary py-0" onclick="return confirm('Kembalikan alat?')">Kembalikan</a>
                                <?php } else { echo '<i class="bi bi-check-circle text-success"></i>'; } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../tampilan/footer.php'; ?>