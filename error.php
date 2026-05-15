<?php
require_once 'config.php';
include 'tampilan/header.php';
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    <h1 class="display-1 text-danger fw-bold">404</h1>
                   <h3 class="mb-4">Halaman Tidak Ditemukan</h3>
                    <p class="text-muted mb-4">Maaf, halaman yang anda cari tidak ada atau terjadi kesalahan sistem.</p>
                    <a href="index.php" class="btn btn-primary px-4 py-2">Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'tampilan/footer.php'; ?>