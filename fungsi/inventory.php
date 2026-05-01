<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
$pageTitle = 'Inventaris Alat';

// ── Pagination ────────────────────────────────────────────
$perPage = 12;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

// ── Filters ───────────────────────────────────────────────
$q       = trim($_GET['q']   ?? '');
$fStatus = trim($_GET['status'] ?? '');
$fRuang  = (int)($_GET['ruangan'] ?? 0);
$fView   = $_GET['view'] ?? 'grid'; // grid | table

$params = [];
$where  = ['1=1'];
if ($q !== '') {
    $where[] = "(a.nama_alat LIKE ? OR a.merk LIKE ? OR a.no_seri LIKE ? OR a.kategori LIKE ?)";
    $like = "%$q%";
    array_push($params, $like, $like, $like, $like);
}
if ($fStatus !== '') { $where[] = "a.status = ?"; $params[] = $fStatus; }
if ($fRuang  > 0)    { $where[] = "a.id_ruangan = ?"; $params[] = $fRuang; }
$whereSQL = implode(' AND ', $where);

// Count total
$stCount = $pdo->prepare("SELECT COUNT(*) FROM alat a WHERE $whereSQL");
$stCount->execute($params);
$totalRows = (int)$stCount->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

// Fetch data
$st = $pdo->prepare("
    SELECT a.*, r.nama_ruangan
    FROM alat a
    LEFT JOIN ruangan r ON a.id_ruangan = r.id_ruangan
    WHERE $whereSQL
    ORDER BY a.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$st->execute($params);
$alats = $st->fetchAll();

// Ruangan list for filter
$ruangans = $pdo->query("SELECT * FROM ruangan ORDER BY nama_ruangan")->fetchAll();

include __DIR__ . '/../tampilan/header.php';
include __DIR__ . '/../tampilan/sidebar.php';
?>

<div class="pc">
  <div class="ph" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px">
    <div>
      <h1>Inventaris Alat Medis</h1>
      <p>Menampilkan <?= $totalRows ?> alat ditemukan</p>
    </div>
    <?php if(in_array($ME['role'],['admin','organizer'])): ?>
    <a href="<?= BASE_URL ?>fungsi/tambah_alat.php" class="btn btn-p">
      <i class="bi bi-plus-lg"></i> Tambah Alat
    </a>
    <?php endif; ?>
  </div>

  <!-- Toolbar -->
  <div class="card" style="margin-bottom:18px">
    <div class="card-bd" style="padding:14px 18px">
      <form method="GET" class="toolbar" id="filterForm">
        <input type="hidden" name="view" value="<?= htmlspecialchars($fView) ?>">
        <div class="sw-wrap">
          <i class="bi bi-search"></i>
          <input class="srch" type="text" name="q" id="searchQ"
            value="<?= htmlspecialchars($q) ?>" placeholder="Cari nama, merk, no. seri…">
        </div>
        <select class="filt" name="status">
          <option value="">Semua Status</option>
          <?php foreach(['Tersedia','Dipinjam','Rusak','Perlu Kalibrasi','Maintenance'] as $s): ?>
          <option value="<?=$s?>" <?=$fStatus===$s?'selected':''?>><?=$s?></option>
          <?php endforeach; ?>
        </select>
        <select class="filt" name="ruangan">
          <option value="0">Semua Ruangan</option>
          <?php foreach($ruangans as $r): ?>
          <option value="<?=$r['id_ruangan']?>" <?=$fRuang==$r['id_ruangan']?'selected':''?>>
            <?= htmlspecialchars($r['nama_ruangan']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-p btn-sm"><i class="bi bi-funnel"></i> Filter</button>
        <a href="<?= BASE_URL ?>fungsi/inventory.php" class="btn btn-o btn-sm">Reset</a>
        <div style="margin-left:auto;display:flex;gap:5px">
          <a href="?<?= http_build_query(array_merge($_GET,['view'=>'grid'])) ?>"
             class="btn btn-sm <?= $fView==='grid'?'btn-p':'btn-o' ?>" title="Grid view">
            <i class="bi bi-grid"></i></a>
          <a href="?<?= http_build_query(array_merge($_GET,['view'=>'table'])) ?>"
             class="btn btn-sm <?= $fView==='table'?'btn-p':'btn-o' ?>" title="Table view">
            <i class="bi bi-list-ul"></i></a>
        </div>
      </form>
    </div>
  </div>

  <?php if(empty($alats)): ?>
  <div class="card"><div class="card-bd">
    <div class="empty"><i class="bi bi-inbox"></i><p>Tidak ada alat yang cocok dengan filter.</p></div>
  </div></div>

  <?php elseif($fView === 'table'): ?>
  <!-- Table View -->
  <div class="card">
    <div class="tw">
      <table class="dt">
        <thead><tr>
          <th>Gambar</th><th>No. Seri</th><th>Nama Alat</th><th>Merk</th>
          <th>Kategori</th><th>Ruangan</th><th>Kalibrasi</th>
          <th>Status</th><th>Kondisi</th><th>Aksi</th>
        </tr></thead>
        <tbody>
        <?php foreach($alats as $r): ?>
        <tr>
          <td><img class="alat-img" src="<?= imgURL($r['gambar']) ?>" alt=""></td>
          <td><code style="font-size:.73rem;background:#F1F5F9;padding:2px 6px;border-radius:4px"><?= clean($r['no_seri']??'–') ?></code></td>
          <td><strong><?= clean($r['nama_alat']) ?></strong></td>
          <td><?= clean($r['merk']??'–') ?></td>
          <td><?= clean($r['kategori']??'–') ?></td>
          <td><?= clean($r['nama_ruangan']??'–') ?></td>
          <td style="white-space:nowrap"><?= tglID($r['masa_kalibrasi']) ?></td>
          <td><span class="badge <?= statusBadge($r['status']) ?>"><?= clean($r['status']) ?></span></td>
          <td><span class="badge <?= kondisiBadge($r['kondisi']) ?>"><?= clean($r['kondisi']) ?></span></td>
          <td>
            <div class="act">
              <button class="btn btn-o btn-xs" title="Detail"
                onclick="openDetail(<?= htmlspecialchars(json_encode($r)) ?>)">
                <i class="bi bi-eye"></i>
              </button>
              <?php if(in_array($ME['role'],['admin','organizer'])): ?>
              <a href="<?= BASE_URL ?>fungsi/edit_alat.php?id=<?= $r['id_alat'] ?>"
                 class="btn btn-a btn-xs" title="Edit"><i class="bi bi-pencil"></i></a>
              <button class="btn btn-o btn-xs" title="Update Status"
                onclick="openStatus(<?= htmlspecialchars(json_encode($r)) ?>)">
                <i class="bi bi-activity"></i></button>
              <?php endif; ?>
              <?php if($ME['role']==='admin'): ?>
              <a href="<?= BASE_URL ?>fungsi/hapus_alat.php?id=<?= $r['id_alat'] ?>"
                 class="btn btn-r btn-xs" title="Hapus"
                 onclick="return confirmDel('Hapus alat <?= addslashes(clean($r['nama_alat'])) ?>?')">
                <i class="bi bi-trash"></i></a>
              <?php endif; ?>
              <?php if($ME['role']==='dokter' && $r['status']==='Tersedia'): ?>
              <button class="btn btn-t btn-xs" title="Pinjam"
                onclick="openPinjam(<?= $r['id_alat'] ?>,'<?= addslashes(clean($r['nama_alat'])) ?>')">
                <i class="bi bi-arrow-up-right-square"></i></button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
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
          $cls=$i===$page?'cur':'';
          echo "<a href='?{$qStr}&page={$i}' class='{$cls}'>{$i}</a>";
        }
        if($page<$totalPages) echo "<a href='?{$qStr}&page=".($page+1)."'>Next ›</a>";
      ?>
    </div>
    <?php endif; ?>
  </div>

  <?php else: ?>
  <!-- Grid View -->
  <div class="ag">
    <?php foreach($alats as $r): ?>
    <div class="ac">
      <img class="ac-img" src="<?= imgURL($r['gambar']) ?>" alt="<?= clean($r['nama_alat']) ?>">
      <div class="ac-body">
        <div class="ac-name"><?= clean($r['nama_alat']) ?></div>
        <div class="ac-sub"><?= clean($r['merk']??'–') ?> &middot; <?= clean($r['kategori']??'–') ?></div>
        <div style="font-size:.76rem;color:var(--mu)">
          <i class="bi bi-geo-alt" style="color:var(--p)"></i> <?= clean($r['nama_ruangan']??'–') ?>
        </div>
        <div style="margin-top:6px;font-size:.76rem;color:var(--mu)">
          Kalibrasi: <?= tglID($r['masa_kalibrasi']) ?>
        </div>
      </div>
      <div class="ac-foot">
        <span class="badge <?= statusBadge($r['status']) ?>"><?= clean($r['status']) ?></span>
        <div class="act">
          <button class="btn btn-o btn-xs"
            onclick="openDetail(<?= htmlspecialchars(json_encode($r)) ?>)"
            title="Detail"><i class="bi bi-eye"></i></button>
          <?php if(in_array($ME['role'],['admin','organizer'])): ?>
          <a href="<?= BASE_URL ?>fungsi/edit_alat.php?id=<?= $r['id_alat'] ?>"
             class="btn btn-a btn-xs" title="Edit"><i class="bi bi-pencil"></i></a>
          <button class="btn btn-o btn-xs" title="Update Status"
            onclick="openStatus(<?= htmlspecialchars(json_encode($r)) ?>)">
            <i class="bi bi-activity"></i></button>
          <?php endif; ?>
          <?php if($ME['role']==='admin'): ?>
          <a href="<?= BASE_URL ?>fungsi/hapus_alat.php?id=<?= $r['id_alat'] ?>"
             class="btn btn-r btn-xs"
             onclick="return confirmDel('Hapus alat <?= addslashes(clean($r['nama_alat'])) ?>?')"
             title="Hapus"><i class="bi bi-trash"></i></a>
          <?php endif; ?>
          <?php if($ME['role']==='dokter' && $r['status']==='Tersedia'): ?>
          <button class="btn btn-t btn-xs"
            onclick="openPinjam(<?= $r['id_alat'] ?>,'<?= addslashes(clean($r['nama_alat'])) ?>')"
            title="Pinjam"><i class="bi bi-arrow-up-right-square"></i></button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <!-- Pagination grid -->
  <?php if($totalPages > 1): ?>
  <div class="pagi" style="margin-top:18px;border:none;justify-content:center">
    <?php
      $qStr = http_build_query(array_filter(array_merge($_GET,['page'=>null]),fn($v)=>$v!==null&&$v!=''));
      if($page>1) echo "<a href='?{$qStr}&page=".($page-1)."'>‹ Prev</a>";
      for($i=max(1,$page-2);$i<=min($totalPages,$page+2);$i++){
        $cls=$i===$page?'cur':'';
        echo "<a href='?{$qStr}&page={$i}' class='{$cls}'>{$i}</a>";
      }
      if($page<$totalPages) echo "<a href='?{$qStr}&page=".($page+1)."'>Next ›</a>";
    ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<!-- Detail Modal -->
<div class="overlay" id="modalDetail">
  <div class="modal">
    <div class="modal-hd">
      <h3><i class="bi bi-info-circle" style="color:var(--p)"></i> Detail Alat</h3>
      <button class="mc" onclick="closeModal('modalDetail')"><i class="bi bi-x"></i></button>
    </div>
    <div class="modal-bd">
      <img id="dImg" src="" alt="" class="detail-img" style="margin-bottom:16px">
      <div id="dRows"></div>
    </div>
    <div class="modal-ft">
      <button class="btn btn-o btn-sm" onclick="closeModal('modalDetail')">Tutup</button>
    </div>
  </div>
</div>

<!-- Pinjam Modal (dokter) -->
<div class="overlay" id="modalPinjam">
  <div class="modal">
    <div class="modal-hd">
      <h3><i class="bi bi-arrow-up-right-square" style="color:var(--t)"></i> Pinjam Alat</h3>
      <button class="mc" onclick="closeModal('modalPinjam')"><i class="bi bi-x"></i></button>
    </div>
    <form method="POST" action="<?= BASE_URL ?>fungsi/proses_pinjam.php">
      <div class="modal-bd">
        <input type="hidden" name="_csrf" value="<?= $_SESSION['_csrf'] ?? '' ?>">
        <input type="hidden" name="id_alat" id="pinjamId">
        <div class="fg">
          <label>Alat yang Dipinjam</label>
          <input class="fc" type="text" id="pinjamNama" readonly
                 style="background:var(--bg);cursor:default">
        </div>
        <div class="fg">
          <label>Keperluan <span class="req">*</span></label>
          <input class="fc" type="text" name="keperluan" id="pinjamKep"
                 placeholder="Contoh: Tindakan operasi pasien…" required>
          <div class="ferr" id="kepErr">Keperluan tidak boleh kosong.</div>
        </div>
        <div class="fg">
          <label>Ruangan Tujuan <span class="req">*</span></label>
          <input class="fc" type="text" name="ruangan_tujuan" id="pinjamRuang"
                 placeholder="Contoh: ICU Bed 3" required>
          <div class="ferr" id="ruangErr">Ruangan tujuan tidak boleh kosong.</div>
        </div>
      </div>
      <div class="modal-ft">
        <button type="button" class="btn btn-o btn-sm" onclick="closeModal('modalPinjam')">Batal</button>
        <button type="submit" class="btn btn-t btn-sm" id="btnPinjamSubmit">
          <i class="bi bi-check-lg"></i> Konfirmasi Pinjam
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Update Status Modal (admin/organizer) -->
<?php if(in_array($ME['role'],['admin','organizer'])): ?>
<div class="overlay" id="modalStatus">
  <div class="modal" style="max-width:420px">
    <div class="modal-hd">
      <h3><i class="bi bi-activity" style="color:var(--amb)"></i> Update Status Alat</h3>
      <button class="mc" onclick="closeModal('modalStatus')"><i class="bi bi-x"></i></button>
    </div>
    <form method="POST" action="<?= BASE_URL ?>fungsi/update_status.php">
      <div class="modal-bd">
        <input type="hidden" name="_csrf" value="<?= $_SESSION['_csrf'] ?? '' ?>">
        <input type="hidden" name="id_alat" id="statusId">
        <div class="fg">
          <label>Nama Alat</label>
          <input class="fc" type="text" id="statusNama" readonly style="background:var(--bg)">
        </div>
        <div class="fg">
          <label>Status <span class="req">*</span></label>
          <select class="fs" name="status" id="statusVal">
            <option value="Tersedia">Tersedia</option>
            <option value="Dipinjam">Dipinjam</option>
            <option value="Rusak">Rusak</option>
            <option value="Maintenance">Maintenance</option>
            <option value="Perlu Kalibrasi">Perlu Kalibrasi</option>
          </select>
        </div>
        <div class="fg">
          <label>Kondisi <span class="req">*</span></label>
          <select class="fs" name="kondisi" id="kondisiVal">
            <option value="Baik">Baik</option>
            <option value="Perlu Kalibrasi">Perlu Kalibrasi</option>
            <option value="Rusak">Rusak</option>
          </select>
        </div>
        <div class="fg">
          <label>Keterangan</label>
          <textarea class="fc" name="keterangan" id="statusKet" rows="2"
                    placeholder="Catatan perubahan status (opsional)"></textarea>
        </div>
      </div>
      <div class="modal-ft">
        <button type="button" class="btn btn-o btn-sm" onclick="closeModal('modalStatus')">Batal</button>
        <button type="submit" class="btn btn-a btn-sm">
          <i class="bi bi-check-lg"></i> Simpan Status
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php
$extraJS = <<<JS
<script>
function openDetail(r){
  document.getElementById('dImg').src = r.gambar
    ? '<?= BASE_URL ?>assets/img/alat_medis/' + encodeURIComponent(r.gambar)
    : '<?= BASE_URL ?>assets/img/alat_medis/stethoscope.webp';
  const rows = [
    ['No. Seri',    r.no_seri   || '–'],
    ['Nama Alat',   r.nama_alat || '–'],
    ['Merk',        r.merk      || '–'],
    ['Kategori',    r.kategori  || '–'],
    ['Ruangan',     r.nama_ruangan || '–'],
    ['Tgl Masuk',   r.tgl_masuk || '–'],
    ['Kalibrasi',   r.masa_kalibrasi || '–'],
    ['Status',      r.status    || '–'],
    ['Kondisi',     r.kondisi   || '–'],
    ['Keterangan',  r.keterangan || '–'],
  ];
  document.getElementById('dRows').innerHTML = rows.map(([k,v])=>
    `<div class="dl-row"><span class="dl-key">${k}</span><span class="dl-val">${v}</span></div>`
  ).join('');
  openModal('modalDetail');
}
function openPinjam(id, nama){
  document.getElementById('pinjamId').value   = id;
  document.getElementById('pinjamNama').value = nama;
  document.getElementById('pinjamKep').value  = '';
  document.getElementById('pinjamRuang').value= '';
  openModal('modalPinjam');
}
function openStatus(r){
  document.getElementById('statusId').value   = r.id_alat;
  document.getElementById('statusNama').value = r.nama_alat;
  document.getElementById('statusVal').value  = r.status;
  document.getElementById('kondisiVal').value = r.kondisi;
  document.getElementById('statusKet').value  = r.keterangan || '';
  openModal('modalStatus');
}
document.querySelector('#modalPinjam form').addEventListener('submit',function(e){
  const k=document.getElementById('pinjamKep');
  const ru=document.getElementById('pinjamRuang');
  const ke=document.getElementById('kepErr');
  const re=document.getElementById('ruangErr');
  let ok=true;
  ke.style.display='none'; re.style.display='none';
  k.classList.remove('err'); ru.classList.remove('err');
  if(!k.value.trim()){ke.style.display='block';k.classList.add('err');ok=false;}
  if(!ru.value.trim()){re.style.display='block';ru.classList.add('err');ok=false;}
  if(!ok) e.preventDefault();
});
</script>
JS;
include __DIR__ . '/../tampilan/footer.php';
