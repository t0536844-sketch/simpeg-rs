<?php
// laporan.php — Laporan kepegawaian dengan filter & grafik
require_once 'config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$start_date = sanitizeDate($_GET['start_date'] ?? date('Y-m-01')) ?: date('Y-m-01');
$end_date = sanitizeDate($_GET['end_date'] ?? date('Y-m-t')) ?: date('Y-m-t');
$statusFilter = sanitize($_GET['status'] ?? '');
$jabatanFilter = sanitize($_GET['jabatan'] ?? '');
$agamaFilter = sanitize($_GET['agama'] ?? '');

$query = "SELECT * FROM pegawai WHERE 1=1";
$params = [];
if ($start_date && $end_date) { $query .= " AND DATE(created_at) BETWEEN ? AND ?"; $params[] = $start_date; $params[] = $end_date; }
if ($statusFilter) { $query .= " AND status_kepegawaian = ?"; $params[] = $statusFilter; }
if ($jabatanFilter) { $query .= " AND jabatan LIKE ?"; $params[] = "%$jabatanFilter%"; }
if ($agamaFilter) { $query .= " AND agama = ?"; $params[] = $agamaFilter; }
$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query); $stmt->execute($params);
$pegawai = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$statsStmt = $db->prepare("SELECT COUNT(*) as total,
    SUM(CASE WHEN status_kepegawaian='PNS' THEN 1 ELSE 0 END) as pns,
    SUM(CASE WHEN status_kepegawaian='Honorer' THEN 1 ELSE 0 END) as honorer,
    SUM(CASE WHEN status_kepegawaian='CPNS' THEN 1 ELSE 0 END) as cpns,
    SUM(CASE WHEN status_kepegawaian='Kontrak' THEN 1 ELSE 0 END) as kontrak,
    SUM(CASE WHEN jenis_kelamin='Pria' THEN 1 ELSE 0 END) as pria,
    SUM(CASE WHEN jenis_kelamin='Wanita' THEN 1 ELSE 0 END) as wanita
    FROM pegawai");
$statsStmt->execute(); $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$jabatan_list = $db->query("SELECT DISTINCT jabatan FROM pegawai WHERE jabatan IS NOT NULL AND jabatan!='' ORDER BY jabatan")->fetchAll(PDO::FETCH_COLUMN, 0);
$agama_list = $db->query("SELECT DISTINCT agama FROM pegawai WHERE agama IS NOT NULL AND agama!='' ORDER BY agama")->fetchAll(PDO::FETCH_COLUMN, 0);

$pageTitle = 'Laporan';
$activePage = 'laporan';
$breadcrumb = [['label' => 'Laporan', 'active' => true]];
require __DIR__ . '/includes/layout.php';
?>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3"><label class="form-label">Tanggal Mulai</label><input type="date" class="form-control" name="start_date" value="<?= e($start_date) ?>"></div>
            <div class="col-md-3"><label class="form-label">Tanggal Akhir</label><input type="date" class="form-control" name="end_date" value="<?= e($end_date) ?>"></div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua</option>
                    <?php foreach(['PNS','CPNS','Honorer','Kontrak'] as $s): ?><option value="<?= $s ?>" <?= $statusFilter===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Jabatan</label>
                <select class="form-select" name="jabatan">
                    <option value="">Semua</option>
                    <?php foreach($jabatan_list as $j): ?><option value="<?= e($j) ?>" <?= $jabatanFilter===$j?'selected':'' ?>><?= e($j) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Agama</label>
                <select class="form-select" name="agama">
                    <option value="">Semua</option>
                    <?php foreach($agama_list as $a): ?><option value="<?= e($a) ?>" <?= $agamaFilter===$a?'selected':'' ?>><?= e($a) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-filter me-1"></i> Filter</button>
                <a href="laporan.php" class="btn btn-secondary">Reset</a>
                <button type="button" onclick="window.print()" class="btn btn-success"><i class="bi bi-printer me-1"></i> Cetak</button>
            </div>
        </form>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card text-center p-3"><small class="text-muted">Total</small><h3><?= $stats['total'] ?></h3></div></div>
    <div class="col-6 col-md-3"><div class="card text-center p-3"><small class="text-muted">PNS</small><h3 class="text-success"><?= $stats['pns'] ?></h3></div></div>
    <div class="col-6 col-md-3"><div class="card text-center p-3"><small class="text-muted">Honorer</small><h3 class="text-warning"><?= $stats['honorer'] ?></h3></div></div>
    <div class="col-6 col-md-3"><div class="card text-center p-3"><small class="text-muted">Pria : Wanita</small><h3><?= $stats['pria'] ?> : <?= $stats['wanita'] ?></h3></div></div>
</div>

<!-- Charts -->
<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="card"><div class="card-body"><h6>Status Kepegawaian</h6><canvas id="statusChart" height="200"></canvas></div></div></div>
    <div class="col-md-6"><div class="card"><div class="card-body"><h6>Jenis Kelamin</h6><canvas id="genderChart" height="200"></canvas></div></div></div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Data Pegawai (<?= count($pegawai) ?> data)</h6>
        <input type="text" class="form-control form-control-sm" style="max-width:250px" id="searchInput" placeholder="Cari...">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0" id="dataTable">
                <thead><tr>
                    <th>No</th><th>Nama</th><th>NIP</th><th>Jabatan</th><th>Status</th><th>Jenis Kelamin</th><th>Tanggal Masuk</th>
                </tr></thead>
                <tbody>
                    <?php $no = 1; foreach ($pegawai as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td><td><?= e($row['nama_lengkap']) ?></td><td><?= e($row['nip']) ?></td>
                        <td><?= e($row['jabatan']) ?></td>
                        <td><span class="badge bg-<?= match($row['status_kepegawaian']){'PNS'=>'success','Honorer'=>'warning','CPNS'=>'info',default=>'secondary'} ?>"><?= e($row['status_kepegawaian']) ?></span></td>
                        <td><?= e($row['jenis_kelamin']) ?></td><td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pegawai)): ?><tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$extraJS = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById("statusChart"), {
    type:"doughnut",
    data:{labels:["PNS","Honorer","CPNS","Kontrak"],datasets:[{data:['.$stats['pns'].','.$stats['honorer'].','.$stats['cpns'].','.$stats['kontrak'].'],backgroundColor:["#28a745","#ffc107","#17a2b8","#6c757d"],borderWidth:1}]},
    options:{responsive:true,plugins:{legend:{position:"bottom"}}}
});
new Chart(document.getElementById("genderChart"), {
    type:"pie",
    data:{labels:["Pria","Wanita"],datasets:[{data:['.$stats['pria'].','.$stats['wanita'].'],backgroundColor:["#3b82f6","#ec4899"],borderWidth:1}]},
    options:{responsive:true,plugins:{legend:{position:"bottom"}}}
});
// Simple table search
document.getElementById("searchInput").addEventListener("input", function(){
    var v=this.value.toLowerCase();
    document.querySelectorAll("#dataTable tbody tr").forEach(function(r){
        r.style.display=r.textContent.toLowerCase().includes(v)?"":"none";
    });
});
</script>';
require __DIR__ . '/includes/layout_footer.php';
?>
