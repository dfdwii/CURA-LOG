<?php
require_once 'config.php';

// ─────────────────────────────────────────────
//  Fungsi validasi password (server-side)
// ─────────────────────────────────────────────
function validatePassword(string $pass): array
{
    $errors = [];
    if (strlen($pass) < 6)                              $errors[] = "Password minimal 6 karakter.";
    if (!preg_match('/[A-Z]/', $pass))                  $errors[] = "Password harus mengandung minimal 1 huruf besar (A-Z).";
    if (!preg_match('/[a-z]/', $pass))                  $errors[] = "Password harus mengandung minimal 1 huruf kecil (a-z).";
    if (!preg_match('/[0-9]/', $pass))                  $errors[] = "Password harus mengandung minimal 1 angka (0-9).";
    if (!preg_match('/[!@#$%^&*()\-_=+\[\]{};:\'",.<>?\/\\\\|`~]/', $pass))
                                                        $errors[] = "Password harus mengandung minimal 1 karakter spesial (!@#\$%^&* dll).";
    return $errors;
}

$errors = [];

if (isset($_POST['daftar'])) {
    $user      = input($_POST['username']);
    $nama      = input($_POST['nama_lengkap']);
    $pass_raw  = input($_POST['password']);
    $pass_conf = input($_POST['konfirmasi_password']);
    $role      = input($_POST['role']);

    // ── 1. Validasi Username ──────────────────
    if (strlen($user) < 4) {
        $errors[] = "Username minimal 4 karakter.";
    }

    // ── 2. Validasi Role ─────────────────────
    $allowed_roles = ['dokter', 'admin'];
    if (!in_array($role, $allowed_roles)) {
        $errors[] = "Role yang dipilih tidak valid.";
    }

    // ── 3. Validasi Password komposisi ───────
    $passErrors = validatePassword($pass_raw);
    $errors     = array_merge($errors, $passErrors);

    // ── 4. Validasi Konfirmasi Password ──────
    if ($pass_raw !== $pass_conf) {
        $errors[] = "Password dan Konfirmasi Password tidak cocok.";
    }

    // ── 5. Proses jika tidak ada error ───────
    if (empty($errors)) {
        // Gunakan prepared statement untuk mencegah SQL Injection
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, 's', $user);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "Username sudah terdaftar, silakan gunakan NIP/ID yang lain!";
        } else {
            $pass_hashed = md5($pass_raw);

            $ins = mysqli_prepare($koneksi,
                "INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($ins, 'ssss', $user, $pass_hashed, $nama, $role);

            if (mysqli_stmt_execute($ins)) {
                echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
                exit;
            } else {
                $errors[] = "Terjadi kesalahan saat menyimpan ke database.";
            }
            mysqli_stmt_close($ins);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Dokter - CURA-LOG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>css/style.css">
    <style>
        /* ── Password strength checklist ── */
        #passwordHint {
            display: none;
            font-size: .82rem;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: .375rem;
            padding: .65rem .85rem;
            margin-top: .4rem;
        }
        #passwordHint ul {
            margin: 0;
            padding-left: 1.2rem;
        }
        #passwordHint li {
            line-height: 1.7;
            transition: color .2s;
        }
        #passwordHint li.ok   { color: #198754; }   /* hijau  */
        #passwordHint li.fail { color: #dc3545; }   /* merah  */
        #passwordHint li.ok::marker   { content: "✔  "; }
        #passwordHint li.fail::marker { content: "✘  "; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 card-regis">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h3 class="text-center fw-bold text-primary">Daftar Akun Dokter</h3>
                    <p class="text-center text-muted">Silakan lengkapi data untuk membuat akun baru.</p>
                    <hr>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Pendaftaran gagal!</strong> Harap perbaiki kesalahan berikut:
                            <ul class="mb-0 mt-1">
                                <?php foreach ($errors as $e): ?>
                                    <li><?php echo htmlspecialchars($e); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="regForm" novalidate>

                        <!-- Nama Lengkap -->
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control"
                                   placeholder="Contoh: Dr. Daffa Fadhil"
                                   value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars(input($_POST['nama_lengkap'])) : ''; ?>"
                                   required>
                        </div>

                        <!-- Username -->
                        <div class="mb-3">
                            <label class="form-label">Username (NIP/ID)</label>
                            <input type="text" name="username" id="regUsername" class="form-control"
                                   placeholder="Contoh: 200106" minlength="4"
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars(input($_POST['username'])) : ''; ?>"
                                   required>
                            <div id="usernameHint" class="form-text text-danger d-none">
                                Username minimal 4 karakter.
                            </div>
                        </div>

                        <!-- Role -->
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-control" required>
                                <option value="">Pilih Role</option>
                                <option value="dokter" <?php echo (isset($_POST['role']) && $_POST['role']==='dokter') ? 'selected' : ''; ?>>Dokter</option>
                                <option value="admin"  <?php echo (isset($_POST['role']) && $_POST['role']==='admin')  ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="regPassword"
                                       class="form-control" placeholder="Masukkan password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleRegPassword">
                                    <i class="bi bi-eye-slash" id="iconRegPass"></i>
                                </button>
                            </div>

                            <!-- ── Kotak petunjuk password ── -->
                            <div id="passwordHint">
                                <strong>Syarat password:</strong>
                                <ul>
                                    <li id="hint-len"    class="fail">Minimal 6 karakter</li>
                                    <li id="hint-upper"  class="fail">Minimal 1 huruf besar (A-Z)</li>
                                    <li id="hint-lower"  class="fail">Minimal 1 huruf kecil (a-z)</li>
                                    <li id="hint-digit"  class="fail">Minimal 1 angka (0-9)</li>
                                    <li id="hint-symbol" class="fail">Minimal 1 karakter spesial (!@#$%^&* dll)</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <div class="input-group">
                                <input type="password" name="konfirmasi_password" id="regConfirmPassword"
                                       class="form-control" placeholder="Ulangi password" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleRegConfirm">
                                    <i class="bi bi-eye-slash" id="iconRegConf"></i>
                                </button>
                            </div>
                            <div id="confirmHint" class="form-text text-danger d-none">
                                Password dan konfirmasi tidak cocok.
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="daftar" id="btnDaftar" class="btn btn-primary w-100">
                                Daftar Sekarang
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p class="small">Sudah punya akun? <a href="login.php">Login di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Toggle show/hide password ───────────────────────────────────────────────
function toggleVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    const type  = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
    icon.classList.toggle('bi-eye');
    icon.classList.toggle('bi-eye-slash');
}
document.getElementById('toggleRegPassword').addEventListener('click', () =>
    toggleVisibility('regPassword', 'iconRegPass'));
document.getElementById('toggleRegConfirm').addEventListener('click', () =>
    toggleVisibility('regConfirmPassword', 'iconRegConf'));

// ── Real-time password strength checker ────────────────────────────────────
const passInput    = document.getElementById('regPassword');
const hintBox      = document.getElementById('passwordHint');
const hintLen      = document.getElementById('hint-len');
const hintUpper    = document.getElementById('hint-upper');
const hintLower    = document.getElementById('hint-lower');
const hintDigit    = document.getElementById('hint-digit');
const hintSymbol   = document.getElementById('hint-symbol');

function setHint(el, ok) {
    el.classList.toggle('ok',   ok);
    el.classList.toggle('fail', !ok);
}

passInput.addEventListener('focus', () => hintBox.style.display = 'block');
passInput.addEventListener('blur',  () => {
    
    if (passInput.value.length === 0) hintBox.style.display = 'none';
});

passInput.addEventListener('input', function () {
    const v = this.value;
    const okLen    = v.length >= 6;
    const okUpper  = /[A-Z]/.test(v);
    const okLower  = /[a-z]/.test(v);
    const okDigit  = /[0-9]/.test(v);
    const okSymbol = /[!@#$%^&*()\-_=+\[\]{};:'",.<>?\/\\|`~]/.test(v);

    setHint(hintLen,    okLen);
    setHint(hintUpper,  okUpper);
    setHint(hintLower,  okLower);
    setHint(hintDigit,  okDigit);
    setHint(hintSymbol, okSymbol);

    // Sembunyikan kotak jika semua OK dan field tidak fokus
    if (okLen && okUpper && okLower && okDigit && okSymbol && document.activeElement !== passInput) {
        hintBox.style.display = 'none';
    } else {
        hintBox.style.display = 'block';
    }

    // Sinkronkan pengecekan konfirmasi
    checkConfirm();
});

// ── Real-time konfirmasi password ───────────────────────────────────────────
const confInput  = document.getElementById('regConfirmPassword');
const confirmHint = document.getElementById('confirmHint');

function checkConfirm() {
    if (confInput.value.length === 0) {
        confirmHint.classList.add('d-none');
        return;
    }
    const match = passInput.value === confInput.value;
    confirmHint.classList.toggle('d-none', match);
}
confInput.addEventListener('input', checkConfirm);

// ── Real-time username length ───────────────────────────────────────────────
const usernameInput = document.getElementById('regUsername');
const usernameHint  = document.getElementById('usernameHint');
usernameInput.addEventListener('input', function () {
    usernameHint.classList.toggle('d-none', this.value.length === 0 || this.value.length >= 4);
});

// ── Blokir submit jika validasi client-side gagal ───────────────────────────
document.getElementById('regForm').addEventListener('submit', function (e) {
    const v        = passInput.value;
    const user     = usernameInput.value;
    const allOk    = user.length >= 4
                  && v.length >= 6
                  && /[A-Z]/.test(v)
                  && /[a-z]/.test(v)
                  && /[0-9]/.test(v)
                  && /[!@#$%^&*()\-_=+\[\]{};:'",.<>?\/\\|`~]/.test(v)
                  && passInput.value === confInput.value;
    if (!allOk) {
        e.preventDefault();
        // Tampilkan hint box supaya user tahu apa yang kurang
        hintBox.style.display = 'block';
        if (user.length < 4) usernameHint.classList.remove('d-none');
        checkConfirm();
    }
});
</script>
</body>
</html>