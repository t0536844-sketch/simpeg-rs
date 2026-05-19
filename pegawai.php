<?php
// pegawai.php — Daftar Pegawai dengan pencarian & filter
require_once 'config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$search = sanitize($_GET['search'] ?? '');
$statusFilter = sanitize($_GET['status'] ?? '');

$query = "SELECT * FROM pegawai WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (nama_lengkap LIKE ? OR nip LIKE ? OR jabatan LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}
if ($statusFilter) {
    $query .= " AND status_kepegawaian = ?";
    $params[] = $statusFilter;
}
$query .= " ORDER BY nama_lengkap ASC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$pegawai = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Data Pegawai';
$activePage = 'pegawai';
$breadcrumb = [['label' => 'Data Pegawai', 'active' => true]];
$headerActions = '<a href="tambah_pegawai.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Tambah Pegawai</a>';
require __DIR__ . '/includes/layout.php';
?>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <input type="text" class="form-control" name="search"
                       placeholder="Cari nama, NIP, atau jabatan..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-4">
                <select class="form-select" name="status">
                    <option value="">Semua Status</option>
                    <option value="PNS" <?= $statusFilter === 'PNS' ? 'selected' : '' ?>>PNS</option>
                    <option value="Honorer" <?= $statusFilter === 'Honorer' ? 'selected' : '' ?>>Honorer</option>
                    <option value="CPNS" <?= $statusFilter === 'CPNS' ? 'selected' : '' ?>>CPNS</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="pegawaiTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th><th>Nama Lengkap</th><th>NIP</th><th>Jabatan</th>
                        <th>Pangkat/Gol</th><th>Status</th><th>Jenis Kelamin</th><th class="no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($pegawai as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= e($row['nama_lengkap']) ?></td>
                        <td><?= e($row['nip']) ?></td>
                        <td><?= e($row['jabatan']) ?></td>
                        <td><?= e($row['pangkat_golongan']) ?></td>
                        <td>
                            <span class="badge bg-<?php
                                echo match($row['status_kepegawaian']) {
                                    'PNS' => 'success', 'Honorer' => 'warning', 'CPNS' => 'info', default => 'secondary'
                                };
                            ?>"><?= e($row['status_kepegawaian']) ?></span>
                        </td>
                        <td><?= e($row['jenis_kelamin']) ?></td>
                        <td class="no-print">
                            <div class="btn-group btn-group-sm">
                                <a href="detail_pegawai.php?id=<?= $row['id'] ?>" class="btn btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                <a href="edit_pegawai.php?id=<?= $row['id'] ?>" class="btn btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                <?php if (isAdmin()): ?>
                                <a href="hapus_pegawai.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger" title="Hapus"
                                   onclick="return confirm('Hapus data <?= e($row['nama_lengkap']) ?>?')"><i class="bi bi-trash"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pegawai)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$extraCSS = '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">';
$extraJS = '<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $("#pegawaiTable").DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json" },
        pageLength: 25,
        columnDefs: [{ targets: -1, orderable: false }]
    });
});
</script>';
require __DIR__ . '/includes/layout_footer.php';
?>
