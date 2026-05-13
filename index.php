<?php
require_once 'config.php';
require_once 'auth_check.php';

$q_notif = mysqli_query($koneksi, "SELECT * FROM alat WHERE status IN ('Rusak', 'Maintenance')");
$notif_rusak = [];
$notif_maint = [];

while($row = mysqli_fetch_array($q_notif)) {
    if($row['status'] == 'Rusak') {
        $notif_rusak[] = $row;
    } else {
        $notif_maint[] = $row;
    }
}

include 'tampilan/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'tampilan/sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <h2>Selamat Datang, <?php echo $_SESSION['nama']; ?>!</h2>
            <p class="text-muted">Ini adalah ringkasan inventaris rumah sakit hari ini.</p>
            
            <div class="row mt-4">
                <div class="col-md-4 mb-3">
                    <div class="card bg-primary text-white h-100 shadow-sm">
                        <div class="card-body">
                            <h5>Total Alat</h5>
                            <h3><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat")); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-success text-white h-100 shadow-sm">
                        <div class="card-body">
                            <h5>Tersedia</h5>
                            <h3><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat WHERE status='Tersedia'")); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-warning text-dark h-100 shadow-sm">
                        <div class="card-body">
                            <h5>Sedang Dipinjam</h5>
                            <h3><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat WHERE status='Dipinjam'")); ?></h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card bg-danger text-white h-100 shadow-sm">
                        <div class="card-body">
                            <h5>Alat Rusak</h5>
                            <h3><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat WHERE status='Rusak'")); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card bg-secondary text-white h-100 shadow-sm">
                        <div class="card-body">
                            <h5>Alat Sedang Dimaintenance</h5>
                            <h3><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat WHERE status='Maintenance'")); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="mb-3 fw-bold">Notifikasi Penting</h5>
            
            <?php if(count($notif_rusak) == 0 && count($notif_maint) == 0) { ?>
                <div class="alert alert-success shadow-sm">Tidak ada alat yang rusak atau sedang dimaintenance saat ini.</div>
            <?php } ?>

            <?php if(count($notif_rusak) > 0) { ?>
            <div class="card border-danger mb-3 shadow-sm">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center" 
                     style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#collapseRusak">
                    <span><i class="bi bi-exclamation-triangle-fill"></i> <strong>Alat Rusak:</strong> Terdapat <?= count($notif_rusak) ?> alat yang rusak. Klik untuk melihat detail.</span>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div id="collapseRusak" class="collapse">
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <thead class="table-danger">
                                <tr>
                                    <th>Nama Alat</th>
                                    <th>Merk</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($notif_rusak as $r) { ?>
                                <tr>
                                    <td><strong><?php echo $r['nama_alat']; ?></strong></td>
                                    <td><?php echo $r['merk']; ?></td>
                                    <td><?php echo $r['keterangan'] ? $r['keterangan'] : '-'; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php } ?>

            <?php if(count($notif_maint) > 0) { ?>
            <div class="card border-secondary mb-4 shadow-sm">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center" 
                     style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#collapseMaint">
                    <span><i class="bi bi-tools"></i> <strong>Alat Maintenance:</strong> Terdapat <?= count($notif_maint) ?> alat yang sedang dimaintenance. Klik untuk detail.</span>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div id="collapseMaint" class="collapse">
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Nama Alat</th>
                                    <th>Merk</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($notif_maint as $m) { ?>
                                <tr>
                                    <td><strong><?php echo $m['nama_alat']; ?></strong></td>
                                    <td><?php echo $m['merk']; ?></td>
                                    <td><?php echo $m['keterangan'] ? $m['keterangan'] : '-'; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php } ?>

            <h5 class="mb-3 mt-5 fw-bold">Alat Terbaru Ditambahkan</h5>
            <div class="card shadow-sm mb-4">
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th>Foto</th>
                                <th>Nama Alat</th>
                                <th>Merk</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_baru = mysqli_query($koneksi, "SELECT * FROM alat ORDER BY id_alat DESC LIMIT 5");
                            while($b = mysqli_fetch_array($q_baru)) {
                            ?>
                            <tr>
                                <td><img src="assets/img/alat_medis/<?php echo $b['gambar']; ?>" style="width:40px;height:40px;object-fit:cover;border-radius:5px;"></td>
                                <td><strong><?php echo $b['nama_alat']; ?></strong></td>
                                <td><?php echo $b['merk']; ?></td>
                                <td>
                                    <span class="badge <?php echo ($b['status'] == 'Tersedia') ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $b['status']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <h5 class="mb-3 mt-4 fw-bold">Peminjaman Aktif</h5>
            <div class="card shadow-sm mb-4">
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-warning">
                            <tr>
                                <th>Nama Alat</th>
                                <th>Dipinjam Oleh</th>
                                <th>Keperluan</th>
                                <th>Tgl Pinjam</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_pinjam = mysqli_query($koneksi, "SELECT h.*, a.nama_alat, u.nama_lengkap 
                                                                FROM history_peminjaman h 
                                                                JOIN alat a ON h.id_alat = a.id_alat 
                                                                JOIN users u ON h.id_user = u.id 
                                                                WHERE h.status_peminjaman = 'Dipinjam' 
                                                                ORDER BY h.id_history DESC LIMIT 5");
                            if(mysqli_num_rows($q_pinjam) == 0) {
                                echo "<tr><td colspan='4' class='text-center py-3'>Tidak ada alat yang sedang dipinjam saat ini.</td></tr>";
                            } else {
                                while($p = mysqli_fetch_array($q_pinjam)) {
                            ?>
                            <tr>
                                <td><strong><?php echo $p['nama_alat']; ?></strong></td>
                                <td><?php echo $p['nama_lengkap']; ?></td>
                                <td><?php echo $p['keperluan']; ?></td>
                                <td><?php echo $p['tgl_pinjam']; ?></td>
                            </tr>
                            <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'tampilan/footer.php'; ?>