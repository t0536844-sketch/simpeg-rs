<?php
// users.php — Manajemen Pengguna (Admin only)
require_once 'config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

// Add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $role = $_POST['role'] ?? 'operator';

    if (empty($username) || empty($password) || empty($nama)) {
        setFlash('error', 'Semua field wajib diisi!');
    } elseif (strlen($password) < 6) {
        setFlash('error', 'Password minimal 6 karakter!');
    } else {
        $check = $db->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->rowCount() > 0) {
            setFlash('error', 'Username sudah digunakan!');
        } else {
            $stmt = $db->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, hashPassword($password), $nama, $role]);
            $newId = (int) $db->lastInsertId();
            logActivity($db, $_SESSION['user_id'], 'CREATE', 'users', $newId, 'Menambah user: ' . $username);
            setFlash('success', 'User berhasil ditambahkan!');
        }
    }
    header('Location: users.php'); exit;
}

// Update user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    verify_csrf();
    $id = sanitizeInt($_POST['id'], 1);
    $username = trim($_POST['username'] ?? '');
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $role = $_POST['role'] ?? 'operator';
    $changePwd = isset($_POST['change_password']);
    $newPwd = $_POST['new_password'] ?? '';

    if (empty($username) || empty($nama)) { setFlash('error', 'Username dan Nama wajib diisi!'); }
    else {
        $check = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $check->execute([$username, $id]);
        if ($check->rowCount() > 0) { setFlash('error', 'Username sudah digunakan!'); }
        else {
            if ($changePwd && !empty($newPwd)) {
                if (strlen($newPwd) < 6) { setFlash('error', 'Password minimal 6 karakter!'); }
                else {
                    $db->prepare("UPDATE users SET username=?, password=?, nama_lengkap=?, role=? WHERE id=?")
                       ->execute([$username, hashPassword($newPwd), $nama, $role, $id]);
                }
            } else {
                $db->prepare("UPDATE users SET username=?, nama_lengkap=?, role=? WHERE id=?")
                   ->execute([$username, $nama, $role, $id]);
            }
            if (!isset($_SESSION['error'])) {
                logActivity($db, $_SESSION['user_id'], 'UPDATE', 'users', $id, 'Update user: ' . $username);
                setFlash('success', 'User berhasil diperbarui!');
            }
        }
    }
    header('Location: users.php'); exit;
}

// Delete user
if (isset($_GET['delete'])) {
    $id = sanitizeInt($_GET['delete'], 1);
    if ($id == $_SESSION['user_id']) { setFlash('error', 'Tidak bisa hapus akun sendiri!'); }
    else {
        $getUser = $db->prepare("SELECT username FROM users WHERE id = ?");
        $getUser->execute([$id]);
        $u = $getUser->fetch(PDO::FETCH_ASSOC);
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        logActivity($db, $_SESSION['user_id'], 'DELETE', 'users', $id, 'Hapus user: ' . ($u['username'] ?? ''));
        setFlash('success', 'User berhasil dihapus!');
    }
    header('Location: users.php'); exit;
}

// Reset password
if (isset($_GET['reset'])) {
    $id = sanitizeInt($_GET['reset'], 1);
    $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([hashPassword('user123'), $id]);
    logActivity($db, $_SESSION['user_id'], 'UPDATE', 'users', $id, 'Reset password user ID: ' . $id);
    setFlash('success', 'Password direset ke "user123"!');
    header('Location: users.php'); exit;
}

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$statsStmt = $db->query("SELECT COUNT(*) as total,
    SUM(CASE WHEN role='admin' THEN 1 ELSE 0 END) as admin,
    SUM(CASE WHEN role='operator' THEN 1 ELSE 0 END) as operator,
    SUM(CASE WHEN role='viewer' THEN 1 ELSE 0 END) as viewer FROM users");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'Manajemen User';
$activePage = 'users';
$breadcrumb = [['label' => 'Manajemen User', 'active' => true]];
$extraCSS = '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">';
require __DIR__ . '/includes/layout.php';
?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card text-center p-3 border-start border-4 border-primary"><small class="text-muted">Total</small><h4><?= $stats['total'] ?></h4></div></div>
    <div class="col-6 col-md-3"><div class="card text-center p-3 border-start border-4 border-danger"><small class="text-muted">Admin</small><h4 class="text-danger"><?= $stats['admin'] ?></h4></div></div>
    <div class="col-6 col-md-3"><div class="card text-center p-3 border-start border-4 border-warning"><small class="text-muted">Operator</small><h4 class="text-warning"><?= $stats['operator'] ?></h4></div></div>
    <div class="col-6 col-md-3"><div class="card text-center p-3 border-start border-4 border-success"><small class="text-muted">Viewer</small><h4 class="text-success"><?= $stats['viewer'] ?></h4></div></div>
</div>

<!-- Add button -->
<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-person-plus me-1"></i> Tambah User</button>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="usersTable" class="table table-hover mb-0">
                <thead><tr><th>No</th><th>Username</th><th>Nama</th><th>Role</th><th>Dibuat</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php $no = 1; foreach ($users as $u): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= e($u['username']) ?></strong><?php if ($u['id']==$_SESSION['user_id']): ?> <span class="badge bg-info">Anda</span><?php endif; ?></td>
                        <td><?= e($u['nama_lengkap']) ?></td>
                        <td><span class="badge bg-<?= $u['role']==='admin'?'danger':($u['role']==='operator'?'warning':'success') ?>"><?= strtoupper(e($u['role'])) ?></span></td>
                        <td><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-warning btn-edit" data-bs-toggle="modal" data-bs-target="#editUserModal"
                                    data-id="<?= $u['id'] ?>" data-username="<?= e($u['username']) ?>" data-nama="<?= e($u['nama_lengkap']) ?>" data-role="<?= e($u['role']) ?>"><i class="bi bi-pencil"></i></button>
                                <a href="?reset=<?= $u['id'] ?>" class="btn btn-outline-info" onclick="return confirm('Reset password <?= e($u['username']) ?> ke user123?')"><i class="bi bi-key"></i></a>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="?delete=<?= $u['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Hapus user <?= e($u['username']) ?>?')"><i class="bi bi-trash"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Role Info -->
<div class="card mt-4">
    <div class="card-body">
        <h6 class="mb-3">Hak Akses Role</h6>
        <div class="row g-3">
            <div class="col-md-4"><div class="border rounded p-3"><strong class="text-danger">ADMIN</strong><ul class="mb-0 small"><li>Semua akses + manajemen user</li></ul></div></div>
            <div class="col-md-4"><div class="border rounded p-3"><strong class="text-warning">OPERATOR</strong><ul class="mb-0 small"><li>CRUD pegawai, import/export, laporan</li></ul></div></div>
            <div class="col-md-4"><div class="border rounded p-3"><strong class="text-success">VIEWER</strong><ul class="mb-0 small"><li>Hanya lihat data & laporan</li></ul></div></div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST"><input type="hidden" name="action" value="add"><?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title">Tambah User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Username</label><input type="text" class="form-control" name="username" required></div>
                    <div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control" name="password" required minlength="6"></div>
                    <div class="mb-3"><label class="form-label">Nama Lengkap</label><input type="text" class="form-control" name="nama_lengkap" required></div>
                    <div class="mb-3"><label class="form-label">Role</label>
                        <select class="form-select" name="role"><option value="admin">Admin</option><option value="operator" selected>Operator</option><option value="viewer">Viewer</option></select>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST"><input type="hidden" name="action" value="update"><?= csrf_field() ?>
                <input type="hidden" name="id" id="editUserId">
                <div class="modal-header"><h5 class="modal-title">Edit User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Username</label><input type="text" class="form-control" name="username" id="editUsername" required></div>
                    <div class="mb-3"><label class="form-label">Nama Lengkap</label><input type="text" class="form-control" name="nama_lengkap" id="editNama" required></div>
                    <div class="mb-3"><label class="form-label">Role</label>
                        <select class="form-select" name="role" id="editRole"><option value="admin">Admin</option><option value="operator">Operator</option><option value="viewer">Viewer</option></select>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="changePwd" name="change_password" onchange="document.getElementById('newPwdGroup').style.display=this.checked?'block':'none'">
                        <label class="form-check-label" for="changePwd">Ganti Password</label>
                    </div>
                    <div class="mb-3" id="newPwdGroup" style="display:none"><label class="form-label">Password Baru</label><input type="password" class="form-control" name="new_password" minlength="6"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-warning">Update</button></div>
            </form>
        </div>
    </div>
</div>

<?php
$extraJS = '<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function(){$("#usersTable").DataTable({language:{url:"//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"},columnDefs:[{targets:-1,orderable:false}]});});
$("#editUserModal").on("show.bs.modal",function(e){
    var b=$(e.relatedTarget);
    $("#editUserId").val(b.data("id"));
    $("#editUsername").val(b.data("username"));
    $("#editNama").val(b.data("nama"));
    $("#editRole").val(b.data("role"));
});
</script>';
require __DIR__ . '/includes/layout_footer.php';
?>
