<?php
/**
 * serve_file.php — File download gateway dengan autentikasi
 *
 * Melayani file dokumen pegawai HANYA untuk user yang sudah login.
 * Akses langsung ke folder uploads/ akan diblokir oleh .htaccess.
 *
 * Usage: serve_file.php?file=uploads/NAME_ID/STR/sk_123.pdf
 */
require_once 'config.php';
requireLogin();

$filePath = $_GET['file'] ?? '';

// Validasi: hanya file di dalam uploads/
$realBase = realpath(UPLOAD_DIR);
$realPath = realpath($filePath);

if ($realPath === false || strpos($realPath, $realBase) !== 0) {
    http_response_code(403);
    die('Akses ditolak.');
}

if (!is_file($realPath)) {
    http_response_code(404);
    die('File tidak ditemukan.');
}

// Log file access
$database = new Database();
$db = $database->getConnection();
logActivity($db, $_SESSION['user_id'], 'VIEW', 'uploads', null, 'Mengakses dokumen: ' . basename($realPath));

// Determine MIME type
$ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
$mimeTypes = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];
$mime = $mimeTypes[$ext] ?? 'application/octet-stream';

// Force download (bukan inline display)
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . basename($realPath) . '"');
header('Content-Length: ' . filesize($realPath));
header('Cache-Control: private, no-cache');
readfile($realPath);
exit;
