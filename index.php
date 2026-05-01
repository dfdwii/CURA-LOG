<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_check.php';
$pageTitle = 'Dashboard';

// ── Stats ─────────────────────────────────────────────────
$total      = $pdo->query("SELECT COUNT(*) FROM alat")->fetchColumn();
$tersedia   = $pdo->query("SELECT COUNT(*) FROM alat WHERE status='Tersedia'")->fetchColumn();
$dipinjam   = $pdo->query("SELECT COUNT(*) FROM alat WHERE status='Dipinjam'")->fetchColumn();
$rusak      = $pdo->query("SELECT COUNT(*) FROM alat WHERE status='Rusak' OR status='Maintenance'")->fetchColumn();
$kalibrasi  = $pdo->query("SELECT COUNT(*) FROM alat WHERE status='Perlu Kalibrasi' OR kondisi='Perlu Kalibrasi'")->fetchColumn();

// ── Latest 5 entries ──────────────────────────────────────
$latest = $pdo->query("
    SELECT a.*, r.nama_ruangan
    FROM alat a LEFT JOIN ruangan r ON a.id_ruangan=r.id_ruangan
    ORDER BY a.created_at DESC LIMIT 5
")->fetchAll();

// ── Active loans ──────────────────────────────────────────
$pinjam_aktif = $pdo->query("
    SELECT h.*, a.nama_alat, a.gambar, u.nama_lengkap, u.role
    FROM history_peminjaman h
    JOIN alat a ON h.id_alat=a.id_alat
    JOIN users u ON h.id_user=u.id
    WHERE h.status_peminjaman='Dipinjam'
    ORDER BY h.tgl_pinjam DESC LIMIT 5
")->fetchAll();

// ── Alat rusak & kalibrasi (notif) ────────────────────────
$notif_rusak = $pdo->query("
    SELECT a.nama_alat, a.no_seri, r.nama_ruangan
    FROM alat a LEFT JOIN ruangan r ON a.id_ruangan=r.id_ruangan
    WHERE a.status='Rusak' OR a.kondisi='Rusak'
")->fetchAll();
$notif_kalib = $pdo->query("
    SELECT a.nama_alat, a.no_seri, a.masa_kalibrasi, r.nama_ruangan
    FROM alat a LEFT JOIN ruangan r ON a.id_ruangan=r.id_ruangan
    WHERE a.masa_kalibrasi <= CURDATE()
      AND a.status NOT IN ('Rusak','Maintenance')
")->fetchAll();

include __DIR__ . '/tampilan/header.php';
include __DIR__ . '/tampilan/sidebar.php';
?>

<div class="pc">
  <div class="ph" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px">
    <div>
      <h1>Dashboard</h1>
      <p>Selamat datang, <strong><?= htmlspecialchars($ME['nama']) ?></strong>. Berikut ringkasan inventaris hari ini.</p>
    </div>
    <?php if(in_array($ME['role'],['admin','organizer'])): ?>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <a href="<?= BASE_URL ?>fungsi/export.php?type=inventaris" class="btn btn-o btn-sm">
        <i class="bi bi-download"></i> Export Inventaris
      </a>
      <a href="<?= BASE_URL ?>fungsi/export.php?type=history" class="btn btn-o btn-sm">
        <i class="bi bi-download"></i> Export Histori
      </a>
    </div>
    <?php endif; ?>
  </div>

  <!-- Stat Cards -->
  <div class="sg">
    <div class="sc">
      <div class="si blue"><i class="bi bi-box-seam-fill"></i></div>
      <div><div class="sv"><?= $total ?></div><div class="sl">Total Alat Medis</div></div>
    </div>
    <div class="sc">
      <div class="si grn"><i class="bi bi-check-circle-fill"></i></div>
      <div><div class="sv"><?= $tersedia ?></div><div class="sl">Tersedia</div></div>
    </div>
    <div class="sc">
      <div class="si teal"><i class="bi bi-arrow-left-right"></i></div>
      <div><div class="sv"><?= $dipinjam ?></div><div class="sl">Sedang Dipinjam</div></div>
    </div>
    <div class="sc">
      <div class="si amb"><i class="bi bi-exclamation-triangle-fill"></i></div>
      <div><div class="sv"><?= $kalibrasi ?></div><div class="sl">Perlu Kalibrasi</div></div>
    </div>
    <div class="sc">
      <div class="si red"><i class="bi bi-tools"></i></div>
      <div><div class="sv"><?= $rusak ?></div><div class="sl">Rusak / Maintenance</div></div>
    </div>
  </div>

  <!-- Notifications -->
  <?php if(count($notif_rusak)>0 || count($notif_kalib)>0): ?>
  <div class="card mb-5" style="margin-bottom:20px">
    <div class="card-hd">
      <span class="card-title"><i class="bi bi-bell-fill" style="color:var(--amb)"></i> Notifikasi Penting</span>
    </div>
    <div class="card-bd" style="display:flex;flex-direction:column;gap:10px">
      <?php if(count($notif_rusak)>0): ?>
      <div class="notif-bar nb-red" onclick="document.getElementById('listRusak').classList.toggle('hidden')" style="flex-direction:column;align-items:flex-start">
        <div style="display:flex;align-items:center;gap:9px;width:100%">
          <i class="bi bi-tools"></i>
          <span><strong><?= count($notif_rusak) ?> alat</strong> dalam kondisi Rusak / Maintenance — klik untuk lihat</span>
          <i class="bi bi-chevron-down" style="margin-left:auto"></i>
        </div>
        <div id="listRusak" class="hidden" style="margin-top:10px;width:100%;font-size:.82rem">
          <?php foreach($notif_rusak as $nr): ?>
          <div style="padding:4px 0;border-bottom:1px solid rgba(0,0,0,.06)">
            <strong><?= clean($nr['nama_alat']) ?></strong>
            <?= $nr['no_seri'] ? '('. clean($nr['no_seri']) .')' : '' ?>
            — <?= clean($nr['nama_ruangan']??'–') ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
      <?php if(count($notif_kalib)>0): ?>
      <div class="notif-bar nb-amb" onclick="document.getElementById('listKalib').classList.toggle('hidden')" style="flex-direction:column;align-items:flex-start">
        <div style="display:flex;align-items:center;gap:9px;width:100%">
          <i class="bi bi-calendar-x-fill"></i>
          <span><strong><?= count($notif_kalib) ?> alat</strong> melewati masa kalibrasi — klik untuk lihat</span>
          <i class="bi bi-chevron-down" style="margin-left:auto"></i>
        </div>
        <div id="listKalib" class="hidden" style="margin-top:10px;width:100%;font-size:.82rem">
          <?php foreach($notif_kalib as $nk): ?>
          <div style="padding:4px 0;border-bottom:1px solid rgba(0,0,0,.06)">
            <strong><?= clean($nk['nama_alat']) ?></strong>
            — Kalibrasi: <?= tglID($nk['masa_kalibrasi']) ?>
            — <?= clean($nk['nama_ruangan']??'–') ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;flex-wrap:wrap">

    <!-- Latest Additions -->
    <div class="card" style="grid-column:1/-1">
      <div class="card-hd">
        <span class="card-title"><i class="bi bi-clock-history" style="color:var(--p)"></i> Alat Terbaru Ditambahkan</span>
        <a href="<?= BASE_URL ?>fungsi/inventory.php" class="btn btn-o btn-sm">Lihat Semua</a>
      </div>
      <div class="tw">
        <table class="dt">
          <thead><tr>
            <th>Gambar</th><th>No. Seri</th><th>Nama Alat</th>
            <th>Merk</th><th>Ruangan</th><th>Status</th><th>Kondisi</th>
          </tr></thead>
          <tbody>
          <?php if(empty($latest)): ?>
            <tr><td colspan="7"><div class="empty"><i class="bi bi-inbox"></i><p>Belum ada data alat</p></div></td></tr>
          <?php else: foreach($latest as $r): ?>
            <tr>
              <td><img class="alat-img" src="<?= imgURL($r['gambar']) ?>" alt="<?= clean($r['nama_alat']) ?>"></td>
              <td><code style="font-size:.75rem;background:#F1F5F9;padding:2px 6px;border-radius:4px"><?= clean($r['no_seri']??'–') ?></code></td>
              <td><strong><?= clean($r['nama_alat']) ?></strong></td>
              <td><?= clean($r['merk']??'–') ?></td>
              <td><?= clean($r['nama_ruangan']??'–') ?></td>
              <td><span class="badge <?= statusBadge($r['status']) ?>"><?= clean($r['status']) ?></span></td>
              <td><span class="badge <?= kondisiBadge($r['kondisi']) ?>"><?= clean($r['kondisi']) ?></span></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Active Loans -->
    <div class="card" style="grid-column:1/-1">
      <div class="card-hd">
        <span class="card-title"><i class="bi bi-arrow-left-right" style="color:var(--t)"></i> Peminjaman Aktif</span>
        <a href="<?= BASE_URL ?>fungsi/history.php" class="btn btn-o btn-sm">Lihat Semua Histori</a>
      </div>
      <div class="tw">
        <table class="dt">
          <thead><tr>
            <th>Alat</th><th>Dipinjam Oleh</th><th>Keperluan</th>
            <th>Ruangan Tujuan</th><th>Tgl Pinjam</th><th>Status</th>
          </tr></thead>
          <tbody>
          <?php if(empty($pinjam_aktif)): ?>
            <tr><td colspan="6"><div class="empty"><i class="bi bi-check2-all"></i><p>Tidak ada peminjaman aktif</p></div></td></tr>
          <?php else: foreach($pinjam_aktif as $h): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:8px">
                  <img class="alat-img" src="<?= imgURL($h['gambar']) ?>" alt="">
                  <span><?= clean($h['nama_alat']) ?></span>
                </div>
              </td>
              <td>
                <div><?= clean($h['nama_lengkap']) ?></div>
                <div style="font-size:.72rem;color:var(--mu)"><?= ucfirst($h['role']) ?></div>
              </td>
              <td><?= clean($h['keperluan']??'–') ?></td>
              <td><?= clean($h['ruangan_tujuan']??'–') ?></td>
              <td><?= tglID(substr($h['tgl_pinjam'],0,10)) ?></td>
              <td><span class="badge badge-warning">Dipinjam</span></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /grid -->
</div><!-- /.pc -->

<?php include __DIR__ . '/tampilan/footer.php'; ?>
<style>.hidden{display:none!important}</style>
