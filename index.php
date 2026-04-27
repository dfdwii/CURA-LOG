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