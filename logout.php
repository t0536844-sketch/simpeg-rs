<?php
// logout.php
require_once 'config.php';

if (isLoggedIn()) {
    $database = new Database();
    $db = $database->getConnection();
    logActivity($db, $_SESSION['user_id'], 'LOGOUT', null, null, 'User logout');
}

// Hapus remember token dari DB
if (isset($_SESSION['user_id'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $stmt = $db->prepare("UPDATE users SET remember_token = NULL, remember_token_expires = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (PDOException $e) { /* ignore */ }
}

// Hapus cookie remember me
setcookie('remember_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);

// Destroy session
$_SESSION = [];
session_destroy();

header("Location: login.php?logout=1");
exit;
