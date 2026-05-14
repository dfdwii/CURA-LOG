<?php
require_once '../config.php';
require_once '../auth_check.php';

$role = $_SESSION['role']; 

if (isset($_POST['simpan_status'])) {
    $id_alat = $_POST['id_alat_status'];
    $status_baru = $_POST['status_baru'];
    
    if ($status_baru != 'Dipinjam') {
        mysqli_query($koneksi, "UPDATE alat SET status='$status_baru' WHERE id_alat='$id_alat'");
        echo "<script>alert('Status alat berhasil diperbarui!'); window.location='inventory.php';</script>";
    }
}

include '../tampilan/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../tampilan/sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Inventaris Alat Medis</h2>
                <?php if ($role != 'dokter') { ?>
                    <a href="tambah_alat.php" class="btn btn-primary"> + Tambah Alat</a>
                <?php } ?>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nama Alat</th>
                                <th>Merk</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM alat ORDER BY id_alat DESC";
                            $query = mysqli_query($koneksi, $sql);
                            
                            while($data = mysqli_fetch_array($query)) {
                            ?>
                            <tr>
                                <td>
                                    <img src="../assets/img/alat_medis/<?php echo $data['gambar']; ?>" class="img-alat">
                                </td>
                                <td><strong><?php echo $data['nama_alat']; ?></strong></td>
                                <td><?php echo $data['merk']; ?></td>
                                <td>
                                    <?php
                                    $warna_badge = 'bg-secondary';
                                    if ($data['status'] == 'Tersedia') $warna_badge = 'bg-success';
                                    if ($data['status'] == 'Dipinjam') $warna_badge = 'bg-warning text-dark';
                                    if ($data['status'] == 'Rusak') $warna_badge = 'bg-danger';
                                    if ($data['status'] == 'Maintenance') $warna_badge = 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $warna_badge; ?>">
                                        <?php echo $data['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm text-white" 
                                            onclick="lihatDetail('<?php echo $data['nama_alat']; ?>', '<?php echo $data['merk']; ?>', '<?php echo $data['keterangan']; ?>')">
                                        Detail
                                    </button>

                                    <?php if ($role == 'dokter') { ?>
                                        <?php if ($data['status'] == 'Tersedia') { ?>
                                            <button class="btn btn-success btn-sm" 
                                                    onclick="bukaPinjam('<?php echo $data['id_alat']; ?>', '<?php echo $data['nama_alat']; ?>')">
                                                Pinjam
                                            </button>
                                        <?php } ?>

                                    <?php } else { ?>
                                        <button class="btn btn-secondary btn-sm" 
                                                onclick="bukaStatus('<?php echo $data['id_alat']; ?>', '<?php echo $data['nama_alat']; ?>', '<?php echo $data['status']; ?>')">
                                            Status
                                        </button>
                                        
                                        <a href="edit_alat.php?id=<?php echo $data['id_alat']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="hapus_alat.php?id=<?php echo $data['id_alat']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin mau hapus?')">Hapus</a>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Alat Medis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Nama Alat:</strong> <span id="detNama"></span></p>
                <p><strong>Merk:</strong> <span id="detMerk"></span></p>
                <p><strong>Keterangan:</strong> <span id="detKet"></span></p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">Ubah Status Alat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_alat_status" id="statusId">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small">Alat yang diubah:</label>
                        <input type="text" id="statusNama" class="form-control fw-bold" readonly style="background-color: #f8f9fa; border: none;">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih Status Baru:</label>
                        <select name="status_baru" id="statusPilihan" class="form-select" required>
                            <option value="Tersedia">Tersedia</option>
                            <option value="Rusak">Rusak</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                        <small class="text-danger d-block mt-2" style="font-size: 11px;">
                            *Catatan: Status 'Dipinjam' tidak dapat diubah secara manual di sini.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_status" class="btn btn-secondary">Simpan Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPinjam" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Pinjam Alat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses_pinjam.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_alat" id="pinjamId">
                    <div class="mb-3">
                        <label>Alat yang Dipinjam</label>
                        <input type="text" id="pinjamNama" class="form-control" readonly style="background-color: #e9ecef;">
                    </div>
                    <div class="mb-3">
                        <label>Keperluan</label>
                        <input type="text" name="keperluan" class="form-control" placeholder="Contoh: Operasi pasien..." required>
                    </div>
                    <div class="mb-3">
                        <label>Ruangan Tujuan</label>
                        <input type="text" name="ruangan_tujuan" class="form-control" placeholder="Contoh: UGD Bed 3" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="pinjam" class="btn btn-success">Konfirmasi Pinjam</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function lihatDetail(nama, merk, ket) {
    document.getElementById('detNama').innerText = nama;
    document.getElementById('detMerk').innerText = merk;
    document.getElementById('detKet').innerText = ket;
    var myModal = new bootstrap.Modal(document.getElementById('modalDetail'));
    myModal.show();
}

function bukaStatus(id, nama, statusSekarang) {
    document.getElementById('statusId').value = id;
    document.getElementById('statusNama').value = nama;
    
    let selectElement = document.getElementById('statusPilihan');
    if(statusSekarang === 'Dipinjam') {
        selectElement.innerHTML = '<option value="Dipinjam">Sedang Dipinjam</option>';
        selectElement.setAttribute('disabled', 'true');
    } else {
        selectElement.innerHTML = `
            <option value="Tersedia">Tersedia</option>
            <option value="Rusak">Rusak</option>
            <option value="Maintenance">Maintenance</option>
        `;
        selectElement.removeAttribute('disabled');
        selectElement.value = statusSekarang;
    }
    
    var myModal = new bootstrap.Modal(document.getElementById('modalStatus'));
    myModal.show();
}

function bukaPinjam(id, nama) {
    document.getElementById('pinjamId').value = id;
    document.getElementById('pinjamNama').value = nama;
    var myModal = new bootstrap.Modal(document.getElementById('modalPinjam'));
    myModal.show();
}
</script>

<?php include '../tampilan/footer.php'; ?>