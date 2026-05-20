<?php
// notifikasi.php — Halaman notifikasi STR/SIP kadaluarsa
require_once 'config.php';
requireLogin();

require_once __DIR__ . '/includes/notifikasi_helper.php';

$database = new Database();
$db = $database->getConnection();

$notifikasi = getExpiryNotifications($db);
$counts = countExpiryBySeverity($notifikasi);

// Filter by severity
$filter = $_GET['filter'] ?? 'all';
if ($filter !== 'all') {
    $notifikasi = array_filter($notifikasi, fn($n) => $n['severity'] === $filter);
}

$pageTitle = 'Notifikasi Dokumen';
$activePage = 'notifikasi';
$breadcrumb = [
    ['label' => 'Notifikasi', 'active' => true],
];
require __DIR__ . '/includes/layout.php';
?>

<!-- Severity Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card bg-gradient-warning">
            <div class="stat-number"><?= $counts['expired'] ?></div>
            <div class="stat-label"><i class="bi bi-x-circle-fill me-1"></i>Kadaluarsa</div>
            <div class="stat-icon"><i class="bi bi-x-circle-fill"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #ef4444, #f97316);">
            <div class="stat-number"><?= $counts['kritis'] ?></div>
            <div class="stat-label"><i class="bi bi-exclamation-circle-fill me-1"></i>Kritis (≤7 hari)</div>
            <div class="stat-icon"><i class="bi bi-exclamation-circle-fill"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #eab308);">
            <div class="stat-number"><?= $counts['segera'] ?></div>
            <div class="stat-label"><i class="bi bi-exclamation-triangle-fill me-1"></i>Segera (≤14 hari)</div>
            <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #06b6d4, #3b82f6);">
            <div class="stat-number"><?= $counts['peringatan'] ?></div>
            <div class="stat-label"><i class="bi bi-info-circle-fill me-1"></i>Peringatan (≤30 hari)</div>
            <div class="stat-icon"><i class="bi bi-info-circle-fill"></i></div>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h6 class="mb-0"><i class="bi bi-bell-fill text-warning me-1"></i> Semua Notifikasi (<?= $counts['total'] ?>)</h6>
            <div class="btn-group btn-group-sm" role="group">
                <a href="notifikasi.php?filter=all" class="btn btn-outline-<?= $filter === 'all' ? 'primary' : 'secondary' ?>">Semua</a>
                <a href="notifikasi.php?filter=expired" class="btn btn-outline-<?= $filter === 'expired' ? 'danger' : 'secondary' ?>">Kadaluarsa (<?= $counts['expired'] ?>)</a>
                <a href="notifikasi.php?filter=kritis" class="btn btn-outline-<?= $filter === 'kritis' ? 'danger' : 'secondary' ?>">Kritis (<?= $counts['kritis'] ?>)</a>
                <a href="notifikasi.php?filter=segera" class="btn btn-outline-<?= $filter === 'segera' ? 'warning' : 'secondary' ?>">Segera (<?= $counts['segera'] ?>)</a>
                <a href="notifikasi.php?filter=peringatan" class="btn btn-outline-<?= $filter === 'peringatan' ? 'info' : 'secondary' ?>">Peringatan (<?= $counts['peringatan'] ?>)</a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($notifikasi)): ?>
        <div class="text-center py-5">
            <i class="bi bi-check-circle text-success" style="font-size:3rem"></i>
            <p class="mt-3 text-muted">Tidak ada notifikasi. Semua dokumen masih berlaku.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pegawai</th>
                        <th>Jabatan</th>
                        <th>Status</th>
                        <th>Dokumen</th>
                        <th>Masa Berlaku</th>
                        <th>Tingkat</th>
                        <th>Sisa Waktu</th>
                        <th>Tindak Lanjut</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($notifikasi as $n):
                        $badgeClass = severityBadgeClass($n['severity']);
                        $label = severityLabel($n['severity']);
                        $icon = severityIcon($n['severity']);
                        $rowClass = match($n['severity']) {
                            'expired' => 'table-danger',
                            'kritis' => 'table-warning',
                            default => '',
                        };
                    ?>
                    <tr class="<?= $rowClass ?>">
                        <td><?= $no++ ?></td>
                        <td><strong><?= e($n['nama']) ?></strong></td>
                        <td><?= e($n['jabatan']) ?></td>
                        <td>
                            <span class="badge bg-<?= $n['status_kepegawaian'] === 'PNS' ? 'success' : 'warning' ?>">
                                <?= e($n['status_kepegawaian']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($n['doc'] === 'STR'): ?>
                                <span class="badge bg-primary"><i class="bi bi-file-medical"></i> <?= $n['doc'] ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><i class="bi bi-file-text"></i> <?= $n['doc'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d F Y', strtotime($n['expiry'])) ?></td>
                        <td>
                            <span class="badge <?= $badgeClass ?>">
                                <i class="bi <?= $icon ?>"></i> <?= $label ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($n['days'] < 0): ?>
                                <span class="text-danger fw-bold">Sudah <?= abs($n['days']) ?> hari lalu</span>
                            <?php elseif ($n['days'] == 0): ?>
                                <span class="text-danger fw-bold">Hari ini!</span>
                            <?php else: ?>
                                <span class="fw-bold"><?= $n['days'] ?> hari lagi</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($n['severity'] === 'expired'): ?>
                                <small class="text-danger fw-bold">Segera perpanjang!</small>
                            <?php elseif ($n['severity'] === 'kritis'): ?>
                                <small class="text-danger fw-bold">Perpanjang minggu ini</small>
                            <?php elseif ($n['severity'] === 'segera'): ?>
                                <small class="text-warning">Siapkan berkas</small>
                            <?php else: ?>
                                <small class="text-info">Jadwalkan perpanjangan</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="detail_pegawai.php?id=<?= $n['pegawai_id'] ?>" class="btn btn-sm btn-outline-primary" title="Lihat detail">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-footer text-muted" style="font-size:0.8rem">
        <i class="bi bi-info-circle"></i> Ditampilkan dokumen STR/SIP yang akan kadaluarsa dalam 30 hari ke depan atau sudah kadaluarsa.
    </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
