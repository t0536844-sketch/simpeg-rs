<?php
/** Quick health/debug endpoint — hapus setelah selesai */
require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== SIMPEG-RS HEALTH CHECK ===\n\n";

// 1. Check data directory
$dataDir = __DIR__ . '/data';
echo "[1] data dir exists: " . (is_dir($dataDir) ? 'YES' : 'NO') . "\n";
echo "    writable: " . (is_writable($dataDir) ? 'YES' : 'NO') . "\n";

// 2. Check sessions directory
$sessDir = __DIR__ . '/sessions';
echo "[2] sessions dir exists: " . (is_dir($sessDir) ? 'YES' : 'NO') . "\n";
echo "    writable: " . (is_writable($sessDir) ? 'YES' : 'NO') . "\n";

// 3. Check DB file
$dbPath = __DIR__ . '/data/rsud_mimika.db';
echo "[3] DB file exists: " . (file_exists($dbPath) ? 'YES' : 'NO') . "\n";

// 4. Try to connect
echo "\n=== DB CONNECTION ===\n";
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "Connected: YES\n";

    // 5. List tables
    echo "\n=== TABLES ===\n";
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        $count = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "  $t ($count rows)\n";
    }

    // 6. Check users
    echo "\n=== USERS ===\n";
    $stmt = $db->query("SELECT id, username, role FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($users)) {
        echo "  (NO USERS FOUND)\n";
    }
    foreach ($users as $u) {
        echo "  id={$u['id']} user={$u['username']} role={$u['role']}\n";
    }

    // 7. Check admin password hash
    echo "\n=== ADMIN PASSWORD ===\n";
    $stmt = $db->query("SELECT password FROM users WHERE username = 'admin'");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        $pw = $admin['password'];
        echo "  hash_prefix: " . substr($pw, 0, 15) . "...\n";
        echo "  verify admin123: " . (password_verify('admin123', $pw) ? 'YES' : 'NO') . "\n";
    } else {
        echo "  (admin user NOT FOUND)\n";
        echo "  Trying to insert admin...\n";
        $hash = '$2y$12$UCLvTpnKl1Y3nPu4v.zQYuKoppkmUEjwaPYtlE/JVVk.i.3BZtCAe';
        $db->exec("INSERT OR IGNORE INTO users (username, password, nama_lengkap, role) VALUES ('admin', '$hash', 'Administrator', 'admin')");
        $stmt = $db->query("SELECT username FROM users WHERE username = 'admin'");
        $check = $stmt->fetchColumn();
        echo "  Inserted: " . ($check ? 'YES' : 'NO') . "\n";
    }

    // 8. Check pegawai
    echo "\n=== PEGAWAI ===\n";
    $count = $db->query("SELECT COUNT(*) FROM pegawai")->fetchColumn();
    echo "  Total: $count\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== ENV ===\n";
echo "USE_SQLITE: " . (getenv('USE_SQLITE') ?: 'not set') . "\n";
echo "PHP version: " . PHP_VERSION . "\n";

echo "\n=== DONE ===\n";
