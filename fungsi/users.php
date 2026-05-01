<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
requireRole('admin');
$pageTitle = 'Manajemen User';

$errors  = [];
$success = '';
$mode    = $_GET['mode'] ?? 'list'; // list | tambah | edit
$editId  = (int)($_GET['id'] ?? 0);
$editRow = null;

// Load for edit
if ($mode === 'edit' && $editId > 0) {
    $st = $pdo->prepare("SELECT id,username,nama_lengkap,role FROM users WHERE id=?");
    $st->execute([$editId]);
    $editRow = $st->fetch();
    if (!$editRow) { setToast('error','User tidak ditemukan.'); header('Location: '.BASE_URL.'fungsi/users.php'); exit; }
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['_csrf']) || $_POST['_csrf'] !== ($_SESSION['_csrf'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $action = $_POST['form_action'] ?? '';

        // ── Tambah user ──────────────────────────────────────
        if ($action === 'tambah') {
            $username = trim($_POST['username'] ?? '');
            $nama     = trim($_POST['nama_lengkap'] ?? '');
            $pass     = trim($_POST['password'] ?? '');
            $role     = trim($_POST['role'] ?? '');
            $validRoles = ['admin','dokter','organizer'];

            if (isBlank($username)) $errors[] = 'Username wajib diisi.';
            elseif (mb_strlen($username) < 3) $errors[] = 'Username minimal 3 karakter.';
            elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = 'Username hanya boleh huruf, angka, underscore.';
            if (isBlank($nama))     $errors[] = 'Nama lengkap wajib diisi.';
            if (isBlank($pass))     $errors[] = 'Password wajib diisi.';
            elseif (mb_strlen($pass) < 6) $errors[] = 'Password minimal 6 karakter.';
            if (!in_array($role, $validRoles)) $errors[] = 'Role tidak valid.';

            if (empty($errors)) {
                // Cek username unik
                $chk = $pdo->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
                $chk->execute([$username]);
                if ($chk->fetch()) {
                    $errors[] = "Username '$username' sudah terdaftar.";
                } else {
                    try {
                        $ins = $pdo->prepare("INSERT INTO users (username,password,nama_lengkap,role) VALUES (?,?,?,?)");
                        $ins->execute([$username, md5($pass), $nama, $role]);
                        setToast('success', "User '$username' berhasil ditambahkan!");
                        header('Location: ' . BASE_URL . 'fungsi/users.php'); exit;
                    } catch (PDOException $e) {
                        error_log('[CURA-LOG] tambah_user: '.$e->getMessage());
                        $errors[] = 'Gagal menyimpan. Coba lagi.';
                    }
                }
            }
        }

        // ── Edit user ────────────────────────────────────────
        if ($action === 'edit') {
            $uid      = (int)($_POST['id'] ?? 0);
            $nama     = trim($_POST['nama_lengkap'] ?? '');
            $role     = trim($_POST['role'] ?? '');
            $newPass  = trim($_POST['password_baru'] ?? '');
            $validRoles = ['admin','dokter','organizer'];

            if (isBlank($nama))     $errors[] = 'Nama lengkap wajib diisi.';
            if (!in_array($role, $validRoles)) $errors[] = 'Role tidak valid.';
            if (!isBlank($newPass) && mb_strlen($newPass) < 6) $errors[] = 'Password baru minimal 6 karakter.';

            if (empty($errors)) {
                try {
                    if (!isBlank($newPass)) {
                        $upd = $pdo->prepare("UPDATE users SET nama_lengkap=?,role=?,password=? WHERE id=?");
                        $upd->execute([$nama,$role,md5($newPass),$uid]);
                    } else {
                        $upd = $pdo->prepare("UPDATE users SET nama_lengkap=?,role=? WHERE id=?");
                        $upd->execute([$nama,$role,$uid]);
                    }
                    setToast('success', 'Data user berhasil diperbarui!');
                    header('Location: ' . BASE_URL . 'fungsi/users.php'); exit;
                } catch (PDOException $e) {
                    error_log('[CURA-LOG] edit_user: '.$e->getMessage());
                    $errors[] = 'Gagal memperbarui. Coba lagi.';
                }
            }
        }

        // ── Hapus user ───────────────────────────────────────
        if ($action === 'hapus') {
            $uid = (int)($_POST['id'] ?? 0);
            if ($uid === $ME['id']) {
                setToast('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            } else {
                try {
                    $del = $pdo->prepare("DELETE FROM users WHERE id=?");
                    $del->execute([$uid]);
                    setToast('success', 'User berhasil dihapus.');
                } catch (PDOException $e) {
                    error_log('[CURA-LOG] hapus_user: '.$e->getMessage());
                    setToast('error', 'Gagal menghapus user.');
                }
            }
            header('Location: ' . BASE_URL . 'fungsi/users.php'); exit;
        }
    }
}

$users = $pdo->query("SELECT id,username,nama_lengkap,role,created_at FROM users ORDER BY created_at DESC")->fetchAll();
$_SESSION['_csrf'] = $_SESSION['_csrf'] ?? bin2hex(random_bytes(32));

include __DIR__ . '/../tampilan/header.php';
include __DIR__ . '/../tampilan/sidebar.php';
?>

<div class="pc">
  <div class="ph" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:10px">
    <div><h1>Manajemen User</h1><p>Kelola akun tenaga medis dan staf sistem.</p></div>
    <button class="btn btn-p" onclick="openModal('modalTambah')">
      <i class="bi bi-person-plus-fill"></i> Tambah User
    </button>
  </div>

  <?php if (!empty($errors)): ?>
  <div style="background:#FEF2F2;border:1.5px solid #FECACA;border-radius:10px;padding:13px 16px;margin-bottom:16px">
    <div style="font-weight:600;color:#DC2626;margin-bottom:5px;display:flex;align-items:center;gap:7px">
      <i class="bi bi-exclamation-circle-fill"></i> Kesalahan:
    </div>
    <ul style="margin:0;padding-left:18px;color:#DC2626;font-size:.83rem">
      <?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <!-- Role Badges Legend -->
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
    <span class="badge badge-danger">Admin – Akses penuh</span>
    <span class="badge badge-info">Dokter – Lihat & pinjam</span>
    <span class="badge badge-secondary">Organizer – Kelola inventaris</span>
  </div>

  <div class="card">
    <div class="card-hd">
      <span class="card-title"><i class="bi bi-people" style="color:var(--p)"></i> Daftar Pengguna (<?= count($users) ?>)</span>
      <div class="sw-wrap">
        <i class="bi bi-search"></i>
        <input class="srch" type="text" id="srchUser" placeholder="Cari user…" style="min-width:180px">
      </div>
    </div>
    <div class="tw">
      <table class="dt" id="tblUsers">
        <thead><tr><th>#</th><th>Username</th><th>Nama Lengkap</th><th>Role</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if(empty($users)): ?>
          <tr class="empty-row"><td colspan="6"><div class="empty"><i class="bi bi-people"></i><p>Belum ada user.</p></div></td></tr>
        <?php else: foreach($users as $i=>$u): ?>
          <tr>
            <td style="color:var(--mu);font-size:.8rem"><?= $i+1 ?></td>
            <td><code style="background:#F1F5F9;padding:2px 7px;border-radius:5px;font-size:.8rem"><?= clean($u['username']) ?></code></td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--p),var(--t));
                            display:flex;align-items:center;justify-content:center;
                            color:#fff;font-weight:700;font-size:.8rem;flex-shrink:0">
                  <?= strtoupper(mb_substr($u['nama_lengkap'],0,1)) ?>
                </div>
                <?= clean($u['nama_lengkap']) ?>
                <?php if($u['id'] === $ME['id']): ?>
                <span class="badge badge-info" style="font-size:.65rem">Anda</span>
                <?php endif; ?>
              </div>
            </td>
            <td>
              <?php $rc = match($u['role']){ 'admin'=>'badge-danger','dokter'=>'badge-info','organizer'=>'badge-secondary',default=>'badge-secondary' }; ?>
              <span class="badge <?=$rc?>"><?= ucfirst($u['role']) ?></span>
            </td>
            <td style="font-size:.8rem;color:var(--mu)"><?= tglID(substr($u['created_at'],0,10)) ?></td>
            <td>
              <div class="act">
                <button class="btn btn-a btn-xs"
                  onclick="openEditUser(<?= htmlspecialchars(json_encode($u)) ?>)">
                  <i class="bi bi-pencil"></i> Edit
                </button>
                <?php if($u['id'] !== $ME['id']): ?>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="_csrf" value="<?= $_SESSION['_csrf'] ?>">
                  <input type="hidden" name="form_action" value="hapus">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button type="submit" class="btn btn-r btn-xs"
                    onclick="return confirmDel('Hapus user <?= addslashes(clean($u['nama_lengkap'])) ?>?')">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah User -->
<div class="overlay" id="modalTambah">
  <div class="modal">
    <div class="modal-hd">
      <h3><i class="bi bi-person-plus-fill" style="color:var(--p)"></i> Tambah User Baru</h3>
      <button class="mc" onclick="closeModal('modalTambah')"><i class="bi bi-x"></i></button>
    </div>
    <form method="POST" id="fTambah">
      <div class="modal-bd">
        <input type="hidden" name="_csrf" value="<?= $_SESSION['_csrf'] ?>">
        <input type="hidden" name="form_action" value="tambah">
        <div class="fg">
          <label>Username <span class="req">*</span></label>
          <input class="fc" type="text" name="username" placeholder="Huruf, angka, underscore" required>
        </div>
        <div class="fg">
          <label>Nama Lengkap <span class="req">*</span></label>
          <input class="fc" type="text" name="nama_lengkap" placeholder="Contoh: Dr. Budi Santoso" required>
        </div>
        <div class="fg">
          <label>Password <span class="req">*</span></label>
          <input class="fc" type="password" name="password" placeholder="Minimal 6 karakter" required>
        </div>
        <div class="fg">
          <label>Role <span class="req">*</span></label>
          <select class="fs" name="role" required>
            <option value="">– Pilih Role –</option>
            <option value="admin">Admin</option>
            <option value="dokter">Dokter</option>
            <option value="organizer">Organizer</option>
          </select>
        </div>
      </div>
      <div class="modal-ft">
        <button type="button" class="btn btn-o btn-sm" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-p btn-sm"><i class="bi bi-check-lg"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit User -->
<div class="overlay" id="modalEdit">
  <div class="modal">
    <div class="modal-hd">
      <h3><i class="bi bi-pencil-square" style="color:var(--amb)"></i> Edit User</h3>
      <button class="mc" onclick="closeModal('modalEdit')"><i class="bi bi-x"></i></button>
    </div>
    <form method="POST">
      <div class="modal-bd">
        <input type="hidden" name="_csrf" value="<?= $_SESSION['_csrf'] ?>">
        <input type="hidden" name="form_action" value="edit">
        <input type="hidden" name="id" id="editUserId">
        <div class="fg">
          <label>Username</label>
          <input class="fc" type="text" id="editUsername" readonly style="background:var(--bg)">
        </div>
        <div class="fg">
          <label>Nama Lengkap <span class="req">*</span></label>
          <input class="fc" type="text" name="nama_lengkap" id="editNama" required>
        </div>
        <div class="fg">
          <label>Role <span class="req">*</span></label>
          <select class="fs" name="role" id="editRole">
            <option value="admin">Admin</option>
            <option value="dokter">Dokter</option>
            <option value="organizer">Organizer</option>
          </select>
        </div>
        <div class="fg">
          <label>Password Baru <span style="font-size:.75rem;color:var(--mu)">(kosongkan jika tidak diubah)</span></label>
          <input class="fc" type="password" name="password_baru" placeholder="Min. 6 karakter">
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
function openEditUser(u){
  document.getElementById('editUserId').value=u.id;
  document.getElementById('editUsername').value=u.username;
  document.getElementById('editNama').value=u.nama_lengkap;
  document.getElementById('editRole').value=u.role;
  openModal('modalEdit');
}
document.addEventListener('DOMContentLoaded',()=>{
  liveSearch('srchUser','tblUsers');
});
</script>
JS;
include __DIR__ . '/../tampilan/footer.php';
?>
