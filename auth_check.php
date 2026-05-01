<?php
// ── Auth guard (include setelah config.php) ────────────────
if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php'); exit;
}
// Timeout
if (!empty($_SESSION['_last']) && (time() - $_SESSION['_last']) > SESSION_TTL) {
    session_unset(); session_destroy(); session_start();
    setToast('warning', 'Sesi habis karena tidak aktif. Silakan login kembali.');
    header('Location: ' . BASE_URL . 'login.php'); exit;
}
$_SESSION['_last'] = time();

$ME = [
    'id'   => (int)$_SESSION['user_id'],
    'user' => $_SESSION['username']     ?? '',
    'nama' => $_SESSION['nama_lengkap'] ?? '',
    'role' => $_SESSION['role']         ?? 'dokter',
];

function requireRole(string ...$roles): void {
    global $ME;
    if (!in_array($ME['role'], $roles, true)) {
        setToast('error', 'Anda tidak memiliki akses ke halaman ini.');
        header('Location: ' . BASE_URL . 'index.php'); exit;
    }
}
