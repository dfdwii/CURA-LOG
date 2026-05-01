<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
requireRole('admin', 'organizer');
$pageTitle = 'Edit Alat';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) {
    setToast('error', 'ID alat tidak valid.');
    header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
}

// Fetch existing data
$st = $pdo->prepare("SELECT a.*, r.nama_ruangan FROM alat a LEFT JOIN ruangan r ON a.id_ruangan=r.id_ruangan WHERE a.id_alat=?");
$st->execute([$id]);
$alat = $st->fetch();
if (!$alat) {
    setToast('error', 'Alat tidak ditemukan.');
    header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
}

$errors = [];
$old    = $alat; // default to DB values

$presetImages = [
    'ventilator.webp'        => 'Ventilator',
    'defibrilator.webp'      => 'Defibrillator',
    'infuse_pump.webp'       => 'Infuse Pump',
    'USG.webp'               => 'USG',
    'EKG.webp'               => 'EKG',
    'X-ray.webp'             => 'X-Ray',
    'Tensimeter_digital.webp'=> 'Tensimeter Digital',
    'mikroskop.webp'         => 'Mikroskop',
    'autoclave.webp'         => 'Autoclave',
    'suction_pump.webp'      => 'Suction Pump',
    'nebulizer.webp'         => 'Nebulizer',
    'stethoscope.webp'       => 'Stetoskop',
    'syringe_pump.webp'      => 'Syringe Pump',
    'syringe_filter.webp'    => 'Syringe Filter',
];
$ruangans  = $pdo->query("SELECT * FROM ruangan ORDER BY nama_ruangan")->fetchAll();
$kategoris = ['Alat Pernapasan','Alat Resusitasi','Alat Terapi','Alat Diagnostik',
              'Alat Monitor','Alat Laboratorium','Alat Sterilisasi','Alat Lainnya'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['_csrf']) || $_POST['_csrf'] !== ($_SESSION['_csrf'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $old = $_POST;

        $required = ['nama_alat'=>'Nama alat','merk'=>'Merk','kategori'=>'Kategori',
                     'tgl_masuk'=>'Tanggal masuk','id_ruangan'=>'Ruangan','masa_kalibrasi'=>'Masa kalibrasi'];
        foreach ($required as $key => $label) {
            if (isBlank($_POST[$key] ?? '')) $errors[] = "$label tidak boleh kosong.";
        }

        // No seri unik (kecuali milik alat ini sendiri)
        $noSeri = trim($_POST['no_seri'] ?? '');
        if ($noSeri !== '') {
            $chk = $pdo->prepare("SELECT id_alat FROM alat WHERE no_seri=? AND id_alat != ? LIMIT 1");
            $chk->execute([$noSeri, $id]);
            if ($chk->fetch()) $errors[] = "No. seri '$noSeri' sudah digunakan alat lain.";
        }

        // Handle gambar
        $gambar = $alat['gambar']; // default: keep existing
        // Jika user pilih preset
        if (!empty($_POST['gambar_preset'])) $gambar = trim($_POST['gambar_preset']);
        // Jika upload baru
        if (!empty($_FILES['gambar_upload']['name'])) {
            $file    = $_FILES['gambar_upload'];
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            if (!in_array($ext, $allowed)) {
                $errors[] = 'Format gambar harus JPG, PNG, atau WEBP.';
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $errors[] = 'Ukuran gambar maksimal 2MB.';
            } else {
                $newName = 'upload_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], IMG_DIR . $newName)) {
                    // Hapus gambar upload lama jika ada
                    if ($alat['gambar'] && strpos($alat['gambar'], 'upload_') === 0) {
                        @unlink(IMG_DIR . $alat['gambar']);
                    }
                    $gambar = $newName;
                } else {
                    $errors[] = 'Gagal menyimpan gambar.';
                }
            }
        }

        if (empty($errors)) {
            try {
                $st = $pdo->prepare("
                    UPDATE alat SET
                      no_seri=?, nama_alat=?, merk=?, kategori=?, gambar=?,
                      tgl_masuk=?, id_ruangan=?, masa_kalibrasi=?,
                      status=?, kondisi=?, keterangan=?
                    WHERE id_alat=?
                ");
                $st->execute([
                    $noSeri ?: null,
                    trim($_POST['nama_alat']),
                    trim($_POST['merk']),
                    trim($_POST['kategori']),
                    $gambar,
                    trim($_POST['tgl_masuk']),
                    (int)$_POST['id_ruangan'],
                    trim($_POST['masa_kalibrasi']),
                    trim($_POST['status']),
                    trim($_POST['kondisi']),
                    trim($_POST['keterangan'] ?? '') ?: null,
                    $id,
                ]);
                setToast('success', 'Alat "'.clean($_POST['nama_alat']).'" berhasil diperbarui!');
                header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
            } catch (PDOException $e) {
                error_log('[CURA-LOG] edit_alat: ' . $e->getMessage());
                $errors[] = 'Gagal menyimpan perubahan. Coba lagi.';
            }
        }
    }
}

$_SESSION['_csrf'] = $_SESSION['_csrf'] ?? bin2hex(random_bytes(32));
include __DIR__ . '/../tampilan/header.php';
include __DIR__ . '/../tampilan/sidebar.php';
?>

<div class="pc">
  <div class="ph">
    <h1>Edit Alat Medis</h1>
    <p>Memperbarui data alat: <strong><?= clean($alat['nama_alat']) ?></strong></p>
  </div>

  <div style="max-width:760px">
    <?php if (!empty($errors)): ?>
    <div style="background:#FEF2F2;border:1.5px solid #FECACA;border-radius:10px;padding:14px 16px;margin-bottom:18px">
      <div style="font-weight:600;color:#DC2626;margin-bottom:6px;display:flex;align-items:center;gap:7px">
        <i class="bi bi-exclamation-circle-fill"></i> Terdapat <?= count($errors) ?> kesalahan:
      </div>
      <ul style="margin:0;padding-left:18px;color:#DC2626;font-size:.84rem">
        <?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="formEdit" novalidate>
      <input type="hidden" name="_csrf" value="<?= $_SESSION['_csrf'] ?>">

      <div class="card">
        <div class="card-hd"><span class="card-title"><i class="bi bi-info-circle" style="color:var(--p)"></i> Informasi Alat</span></div>
        <div class="card-bd">
          <div class="fg2">
            <div class="fg">
              <label>Nama Alat <span class="req">*</span></label>
              <input class="fc" type="text" name="nama_alat"
                     value="<?= clean($old['nama_alat'] ?? '') ?>" placeholder="Contoh: Ventilator">
              <div class="ferr" id="e_nama">Nama alat wajib diisi.</div>
            </div>
            <div class="fg">
              <label>Merk / Produsen <span class="req">*</span></label>
              <input class="fc" type="text" name="merk"
                     value="<?= clean($old['merk'] ?? '') ?>" placeholder="Contoh: Philips">
              <div class="ferr" id="e_merk">Merk wajib diisi.</div>
            </div>
            <div class="fg">
              <label>No. Seri</label>
              <input class="fc" type="text" name="no_seri"
                     value="<?= clean($old['no_seri'] ?? '') ?>" placeholder="Contoh: SN-202401-001">
            </div>
            <div class="fg">
              <label>Kategori <span class="req">*</span></label>
              <select class="fs" name="kategori">
                <option value="">– Pilih Kategori –</option>
                <?php foreach($kategoris as $k): ?>
                <option value="<?=$k?>" <?= ($old['kategori']??'')===$k?'selected':'' ?>><?=$k?></option>
                <?php endforeach; ?>
              </select>
              <div class="ferr" id="e_kategori">Kategori wajib dipilih.</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card" style="margin-top:14px">
        <div class="card-hd"><span class="card-title"><i class="bi bi-geo-alt" style="color:var(--t)"></i> Lokasi & Jadwal</span></div>
        <div class="card-bd">
          <div class="fg2">
            <div class="fg">
              <label>Ruangan <span class="req">*</span></label>
              <select class="fs" name="id_ruangan">
                <option value="">– Pilih Ruangan –</option>
                <?php foreach($ruangans as $r): ?>
                <option value="<?=$r['id_ruangan']?>"
                  <?= ($old['id_ruangan']??'')==$r['id_ruangan']?'selected':'' ?>>
                  <?= htmlspecialchars($r['nama_ruangan']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <div class="ferr" id="e_ruangan">Ruangan wajib dipilih.</div>
            </div>
            <div class="fg">
              <label>Tanggal Masuk <span class="req">*</span></label>
              <input class="fc" type="date" name="tgl_masuk"
                     value="<?= clean($old['tgl_masuk'] ?? '') ?>">
              <div class="ferr" id="e_tgl">Tanggal masuk wajib diisi.</div>
            </div>
            <div class="fg">
              <label>Masa Kalibrasi <span class="req">*</span></label>
              <input class="fc" type="date" name="masa_kalibrasi"
                     value="<?= clean($old['masa_kalibrasi'] ?? '') ?>">
              <div class="ferr" id="e_kalib">Masa kalibrasi wajib diisi.</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card" style="margin-top:14px">
        <div class="card-hd"><span class="card-title"><i class="bi bi-activity" style="color:var(--amb)"></i> Status & Kondisi</span></div>
        <div class="card-bd">
          <div class="fg2">
            <div class="fg">
              <label>Status</label>
              <select class="fs" name="status">
                <?php foreach(['Tersedia','Dipinjam','Rusak','Maintenance','Perlu Kalibrasi'] as $s): ?>
                <option value="<?=$s?>" <?= ($old['status']??'')===$s?'selected':'' ?>><?=$s?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label>Kondisi</label>
              <select class="fs" name="kondisi">
                <?php foreach(['Baik','Perlu Kalibrasi','Rusak'] as $k): ?>
                <option value="<?=$k?>" <?= ($old['kondisi']??'')===$k?'selected':'' ?>><?=$k?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg s2">
              <label>Keterangan</label>
              <textarea class="fc" name="keterangan" rows="3"
                        placeholder="Catatan khusus…"><?= clean($old['keterangan'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="card" style="margin-top:14px">
        <div class="card-hd"><span class="card-title"><i class="bi bi-image" style="color:var(--p)"></i> Gambar Alat</span></div>
        <div class="card-bd">
          <!-- Current image -->
          <?php if (!empty($alat['gambar'])): ?>
          <div style="margin-bottom:14px">
            <div style="font-size:.82rem;color:var(--mu);margin-bottom:6px;font-weight:500">Gambar Saat Ini:</div>
            <img src="<?= imgURL($alat['gambar']) ?>" alt=""
                 style="height:90px;border-radius:8px;object-fit:contain;
                        border:1.5px solid var(--br);background:var(--bg);padding:8px">
          </div>
          <?php endif; ?>

          <div class="fg">
            <label>Ganti dengan Preset</label>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(88px,1fr));gap:9px;margin-top:6px">
              <?php foreach($presetImages as $file => $label): ?>
              <label style="cursor:pointer;text-align:center" class="preset-item">
                <input type="radio" name="gambar_preset" value="<?=$file?>"
                       <?= ($old['gambar']??'')===$file?'checked':'' ?>
                       style="display:none" class="preset-radio">
                <img src="<?= BASE_URL ?>assets/img/alat_medis/<?= rawurlencode($file) ?>"
                     alt="<?=$label?>"
                     style="width:68px;height:58px;object-fit:contain;border-radius:8px;
                            background:#F8FAFC;border:2px solid #E2E8F0;padding:3px;transition:.15s"
                     class="preset-img">
                <div style="font-size:.68rem;color:var(--mu);margin-top:3px"><?=$label?></div>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div style="display:flex;align-items:center;gap:12px;margin:10px 0">
            <div style="flex:1;height:1px;background:var(--br)"></div>
            <span style="font-size:.76rem;color:var(--mu);font-weight:600">ATAU</span>
            <div style="flex:1;height:1px;background:var(--br)"></div>
          </div>

          <div class="fg">
            <label>Upload Gambar Baru</label>
            <input class="fc" type="file" name="gambar_upload" accept=".jpg,.jpeg,.png,.webp" id="uploadInput">
            <div class="fhint">Format: JPG, PNG, WEBP. Maks 2MB.</div>
            <div style="margin-top:10px;display:none" id="previewWrap">
              <img id="previewImg" src="" alt="Preview"
                   style="height:80px;border-radius:8px;object-fit:contain;
                          border:1.5px solid var(--br);background:var(--bg);padding:6px">
            </div>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:10px;margin-top:18px">
        <button type="submit" class="btn btn-p">
          <i class="bi bi-check-lg"></i> Simpan Perubahan
        </button>
        <a href="<?= BASE_URL ?>fungsi/inventory.php" class="btn btn-o">
          <i class="bi bi-arrow-left"></i> Batal
        </a>
      </div>
    </form>
  </div>
</div>

<?php
$extraJS = <<<JS
<script>
document.querySelectorAll('.preset-radio').forEach(function(r){
  r.addEventListener('change',function(){
    document.querySelectorAll('.preset-img').forEach(i=>{i.style.borderColor='#E2E8F0';i.style.boxShadow='none';});
    if(this.checked){const i=this.parentElement.querySelector('.preset-img');i.style.borderColor='var(--p)';i.style.boxShadow='0 0 0 3px rgba(0,87,184,.15)';}
  });
  if(r.checked){const i=r.parentElement.querySelector('.preset-img');i.style.borderColor='var(--p)';i.style.boxShadow='0 0 0 3px rgba(0,87,184,.15)';}
});
document.getElementById('uploadInput').addEventListener('change',function(){
  const f=this.files[0];
  if(!f){document.getElementById('previewWrap').style.display='none';return;}
  const reader=new FileReader();
  reader.onload=e=>{document.getElementById('previewImg').src=e.target.result;document.getElementById('previewWrap').style.display='block';};
  reader.readAsDataURL(f);
});
document.getElementById('formEdit').addEventListener('submit',function(e){
  let ok=true;
  [['nama_alat','e_nama'],['merk','e_merk'],['kategori','e_kategori'],['id_ruangan','e_ruangan'],['tgl_masuk','e_tgl'],['masa_kalibrasi','e_kalib']].forEach(function([n,id]){
    const el=document.querySelector('[name="'+n+'"]'),er=document.getElementById(id);
    if(er)er.style.display='none';if(el)el.classList.remove('err');
    if(el&&!el.value.trim()){if(er)er.style.display='block';if(el)el.classList.add('err');ok=false;}
  });
  if(!ok)e.preventDefault();
});
</script>
JS;
include __DIR__ . '/../tampilan/footer.php';
?>
