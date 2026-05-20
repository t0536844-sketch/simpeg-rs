<?php
require_once 'config.php';
header('Content-Type: text/plain');

// Test: set session variable and redirect
session_start();
$_SESSION['test_login'] = 'working';
$_SESSION['test_time'] = time();

echo "Session ID: " . session_id() . "\n";
echo "Session save path: " . session_save_path() . "\n";
echo "Session dir writable: " . (is_writable(session_save_path()) ? 'YES' : 'NO') . "\n";
echo "Session file: " . session_save_path() . '/sess_' . session_id() . "\n";

// Write session
session_write_close();

// Check if file exists
echo "\nSession file exists: " . (file_exists(session_save_path() . '/sess_' . session_id()) ? 'YES' : 'NO') . "\n";

// Check disk space
echo "\nDisk space:\n";
echo shell_exec('df -h /tmp 2>/dev/null || echo "df failed"');

// Check current session vars
echo "\nSession vars set\n";
echo "Redirect to /debug_session2.php in 2 seconds...\n";
