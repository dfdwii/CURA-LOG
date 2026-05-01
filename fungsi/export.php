<?php
/**
 * CURA-LOG  |  fungsi/export.php
 * Export data inventaris ke CSV
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
requireRole('admin', 'organizer');

$type = trim($_GET['type'] ?? 'inventaris');

try {
    if ($type === 'inventaris') {
        $rows = $pdo->query("
            SELECT a.no_seri, a.nama_alat, a.merk, a.kategori,
                   r.nama_ruangan, a.tgl_masuk, a.masa_kalibrasi,
                   a.status, a.kondisi, a.keterangan
            FROM alat a
            LEFT JOIN ruangan r ON a.id_ruangan=r.id_ruangan
            ORDER BY a.nama_alat
        ")->fetchAll();

        $filename = 'inventaris_alat_' . date('Ymd_His') . '.csv';
        $headers  = ['No. Seri','Nama Alat','Merk','Kategori','Ruangan',
                     'Tgl Masuk','Masa Kalibrasi','Status','Kondisi','Keterangan'];

    } elseif ($type === 'history') {
        $rows = $pdo->query("
            SELECT h.id_history, a.nama_alat, a.no_seri,
                   u.nama_lengkap, u.role,
                   h.keperluan, h.ruangan_tujuan,
                   h.tgl_pinjam, h.tgl_kembali,
                   h.status_peminjaman, h.catatan
            FROM history_peminjaman h
            JOIN alat a ON h.id_alat=a.id_alat
            JOIN users u ON h.id_user=u.id
            ORDER BY h.tgl_pinjam DESC
        ")->fetchAll();

        $filename = 'history_peminjaman_' . date('Ymd_His') . '.csv';
        $headers  = ['ID','Nama Alat','No. Seri','Dipinjam Oleh','Role',
                     'Keperluan','Ruangan Tujuan','Tgl Pinjam','Tgl Kembali',
                     'Status','Catatan'];
    } else {
        setToast('error', 'Tipe export tidak valid.');
        header('Location: ' . BASE_URL . 'index.php'); exit;
    }

    // Stream CSV
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    // BOM untuk Excel UTF-8
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, $headers);
    foreach ($rows as $row) {
        fputcsv($out, array_values($row));
    }
    fclose($out);
    exit;

} catch (PDOException $e) {
    error_log('[CURA-LOG] export: '.$e->getMessage());
    setToast('error', 'Gagal mengekspor data.');
    header('Location: ' . BASE_URL . 'index.php'); exit;
}
