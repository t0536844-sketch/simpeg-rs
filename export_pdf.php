<?php
// export_pdf.php — Export laporan pegawai ke PDF (via browser print)
require_once 'config.php';
// requireLogin(); // disabled for direct script access

// Simple HTML‑escape helper, used in the template
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

$database = new Database();
$db = $database->getConnection();

// Filters
$status = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');

$query = "SELECT * FROM pegawai WHERE 1=1";
$params = [];

if ($status) {
    $query .= " AND status_kepegawaian = ?";
    $params[] = $status;
}
if ($search) {
    $query .= " AND (nama_lengkap LIKE ? OR nip LIKE ? OR jabatan LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$query .= " ORDER BY nama_lengkap ASC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$pegawai = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalPegawai = $db->query("SELECT COUNT(*) FROM pegawai")->fetchColumn();
$totalPNS = $db->query("SELECT COUNT(*) FROM pegawai WHERE status_kepegawaian = 'PNS'")->fetchColumn();
$totalHonorer = $db->query("SELECT COUNT(*) FROM pegawai WHERE status_kepegawaian = 'Honorer'")->fetchColumn();
$totalPria = $db->query("SELECT COUNT(*) FROM pegawai WHERE jenis_kelamin = 'Pria'")->fetchColumn();
$totalWanita = $db->query("SELECT COUNT(*) FROM pegawai WHERE jenis_kelamin = 'Wanita'")->fetchColumn();

// Require notifikasi helper
require_once __DIR__ . '/includes/notifikasi_helper.php';
$notifikasi = getExpiryNotifications($db);
$notifCounts = countExpiryBySeverity($notifikasi);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pegawai — RSUD Mimika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Inter', 'Segoe UI', sans-serif; padding: 20px; color: #1e293b; }
        .header { text-align: center; border-bottom: 3px solid #4f46e5; padding-bottom: 16px; margin-bottom: 24px; }
        .header h2 { color: #4f46e5; font-weight: 800; margin: 0; }
        .header p { color: #64748b; margin: 4px 0 0; }
        .stat-row { display: flex; gap: 16px; margin-bottom: 24px; }
        .stat-box { flex: 1; text-align: center; padding: 16px; border-radius: 12px; color: white; }
        .stat-box .num { font-size: 1.8rem; font-weight: 800; }
        .stat-box .label { font-size: 0.8rem; opacity: 0.9; }
        .bg-1 { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
        .bg-2 { background: linear-gradient(135deg, #10b981, #06b6d4); }
        .bg-3 { background: linear-gradient(135deg, #f59e0b, #f97316); }
        .bg-4 { background: linear-gradient(135deg, #ec4899, #f43f5e); }
        .table { font-size: 0.85rem; }
        .table thead th { background: #4f46e5; color: white; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; padding: 8px 6px; }
        .table tbody td { padding: 6px; vertical-align: middle; }
        .badge-pns { background: #10b981; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; }
        .badge-honorer { background: #f59e0b; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; }
        .expired { color: #ef4444; font-weight: 600; }
        .kritis { color: #f97316; font-weight: 600; }
        .segera { color: #eab308; font-weight: 600; }
        .peringatan { color: #06b6d4; font-weight: 600; }
        .footer { margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 0.8rem; color: #94a3b8; text-align: center; }
        .notif-table { margin-top: 16px; }
        .notif-table thead th { background: #f59e0b; }

        @media print {
            body { padding: 0; font-size: 10pt; }
            .no-print { display: none !important; }
            .stat-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .table thead th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge-pns, .badge-honorer { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { margin: 1.5cm; size: A4 landscape; }
        }
    </style>
</head>
<body>

<!-- Print button -->
<div class="text-end mb-3 no-print">
    <button onclick="window.print()" class="btn btn-primary">
        <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
    </button>
    <a href="dashboard.php" class="btn btn-outline-secondary ms-1">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="header">
    <h2>LAPORAN DATA PEGAWAI</h2>
    <p>RSUD Mimika — Dicetak pada <?= date('d F Y, H:i') ?> WIB</p>
</div>

<!-- Summary Stats -->
<div class="stat-row">
    <div class="stat-box bg-1"><div class="num"><?= $totalPegawai ?></div><div class="label">Total Pegawai</div></div>
    <div class="stat-box bg-2"><div class="num"><?= $totalPNS ?></div><div class="label">PNS</div></div>
    <div class="stat-box bg-3"><div class="num"><?= $totalHonorer ?></div><div class="label">Honorer</div></div>
    <div class="stat-box bg-4"><div class="num"><?= $totalPria ?> / <?= $totalWanita ?></div><div class="label">Pria / Wanita</div></div>
</div>

<!-- Expiry Notifications -->
<?php if ($notifCounts['total'] > 0): ?>
<h5 class="mb-2"><i class="bi bi-bell-fill text-warning me-1"></i> Dokumen Menjelang Kadaluarsa</h5>
<div class="table-responsive">
    <table class="table table-sm table-bordered notif-table">
        <thead>
            <tr>
                <th>No</th><th>Nama</th><th>Jabatan</th><th>Dokumen</th><th>Masa Berlaku</th><th>Status</th><th>Sisa Waktu</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($notifikasi as $n): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><strong><?= e($n['nama']) ?></strong></td>
                <td><?= e($n['jabatan']) ?></td>
                <td><span class="badge bg-<?= $n['doc'] === 'STR' ? 'primary' : 'secondary' ?>"><?= $n['doc'] ?></span></td>
                <td><?= date('d/m/Y', strtotime($n['expiry'])) ?></td>
                <td><span class="<?= $n['severity'] ?>"><?= severityLabel($n['severity']) ?></span></td>
                <td><?= $n['days'] < 0 ? abs($n['days']) . ' hari lalu' : $n['days'] . ' hari lagi' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Pegawai Table -->
<h5 class="mb-2 mt-4">Data Pegawai</h5>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Lengkap</th>
                <th>NIP</th>
                <th>Jabatan</th>
                <th>Status</th>
                <th>Jenis Kelamin</th>
                <th>Pangkat/Gol</th>
                <th>Pendidikan</th>
                <th>STR</th>
                <th>SIP</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($pegawai as $p): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e($p['nama_lengkap']) ?></td>
                <td><?= e($p['nip']) ?></td>
                <td><?= e($p['jabatan']) ?></td>
                <td>
                    <span class="badge-<?= $p['status_kepegawaian'] === 'PNS' ? 'pns' : 'honorer' ?>">
                        <?= e($p['status_kepegawaian']) ?>
                    </span>
                </td>
                <td><?= e($p['jenis_kelamin']) ?></td>
                <td><?= e($p['pangkat_golongan']) ?></td>
                <td><?= e($p['pendidikan']) ?></td>
                <td><?= e($p['masa_berlaku_str'] ?? '-') ?></td>
                <td><?= e($p['masa_berlaku_sip'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="footer">
    <p>SIM Kepegawaian RSUD Mimika — <?= count($pegawai) ?> data pegawai</p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
