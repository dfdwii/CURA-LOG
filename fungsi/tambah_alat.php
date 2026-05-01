<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
requireRole('admin', 'organizer');
$pageTitle = 'Tambah Alat';

$errors = [];
$old    = [];

// Gambar preset dari assets
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
    // CSRF
    if (empty($_POST['_csrf']) || $_POST['_csrf'] !== ($_SESSION['_csrf'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $old = $_POST;

        // Validasi wajib
        $fields = [
            'nama_alat'  => 'Nama alat',
            'merk'       => 'Merk',
            'kategori'   => 'Kategori',
            'tgl_masuk'  => 'Tanggal masuk',
            'id_ruangan' => 'Ruangan',
            'masa_kalibrasi' => 'Masa kalibrasi',
        ];
        foreach ($fields as $key => $label) {
            if (isBlank($_POST[$key] ?? '')) {
                $errors[] = "$label tidak boleh kosong.";
            }
        }

        // Validasi no_seri unik jika diisi
        $noSeri = trim($_POST['no_seri'] ?? '');
        if ($noSeri !== '') {
            $chk = $pdo->prepare("SELECT id_alat FROM alat WHERE no_seri=? LIMIT 1");
            $chk->execute([$noSeri]);
            if ($chk->fetch()) $errors[] = "No. seri '$noSeri' sudah terdaftar.";
        }

        // Handle gambar
        $gambar = trim($_POST['gambar_preset'] ?? '');
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
                $dest    = IMG_DIR . $newName;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $gambar = $newName;
                } else {
                    $errors[] = 'Gagal menyimpan gambar. Periksa permission folder uploads.';
                }
            }
        }

        if (empty($errors)) {
            try {
                $st = $pdo->prepare("
                    INSERT INTO alat
                      (no_seri, nama_alat, merk, kategori, gambar,
                       tgl_masuk, id_ruangan, masa_kalibrasi,
                       status, kondisi, keterangan)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?)
                ");
                $st->execute([
                    $noSeri ?: null,
                    trim($_POST['nama_alat']),
                    trim($_POST['merk']),
                    trim($_POST['kategori']),
                    $gambar ?: null,
                    trim($_POST['tgl_masuk']),
                    (int)$_POST['id_ruangan'],
                    trim($_POST['masa_kalibrasi']),
                    trim($_POST['status']   ?? 'Tersedia'),
                    trim($_POST['kondisi']  ?? 'Baik'),
                    trim($_POST['keterangan'] ?? '') ?: null,
                ]);
                setToast('success', 'Alat "'.clean($_POST['nama_alat']).'" berhasil ditambahkan!');
                header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
            } catch (PDOException $e) {
                error_log('[CURA-LOG] tambah_alat: ' . $e->getMessage());
                $errors[] = 'Gagal menyimpan ke database. Coba lagi.';
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
    <h1>Tambah Alat Medis</h1>
    <p>Isi formulir di bawah untuk mendaftarkan alat baru ke inventaris.</p>
  </div>

  <div style="max-width:760px">
    <?php if (!empty($errors)): ?>
    <div style="background:#FEF2F2;border:1.5px solid #FECACA;border-radius:10px;
                padding:14px 16px;margin-bottom:18px">
      <div style="font-weight:600;color:#DC2626;margin-bottom:6px;display:flex;align-items:center;gap:7px">
        <i class="bi bi-exclamation-circle-fill"></i> Terdapat <?= count($errors) ?> kesalahan:
      </div>
      <ul style="margin:0;padding-left:18px;color:#DC2626;font-size:.84rem">
        <?php foreach($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="formTambah" novalidate>
      <input type="hidden" name="_csrf" value="<?= $_SESSION['_csrf'] ?>">

      <div class="card">
        <div class="card-hd"><span class="card-title"><i class="bi bi-info-circle" style="color:var(--p)"></i> Informasi Alat</span></div>
        <div class="card-bd">
          <div class="fg2">
            <div class="fg">
              <label>Nama Alat <span class="req">*</span></label>
              <input class="fc <?= isset($errors) && isBlank($old['nama_alat']??'') ? 'err':'' ?>"
                     type="text" name="nama_alat"
                     value="<?= clean($old['nama_alat'] ?? '') ?>"
                     placeholder="Contoh: Ventilator">
              <div class="ferr" id="e_nama">Nama alat wajib diisi.</div>
            </div>
            <div class="fg">
              <label>Merk / Produsen <span class="req">*</span></label>
              <input class="fc" type="text" name="merk"
                     value="<?= clean($old['merk'] ?? '') ?>"
                     placeholder="Contoh: Philips">
              <div class="ferr" id="e_merk">Merk wajib diisi.</div>
            </div>
            <div class="fg">
              <label>No. Seri</label>
              <input class="fc" type="text" name="no_seri"
                     value="<?= clean($old['no_seri'] ?? '') ?>"
                     placeholder="Contoh: SN-202401-001">
              <div class="fhint">Kosongkan jika tidak ada.</div>
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
                     value="<?= clean($old['tgl_masuk'] ?? date('Y-m-d')) ?>">
              <div class="ferr" id="e_tgl">Tanggal masuk wajib diisi.</div>
            </div>
            <div class="fg">
              <label>Masa Kalibrasi <span class="req">*</span></label>
              <input class="fc" type="date" name="masa_kalibrasi"
                     value="<?= clean($old['masa_kalibrasi'] ?? '') ?>">
              <div class="ferr" id="e_kalib">Masa kalibrasi wajib diisi.</div>
            </div>
            <div class="fg"><!-- spacer --></div>
          </div>
        </div>
      </div>

      <div class="card" style="margin-top:14px">
        <div class="card-hd"><span class="card-title"><i class="bi bi-activity" style="color:var(--amb)"></i> Status & Kondisi</span></div>
        <div class="card-bd">
          <div class="fg2">
            <div class="fg">
              <label>Status Awal</label>
              <select class="fs" name="status">
                <?php foreach(['Tersedia','Dipinjam','Rusak','Maintenance','Perlu Kalibrasi'] as $s): ?>
                <option value="<?=$s?>" <?= ($old['status']??'Tersedia')===$s?'selected':'' ?>><?=$s?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label>Kondisi</label>
              <select class="fs" name="kondisi">
                <?php foreach(['Baik','Perlu Kalibrasi','Rusak'] as $k): ?>
                <option value="<?=$k?>" <?= ($old['kondisi']??'Baik')===$k?'selected':'' ?>><?=$k?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg s2">
              <label>Keterangan Tambahan</label>
              <textarea class="fc" name="keterangan" rows="3"
                        placeholder="Catatan khusus tentang alat ini (opsional)…"><?= clean($old['keterangan'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="card" style="margin-top:14px">
        <div class="card-hd"><span class="card-title"><i class="bi bi-image" style="color:var(--p)"></i> Gambar Alat</span></div>
        <div class="card-bd">
          <!-- Preset picker -->
          <div class="fg">
            <label>Pilih Gambar Preset</label>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:10px;margin-top:6px" id="presetGrid">
              <?php foreach($presetImages as $file => $label): ?>
              <label style="cursor:pointer;text-align:center" class="preset-item">
                <input type="radio" name="gambar_preset" value="<?=$file?>"
                       <?= ($old['gambar_preset']??'')===$file?'checked':'' ?>
                       style="display:none" class="preset-radio">
                <img src="<?= BASE_URL ?>assets/img/alat_medis/<?= rawurlencode($file) ?>"
                     alt="<?=$label?>"
                     style="width:70px;height:60px;object-fit:contain;border-radius:8px;
                            background:#F8FAFC;border:2px solid #E2E8F0;padding:4px;
                            transition:.15s" class="preset-img">
                <div style="font-size:.7rem;color:var(--mu);margin-top:4px"><?=$label?></div>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:12px;margin:10px 0">
            <div style="flex:1;height:1px;background:var(--br)"></div>
            <span style="font-size:.78rem;color:var(--mu);font-weight:600">ATAU</span>
            <div style="flex:1;height:1px;background:var(--br)"></div>
          </div>
          <div class="fg">
            <label>Upload Gambar Sendiri</label>
            <input class="fc" type="file" name="gambar_upload"
                   accept=".jpg,.jpeg,.png,.webp" id="uploadInput">
            <div class="fhint">Format: JPG, PNG, WEBP. Maks 2MB. Jika diupload, akan menggantikan preset.</div>
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
          <i class="bi bi-check-lg"></i> Simpan Alat
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
// Preset image selector highlight
document.querySelectorAll('.preset-radio').forEach(function(radio){
  radio.addEventListener('change',function(){
    document.querySelectorAll('.preset-img').forEach(img=>{
      img.style.borderColor='#E2E8F0';img.style.boxShadow='none';
    });
    if(this.checked){
      const img=this.parentElement.querySelector('.preset-img');
      img.style.borderColor='var(--p)';
      img.style.boxShadow='0 0 0 3px rgba(0,87,184,.15)';
    }
  });
  // Init
  if(radio.checked){
    const img=radio.parentElement.querySelector('.preset-img');
    img.style.borderColor='var(--p)';
    img.style.boxShadow='0 0 0 3px rgba(0,87,184,.15)';
  }
});

// Image upload preview
document.getElementById('uploadInput').addEventListener('change',function(){
  const f=this.files[0];
  if(!f){document.getElementById('previewWrap').style.display='none';return;}
  const reader=new FileReader();
  reader.onload=function(e){
    document.getElementById('previewImg').src=e.target.result;
    document.getElementById('previewWrap').style.display='block';
  };
  reader.readAsDataURL(f);
});

// Client validation
document.getElementById('formTambah').addEventListener('submit',function(e){
  let ok=true;
  const required=[
    ['nama_alat','e_nama'],['merk','e_merk'],
    ['kategori','e_kategori'],['id_ruangan','e_ruangan'],
    ['tgl_masuk','e_tgl'],['masa_kalibrasi','e_kalib']
  ];
  required.forEach(function([name,errId]){
    const el=document.querySelector('[name="'+name+'"]');
    const er=document.getElementById(errId);
    if(er) er.style.display='none';
    if(el) el.classList.remove('err');
    if(el && !el.value.trim()){
      if(er) er.style.display='block';
      if(el) el.classList.add('err');
      ok=false;
    }
  });
  if(!ok) e.preventDefault();
});
</script>
JS;
include __DIR__ . '/../tampilan/footer.php';
?>
