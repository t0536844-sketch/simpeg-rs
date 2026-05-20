<?php
// tambah_pegawai.php — Tambah data pegawai baru + upload dokumen
require_once 'config.php';
requireOperator();

$database = new Database();
$db = $database->getConnection();

$field_folders = [
    'link_sk' => 'SK', 'link_ktp' => 'KTP', 'link_kartu_keluarga' => 'KARTU_KELUARGA',
    'link_ijazah' => 'IJAZAH', 'link_str' => 'STR', 'link_sip' => 'SIP',
    'link_npwp' => 'NPWP', 'link_foto' => 'FOTO', 'link_akta_lahir' => 'AKTA_LAHIR',
    'link_akta_nikah' => 'AKTA_NIKAH', 'link_skp' => 'SKP',
    'link_sk_kenaikan_pangkat' => 'KENAIKAN_PANGKAT', 'link_sk_jabatan' => 'SK_JABATAN',
    'link_sk_mutasi' => 'SK_MUTASI', 'link_sk_pensiun' => 'SK_PENSIUN',
    'link_sertifikat' => 'SERTIFIKAT',
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        $nama = sanitize($_POST['nama_lengkap'] ?? '');
        if (empty($nama)) throw new Exception('Nama lengkap wajib diisi.');

        $namaFolder = preg_replace('/[^a-zA-Z0-9\s]/', '', $nama);
        $namaFolder = str_replace(' ', '_', strtoupper($namaFolder));

        $uploadedFiles = [];

        foreach ($field_folders as $field => $folderName) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $typeDir = UPLOAD_DIR . $namaFolder . '/' . $folderName . '/';
                $result = processUpload($_FILES[$field], $typeDir);
                if (!$result['success']) throw new Exception($result['error']);
                $uploadedFiles[$field] = $result['path'];
            } else {
                $uploadedFiles[$field] = '';
            }
        }

        $query = "INSERT INTO pegawai (
            nama_lengkap, tempat_lahir, tanggal_lahir, agama, jenis_kelamin, nip,
            pangkat_golongan, pendidikan, status_pernikahan, jabatan, status_kepegawaian,
            link_sk, jumlah_keluarga, alamat_rumah, link_ktp, link_kartu_keluarga,
            link_ijazah, link_str, masa_berlaku_str, link_sip, masa_berlaku_sip,
            nomor_kartu_pegawai, link_npwp, link_foto, link_akta_lahir, link_akta_nikah,
            link_skp, link_sk_kenaikan_pangkat, link_sk_jabatan, link_sk_mutasi,
            link_sk_pensiun, link_sertifikat
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = $db->prepare($query);
        $stmt->execute([
            $nama,
            sanitize($_POST['tempat_lahir'] ?? ''),
            sanitizeDate($_POST['tanggal_lahir'] ?? ''),
            sanitize($_POST['agama'] ?? ''),
            sanitize($_POST['jenis_kelamin'] ?? 'Pria'),
            sanitize($_POST['nip'] ?? ''),
            sanitize($_POST['pangkat_golongan'] ?? ''),
            sanitize($_POST['pendidikan'] ?? ''),
            sanitize($_POST['status_pernikahan'] ?? ''),
            sanitize($_POST['jabatan'] ?? ''),
            sanitize($_POST['status_kepegawaian'] ?? 'PNS'),
            $uploadedFiles['link_sk'],
            sanitizeInt($_POST['jumlah_keluarga'] ?? 0),
            sanitize($_POST['alamat_rumah'] ?? ''),
            $uploadedFiles['link_ktp'], $uploadedFiles['link_kartu_keluarga'],
            $uploadedFiles['link_ijazah'], $uploadedFiles['link_str'],
            sanitizeDate($_POST['masa_berlaku_str'] ?? ''),
            $uploadedFiles['link_sip'],
            sanitizeDate($_POST['masa_berlaku_sip'] ?? ''),
            sanitize($_POST['nomor_kartu_pegawai'] ?? ''),
            $uploadedFiles['link_npwp'], $uploadedFiles['link_foto'],
            $uploadedFiles['link_akta_lahir'], $uploadedFiles['link_akta_nikah'],
            $uploadedFiles['link_skp'], $uploadedFiles['link_sk_kenaikan_pangkat'],
            $uploadedFiles['link_sk_jabatan'], $uploadedFiles['link_sk_mutasi'],
            $uploadedFiles['link_sk_pensiun'], $uploadedFiles['link_sertifikat'],
        ]);

        $lastId = (int) $db->lastInsertId();

        // Rename folder with ID
        $oldDir = UPLOAD_DIR . $namaFolder . '/';
        $newDir = UPLOAD_DIR . $namaFolder . '_' . $lastId . '/';
        if (is_dir($oldDir)) {
            rename($oldDir, $newDir);
            foreach ($field_folders as $field => $folderName) {
                if (!empty($uploadedFiles[$field])) {
                    $newPath = str_replace($oldDir, $newDir, $uploadedFiles[$field]);
                    $db->prepare("UPDATE pegawai SET $field = ? WHERE id = ?")->execute([$newPath, $lastId]);
                }
            }
        }

        logActivity($db, $_SESSION['user_id'], 'CREATE', 'pegawai', $lastId, 'Menambah data pegawai: ' . $nama);
        setFlash('success', 'Data pegawai berhasil ditambahkan.');
        header('Location: pegawai.php');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
        // Cleanup on error — recursive delete
        $cleanupDirs = [];
        if (isset($newDir)) $cleanupDirs[] = $newDir;
        if (isset($oldDir)) $cleanupDirs[] = $oldDir;
        foreach ($cleanupDirs as $dir) {
            if (is_dir($dir)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($files as $file) {
                    $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
                }
                rmdir($dir);
            }
        }
    }
}

$pageTitle = 'Tambah Pegawai';
$activePage = 'tambah';
$breadcrumb = [['label' => 'Data Pegawai', 'url' => 'pegawai.php'], ['label' => 'Tambah', 'active' => true]];
$headerActions = '<a href="pegawai.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>';
require __DIR__ . '/includes/layout.php';
?>

<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <h6 class="mb-1"><i class="bi bi-folder me-1"></i>Struktur Penyimpanan File</h6>
        <small class="text-muted">File disimpan di: <code>uploads/NAMA_PEGAWAI_ID/DOKUMEN/</code> — Maks 10MB per file (PDF, JPG, PNG, DOC)</small>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- Data Pribadi -->
    <div class="card mb-3">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-person me-1"></i> Data Pribadi</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" class="form-control" name="nama_lengkap" required
                           oninput="this.value=this.value.toUpperCase()">
                </div>
                <div class="col-md-3"><label class="form-label">Tempat Lahir</label><input type="text" class="form-control" name="tempat_lahir"></div>
                <div class="col-md-3"><label class="form-label">Tanggal Lahir</label><input type="date" class="form-control" name="tanggal_lahir"></div>
                <div class="col-md-3">
                    <label class="form-label">Agama</label>
                    <select class="form-select" name="agama">
                        <option value="">Pilih</option>
                        <?php foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $a): ?>
                        <option value="<?= $a ?>"><?= $a ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select class="form-select" name="jenis_kelamin"><option>Pria</option><option>Wanita</option></select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status Pernikahan</label>
                    <select class="form-select" name="status_pernikahan">
                        <option>Menikah</option><option>Belum Menikah</option><option>Cerai</option>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Jumlah Keluarga</label><input type="number" class="form-control" name="jumlah_keluarga" min="0" value="0"></div>
            </div>
        </div>
    </div>

    <!-- Data Kepegawaian -->
    <div class="card mb-3">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-briefcase me-1"></i> Data Kepegawaian</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">NIP</label><input type="text" class="form-control" name="nip"></div>
                <div class="col-md-4">
                    <label class="form-label">Pangkat/Golongan</label>
                    <select class="form-select" name="pangkat_golongan">
                        <option value="">Pilih</option>
                        <?php foreach(['I/a','I/b','I/c','I/d','II/a','II/b','II/c','II/d','III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c','IV/d','IV/e'] as $p): ?>
                        <option><?= $p ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status Kepegawaian</label>
                    <select class="form-select" name="status_kepegawaian">
                        <option>PNS</option><option>CPNS</option><option>Honorer</option><option>Kontrak</option>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Pendidikan</label><input type="text" class="form-control" name="pendidikan"></div>
                <div class="col-md-6"><label class="form-label">Jabatan</label><input type="text" class="form-control" name="jabatan"></div>
                <div class="col-md-6"><label class="form-label">Nomor Kartu Pegawai</label><input type="text" class="form-control" name="nomor_kartu_pegawai"></div>
                <div class="col-md-6"><label class="form-label">Alamat</label><textarea class="form-control" name="alamat_rumah" rows="2"></textarea></div>
            </div>
        </div>
    </div>

    <!-- Dokumen -->
    <div class="card mb-3">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-file-earmark me-1"></i> Dokumen</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <?php
                $docLabels = [
                    'link_sk' => 'SK', 'link_ktp' => 'KTP', 'link_kartu_keluarga' => 'Kartu Keluarga',
                    'link_ijazah' => 'Ijazah', 'link_str' => 'STR', 'link_sip' => 'SIP',
                    'link_npwp' => 'NPWP', 'link_foto' => 'Pas Foto', 'link_akta_lahir' => 'Akta Lahir',
                    'link_akta_nikah' => 'Akta Nikah', 'link_skp' => 'SKP',
                    'link_sk_kenaikan_pangkat' => 'SK Kenaikan Pangkat', 'link_sk_jabatan' => 'SK Jabatan',
                    'link_sk_mutasi' => 'SK Mutasi', 'link_sk_pensiun' => 'SK Pensiun',
                    'link_sertifikat' => 'Sertifikat',
                ];
                foreach ($docLabels as $field => $label): ?>
                <div class="col-md-6">
                    <label class="form-label"><?= $label ?></label>
                    <input type="file" class="form-control" name="<?= $field ?>" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <div class="form-text">Maks 10MB — PDF, JPG, PNG, DOC</div>
                </div>
                <?php endforeach; ?>
                <div class="col-md-6"><label class="form-label">Masa Berlaku STR</label><input type="date" class="form-control" name="masa_berlaku_str"></div>
                <div class="col-md-6"><label class="form-label">Masa Berlaku SIP</label><input type="date" class="form-control" name="masa_berlaku_sip"></div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan</button>
        <a href="pegawai.php" class="btn btn-secondary">Batal</a>
    </div>
</form>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
