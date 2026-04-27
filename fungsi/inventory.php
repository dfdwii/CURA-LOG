<?php
include '../auth_check.php';
include '../config.php';
include '../tampilan/header.php';
include '../tampilan/sidebar.php';

$keyword='';
if(isset($_GET['cari'])){
$keyword=$_GET['cari'];

$query="
SELECT alat.*, ruangan.nama_ruangan
FROM alat
LEFT JOIN ruangan ON alat.id_ruangan=ruangan.id_ruangan
WHERE alat.nama_alat LIKE '%$keyword%'
OR alat.merk LIKE '%$keyword%'
OR ruangan.nama_ruangan LIKE '%$keyword%'
OR alat.status_awal LIKE '%$keyword%'
";
}else{
$query="
SELECT alat.*, ruangan.nama_ruangan
FROM alat
LEFT JOIN ruangan ON alat.id_ruangan=ruangan.id_ruangan
";
}

$data=mysqli_query($conn,$query);
?>

<div class="content">
<div class="card">

<h2>Inventaris Alat</h2>

<form method="GET" style="margin-bottom:15px;">
<input type="text" name="cari" value="<?= $keyword ?>" placeholder="Cari alat...">
<button class="btn btn-primary">Cari</button>
<a href="inventory.php" class="btn">Reset</a>
<a href="tambah_alat.php" class="btn btn-primary">+ Tambah</a>
</form>

<div class="grid">

<?php while($r=mysqli_fetch_assoc($data)): ?>

<div class="card-alat">

<img src="../uploads/<?= $r['gambar'] ? $r['gambar'] : 'default.png' ?>">

<div class="card-body">
<h3><?= $r['nama_alat'] ?></h3>

<p><b>Merk:</b> <?= $r['merk'] ?></p>
<p><b>Ruangan:</b> <?= $r['nama_ruangan'] ?></p>
<p><b>Status:</b> <?= $r['status_awal'] ?></p>
<p><b>Kalibrasi:</b> <?= $r['masa_kalibrasi'] ?></p>

<div style="margin-top:10px;">
<a href="edit_alat.php?id=<?= $r['id_alat'] ?>" class="btn btn-warning">Edit</a>
<a href="hapus_alat.php?id=<?= $r['id_alat'] ?>" class="btn btn-danger">Hapus</a>
</div>

</div>
</div>

<?php endwhile; ?>

</div>

</div>
</div>