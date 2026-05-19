<?php
// export.php — Export data pegawai ke CSV
require_once 'config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

// Optional filter
$status = sanitize($_GET['status'] ?? '');
$query = "SELECT * FROM pegawai";
$params = [];
if ($status) {
    $query .= " WHERE status_kepegawaian = ?";
    $params[] = $status;
}
$query .= " ORDER BY nama_lengkap ASC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($data)) {
    setFlash('error', 'Tidak ada data untuk di-export.');
    header('Location: pegawai.php');
    exit;
}

// Headers
$filename = 'Data_Pegawai_RSUD_Mimika_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
// BOM for UTF-8 (Excel compatible)
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

$headers = array_keys($data[0]);
fputcsv($output, $headers);

foreach ($data as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;
