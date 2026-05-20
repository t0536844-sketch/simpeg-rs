<?php
/** Debug endpoint — hapus file ini setelah selesai */
require_once 'config.php';

header('Content-Type: text/plain');

try {
    $db = (new Database())->getConnection();

    // Check users table
    echo "=== USERS TABLE ===\n";
    $stmt = $db->query("SELECT id, username, role, password FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $u) {
        $pw = $u['password'] ?? 'NULL';
        $pwPreview = substr($pw, 0, 20);
        echo "  id={$u['id']} user={$u['username']} role={$u['role']} pw_prefix={$pwPreview}\n";
        $verify = password_verify('admin123', $pw);
        echo "  verify admin123: " . ($verify ? 'YES' : 'NO') . "\n";
    }
    echo "Total users: " . count($users) . "\n\n";

    // Check tables
    echo "=== TABLES ===\n";
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $t) {
        echo "  $t\n";
    }

    // Check pegawai count
    echo "\n=== PEGAWAI ===\n";
    $count = $db->query("SELECT COUNT(*) FROM pegawai")->fetchColumn();
    echo "Total pegawai: $count\n";

    // Check login attempts
    echo "\n=== LOGIN ATTEMPTS ===\n";
    $rate_file = __DIR__ . '/.login_attempts';
    if (file_exists($rate_file)) {
        echo file_get_contents($rate_file) . "\n";
    } else {
        echo "No login attempts file\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
