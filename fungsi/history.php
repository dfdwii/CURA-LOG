<?php
require_once '../config.php';
require_once '../auth_check.php';

$role = $_SESSION['role'];
$my_id = $_SESSION['user_id'];

if (isset($_GET['kembali'])) {
    $id_h = $_GET['kembali'];
    $id_a = $_GET['alat'];

    $cek_pinjaman = mysqli_query($koneksi, "SELECT id_user FROM history_peminjaman WHERE id_history='$id_h'");
    $data_h = mysqli_fetch_array($cek_pinjaman);

    if ($role == 'dokter' && $data_h['id_user'] != $my_id) {
        echo "<script>alert('Gagal! Anda tidak berhak mengembalikan alat milik dokter lain.'); window.location='history.php';</script>";
    } else {
        mysqli_query($koneksi, "UPDATE history_peminjaman SET status_peminjaman='Dikembalikan', tgl_kembali=NOW() WHERE id_history='$id_h'");
        mysqli_query($koneksi, "UPDATE alat SET status='Tersedia' WHERE id_alat='$id_a'");
        echo "<script>alert('Alat sudah dikembalikan'); window.location='history.php';</script>";
    }
}

include '../tampilan/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../tampilan/sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <h3>Histori Peminjaman</h3>
            <p class="text-muted">
                <?php 
                if($role == 'dokter') {
                    echo "Menampilkan riwayat peminjaman pribadi anda.";
                } else {
                    echo "Menampilkan seluruh riwayat peminjaman rumah sakit.";
                }
                ?>
            </p>
            <hr>
            
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="table-light">
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
                            if ($role == 'dokter') {
                                $sql = "SELECT h.*, a.nama_alat, u.nama_lengkap FROM history_peminjaman h 
                                        JOIN alat a ON h.id_alat=a.id_alat 
                                        JOIN users u ON h.id_user=u.id 
                                        WHERE h.id_user = '$my_id'
                                        ORDER BY h.id_history DESC";
                            } else {
                                $sql = "SELECT h.*, a.nama_alat, u.nama_lengkap FROM history_peminjaman h 
                                        JOIN alat a ON h.id_alat=a.id_alat 
                                        JOIN users u ON h.id_user=u.id 
                                        ORDER BY h.id_history DESC";
                            }

                            $q = mysqli_query($koneksi, $sql);
                            
                            if (mysqli_num_rows($q) == 0) {
                                echo "<tr><td colspan='6' class='text-center py-4 text-muted'>Tidak ada data histori.</td></tr>";
                            }

                            while($h = mysqli_fetch_array($q)) {
                            ?>
                            <tr>
                                <td class="ps-3"><?php echo $h['nama_alat']; ?></td>
                                <td><?php echo $h['nama_lengkap']; ?></td>
                                <td><?php echo $h['tgl_pinjam']; ?></td>
                                <td><?php echo ($h['tgl_kembali'] == "") ? "-" : $h['tgl_kembali']; ?></td>
                                <td>
                                    <span class="badge <?php echo ($h['status_peminjaman'] == 'Dipinjam') ? 'bg-warning text-dark' : 'bg-success'; ?>">
                                        <?php echo $h['status_peminjaman']; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if($h['status_peminjaman'] == 'Dipinjam') { ?>
                                        <a href="history.php?kembali=<?php echo $h['id_history']; ?>&alat=<?php echo $h['id_alat']; ?>" 
                                           class="btn btn-sm btn-primary" 
                                           onclick="return confirm('Apakah anda yakin ingin mengembalikan alat ini?')">
                                           Kembalikan
                                        </a>
                                    <?php } ?>
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