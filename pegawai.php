<?php
// pegawai.php — Daftar Pegawai dengan pencarian & filter
require_once 'config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$search = sanitize($_GET['search'] ?? '');
$statusFilter = sanitize($_GET['status'] ?? '');
$page = max(1, sanitizeInt($_GET['page'] ?? 1, 1));
$perPage = 25;
$offset = ($page - 1) * $perPage;

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

// Count total for pagination
$countQuery = "SELECT COUNT(*) as c FROM pegawai WHERE 1=1";
$countParams = [];
if ($search) {
    $countQuery .= " AND (nama_lengkap LIKE ? OR nip LIKE ? OR jabatan LIKE ?)";
    $countParams = ["%$search%", "%$search%", "%$search%"];
}
if ($statusFilter) {
    $countQuery .= " AND status_kepegawaian = ?";
    $countParams[] = $statusFilter;
}
$totalRecords = $db->query($countQuery, $countParams)->fetch(PDO::FETCH_ASSOC)['c'];
$totalPages = ceil($totalRecords / $perPage);

$query .= " ORDER BY nama_lengkap ASC LIMIT $perPage OFFSET $offset";

$stmt = $db->prepare($query);
$stmt->execute($params);
$pegawai = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Data Pegawai';
$activePage = 'pegawai';
$breadcrumb = [['label' => 'Data Pegawai', 'active' => true]];
$headerActions = '<a href="tambah_pegawai.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Tambah Pegawai</a>';
$extraCSS = '';
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
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th><th>Nama Lengkap</th><th>NIP</th><th>Jabatan</th>
                        <th>Pangkat/Gol</th><th>Status</th><th>Jenis Kelamin</th><th class="no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = $offset + 1; foreach ($pegawai as $row): ?>
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

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="card-footer bg-white">
        <nav aria-label="Pagination">
            <ul class="pagination justify-content-center mb-0">
                <?php
                $queryParams = $_GET;
                $pageFn = function($p) use ($queryParams) {
                    $queryParams['page'] = $p;
                    return '?' . http_build_query($queryParams);
                };
                ?>
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $pageFn($page - 1) ?>">&laquo; Prev</a>
                </li>
                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                if ($start > 1): ?>
                <li class="page-item"><a class="page-link" href="<?= $pageFn(1) ?>">1</a></li>
                <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                <?php endif; ?>
                <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $pageFn($i) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                <li class="page-item"><a class="page-link" href="<?= $pageFn($totalPages) ?>"><?= $totalPages ?></a></li>
                <?php endif; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $pageFn($page + 1) ?>">Next &raquo;</a>
                </li>
            </ul>
        </nav>
        <div class="text-center mt-2">
            <small class="text-muted">Menampilkan <?= $offset + 1 ?>–<?= min($offset + $perPage, $totalRecords) ?> dari <?= $totalRecords ?> data</small>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
require __DIR__ . '/includes/layout_footer.php';
?>
