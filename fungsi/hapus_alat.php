<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
requireRole('admin');

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) {
    setToast('error', 'ID alat tidak valid.');
    header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
}

try {
    // Ambil data dulu untuk validasi & cleanup gambar
    $st = $pdo->prepare("SELECT nama_alat, gambar, status FROM alat WHERE id_alat=?");
    $st->execute([$id]);
    $alat = $st->fetch();

    if (!$alat) {
        setToast('error', 'Alat tidak ditemukan.');
        header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
    }

    // Jangan hapus alat yang sedang dipinjam
    if ($alat['status'] === 'Dipinjam') {
        setToast('error', 'Alat "'.clean($alat['nama_alat']).'" sedang dipinjam dan tidak dapat dihapus.');
        header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
    }

    $pdo->beginTransaction();

    // History terkait akan terhapus otomatis (ON DELETE CASCADE)
    $del = $pdo->prepare("DELETE FROM alat WHERE id_alat=?");
    $del->execute([$id]);

    $pdo->commit();

    // Hapus file gambar upload (bukan preset)
    if (!empty($alat['gambar']) && strpos($alat['gambar'], 'upload_') === 0) {
        $path = IMG_DIR . $alat['gambar'];
        if (file_exists($path)) @unlink($path);
    }

    setToast('success', 'Alat "'.clean($alat['nama_alat']).'" berhasil dihapus.');

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('[CURA-LOG] hapus_alat: ' . $e->getMessage());
    setToast('error', 'Gagal menghapus alat. Coba lagi.');
}

header('Location: ' . BASE_URL . 'fungsi/inventory.php'); exit;
