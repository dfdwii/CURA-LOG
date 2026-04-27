<?php
include '../config.php';
$ruangan=mysqli_query($conn,"SELECT * FROM ruangan");

if(isset($_POST['submit'])){

$gambar = $_FILES['gambar']['name'];
$tmp = $_FILES['gambar']['tmp_name'];

if($gambar != ''){
    move_uploaded_file($tmp, "../uploads/".$gambar);
}else{
    $gambar = 'default.png';
}

mysqli_query($conn,"INSERT INTO alat 
(nama_alat,merk,tgl_masuk,id_ruangan,masa_kalibrasi,status_awal,gambar)
VALUES
('$_POST[nama]','$_POST[merk]','$_POST[tgl]','$_POST[ruangan]','$_POST[kalibrasi]','$_POST[status]','$gambar')");

header("Location: inventory.php");
}
?>

<form method="POST" enctype="multipart/form-data">

<input type="text" name="nama" placeholder="Nama Alat">
<input type="text" name="merk" placeholder="Merk">
<input type="date" name="tgl">

<select name="ruangan">
<?php while($r=mysqli_fetch_assoc($ruangan)): ?>
<option value="<?= $r['id_ruangan'] ?>"><?= $r['nama_ruangan'] ?></option>
<?php endwhile; ?>
</select>

<input type="date" name="kalibrasi">

<select name="status">
<option>Baik</option>
<option>Rusak</option>
<option>Perlu Kalibrasi</option>
</select>

<input type="file" name="gambar">

<button class="btn btn-primary" name="submit">Simpan</button>

</form>