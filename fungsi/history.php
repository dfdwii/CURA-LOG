<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
$pageTitle = 'Histori Peminjaman';

// ── Filters ───────────────────────────────────────────────
$fStatus = trim($_GET['status'] ?? '');
$fUser   = ($ME['role'] === 'dokter') ? $ME['id'] : (int)($_GET['user'] ?? 0);
$q       = trim($_GET['q'] ?? '');
$perPage = 15;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$where  = ['1=1'];
$params = [];
if ($fStatus !== '') { $where[] = "h.status_peminjaman=?"; $params[] = $fStatus; }
if ($fUser  >  0)    { $where[] = "h.id_user=?";            $params[] = $fUser; }
if ($q !== '') {
    $where[] = "(a.nama_alat LIKE ? OR u.nama_lengkap LIKE ? OR h.keperluan LIKE ?)";
    $like = "%$q%"; array_push($params, $like, $like, $like);
}
$whereSQL = implode(' AND ', $where);

$stCount = $pdo->prepare("
    SELECT COUNT(*) FROM history_peminjaman h
    JOIN alat a ON h.id_alat=a.id_alat
    JOIN users u ON h.id_user=u.id
    WHERE $whereSQL
");
$stCount->execute($params);
$totalRows  = (int)$stCount->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

$st = $pdo->prepare("
    SELECT h.*, a.nama_alat, a.gambar, a.no_seri,
           u.nama_lengkap, u.role as user_role
    FROM history_peminjaman h
    JOIN alat a ON h.id_alat=a.id_alat
    JOIN users u ON h.id_user=u.id
    WHERE $whereSQL
    ORDER BY h.tgl_pinjam DESC
    LIMIT $perPage OFFSET $offset
");
$st->execute($params);
$histories = $st->fetchAll();

// Users list (for admin/organizer filter)
$users = [];
if ($ME['role'] !== 'dokter') {
    $users = $pdo->query("SELECT id, nama_lengkap, role FROM users ORDER BY nama_lengkap")->fetchAll();
}

// Handle pengembalian
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'kembalikan') {
    if (empty($_POST['_csrf']) || $_POST['_csrf'] !== ($_SESSION['_csrf'] ?? '')) {
        setToast('error', 'Token keamanan tidak valid.'); 
    } else {
        $id_hist = (int)($_POST['id_history'] ?? 0);
        $catatan = trim($_POST['catatan'] ?? '');
        try {
            $pdo->beginTransaction();
            // Ambil data
            $stH = $pdo->prepare("SELECT h.*,a.nama_alat FROM history_peminjaman h JOIN alat a ON h.id_alat=a.id_alat WHERE h.id_history=? AND h.status_peminjaman='Dipinjam'");
            $stH->execute([$id_hist]);
            $hist = $stH->fetch();
            if (!$hist) throw new Exception("Data peminjaman tidak valid.");
            // Cek akses dokter hanya bisa kembalikan miliknya sendiri
            if ($ME['role'] === 'dokter' && $hist['id_user'] !== $ME['id']) {
                throw new Exception("Anda hanya bisa mengembalikan alat yang Anda pinjam.");
            }
            $stU = $pdo->prepare("UPDATE history_peminjaman SET status_peminjaman='Dikembalikan', tgl_kembali=NOW(), catatan=? WHERE id_history=?");
            $stU->execute([$catatan ?: null, $id_hist]);
            $stA = $pdo->prepare("UPDATE alat SET status='Tersedia' WHERE id_alat=?");
            $stA->execute([$hist['id_alat']]);
            $pdo->commit();
            setToast('success', 'Alat "'.clean($hist['nama_alat']).'" berhasil dikembalikan!');
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            setToast('error', $e->getMessage());
        }
    }
    header('Location: ' . BASE_URL . 'fungsi/history.php'); exit;
}

$_SESSION['_csrf'] = $_SESSION['_csrf'] ?? bin2hex(random_bytes(32));
include __DIR__ . '/../tampilan/header.php';
include __DIR__ . '/../tampilan/sidebar.php';
?>

<div class="pc">
  <div class="ph">
    <h1>Histori Peminjaman</h1>
    <p>Rekam jejak seluruh aktivitas peminjaman alat medis.</p>
  </div>

  <!-- Toolbar -->
  <div class="card" style="margin-bottom:16px">
    <div class="card-bd" style="padding:13px 18px">
      <form method="GET" class="toolbar">
        <div class="sw-wrap">
          <i class="bi bi-search"></i>
          <input class="srch" type="text" name="q" value="<?= htmlspecialchars($q) ?>"
                 placeholder="Cari alat, peminjam, keperluan…">
        </div>
        <select class="filt" name="status">
          <option value="">Semua Status</option>
          <option value="Dipinjam"    <?= $fStatus==='Dipinjam'?'selected':'' ?>>Dipinjam</option>
          <option value="Dikembalikan"<?= $fStatus==='Dikembalikan'?'selected':'' ?>>Dikembalikan</option>
        </select>
        <?php if ($ME['role'] !== 'dokter'): ?>
        <select class="filt" name="user">
          <option value="0">Semua Pengguna</option>
          <?php foreach($users as $u): ?>
          <option value="<?=$u['id']?>" <?= $fUser==$u['id']?'selected':'' ?>>
            <?= htmlspecialchars($u['nama_lengkap']) ?> (<?= $u['role'] ?>)
          </option>
          <?php endforeach; ?>
        </select>
        <?php endif; ?>
        <button type="submit" class="btn btn-p btn-sm"><i class="bi bi-funnel"></i> Filter</button>
        <a href="<?= BASE_URL ?>fungsi/history.php" class="btn btn-o btn-sm">Reset</a>
        <span style="margin-left:auto;font-size:.8rem;color:var(--mu)">
          Total: <strong><?= $totalRows ?></strong> record
        </span>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="card">
    <div class="tw">
      <table class="dt">
        <thead><tr>
          <th>Alat</th>
          <th>Dipinjam Oleh</th>
          <th>Keperluan</th>
          <th>Ruangan Tujuan</th>
          <th>Tgl Pinjam</th>
          <th>Tgl Kembali</th>
          <th>Status</th>
          <th>Catatan</th>
          <?php if(in_array($ME['role'],['admin','organizer','dokter'])): ?>
          <th>Aksi</th>
          <?php endif; ?>
        </tr></thead>
        <tbody>
        <?php if(empty($histories)): ?>
          <tr><td colspan="9">
            <div class="empty">
              <i class="bi bi-clock-history"></i>
              <p>Belum ada data histori peminjaman.</p>
            </div>
          </td></tr>
        <?php else: foreach($histories as $h): ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <img class="alat-img" src="<?= imgURL($h['gambar']) ?>" alt="">
                <div>
                  <div style="font-weight:600;font-size:.84rem"><?= clean($h['nama_alat']) ?></div>
                  <?php if($h['no_seri']): ?>
                  <div style="font-size:.72rem;color:var(--mu)"><?= clean($h['no_seri']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td>
              <div style="font-weight:500"><?= clean($h['nama_lengkap']) ?></div>
              <div style="font-size:.72rem;color:var(--mu)"><?= ucfirst($h['user_role']) ?></div>
            </td>
            <td style="max-width:160px;word-break:break-word"><?= clean($h['keperluan']??'–') ?></td>
            <td><?= clean($h['ruangan_tujuan']??'–') ?></td>
            <td style="white-space:nowrap;font-size:.82rem"><?= tglID(substr($h['tgl_pinjam'],0,10)) ?></td>
            <td style="white-space:nowrap;font-size:.82rem">
              <?= $h['tgl_kembali'] ? tglID(substr($h['tgl_kembali'],0,10)) : '<span style="color:var(--mu)">–</span>' ?>
            </td>
            <td>
              <?php if($h['status_peminjaman']==='Dipinjam'): ?>
                <span class="badge badge-warning">Dipinjam</span>
              <?php else: ?>
                <span class="badge badge-success">Dikembalikan</span>
              <?php endif; ?>
            </td>
            <td style="max-width:130px;font-size:.8rem;color:var(--mu)">
              <?= clean($h['catatan']??'–') ?>
            </td>
            <td>
              <?php if($h['status_peminjaman']==='Dipinjam'):
                $canReturn = ($ME['role'] !== 'dokter') || ($h['id_user'] == $ME['id']);
                if($canReturn): ?>
                <button class="btn btn-g btn-xs"
                  onclick="openKembali(<?= $h['id_history'] ?>,'<?= addslashes(clean($h['nama_alat'])) ?>')">
                  <i class="bi bi-arrow-return-left"></i> Kembalikan
                </button>
              <?php endif; endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
    <!-- Pagination -->
    <?php if($totalPages > 1): ?>
    <div class="pagi">
      <?php
        $qStr = http_build_query(array_filter(array_merge($_GET,['page'=>null]),fn($v)=>$v!==null&&$v!=''));
        if($page>1) echo "<a href='?{$qStr}&page=".($page-1)."'>‹ Prev</a>";
        for($i=max(1,$page-2);$i<=min($totalPages,$page+2);$i++){
          $c=$i===$page?'cur':'';
          echo "<a href='?{$qStr}&page={$i}' class='{$c}'>{$i}</a>";
        }
        if($page<$totalPages) echo "<a href='?{$qStr}&page=".($page+1)."'>Next ›</a>";
      ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Kembalikan -->
<div class="overlay" id="modalKembali">
  <div class="modal">
    <div class="modal-hd">
      <h3><i class="bi bi-arrow-return-left" style="color:var(--grn)"></i> Kembalikan Alat</h3>
      <button class="mc" onclick="closeModal('modalKembali')"><i class="bi bi-x"></i></button>
    </div>
    <form method="POST">
      <div class="modal-bd">
        <input type="hidden" name="_csrf" value="<?= $_SESSION['_csrf'] ?>">
        <input type="hidden" name="action" value="kembalikan">
        <input type="hidden" name="id_history" id="kembaliId">
        <div class="fg">
          <label>Alat yang Dikembalikan</label>
          <input class="fc" type="text" id="kembaliNama" readonly style="background:var(--bg)">
        </div>
        <div class="fg">
          <label>Catatan Pengembalian</label>
          <textarea class="fc" name="catatan" rows="3"
            placeholder="Kondisi saat dikembalikan, catatan kerusakan, dll. (opsional)"></textarea>
        </div>
      </div>
      <div class="modal-ft">
        <button type="button" class="btn btn-o btn-sm" onclick="closeModal('modalKembali')">Batal</button>
        <button type="submit" class="btn btn-g btn-sm">
          <i class="bi bi-check-lg"></i> Konfirmasi Pengembalian
        </button>
      </div>
    </form>
  </div>
</div>

<?php
$extraJS = <<<JS
<script>
function openKembali(id, nama){
  document.getElementById('kembaliId').value=id;
  document.getElementById('kembaliNama').value=nama;
  openModal('modalKembali');
}
</script>
JS;
include __DIR__ . '/../tampilan/footer.php';
?>
