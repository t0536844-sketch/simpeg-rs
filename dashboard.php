<?php
// dashboard.php
require_once 'config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

// Statistics
$stats = [];
$queries = [
    'total'   => "SELECT COUNT(*) as c FROM pegawai",
    'pns'     => "SELECT COUNT(*) as c FROM pegawai WHERE status_kepegawaian = 'PNS'",
    'honorer' => "SELECT COUNT(*) as c FROM pegawai WHERE status_kepegawaian = 'Honorer'",
    'aktif'   => "SELECT COUNT(*) as c FROM pegawai WHERE link_sk_pensiun IS NULL OR link_sk_pensiun = ''",
    'pensiun' => "SELECT COUNT(*) as c FROM pegawai WHERE link_sk_pensiun IS NOT NULL AND link_sk_pensiun != ''",
    'pria'    => "SELECT COUNT(*) as c FROM pegawai WHERE jenis_kelamin = 'Pria'",
    'wanita'  => "SELECT COUNT(*) as c FROM pegawai WHERE jenis_kelamin = 'Wanita'",
];
foreach ($queries as $key => $q) {
    $stats[$key] = $db->query($q)->fetch(PDO::FETCH_ASSOC)['c'];
}

// Recent employees
$recentStmt = $db->query("SELECT * FROM pegawai ORDER BY created_at DESC LIMIT 10");
$recentEmployees = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/includes/layout.php';
?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,var(--primary),var(--secondary))">
            <div class="d-flex justify-content-between">
                <div>
                    <small>Total Pegawai</small>
                    <h2 class="mb-0"><?= $stats['total'] ?></h2>
                </div>
                <i class="bi bi-people-fill" style="font-size:2.5rem;opacity:.4"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#10b981,#059669)">
            <div class="d-flex justify-content-between">
                <div>
                    <small>PNS</small>
                    <h2 class="mb-0"><?= $stats['pns'] ?></h2>
                </div>
                <i class="bi bi-person-check-fill" style="font-size:2.5rem;opacity:.4"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)">
            <div class="d-flex justify-content-between">
                <div>
                    <small>Honorer</small>
                    <h2 class="mb-0"><?= $stats['honorer'] ?></h2>
                </div>
                <i class="bi bi-person" style="font-size:2.5rem;opacity:.4"></i>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
            <div class="d-flex justify-content-between">
                <div>
                    <small>Aktif</small>
                    <h2 class="mb-0"><?= $stats['aktif'] ?></h2>
                </div>
                <i class="bi bi-check-circle" style="font-size:2.5rem;opacity:.4"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Distribusi Jenis Kelamin</h5>
                <canvas id="genderChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Status Kepegawaian</h5>
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Employees -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">Pegawai Terbaru</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nama Lengkap</th><th>NIP</th><th>Jabatan</th><th>Status</th><th>Tanggal Input</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentEmployees as $emp): ?>
                    <tr>
                        <td><?= e($emp['nama_lengkap']) ?></td>
                        <td><?= e($emp['nip']) ?></td>
                        <td><?= e($emp['jabatan']) ?></td>
                        <td>
                            <span class="badge bg-<?= $emp['status_kepegawaian'] === 'PNS' ? 'success' : 'warning' ?>">
                                <?= e($emp['status_kepegawaian']) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($emp['created_at'])) ?></td>
                        <td>
                            <a href="detail_pegawai.php?id=<?= $emp['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentEmployees)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data pegawai.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$extraJS = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById("genderChart"), {
    type: "doughnut",
    data: {
        labels: ["Pria","Wanita"],
        datasets: [{ data: [' . $stats['pria'] . ',' . $stats['wanita'] . '], backgroundColor: ["#3b82f6","#ec4899"], borderWidth: 1 }]
    },
    options: { responsive: true, plugins: { legend: { position: "bottom" } } }
});
new Chart(document.getElementById("statusChart"), {
    type: "pie",
    data: {
        labels: ["Aktif","Pensiun"],
        datasets: [{ data: [' . $stats['aktif'] . ',' . $stats['pensiun'] . '], backgroundColor: ["#10b981","#8b5cf6"], borderWidth: 1 }]
    },
    options: { responsive: true, plugins: { legend: { position: "bottom" } } }
});
</script>';
require __DIR__ . '/includes/layout_footer.php';
?>
