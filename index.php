<<<<<<< HEAD
<doctype html>
<title>Dashboard Inventaris</title>
<link rel="stylesheet" href="assets/style.css">
<?php
include 'auth_check.php';
include 'config.php';
include 'tampilan/header.php';
include 'tampilan/sidebar.php';

$total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM alat"))['t'];
$baik = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM alat WHERE status_awal='Baik'"))['t'];
$rusak = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM alat WHERE status_awal='Rusak'"))['t'];
$kalibrasi = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM alat WHERE status_awal='Perlu Kalibrasi'"))['t'];
?>

<script>
function toggle(id){
    var x = document.getElementById(id);
    if(x.style.display === "none"){
        x.style.display = "block";
    } else {
        x.style.display = "none";
    }
}
</script>

<div class="content">

<div class="card">
<h2>Dashboard Inventaris</h2>

<!-- QUICK ACTIONS -->
<div style="margin-bottom: 20px;">
<a href="fungsi/inventory.php" class="btn btn-primary">📋 Lihat Inventaris</a>
<a href="logout.php" class="btn btn-warning">🚪 Logout</a>
</div>

<!-- CARD STATISTIK -->
<div class="dashboard">

<div class="card">
<p>Total Alat</p>
<h2><?= $total ?></h2>
</div>

<div class="card">
<p>Baik</p>
<h2 class="status-baik"><?= $baik ?></h2>
</div>

<div class="card">
<p>Rusak</p>
<h2 class="status-rusak"><?= $rusak ?></h2>
</div>

<div class="card">
<p>Perlu Kalibrasi</p>
<h2 class="status-kalibrasi"><?= $kalibrasi ?></h2>
</div>

</div>
</div>

<?php
$rusak_data = mysqli_query($conn,"
SELECT alat.*, ruangan.nama_ruangan
FROM alat
LEFT JOIN ruangan ON alat.id_ruangan = ruangan.id_ruangan
WHERE status_awal='Rusak'
");

$kalibrasi_data = mysqli_query($conn,"
SELECT alat.*, ruangan.nama_ruangan
FROM alat
LEFT JOIN ruangan ON alat.id_ruangan = ruangan.id_ruangan
WHERE masa_kalibrasi <= CURDATE()
");
?>
<div class="card">

<h3>🔔 Notifikasi Penting</h3>

<!-- RUSAK -->
<div onclick="toggle('rusak')" style="cursor:pointer; color:red; font-weight:bold;">
🔴 Ada <?= mysqli_num_rows($rusak_data) ?> alat rusak (klik untuk lihat)
</div>

<div id="rusak" style="display:none; margin-top:10px;">
<table>
<tr>
<th>Nama</th>
<th>Ruangan</th>
</tr>

<?php while($r=mysqli_fetch_assoc($rusak_data)): ?>
<tr>
<td><?= $r['nama_alat'] ?></td>
<td><?= $r['nama_ruangan'] ?></td>
</tr>
<?php endwhile; ?>

</table>
</div>

<br>

<!-- KALIBRASI -->
<div onclick="toggle('kalibrasi')" style="cursor:pointer; color:orange; font-weight:bold;">
🟠 Ada <?= mysqli_num_rows($kalibrasi_data) ?> alat perlu kalibrasi (klik untuk lihat)
</div>

<div id="kalibrasi" style="display:none; margin-top:10px;">
<table>
<tr>
<th>Nama</th>
<th>Ruangan</th>
<th>Tanggal</th>
</tr>

<?php while($k=mysqli_fetch_assoc($kalibrasi_data)): ?>
<tr>
<td><?= $k['nama_alat'] ?></td>
<td><?= $k['nama_ruangan'] ?></td>
<td><?= $k['masa_kalibrasi'] ?></td>
</tr>
<?php endwhile; ?>

</table>
</div>

</div>
</div>

</div>
=======
<?php
include 'includes/config.php'; 
include 'includes/auth_check.php'; 

$total_alat = 124;
$alat_rusak = 3;
$perlu_kalibrasi = 5;
?>

<?php include 'tampilan/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        
        <?php include 'tampilan/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-2 mb-4 border-bottom">
                <h1 class="h2" style="color: #004b87;">Dashboard CURA-LOG</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-download"></i> Unduh Laporan
                    </button>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Total Alat Medis</h5>
                            <h2 class="card-text fw-bold"><?php echo $total_alat; ?> Unit</h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card text-dark bg-warning mb-3 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Perlu Kalibrasi</h5>
                            <h2 class="card-text fw-bold"><?php echo $perlu_kalibrasi; ?> Unit</h2>
                            <small>Jadwal bulan ini</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white bg-danger mb-3 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Alat Rusak / Perbaikan</h5>
                            <h2 class="card-text fw-bold"><?php echo $alat_rusak; ?> Unit</h2>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="mb-3">Alat Medis Masuk Terakhir</h4>
            <div class="table-responsive shadow-sm rounded">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">No. Seri</th>
                            <th scope="col">Nama Alat</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Lokasi</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>SN-202604-001</strong></td>
                            <td>Defibrillator Philips HeartStart</td>
                            <td>Alat Resusitasi</td>
                            <td>IGD - Bed 1</td>
                            <td><span class="badge bg-success">Tersedia</span></td>
                        </tr>
                        <tr>
                            <td><strong>SN-202604-002</strong></td>
                            <td>Patient Monitor Mindray</td>
                            <td>Alat Monitor</td>
                            <td>ICU - Bed 4</td>
                            <td><span class="badge bg-warning text-dark">Dipinjam</span></td>
                        </tr>
                        <tr>
                            <td><strong>SN-202604-003</strong></td>
                            <td>Ventilator Hamilton-C1</td>
                            <td>Alat Pernapasan</td>
                            <td>Gudang Utama</td>
                            <td><span class="badge bg-danger">Rusak</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
        </main>
    </div>
</div>

<?php include 'tampilan/footer.php'; ?>
>>>>>>> 0cd108c8171fc382a968c10376e2ec237a6feed7
