<?php
require_once '../config.php';
require_once '../auth_check.php';

$role = $_SESSION['role']; 

if (isset($_POST['simpan_status'])) {
    $id_alat = $_POST['id_alat_status'];
    $status_baru = $_POST['status_baru'];
    
    mysqli_query($koneksi, "UPDATE alat SET status='$status_baru' WHERE id_alat='$id_alat'");
    
    if ($status_baru == 'Tersedia') {
        mysqli_query($koneksi, "UPDATE history_peminjaman SET status_peminjaman='Dikembalikan', tgl_kembali=NOW() 
                                WHERE id_alat='$id_alat' AND status_peminjaman='Dipinjam'");
    }
    
    echo "<script>alert('Status alat berhasil diperbarui!'); window.location='inventory.php';</script>";
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
                                <td><img src="../assets/img/alat_medis/<?php echo $data['gambar']; ?>" class="img-alat"></td>
                                <td><strong><?php echo $data['nama_alat']; ?></strong></td>
                                <td><?php echo $data['merk']; ?></td>
                                <td>
                                    <?php
                                    $warna = 'bg-secondary';
                                    if ($data['status'] == 'Tersedia') $warna = 'bg-success';
                                    if ($data['status'] == 'Dipinjam') $warna = 'bg-warning text-dark';
                                    if ($data['status'] == 'Rusak') $warna = 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $warna; ?>"><?php echo $data['status']; ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm text-white" onclick="lihatDetail('<?php echo $data['nama_alat']; ?>', '<?php echo $data['merk']; ?>', '<?php echo $data['keterangan']; ?>')">Detail</button>

                                    <?php if ($role == 'dokter') { ?>
                                        <?php if ($data['status'] == 'Tersedia') { ?>
                                            <button class="btn btn-success btn-sm" onclick="bukaPinjam('<?php echo $data['id_alat']; ?>', '<?php echo $data['nama_alat']; ?>')">Pinjam</button>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <button class="btn btn-secondary btn-sm" onclick="bukaStatus('<?php echo $data['id_alat']; ?>', '<?php echo $data['nama_alat']; ?>', '<?php echo $data['status']; ?>')">Status</button>
                                        <a href="edit_alat.php?id=<?php echo $data['id_alat']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="hapus_alat.php?id=<?php echo $data['id_alat']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Hapus</a>
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

<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">Ubah Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_alat_status" id="statusId">
                    <div class="mb-3">
                        <label class="small text-muted">Alat:</label>
                        <input type="text" id="statusNama" class="form-control fw-bold border-0 bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Pilih Status:</label>
                        <select name="status_baru" id="statusPilihan" class="form-select" required>
                            <option value="Tersedia">Tersedia (Kembali)</option>
                            <option value="Rusak">Rusak</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Dipinjam" id="opsiDipinjam" hidden>Dipinjam</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="simpan_status" class="btn btn-secondary w-100">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Alat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Nama:</strong> <span id="detNama"></span></p>
                <p><strong>Merk:</strong> <span id="detMerk"></span></p>
                <p><strong>Keterangan:</strong> <span id="detKet"></span></p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPinjam" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Form Pinjam Alat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses_pinjam.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_alat" id="pinjamId">
                    <div class="mb-3">
                        <label>Alat:</label>
                        <input type="text" id="pinjamNama" class="form-control bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Keperluan:</label>
                        <input type="text" name="keperluan" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Ruangan:</label>
                        <input type="text" name="ruangan_tujuan" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="pinjam" class="btn btn-success w-100">Konfirmasi Pinjam</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function bukaStatus(id, nama, statusSekarang) {
    document.getElementById('statusId').value = id;
    document.getElementById('statusNama').value = nama;
    const select = document.getElementById('statusPilihan');
    const opsiDipinjam = document.getElementById('opsiDipinjam');
    opsiDipinjam.hidden = statusSekarang !== 'Dipinjam';
    select.value = statusSekarang;
    new bootstrap.Modal(document.getElementById('modalStatus')).show();
}

function lihatDetail(nama, merk, ket) {
    document.getElementById('detNama').innerText = nama;
    document.getElementById('detMerk').innerText = merk;
    document.getElementById('detKet').innerText = ket;
    new bootstrap.Modal(document.getElementById('modalDetail')).show();
}

function bukaPinjam(id, nama) {
    document.getElementById('pinjamId').value = id;
    document.getElementById('pinjamNama').value = nama;
    new bootstrap.Modal(document.getElementById('modalPinjam')).show();
}
</script>

<?php include '../tampilan/footer.php'; ?>