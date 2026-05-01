<?php
// ============================================================
//  CURA-LOG  |  config.php
// ============================================================

define('DB_HOST',    'localhost');
define('DB_NAME',    'inventaris');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');
define('BASE_URL',   'http://localhost/CURALOG/');
define('IMG_DIR',    __DIR__ . '/assets/img/alat_medis/');
define('IMG_URL',    BASE_URL . 'assets/img/alat_medis/');
define('SESSION_TTL', 7200);

/* ── Session ──────────────────────────────────────────────── */
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_set_cookie_params([
        'lifetime' => SESSION_TTL,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

/* ── PDO ──────────────────────────────────────────────────── */
try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET),
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('[CURA-LOG] DB: ' . $e->getMessage());
    $error_msg = 'Koneksi database gagal. Hubungi administrator.';
    include __DIR__ . '/error.php'; exit;
}

/* ── Helpers ──────────────────────────────────────────────── */
function clean(string $v): string {
    return htmlspecialchars(trim($v), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function isBlank(string $v): bool { return trim($v) === ''; }

function tglID(?string $d): string {
    if (!$d || $d === '0000-00-00') return '–';
    $b = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei',
          '06'=>'Jun','07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt',
          '11'=>'Nov','12'=>'Des'];
    [$y,$m,$dd] = explode('-', $d);
    return (int)$dd . ' ' . ($b[$m] ?? $m) . ' ' . $y;
}

function statusBadge(string $s): string {
    return match($s) {
        'Tersedia'        => 'badge-success',
        'Dipinjam'        => 'badge-warning',
        'Rusak'           => 'badge-danger',
        'Perlu Kalibrasi' => 'badge-info',
        'Maintenance'     => 'badge-secondary',
        default           => 'badge-secondary',
    };
}
function kondisiBadge(string $k): string {
    return match($k) {
        'Baik'            => 'badge-success',
        'Perlu Kalibrasi' => 'badge-info',
        'Rusak'           => 'badge-danger',
        default           => 'badge-secondary',
    };
}
function imgURL(?string $f): string {
    if (!$f) return BASE_URL . 'assets/img/alat_medis/stethoscope.webp';
    if (file_exists(IMG_DIR . $f)) return IMG_URL . rawurlencode($f);
    return BASE_URL . 'assets/img/alat_medis/stethoscope.webp';
}
function setToast(string $type, string $msg): void {
    $_SESSION['_toast'] = compact('type', 'msg');
}
function popToast(): ?array {
    if (!empty($_SESSION['_toast'])) {
        $t = $_SESSION['_toast']; unset($_SESSION['_toast']); return $t;
    }
    return null;
}
