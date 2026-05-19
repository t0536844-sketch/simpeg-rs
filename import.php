<?php
// import.php — Import data pegawai dari CSV
require_once 'config.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    verify_csrf();

    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        setFlash('error', 'Error upload file: ' . $file['error']);
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv'])) {
            setFlash('error', 'File harus berformat CSV.');
        } else {
            $tmpPath = $file['tmp_name'];
            $count = 0;
            $errors = [];

            $handle = fopen($tmpPath, 'r');
            if ($handle === false) {
                setFlash('error', 'Gagal membaca file CSV.');
            } else {
                $headers = fgetcsv($handle);

                // Map header columns to database fields
                $columnMap = [];
                $knownColumns = [
                    'nama_lengkap' => 'nama_lengkap', 'nama' => 'nama_lengkap',
                    'nip' => 'nip', 'jabatan' => 'jabatan',
                    'status_kepegawaian' => 'status_kepegawaian', 'status' => 'status_kepegawaian',
                    'jenis_kelamin' => 'jenis_kelamin', 'jk' => 'jenis_kelamin',
                    'agama' => 'agama', 'pangkat_golongan' => 'pangkat_golongan',
                    'pangkat' => 'pangkat_golongan', 'pendidikan' => 'pendidikan',
                    'tempat_lahir' => 'tempat_lahir', 'tanggal_lahir' => 'tanggal_lahir',
                    'alamat' => 'alamat_rumah', 'alamat_rumah' => 'alamat_rumah',
                    'jumlah_keluarga' => 'jumlah_keluarga',
                ];

                foreach ($headers as $i => $header) {
                    $headerLower = strtolower(trim($header));
                    foreach ($knownColumns as $keyword => $dbField) {
                        if (stripos($headerLower, $keyword) !== false) {
                            $columnMap[$i] = $dbField;
                            break;
                        }
                    }
                }

                if (empty($columnMap)) {
                    setFlash('error', 'Tidak ada kolom yang dikenali. Pastikan CSV memiliki header yang sesuai.');
                } else {
                    $query = "INSERT INTO pegawai (nama_lengkap, nip, jabatan, status_kepegawaian, jenis_kelamin, agama, pangkat_golongan, pendidikan, tempat_lahir, tanggal_lahir, alamat_rumah, jumlah_keluarga)
                              VALUES (:nama_lengkap, :nip, :jabatan, :status_kepegawaian, :jenis_kelamin, :agama, :pangkat_golongan, :pendidikan, :tempat_lahir, :tanggal_lahir, :alamat_rumah, :jumlah_keluarga)
                              ON DUPLICATE KEY UPDATE nama_lengkap=VALUES(nama_lengkap)";

                    $stmt = $db->prepare($query);

                    while (($data = fgetcsv($handle)) !== false) {
                        $row = [];
                        foreach ($columnMap as $colIdx => $dbField) {
                            $row[$dbField] = trim($data[$colIdx] ?? '');
                        }

                        // Skip empty rows
                        if (empty($row['nama_lengkap'])) continue;

                        try {
                            $stmt->execute([
                                ':nama_lengkap' => $row['nama_lengkap'] ?? '',
                                ':nip' => $row['nip'] ?? '',
                                ':jabatan' => $row['jabatan'] ?? '',
                                ':status_kepegawaian' => $row['status_kepegawaian'] ?? 'PNS',
                                ':jenis_kelamin' => $row['jenis_kelamin'] ?? 'Pria',
                                ':agama' => $row['agama'] ?? '',
                                ':pangkat_golongan' => $row['pangkat_golongan'] ?? '',
                                ':pendidikan' => $row['pendidikan'] ?? '',
                                ':tempat_lahir' => $row['tempat_lahir'] ?? '',
                                ':tanggal_lahir' => sanitizeDate($row['tanggal_lahir'] ?? ''),
                                ':alamat_rumah' => $row['alamat_rumah'] ?? '',
                                ':jumlah_keluarga' => sanitizeInt($row['jumlah_keluarga'] ?? 0),
                            ]);
                            $count++;
                        } catch (PDOException $e) {
                            $errors[] = 'Baris ' . ($count + 2) . ': ' . $e->getMessage();
                        }
                    }
                    fclose($handle);

                    if ($count > 0) {
                        logActivity($db, $_SESSION['user_id'], 'IMPORT', 'pegawai', null, "Import $count data dari CSV");
                        $msg = "Berhasil mengimport $count data.";
                        if (!empty($errors)) {
                            $msg .= ' ' . count($errors) . ' baris gagal.';
                        }
                        setFlash('success', $msg);
                    } else {
                        setFlash('error', 'Tidak ada data yang berhasil diimport.');
                    }
                }
            }
        }
    }
    header('Location: import.php');
    exit;
}

$pageTitle = 'Import Data';
$activePage = 'import';
$breadcrumb = [['label' => 'Data Pegawai', 'url' => 'pegawai.php'], ['label' => 'Import', 'active' => true]];
require __DIR__ . '/includes/layout.php';
?>

<div class="card">
    <div class="card-header bg-white"><h5 class="mb-0"><i class="bi bi-upload me-2"></i> Import Data Pegawai</h5></div>
    <div class="card-body">
        <div class="alert alert-info">
            <h6><i class="bi bi-info-circle me-1"></i> Petunjuk:</h6>
            <ul class="mb-0">
                <li>File harus berformat <strong>CSV</strong> dengan header baris pertama</li>
                <li>Kolom yang dikenal: <code>nama_lengkap</code>, <code>nip</code>, <code>jabatan</code>, <code>status_kepegawaian</code>, <code>jenis_kelamin</code>, <code>agama</code>, <code>pangkat_golongan</code>, <code>pendidikan</code>, <code>tempat_lahir</code>, <code>tanggal_lahir</code>, <code>alamat</code></li>
                <li>Baris dengan NIP duplikat akan di-update</li>
            </ul>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="file" class="form-label">Pilih File CSV</label>
                <input type="file" class="form-control" id="file" name="file" accept=".csv" required>
                <div class="form-text">
                    <a href="template_import.csv" download><i class="bi bi-download me-1"></i>Download template CSV</a>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i> Import</button>
                <a href="pegawai.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
