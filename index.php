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