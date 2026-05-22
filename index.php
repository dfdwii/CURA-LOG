<?php
require_once 'config.php';
require_once 'auth_check.php';

$role = $_SESSION['role'];
$q_notif = mysqli_query($koneksi, "SELECT * FROM alat WHERE status IN ('Rusak', 'Maintenance', 'Perlu Kalibrasi')");
$notif_rusak = [];
$notif_maint = [];
$notif_kalibrasi = [];
while ($row = mysqli_fetch_array($q_notif)) {
    if ($row['status'] == 'Rusak') {
        $notif_rusak[] = $row;
    } else if ($row['status'] == 'Maintenance') {
        $notif_maint[] = $row;
    } else {
        $notif_kalibrasi[] = $row;
    }
}
$q_status = mysqli_query($koneksi, "SELECT status, COUNT(*) as jumlah FROM alat GROUP BY status");
$label_status = [];
$data_status = [];
$warna_status = [];

while ($row = mysqli_fetch_assoc($q_status)) {
    $label_status[] = $row['status'];
    $data_status[] = $row['jumlah'];
    if ($row['status'] == 'Tersedia') $warna_status[] = '#198754'; 
    else if ($row['status'] == 'Dipinjam') $warna_status[] = '#eb0000'; 
    else if ($row['status'] == 'Rusak') $warna_status[] = '#dc3545'; 
    else if ($row['status'] == 'Perlu Kalibrasi') $warna_status[] = '#0dcaf0'; 
    else $warna_status[] = '#6c757d'; 
}
$q_merk = mysqli_query($koneksi, "SELECT merk, COUNT(*) as jumlah FROM alat GROUP BY merk ORDER BY jumlah DESC LIMIT 5");
$label_merk = [];
$data_merk = [];

while ($row = mysqli_fetch_assoc($q_merk)) {
    $label_merk[] = $row['merk'] ? $row['merk'] : 'Tanpa Merk';
    $data_merk[] = $row['jumlah'];
}
include 'tampilan/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'tampilan/sidebar.php'; ?>
        <div class="main-content px-4 pt-0">
            <div class="sticky-top pt-4 pb-3 mb-3" style="z-index:10;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="m-0">Selamat Datang, <?php echo $_SESSION['nama']; ?></h2>
                        <p class="text-muted m-0 mt-1" style="font-size:13.5px;">Ini adalah ringkasan inventaris rumah sakit hari ini.</p>
                    </div>
                    <span class="badge rounded-pill px-3 py-2" style="background:var(--teal-light);color:var(--teal-dark);font-size:13px;font-weight:600;">
                        <i class="bi bi-calendar3 me-1"></i><?php echo date('d M Y'); ?>
                    </span>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white border-0 stat-card">
                        <div class="card-body d-flex align-items-center gap-3 py-4">
                            <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
                            <div>
                                <div class="stat-label">Total Alat</div>
                                <div class="stat-number"><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat")); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white border-0 stat-card">
                        <div class="card-body d-flex align-items-center gap-3 py-4">
                            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                            <div>
                                <div class="stat-label">Tersedia</div>
                                <div class="stat-number"><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat WHERE status='Tersedia'")); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark border-0 stat-card">
                        <div class="card-body d-flex align-items-center gap-3 py-4">
                            <div class="stat-icon" style="background:rgba(0,0,0,.1)"><i class="bi bi-arrow-left-right"></i></div>
                            <div>
                                <div class="stat-label">Sedang Dipinjam</div>
                                <div class="stat-number"><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat WHERE status='Dipinjam'")); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($role != 'dokter'): ?>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="form-section-card" style="max-width:100%;">
                        <div class="form-section-header">
                            <div class="icon-wrap"><i class="bi bi-pie-chart"></i></div>
                            <div>
                                <h5>Distribusi Status Alat</h5>
                                <p>Persentase kondisi semua alat</p>
                            </div>
                        </div>
                        <div class="p-4" style="height:280px;display:flex;justify-content:center;">
                            <canvas id="chartStatus"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-section-card" style="max-width:100%;">
                        <div class="form-section-header">
                            <div class="icon-wrap"><i class="bi bi-bar-chart"></i></div>
                            <div>
                                <h5>Top 5 Merk Terbanyak</h5>
                                <p>Berdasarkan jumlah unit</p>
                            </div>
                        </div>
                        <div class="p-4" style="height:280px;">
                            <canvas id="chartMerk"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="section-title mb-3">
                <i class="bi bi-bell-fill me-2" style="color:#4DB8B8;"></i>
                <span>Notifikasi Penting</span>
            </div>
            <?php if (count($notif_rusak) == 0 && count($notif_maint) == 0 && count($notif_kalibrasi) == 0) { ?>
                <div class="notif-banner notif-ok mb-4">
                    <i class="bi bi-shield-check fs-4"></i>
                    <div>
                        <strong>Kondisi Baik</strong>
                        <p class="mb-0" style="font-size:13px;">Tidak ada alat yang bermasalah saat ini.</p>
                    </div>
                </div>
            <?php } ?>
            <?php if (count($notif_rusak) > 0) { ?>
            <div class="notif-card notif-danger mb-3">
                <div class="notif-card-header" data-bs-toggle="collapse" data-bs-target="#collapseRusak" style="cursor:pointer;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Alat Rusak</strong>
                        <span class="notif-count"><?= count($notif_rusak) ?></span>
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div id="collapseRusak" class="collapse">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th class="ps-3">Nama Alat</th><th>Merk</th><th>Keterangan</th></tr></thead>
                            <tbody>
                                <?php foreach ($notif_rusak as $r) { ?>
                                <tr><td class="ps-3"><strong><?= $r['nama_alat']; ?></strong></td><td><?= $r['merk']; ?></td><td><?= $r['keterangan'] ? htmlspecialchars($r['keterangan']) : '-'; ?></td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php } ?>
            <?php if (count($notif_maint) > 0) { ?>
            <div class="notif-card notif-secondary mb-3">
                <div class="notif-card-header" data-bs-toggle="collapse" data-bs-target="#collapseMaint" style="cursor:pointer;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-tools"></i>
                        <strong>Maintenance</strong>
                        <span class="notif-count"><?= count($notif_maint) ?></span>
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div id="collapseMaint" class="collapse">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th class="ps-3">Nama Alat</th><th>Merk</th><th>Keterangan</th></tr></thead>
                            <tbody>
                                <?php foreach ($notif_maint as $m) { ?>
                                <tr><td class="ps-3"><strong><?= $m['nama_alat']; ?></strong></td><td><?= $m['merk']; ?></td><td><?= $m['keterangan'] ? htmlspecialchars($m['keterangan']) : '-'; ?></td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php } ?>
            <?php if (count($notif_kalibrasi) > 0) { ?>
            <div class="notif-card notif-info mb-4">
                <div class="notif-card-header" data-bs-toggle="collapse" data-bs-target="#collapseKalibrasi" style="cursor:pointer;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-speedometer2"></i>
                        <strong>Perlu Kalibrasi</strong>
                        <span class="notif-count"><?= count($notif_kalibrasi) ?></span>
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div id="collapseKalibrasi" class="collapse">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th class="ps-3">Nama Alat</th><th>Merk</th><th>Keterangan</th></tr></thead>
                            <tbody>
                                <?php foreach ($notif_kalibrasi as $k) { ?>
                                <tr><td class="ps-3"><strong><?= $k['nama_alat']; ?></strong></td><td><?= $k['merk']; ?></td><td><?= $k['keterangan'] ? htmlspecialchars($k['keterangan']) : '-'; ?></td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php } ?>
            <?php endif; ?>
            <div class="section-title mb-3">
                <i class="bi bi-clock-history me-2" style="color:#4DB8B8;"></i>
                <span>Alat Terbaru Ditambahkan</span>
            </div>
            <div class="form-section-card mb-4" style="max-width:100%;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Foto</th>
                                <th>Nama Alat</th>
                                <th>Merk</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_baru = mysqli_query($koneksi, "SELECT * FROM alat ORDER BY id_alat DESC LIMIT 5");
                            while ($b = mysqli_fetch_array($q_baru)) {
                                $warna = 'badge-status-secondary';
                                if ($b['status'] == 'Tersedia') $warna = 'badge-status-success';
                                else if ($b['status'] == 'Dipinjam') $warna = 'badge-status-warning';
                                else if ($b['status'] == 'Rusak') $warna = 'badge-status-danger';
                            ?>
                            <tr>
                                <td class="ps-3"><img src="assets/img/alat_medis/<?php echo htmlspecialchars($b['gambar']); ?>" class="img-alat"></td>
                                <td><strong><?php echo htmlspecialchars($b['nama_alat']); ?></strong></td>
                                <td><?php echo htmlspecialchars($b['merk']); ?></td>
                                <td><span class="badge <?php echo $warna; ?>"><?php echo htmlspecialchars($b['status']); ?></span></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="section-title mb-3">
                <i class="bi bi-arrow-left-right me-2" style="color:#4DB8B8;"></i>
                <span>Peminjaman Aktif</span>
            </div>
            <div class="form-section-card mb-4" style="max-width:100%;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Nama Alat</th>
                                <th>Dipinjam Oleh</th>
                                <th>Keperluan</th>
                                <th>Tgl Pinjam</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_pinjam = mysqli_query($koneksi, "SELECT h.*, a.nama_alat, u.nama_lengkap FROM history_peminjaman h 
                                                                JOIN alat a ON h.id_alat = a.id_alat JOIN users u ON h.id_user = u.id 
                                                                WHERE h.status_peminjaman = 'Dipinjam' ORDER BY h.id_history DESC LIMIT 5");
                            if (mysqli_num_rows($q_pinjam) == 0) {
                                echo "<tr><td colspan='4' class='text-center py-4 text-muted'><i class='bi bi-inbox d-block fs-3 mb-2'></i>Tidak ada alat yang sedang dipinjam saat ini.</td></tr>";
                            } else {
                                while ($p = mysqli_fetch_array($q_pinjam)) {
                            ?>
                            <tr>
                                <td class="ps-3"><strong><?php echo htmlspecialchars($p['nama_alat']); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($p['keperluan']); ?></td>
                                <td><small><?php echo date('d M Y, H:i', strtotime($p['tgl_pinjam'])); ?></small></td>
                            </tr>
                            <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if ($role != 'dokter'): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    Chart.defaults.color = '#6B8C93';
    Chart.defaults.font.family = "'DM Sans', sans-serif";

    const ctxStatus = document.getElementById('chartStatus').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($label_status); ?>,
            datasets: [{
                data: <?php echo json_encode($data_status); ?>,
                backgroundColor: <?php echo json_encode($warna_status); ?>,
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'right' } },
            cutout: '70%' 
        }
    });
    const ctxMerk = document.getElementById('chartMerk').getContext('2d');
    new Chart(ctxMerk, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($label_merk); ?>,
            datasets: [{
                label: 'Jumlah Alat',
                data: <?php echo json_encode($data_merk); ?>,
                backgroundColor: '#4DB8B8', 
                borderRadius: 8, 
                barThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(77,184,184,.1)' } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
<?php endif; ?>
<?php include 'tampilan/footer.php'; ?>
