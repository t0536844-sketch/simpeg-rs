<?php
// hapus_pegawai.php — Konfirmasi & hapus pegawai (Admin only)
require_once 'config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$id = sanitizeInt($_GET['id'] ?? 0, 1);

$stmt = $db->prepare("SELECT nama_lengkap FROM pegawai WHERE id = ?");
$stmt->execute([$id]);
$pegawai = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pegawai) {
    setFlash('error', 'Data pegawai tidak ditemukan.');
    header('Location: pegawai.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $delStmt = $db->prepare("DELETE FROM pegawai WHERE id = ?");
    if ($delStmt->execute([$id])) {
        logActivity($db, $_SESSION['user_id'], 'DELETE', 'pegawai', $id, 'Menghapus data pegawai: ' . $pegawai['nama_lengkap']);
        setFlash('success', 'Data pegawai berhasil dihapus.');
        header('Location: pegawai.php');
        exit;
    } else {
        $error = 'Gagal menghapus data pegawai.';
    }
}

$pageTitle = 'Hapus Pegawai';
$activePage = 'pegawai';
require __DIR__ . '/includes/layout.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i> Konfirmasi Penghapusan</h5>
            </div>
            <div class="card-body text-center">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-octagon" style="font-size:48px"></i>
                    <h5 class="mt-3">Apakah Anda yakin?</h5>
                    <p>Anda akan menghapus data pegawai:</p>
                    <h4 class="text-danger"><?= e($pegawai['nama_lengkap']) ?></h4>
                    <p class="text-muted">Data yang telah dihapus tidak dapat dikembalikan.</p>
                </div>
                <form method="POST">
                    <?= csrf_field() ?>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="pegawai.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i> Batal</a>
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i> Ya, Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
