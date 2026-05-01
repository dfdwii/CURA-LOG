<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
requireRole('admin');
$pageTitle = 'Data Ruangan';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['_csrf']) || $_POST['_csrf'] !== ($_SESSION['_csrf'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $action = $_POST['form_action'] ?? '';

        if ($action === 'tambah') {
            $nama = trim($_POST['nama_ruangan'] ?? '');
            if (isBlank($nama)) { $errors[] = 'Nama ruangan wajib diisi.'; }
            else {
                $chk = $pdo->prepare("SELECT id_ruangan FROM ruangan WHERE nama_ruangan=? LIMIT 1");
                $chk->execute([$nama]);
                if ($chk->fetch()) { $errors[] = "Ruangan '$nama' sudah ada."; }
                else {
                    try {
                        $pdo->prepare("INSERT INTO ruangan (nama_ruangan) VALUES (?)")->execute([$nama]);
                        setToast('success', "Ruangan '$nama' berhasil ditambahkan!");
                        header('Location: ' . BASE_URL . 'fungsi/ruangan.php'); exit;
                    } catch (PDOException $e) {
                        $errors[] = 'Gagal menyimpan.';
                    }
                }
            }
        }

        if ($action === 'edit') {
            $id   = (int)($_POST['id'] ?? 0);
            $nama = trim($_POST['nama_ruangan'] ?? '');
            if (isBlank($nama)) { $errors[] = 'Nama ruangan wajib diisi.'; }
            else {
                try {
                    $pdo->prepare("UPDATE ruangan SET nama_ruangan=? WHERE id_ruangan=?")->execute([$nama,$id]);
                    setToast('success', 'Ruangan berhasil diperbarui!');
                    header('Location: ' . BASE_URL . 'fungsi/ruangan.php'); exit;
                } catch (PDOException $e) { $errors[] = 'Gagal memperbarui.'; }
            }
        }

        if ($action === 'hapus') {
            $id = (int)($_POST['id'] ?? 0);
            // Cek ada alat di ruangan ini
            $chk = $pdo->prepare("SELECT COUNT(*) FROM alat WHERE id_ruangan=?");
            $chk->execute([$id]);
            if ($chk->fetchColumn() > 0) {
                setToast('error', 'Ruangan masih memiliki alat terdaftar, tidak dapat dihapus.');
            } else {
                try {
                    $pdo->prepare("DELETE FROM ruangan WHERE id_ruangan=?")->execute([$id]);
                    setToast('success', 'Ruangan berhasil dihapus.');
                } catch (PDOException $e) { setToast('error','Gagal menghapus ruangan.'); }
            }
            header('Location: ' . BASE_URL . 'fungsi/ruangan.php'); exit;
        }
    }
}

$ruangans = $pdo->query("
    SELECT r.*, COUNT(a.id_alat) as jml_alat
    FROM ruangan r
    LEFT JOIN alat a ON r.id_ruangan=a.id_ruangan
    GROUP BY r.id_ruangan
    ORDER BY r.nama_ruangan
")->fetchAll();

$_SESSION['_csrf'] = $_SESSION['_csrf'] ?? bin2hex(random_bytes(32));
include __DIR__ . '/../tampilan/header.php';
include __DIR__ . '/../tampilan/sidebar.php';
?>

<div class="pc">
  <div class="ph" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px">
    <div><h1>Data Ruangan</h1><p>Kelola daftar ruangan untuk penempatan alat medis.</p></div>
    <button class="btn btn-p" onclick="openModal('modalTambah')">
      <i class="bi bi-plus-lg"></i> Tambah Ruangan
    </button>
  </div>

  <?php if(!empty($errors)): ?>
  <div style="background:#FEF2F2;border:1.5px solid #FECACA;border-radius:10px;padding:13px 16px;margin-bottom:16px">
    <div style="font-weight:600;color:#DC2626;display:flex;align-items:center;gap:7px">
      <i class="bi bi-exclamation-circle-fill"></i> Kesalahan:
    </div>
    <ul style="margin:4px 0 0;padding-left:18px;color:#DC2626;font-size:.83rem">
      <?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-hd">
      <span class="card-title"><i class="bi bi-building" style="color:var(--p)"></i> Daftar Ruangan (<?= count($ruangans) ?>)</span>
    </div>
    <div class="tw">
      <table class="dt">
        <thead><tr><th>#</th><th>Nama Ruangan</th><th>Jumlah Alat</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if(empty($ruangans)): ?>
          <tr><td colspan="4"><div class="empty"><i class="bi bi-building"></i><p>Belum ada ruangan.</p></div></td></tr>
        <?php else: foreach($ruangans as $i=>$r): ?>
          <tr>
            <td style="color:var(--mu)"><?= $i+1 ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="width:32px;height:32px;border-radius:8px;background:var(--pl);
                            display:flex;align-items:center;justify-content:center;flex-shrink:0">
                  <i class="bi bi-building-fill" style="color:var(--p);font-size:14px"></i>
                </div>
                <strong><?= clean($r['nama_ruangan']) ?></strong>
              </div>
            </td>
            <td>
              <span class="badge <?= $r['jml_alat']>0?'badge-success':'badge-secondary' ?>">
                <?= $r['jml_alat'] ?> Alat
              </span>
            </td>
            <td>
              <div class="act">
                <button class="btn btn-a btn-xs"
                  onclick="openEditR(<?= $r['id_ruangan'] ?>,'<?= addslashes(clean($r['nama_ruangan'])) ?>')">
                  <i class="bi bi-pencil"></i> Edit
                </button>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="_csrf" value="<?= $_SESSION['_csrf'] ?>">
                  <input type="hidden" name="form_action" value="hapus">
                  <input type="hidden" name="id" value="<?= $r['id_ruangan'] ?>">
                  <button type="submit" class="btn btn-r btn-xs"
                    onclick="return confirmDel('Hapus ruangan <?= addslashes(clean($r['nama_ruangan'])) ?>?')"
                    <?= $r['jml_alat']>0?'disabled title="Ada alat di ruangan ini"':'' ?>>
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah -->
<div class="overlay" id="modalTambah">
  <div class="modal" style="max-width:400px">
    <div class="modal-hd">
      <h3><i class="bi bi-plus-circle" style="color:var(--p)"></i> Tambah Ruangan</h3>
      <button class="mc" onclick="closeModal('modalTambah')"><i class="bi bi-x"></i></button>
    </div>
    <form method="POST">
      <div class="modal-bd">
        <input type="hidden" name="_csrf" value="<?= $_SESSION['_csrf'] ?>">
        <input type="hidden" name="form_action" value="tambah">
        <div class="fg">
          <label>Nama Ruangan <span class="req">*</span></label>
          <input class="fc" type="text" name="nama_ruangan"
                 placeholder="Contoh: Ruang Operasi" required>
        </div>
      </div>
      <div class="modal-ft">
        <button type="button" class="btn btn-o btn-sm" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-p btn-sm"><i class="bi bi-check-lg"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="overlay" id="modalEdit">
  <div class="modal" style="max-width:400px">
    <div class="modal-hd">
      <h3><i class="bi bi-pencil-square" style="color:var(--amb)"></i> Edit Ruangan</h3>
      <button class="mc" onclick="closeModal('modalEdit')"><i class="bi bi-x"></i></button>
    </div>
    <form method="POST">
      <div class="modal-bd">
        <input type="hidden" name="_csrf" value="<?= $_SESSION['_csrf'] ?>">
        <input type="hidden" name="form_action" value="edit">
        <input type="hidden" name="id" id="editRId">
        <div class="fg">
          <label>Nama Ruangan <span class="req">*</span></label>
          <input class="fc" type="text" name="nama_ruangan" id="editRNama" required>
        </div>
      </div>
      <div class="modal-ft">
        <button type="button" class="btn btn-o btn-sm" onclick="closeModal('modalEdit')">Batal</button>
        <button type="submit" class="btn btn-a btn-sm"><i class="bi bi-check-lg"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<?php
$extraJS = <<<JS
<script>
function openEditR(id,nama){
  document.getElementById('editRId').value=id;
  document.getElementById('editRNama').value=nama;
  openModal('modalEdit');
}
</script>
JS;
include __DIR__ . '/../tampilan/footer.php';
?>
