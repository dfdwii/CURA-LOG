<?php
require_once 'config.php';
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit; }

include 'tampilan/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar p-0">
            <div class="sb-brand"><h4>CURA-LOG</h4></div>
            <div class="list-group list-group-flush">
                <a href="index.php" class="list-group-item list-group-item-action active">Dashboard</a>
                <a href="fungsi/inventory.php" class="list-group-item list-group-item-action">Inventaris</a>
                <a href="fungsi/history.php" class="list-group-item list-group-item-action">Histori Pinjam</a>
                <a href="logout.php" class="list-group-item list-group-item-action text-danger">Keluar</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <h2>Selamat Datang, <?php echo $_SESSION['nama']; ?>!</h2>
            <p class="text-muted">Ini adalah ringkasan inventaris rumah sakit hari ini.</p>
            
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white mb-3">
                        <div class="card-body">
                            <h5>Total Alat</h5>
                            <h3><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat")); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white mb-3">
                        <div class="card-body">
                            <h5>Tersedia</h5>
                            <h3><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat WHERE status='Tersedia'")); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark mb-3">
                        <div class="card-body">
                            <h5>Sedang Dipinjam</h5>
                            <h3><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat WHERE status='Dipinjam'")); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'tampilan/footer.php'; ?>