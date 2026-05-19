<?php
// logs.php — Audit Logs (Admin only)
require_once 'config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$start_date = sanitizeDate($_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'))) ?: date('Y-m-d', strtotime('-7 days'));
$end_date = sanitizeDate($_GET['end_date'] ?? date('Y-m-d')) ?: date('Y-m-d');
$userFilter = sanitizeInt($_GET['user_id'] ?? 0);
$actionFilter = sanitize($_GET['action'] ?? '');

$query = "SELECT l.*, u.username, u.nama_lengkap FROM logs l LEFT JOIN users u ON l.user_id = u.id WHERE DATE(l.created_at) BETWEEN ? AND ?";
$params = [$start_date, $end_date];
if ($userFilter) { $query .= " AND l.user_id = ?"; $params[] = $userFilter; }
if ($actionFilter) { $query .= " AND l.action = ?"; $params[] = $actionFilter; }
$query .= " ORDER BY l.created_at DESC LIMIT 500";

$stmt = $db->prepare($query); $stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$users = $db->query("SELECT id, username, nama_lengkap FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
$actions = $db->query("SELECT DISTINCT action FROM logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN, 0);

$pageTitle = 'Audit Logs';
$activePage = 'logs';
$breadcrumb = [['label' => 'Audit Logs', 'active' => true]];
require __DIR__ . '/includes/layout.php';
?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3"><label class="form-label">Tanggal Mulai</label><input type="date" class="form-control" name="start_date" value="<?= e($start_date) ?>"></div>
            <div class="col-md-3"><label class="form-label">Tanggal Akhir</label><input type="date" class="form-control" name="end_date" value="<?= e($end_date) ?>"></div>
            <div class="col-md-3">
                <label class="form-label">User</label>
                <select class="form-select" name="user_id">
                    <option value="">Semua</option>
                    <?php foreach($users as $u): ?><option value="<?= $u['id'] ?>" <?= $userFilter==$u['id']?'selected':'' ?>><?= e($u['username'].' - '.$u['nama_lengkap']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Aksi</label>
                <select class="form-select" name="action">
                    <option value="">Semua</option>
                    <?php foreach($actions as $a): ?><option value="<?= e($a) ?>" <?= $actionFilter===$a?'selected':'' ?>><?= e($a) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary"><i class="bi bi-filter me-1"></i> Filter</button>
                <a href="logs.php" class="btn btn-secondary">Reset</a>
                <button type="button" onclick="window.print()" class="btn btn-success"><i class="bi bi-printer me-1"></i> Cetak</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Activity Logs (<?= count($logs) ?> records)</h6>
        <small class="text-muted"><?= date('d M Y', strtotime($start_date)) ?> — <?= date('d M Y', strtotime($end_date)) ?></small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Tabel</th><th>ID</th><th>Deskripsi</th><th>IP</th></tr></thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                        <td><?= $log['user_id'] ? e($log['username'].' ('.$log['nama_lengkap'].')') : '<span class="text-muted">System</span>' ?></td>
                        <td><span class="badge bg-<?= match($log['action']){'CREATE'=>'success','UPDATE'=>'warning','DELETE'=>'danger','LOGIN'=>'info','LOGOUT'=>'secondary',default=>'primary'} ?>"><?= e($log['action']) ?></span></td>
                        <td><?= e($log['table_name'] ?? '-') ?></td>
                        <td><?= $log['record_id'] ?? '-' ?></td>
                        <td><?= e($log['description']) ?></td>
                        <td><small><?= e($log['ip_address']) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($logs)): ?><tr><td colspan="7" class="text-center text-muted py-4">Tidak ada log ditemukan.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
