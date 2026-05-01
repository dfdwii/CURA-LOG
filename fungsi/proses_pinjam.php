<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
requireRole('dokter', 'admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
}

// CSRF
if (empty($_POST['_csrf']) || $_POST['_csrf'] !== ($_SESSION['_csrf'] ?? '')) {
    setToast('error', 'Token keamanan tidak valid.');
    header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
}

$id_alat        = (int)($_POST['id_alat'] ?? 0);
$keperluan      = trim($_POST['keperluan'] ?? '');
$ruangan_tujuan = trim($_POST['ruangan_tujuan'] ?? '');

// Validasi input
if ($id_alat === 0 || isBlank($keperluan) || isBlank($ruangan_tujuan)) {
    setToast('error', 'Semua kolom peminjaman wajib diisi.');
    header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
}

try {
    // Cek status terbaru (re-check untuk race condition)
    $st = $pdo->prepare("SELECT nama_alat, status FROM alat WHERE id_alat=? FOR UPDATE");
    // Tidak semua engine support FOR UPDATE di luar transaksi, gunakan transaksi
    $pdo->beginTransaction();

    $st = $pdo->prepare("SELECT nama_alat, status FROM alat WHERE id_alat=?");
    $st->execute([$id_alat]);
    $alat = $st->fetch();

    if (!$alat) {
        $pdo->rollBack();
        setToast('error', 'Alat tidak ditemukan.');
        header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
    }
    if ($alat['status'] !== 'Tersedia') {
        $pdo->rollBack();
        setToast('error', 'Alat "'.clean($alat['nama_alat']).'" tidak tersedia (status: '.$alat['status'].').');
        header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
    }

    // Insert history
    $ins = $pdo->prepare("
        INSERT INTO history_peminjaman (id_alat, id_user, keperluan, ruangan_tujuan, status_peminjaman)
        VALUES (?, ?, ?, ?, 'Dipinjam')
    ");
    $ins->execute([$id_alat, $ME['id'], $keperluan, $ruangan_tujuan]);

    // Update status alat
    $upd = $pdo->prepare("UPDATE alat SET status='Dipinjam' WHERE id_alat=?");
    $upd->execute([$id_alat]);

    $pdo->commit();
    setToast('success', 'Alat "'.clean($alat['nama_alat']).'" berhasil dipinjam!');

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('[CURA-LOG] proses_pinjam: ' . $e->getMessage());
    setToast('error', 'Gagal memproses peminjaman. Coba lagi.');
}

header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
