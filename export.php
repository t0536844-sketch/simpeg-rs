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

$columns = [
    'nama_lengkap','tempat_lahir','tanggal_lahir','agama','jenis_kelamin','nip',
    'pangkat_golongan','pendidikan','status_pernikahan','jabatan','status_kepegawaian',
    'link_sk','jumlah_keluarga','alamat_rumah','link_ktp','link_kartu_keluarga',
    'link_ijazah','link_str','masa_berlaku_str','link_sip','masa_berlaku_sip','nomor_kartu_pegawai',
    'link_npwp','link_foto','link_akta_lahir','link_akta_nikah','link_skp','link_sk_kenaikan_pangkat',
    'link_sk_jabatan','link_sk_mutasi','link_sk_pensiun','link_sertifikat'
];
$fputcsv($output, $columns);
foreach ($data as $row) {
    $rowData = [];
    foreach ($columns as $col) {
        $rowData[] = $row[$col] ?? '';
    }
    fputcsv($output, $rowData);
}

fclose($output);
exit;
