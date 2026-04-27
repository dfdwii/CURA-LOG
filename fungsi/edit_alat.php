<?php
include '../config.php';

$id=$_GET['id'];
$d=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM alat WHERE id_alat=$id"));
$ruangan=mysqli_query($conn,"SELECT * FROM ruangan");

if(isset($_POST['submit'])){

$gambar = $d['gambar'];

if($_FILES['gambar']['name'] != ''){
    $gambar = $_FILES['gambar']['name'];
    move_uploaded_file($_FILES['gambar']['tmp_name'], "../uploads/".$gambar);
}

mysqli_query($conn,"UPDATE alat SET
nama_alat='$_POST[nama]',
merk='$_POST[merk]',
tgl_masuk='$_POST[tgl]',
id_ruangan='$_POST[ruangan]',
masa_kalibrasi='$_POST[kalibrasi]',
status_awal='$_POST[status]',
gambar='$gambar'
WHERE id_alat=$id");

header("Location: inventory.php");
}
?>

<form method="POST" enctype="multipart/form-data">

<input type="text" name="nama" value="<?= $d['nama_alat'] ?>">
<input type="text" name="merk" value="<?= $d['merk'] ?>">
<input type="date" name="tgl" value="<?= $d['tgl_masuk'] ?>">

<select name="ruangan">
<?php while($r=mysqli_fetch_assoc($ruangan)): ?>
<option value="<?= $r['id_ruangan'] ?>"><?= $r['nama_ruangan'] ?></option>
<?php endwhile; ?>
</select>

<input type="date" name="kalibrasi" value="<?= $d['masa_kalibrasi'] ?>">

<input type="text" name="status" value="<?= $d['status_awal'] ?>">

<input type="file" name="gambar">

<button class="btn btn-primary" name="submit">Update</button>

</form>