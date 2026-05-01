<?php
require_once __DIR__ . '/config.php';
if (!empty($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['_csrf']) || $_POST['_csrf'] !== ($_SESSION['_csrf'] ?? '')) {
        $err = 'Token keamanan tidak valid. Muat ulang halaman.';
    } else {
        $u = $_POST['username'] ?? '';
        $p = $_POST['password'] ?? '';
        if (isBlank($u) || isBlank($p)) {
            $err = 'Username dan password tidak boleh kosong atau hanya spasi.';
        } elseif (mb_strlen(trim($u)) < 3) {
            $err = 'Username minimal 3 karakter.';
        } else {
            try {
                $st = $pdo->prepare(
                    "SELECT id,username,password,nama_lengkap,role FROM users WHERE username=? LIMIT 1"
                );
                $st->execute([trim($u)]);
                $row = $st->fetch();
                if ($row && $row['password'] === md5(trim($p))) {
                    session_regenerate_id(true);
                    $_SESSION['user_id']      = $row['id'];
                    $_SESSION['username']     = $row['username'];
                    $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
                    $_SESSION['role']         = $row['role'];
                    $_SESSION['_last']        = time();
                    setToast('success', 'Selamat datang, ' . htmlspecialchars($row['nama_lengkap'] ?: $row['username']) . '!');
                    header('Location: index.php'); exit;
                } else {
                    $err = 'Username atau password tidak sesuai.';
                    sleep(1);
                }
            } catch (PDOException $e) {
                error_log('[CURA-LOG] Login: ' . $e->getMessage());
                $err = 'Terjadi kesalahan sistem. Coba lagi sebentar.';
            }
        }
    }
}
$_SESSION['_csrf'] = $_SESSION['_csrf'] ?? bin2hex(random_bytes(32));
$timeout = isset($_GET['timeout']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login – CURA-LOG</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter','Segoe UI',sans-serif;min-height:100vh;
  display:flex;align-items:center;justify-content:center;padding:16px;
  background:linear-gradient(135deg,#0057B8 0%,#00A19C 100%)}
.card{background:#fff;border-radius:20px;box-shadow:0 24px 64px rgba(0,0,0,.18);
  padding:42px 38px;width:100%;max-width:408px}
.brand{display:flex;align-items:center;gap:12px;margin-bottom:6px}
.b-ico{width:44px;height:44px;border-radius:11px;flex-shrink:0;
  background:linear-gradient(135deg,#0057B8,#00A19C);
  display:flex;align-items:center;justify-content:center}
.b-ico i{color:#fff;font-size:21px}
.b-name{font-size:1.3rem;font-weight:700;color:#0057B8;letter-spacing:-.3px}
.b-sub{font-size:.73rem;color:#64748B;margin-bottom:26px;line-height:1.5}
.lbl{display:block;font-weight:500;font-size:.82rem;color:#1E293B;margin-bottom:5px}
.ig{position:relative;margin-bottom:4px}
.ig-i{position:absolute;left:11px;top:50%;transform:translateY(-50%);
  color:#94A3B8;font-size:13px;pointer-events:none}
.inp{width:100%;padding:9px 12px 9px 33px;border:1.5px solid #E2E8F0;
  border-radius:9px;font-size:.88rem;color:#1E293B;font-family:inherit;
  background:#fff;transition:.2s}
.inp:focus{outline:none;border-color:#0057B8;box-shadow:0 0 0 3px rgba(0,87,184,.1)}
.inp.err{border-color:#EF4444}
.eye{position:absolute;right:10px;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;color:#94A3B8;font-size:13px;padding:2px}
.fe{color:#EF4444;font-size:.73rem;margin-top:3px;display:none}
.fe.on{display:block}
.alert{border-radius:9px;padding:10px 13px;font-size:.82rem;
  display:flex;align-items:flex-start;gap:8px;margin-bottom:16px}
.alert i{flex-shrink:0;margin-top:1px}
.ae{background:#FEF2F2;border:1.5px solid #FECACA;color:#DC2626}
.aw{background:#FFFBEB;border:1.5px solid #FDE68A;color:#B45309}
.mb{margin-bottom:13px}
.btn-go{width:100%;background:linear-gradient(135deg,#0057B8,#0041A3);
  color:#fff;border:none;border-radius:9px;padding:11px;font-size:.93rem;
  font-weight:600;cursor:pointer;font-family:inherit;transition:.2s;
  display:flex;align-items:center;justify-content:center;gap:7px}
.btn-go:hover{transform:translateY(-1px);box-shadow:0 8px 22px rgba(0,87,184,.3)}
.btn-go:disabled{opacity:.65;pointer-events:none}
.demo{background:#EFF6FF;border-radius:9px;padding:10px 13px;
  font-size:.76rem;color:#1D4ED8;margin-top:18px;line-height:1.75}
.demo code{background:rgba(0,0,0,.07);padding:1px 5px;border-radius:4px}
.spin{width:15px;height:15px;border:2px solid rgba(255,255,255,.4);
  border-top-color:#fff;border-radius:50%;animation:sp .7s linear infinite}
@keyframes sp{to{transform:rotate(360deg)}}
</style>
</head>
<body>
<div class="card">
  <div class="brand">
    <div class="b-ico"><i class="bi bi-heart-pulse-fill"></i></div>
    <div>
      <div class="b-name">CURA-LOG</div>
    </div>
  </div>
  <p class="b-sub">Sistem Manajemen Inventaris Alat Medis<br>Khusus Tenaga Medis & Admin RS</p>

  <?php if ($timeout): ?>
  <div class="alert aw"><i class="bi bi-clock-history"></i>
    Sesi berakhir karena tidak aktif. Silakan login kembali.</div>
  <?php endif; ?>
  <?php if ($err): ?>
  <div class="alert ae"><i class="bi bi-exclamation-circle-fill"></i>
    <?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <form id="lf" method="POST" novalidate>
    <input type="hidden" name="_csrf" value="<?= $_SESSION['_csrf'] ?>">
    <div class="mb">
      <label class="lbl" for="uname">Username / ID Tenaga Medis</label>
      <div class="ig">
        <i class="bi bi-person-fill ig-i"></i>
        <input class="inp" type="text" id="uname" name="username"
          value="<?= isset($_POST['username']) ? clean($_POST['username']) : '' ?>"
          placeholder="Contoh: admin atau 200101" autocomplete="username">
      </div>
      <div class="fe" id="ue"></div>
    </div>
    <div class="mb">
      <label class="lbl" for="pwd">Password</label>
      <div class="ig">
        <i class="bi bi-lock-fill ig-i"></i>
        <input class="inp" type="password" id="pwd" name="password"
          placeholder="Masukkan password" autocomplete="current-password">
        <button type="button" class="eye" id="eyeBtn">
          <i class="bi bi-eye-fill" id="eyeIco"></i>
        </button>
      </div>
      <div class="fe" id="pe"></div>
    </div>
    <button type="submit" class="btn-go" id="sbtn">
      <i class="bi bi-box-arrow-in-right"></i> Masuk ke Sistem
    </button>
  </form>

  <div class="demo">
    <strong>Akun Demo:</strong><br>
    Admin&nbsp;&nbsp;&nbsp;&nbsp;: <code>admin</code> / <code>admin123</code><br>
    Dokter&nbsp;&nbsp;&nbsp;: <code>200101</code> / <code>dokter123</code><br>
    Organizer: <code>organizer</code> / <code>org123</code>
  </div>
</div>
<script>
document.getElementById('eyeBtn').addEventListener('click',function(){
  const p=document.getElementById('pwd'),i=document.getElementById('eyeIco');
  p.type=p.type==='password'?'text':'password';
  i.className=p.type==='text'?'bi bi-eye-slash-fill':'bi bi-eye-fill';
});
document.getElementById('lf').addEventListener('submit',function(e){
  const u=document.getElementById('uname'),p=document.getElementById('pwd');
  const ue=document.getElementById('ue'),pe=document.getElementById('pe');
  let ok=true;
  ue.className='fe';pe.className='fe';
  u.classList.remove('err');p.classList.remove('err');
  if(!u.value.trim()){ue.textContent='Username tidak boleh kosong.';ue.className='fe on';u.classList.add('err');ok=false;}
  else if(u.value.trim().length<3){ue.textContent='Username minimal 3 karakter.';ue.className='fe on';u.classList.add('err');ok=false;}
  if(!p.value.trim()){pe.textContent='Password tidak boleh kosong.';pe.className='fe on';p.classList.add('err');ok=false;}
  if(!ok){e.preventDefault();return;}
  const btn=document.getElementById('sbtn');
  btn.innerHTML='<div class="spin"></div> Memproses...';btn.disabled=true;
});
</script>
</body>
</html>
