<?php
/**
 * CURA-LOG  |  api.php
 * JSON API untuk request AJAX dari frontend
 * Semua response: {"success":bool,"message":"...","data":...}
 */
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

// Harus login
if (empty($_SESSION['user_id'])) {
    apiResp(false, 'Sesi tidak valid. Silakan login kembali.', null, 401);
}

$ME = [
    'id'   => (int)$_SESSION['user_id'],
    'nama' => $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? '',
    'role' => $_SESSION['role'] ?? 'dokter',
];

$action = clean($_REQUEST['action'] ?? '');

try {
    switch ($action) {

        // ── Cari alat (live search) ──────────────────────────
        case 'search_alat':
            $q = trim($_GET['q'] ?? '');
            $s = trim($_GET['status'] ?? '');
            $params = [];
            $where  = ['1=1'];
            if ($q !== '') {
                $where[] = "(a.nama_alat LIKE ? OR a.merk LIKE ? OR a.no_seri LIKE ?)";
                $like    = "%$q%";
                array_push($params, $like, $like, $like);
            }
            if ($s !== '') { $where[] = "a.status=?"; $params[] = $s; }
            $sql = "SELECT a.id_alat,a.nama_alat,a.merk,a.no_seri,a.gambar,
                           a.status,a.kondisi,r.nama_ruangan
                    FROM alat a
                    LEFT JOIN ruangan r ON a.id_ruangan=r.id_ruangan
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY a.nama_alat LIMIT 30";
            $st = $pdo->prepare($sql);
            $st->execute($params);
            apiResp(true, 'OK', $st->fetchAll());
            break;

        // ── Detail satu alat ─────────────────────────────────
        case 'get_alat':
            $id = (int)($_GET['id'] ?? 0);
            $st = $pdo->prepare("
                SELECT a.*, r.nama_ruangan
                FROM alat a LEFT JOIN ruangan r ON a.id_ruangan=r.id_ruangan
                WHERE a.id_alat=?
            ");
            $st->execute([$id]);
            $row = $st->fetch();
            if (!$row) apiResp(false, 'Alat tidak ditemukan.', null, 404);
            apiResp(true, 'OK', $row);
            break;

        // ── Pinjam alat ──────────────────────────────────────
        case 'pinjam':
            if (!in_array($ME['role'], ['dokter','admin'], true)) {
                apiResp(false, 'Role Anda tidak dapat meminjam alat.', null, 403);
            }
            $id_alat        = (int)($_POST['id_alat']        ?? 0);
            $keperluan      = trim($_POST['keperluan']        ?? '');
            $ruangan_tujuan = trim($_POST['ruangan_tujuan']   ?? '');
            if ($id_alat === 0 || isBlank($keperluan) || isBlank($ruangan_tujuan)) {
                apiResp(false, 'Semua kolom wajib diisi.');
            }
            $pdo->beginTransaction();
            $st = $pdo->prepare("SELECT nama_alat,status FROM alat WHERE id_alat=?");
            $st->execute([$id_alat]);
            $alat = $st->fetch();
            if (!$alat) { $pdo->rollBack(); apiResp(false, 'Alat tidak ditemukan.', null, 404); }
            if ($alat['status'] !== 'Tersedia') {
                $pdo->rollBack();
                apiResp(false, "Alat tidak tersedia (status: {$alat['status']}).");
            }
            $pdo->prepare("
                INSERT INTO history_peminjaman (id_alat,id_user,keperluan,ruangan_tujuan,status_peminjaman)
                VALUES (?,?,?,?,'Dipinjam')
            ")->execute([$id_alat, $ME['id'], $keperluan, $ruangan_tujuan]);
            $pdo->prepare("UPDATE alat SET status='Dipinjam' WHERE id_alat=?")->execute([$id_alat]);
            $pdo->commit();
            apiResp(true, "Alat \"{$alat['nama_alat']}\" berhasil dipinjam.");
            break;

        // ── Kembalikan alat ──────────────────────────────────
        case 'kembalikan':
            $id_hist = (int)($_POST['id_history'] ?? 0);
            $catatan = trim($_POST['catatan'] ?? '');
            if ($id_hist === 0) apiResp(false, 'ID history tidak valid.');
            $pdo->beginTransaction();
            $st = $pdo->prepare("
                SELECT h.*,a.nama_alat FROM history_peminjaman h
                JOIN alat a ON h.id_alat=a.id_alat
                WHERE h.id_history=? AND h.status_peminjaman='Dipinjam'
            ");
            $st->execute([$id_hist]);
            $hist = $st->fetch();
            if (!$hist) { $pdo->rollBack(); apiResp(false, 'Data peminjaman tidak ditemukan.', null, 404); }
            if ($ME['role'] === 'dokter' && $hist['id_user'] != $ME['id']) {
                $pdo->rollBack();
                apiResp(false, 'Anda hanya dapat mengembalikan alat yang Anda pinjam.', null, 403);
            }
            $pdo->prepare("
                UPDATE history_peminjaman
                SET status_peminjaman='Dikembalikan', tgl_kembali=NOW(), catatan=?
                WHERE id_history=?
            ")->execute([$catatan ?: null, $id_hist]);
            $pdo->prepare("UPDATE alat SET status='Tersedia' WHERE id_alat=?")->execute([$hist['id_alat']]);
            $pdo->commit();
            apiResp(true, "Alat \"{$hist['nama_alat']}\" berhasil dikembalikan.");
            break;

        // ── Update status & kondisi ──────────────────────────
        case 'update_status':
            if (!in_array($ME['role'], ['admin','organizer'], true)) {
                apiResp(false, 'Akses ditolak.', null, 403);
            }
            $id_alat = (int)($_POST['id_alat'] ?? 0);
            $status  = trim($_POST['status']   ?? '');
            $kondisi = trim($_POST['kondisi']  ?? '');
            $validS  = ['Tersedia','Dipinjam','Rusak','Maintenance','Perlu Kalibrasi'];
            $validK  = ['Baik','Perlu Kalibrasi','Rusak'];
            if (!in_array($status, $validS) || !in_array($kondisi, $validK)) {
                apiResp(false, 'Nilai status atau kondisi tidak valid.');
            }
            $pdo->prepare("UPDATE alat SET status=?,kondisi=? WHERE id_alat=?")
                ->execute([$status, $kondisi, $id_alat]);
            apiResp(true, 'Status alat berhasil diperbarui.');
            break;

        // ── Hapus alat (admin only) ──────────────────────────
        case 'hapus_alat':
            if ($ME['role'] !== 'admin') apiResp(false, 'Hanya admin yang dapat menghapus alat.', null, 403);
            $id_alat = (int)($_POST['id_alat'] ?? 0);
            $st = $pdo->prepare("SELECT nama_alat,gambar,status FROM alat WHERE id_alat=?");
            $st->execute([$id_alat]);
            $alat = $st->fetch();
            if (!$alat) apiResp(false, 'Alat tidak ditemukan.', null, 404);
            if ($alat['status'] === 'Dipinjam') apiResp(false, 'Alat sedang dipinjam, tidak dapat dihapus.');
            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM history_peminjaman WHERE id_alat=?")->execute([$id_alat]);
            $pdo->prepare("DELETE FROM alat WHERE id_alat=?")->execute([$id_alat]);
            $pdo->commit();
            if (!empty($alat['gambar']) && strpos($alat['gambar'], 'upload_') === 0) {
                $p = IMG_DIR . $alat['gambar'];
                if (file_exists($p)) @unlink($p);
            }
            apiResp(true, "Alat \"{$alat['nama_alat']}\" berhasil dihapus.");
            break;

        // ── Stats dashboard ──────────────────────────────────
        case 'stats':
            apiResp(true, 'OK', [
                'total'     => (int)$pdo->query("SELECT COUNT(*) FROM alat")->fetchColumn(),
                'tersedia'  => (int)$pdo->query("SELECT COUNT(*) FROM alat WHERE status='Tersedia'")->fetchColumn(),
                'dipinjam'  => (int)$pdo->query("SELECT COUNT(*) FROM alat WHERE status='Dipinjam'")->fetchColumn(),
                'rusak'     => (int)$pdo->query("SELECT COUNT(*) FROM alat WHERE status IN('Rusak','Maintenance')")->fetchColumn(),
                'kalibrasi' => (int)$pdo->query("SELECT COUNT(*) FROM alat WHERE status='Perlu Kalibrasi' OR kondisi='Perlu Kalibrasi'")->fetchColumn(),
                'aktif_pinjam' => (int)$pdo->query("SELECT COUNT(*) FROM history_peminjaman WHERE status_peminjaman='Dipinjam'")->fetchColumn(),
            ]);
            break;

        // ── Pinjaman aktif user ──────────────────────────────
        case 'my_loans':
            $uid = ($ME['role'] === 'dokter') ? $ME['id'] : (int)($_GET['user_id'] ?? $ME['id']);
            $st  = $pdo->prepare("
                SELECT h.id_history,h.tgl_pinjam,h.keperluan,h.ruangan_tujuan,
                       a.nama_alat,a.gambar,a.no_seri,u.nama_lengkap
                FROM history_peminjaman h
                JOIN alat a ON h.id_alat=a.id_alat
                JOIN users u ON h.id_user=u.id
                WHERE h.status_peminjaman='Dipinjam' AND h.id_user=?
                ORDER BY h.tgl_pinjam DESC
            ");
            $st->execute([$uid]);
            apiResp(true, 'OK', $st->fetchAll());
            break;

        default:
            apiResp(false, "Action '$action' tidak dikenal.", null, 400);
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('[CURA-LOG] api.php PDO: ' . $e->getMessage());
    apiResp(false, 'Terjadi kesalahan database. Silakan coba lagi.');
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('[CURA-LOG] api.php: ' . $e->getMessage());
    apiResp(false, $e->getMessage());
}

function apiResp(bool $ok, string $msg, $data = null, int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success'=>$ok,'message'=>$msg,'data'=>$data],
                     JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
