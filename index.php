<?php
require_once 'config.php';
require_once 'auth_check.php';

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
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <h5>Total Alat</h5>
                            <h3><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat")); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <h5>Alat Tersedia</h5>
                            <h3><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat WHERE status='Tersedia'")); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-warning text-dark h-100">
                        <div class="card-body">
                            <h5>Alat Sedang Dipinjam</h5>
                            <h3><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat WHERE status='Dipinjam'")); ?></h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card bg-danger text-white h-100">
                        <div class="card-body">
                            <h5>Alat Rusak</h5>
                            <h3><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat WHERE status='Rusak'")); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card bg-secondary text-white h-100">
                        <div class="card-body">
                            <h5>Alat Sedang Dimaintenance</h5>
                            <h3><?php echo mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM alat WHERE status='Maintenance'")); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'tampilan/footer.php'; ?>



