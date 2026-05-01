<?php
/**
 * CURA-LOG  |  fungsi/update_status.php
 * Quick-update status & kondisi alat (dipakai dari inventory.php)
 * Hanya admin & organizer
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
requireRole('admin', 'organizer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
}

// CSRF
if (empty($_POST['_csrf']) || $_POST['_csrf'] !== ($_SESSION['_csrf'] ?? '')) {
    setToast('error', 'Token keamanan tidak valid.');
    header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
}

$id_alat = (int)($_POST['id_alat'] ?? 0);
$status  = trim($_POST['status']  ?? '');
$kondisi = trim($_POST['kondisi'] ?? '');
$ket     = trim($_POST['keterangan'] ?? '');

$validStatus = ['Tersedia','Dipinjam','Rusak','Maintenance','Perlu Kalibrasi'];
$validKondisi= ['Baik','Perlu Kalibrasi','Rusak'];

if ($id_alat === 0 || !in_array($status,$validStatus) || !in_array($kondisi,$validKondisi)) {
    setToast('error', 'Data tidak valid.');
    header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
}

try {
    $st = $pdo->prepare("SELECT nama_alat FROM alat WHERE id_alat=?");
    $st->execute([$id_alat]);
    $alat = $st->fetch();
    if (!$alat) {
        setToast('error', 'Alat tidak ditemukan.');
        header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
    }

    $upd = $pdo->prepare("UPDATE alat SET status=?,kondisi=?,keterangan=? WHERE id_alat=?");
    $upd->execute([$status, $kondisi, $ket ?: null, $id_alat]);
    setToast('success', 'Status alat "'.clean($alat['nama_alat']).'" berhasil diperbarui!');

} catch (PDOException $e) {
    error_log('[CURA-LOG] update_status: '.$e->getMessage());
    setToast('error', 'Gagal memperbarui status. Coba lagi.');
}

// Redirect kembali ke halaman sebelumnya
$ref = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . 'fungsi/inventory.php');
header('Location: ' . $ref); exit;
