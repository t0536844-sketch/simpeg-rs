<?php
// edit_pegawai.php — Edit data pegawai + upload dokumen
require_once 'config.php';
requireOperator();

$database = new Database();
$db = $database->getConnection();

$id = sanitizeInt($_GET['id'] ?? 0, 1);
$stmt = $db->prepare("SELECT * FROM pegawai WHERE id = ?");
$stmt->execute([$id]);
$pegawai = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pegawai) {
    setFlash('error', 'Data pegawai tidak ditemukan.');
    header('Location: pegawai.php');
    exit;
}

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
$namaFolderOld = preg_replace('/[^a-zA-Z0-9\s]/', '', $pegawai['nama_lengkap']);
$namaFolderOld = str_replace(' ', '_', strtoupper($namaFolderOld)) . '_' . $id;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    try {
        $nama = sanitize($_POST['nama_lengkap'] ?? '');
        if (empty($nama)) throw new Exception('Nama lengkap wajib diisi.');

        $namaFolder = preg_replace('/[^a-zA-Z0-9\s]/', '', $nama);
        $namaFolder = str_replace(' ', '_', strtoupper($namaFolder)) . '_' . $id;
        $pegawaiDir = UPLOAD_DIR . $namaFolder . '/';

        if (!is_dir($pegawaiDir)) mkdir($pegawaiDir, 0755, true);

        $uploadedFiles = [];
        foreach ($field_folders as $field => $folderName) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $typeDir = $pegawaiDir . $folderName . '/';
                $result = processUpload($_FILES[$field], $typeDir);
                if (!$result['success']) throw new Exception($result['error']);
                deleteOldFile($pegawai[$field]);
                $uploadedFiles[$field] = $result['path'];
            } else {
                $uploadedFiles[$field] = $pegawai[$field];
            }
        }

        $query = "UPDATE pegawai SET
            nama_lengkap=?, tempat_lahir=?, tanggal_lahir=?, agama=?, jenis_kelamin=?,
            nip=?, pangkat_golongan=?, pendidikan=?, status_pernikahan=?, jabatan=?,
            status_kepegawaian=?, link_sk=?, jumlah_keluarga=?, alamat_rumah=?,
            link_ktp=?, link_kartu_keluarga=?, link_ijazah=?, link_str=?, masa_berlaku_str=?,
            link_sip=?, masa_berlaku_sip=?, nomor_kartu_pegawai=?, link_npwp=?, link_foto=?,
            link_akta_lahir=?, link_akta_nikah=?, link_skp=?, link_sk_kenaikan_pangkat=?,
            link_sk_jabatan=?, link_sk_mutasi=?, link_sk_pensiun=?, link_sertifikat=?
            WHERE id = ?";

        $db->prepare($query)->execute([
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
            $id,
        ]);

        // Rename folder if name changed
        $oldDir = UPLOAD_DIR . $namaFolderOld . '/';
        if ($namaFolderOld !== $namaFolder && is_dir($oldDir)) {
            rename($oldDir, $pegawaiDir);
            foreach ($field_folders as $field => $folderName) {
                if (!empty($uploadedFiles[$field]) && strpos($uploadedFiles[$field], $oldDir) !== false) {
                    $newPath = str_replace($oldDir, $pegawaiDir, $uploadedFiles[$field]);
                    $db->prepare("UPDATE pegawai SET $field = ? WHERE id = ?")->execute([$newPath, $id]);
                }
            }
        }

        logActivity($db, $_SESSION['user_id'], 'UPDATE', 'pegawai', $id, 'Mengupdate data pegawai: ' . $nama);
        setFlash('success', 'Data pegawai berhasil diperbarui.');
        header('Location: detail_pegawai.php?id=' . $id);
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'Edit Pegawai';
$activePage = 'pegawai';
$breadcrumb = [
    ['label' => 'Data Pegawai', 'url' => 'pegawai.php'],
    ['label' => e($pegawai['nama_lengkap']), 'url' => 'detail_pegawai.php?id=' . $id],
    ['label' => 'Edit', 'active' => true],
];
require __DIR__ . '/includes/layout.php';
?>

<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <small class="text-muted">Folder: <code>uploads/<?= e($namaFolderOld) ?>/</code> — Upload file baru akan menggantikan file lama.</small>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="card mb-3">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-person me-1"></i> Data Pribadi</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" class="form-control" name="nama_lengkap" value="<?= e($pegawai['nama_lengkap']) ?>" required
                           oninput="this.value=this.value.toUpperCase()">
                </div>
                <div class="col-md-3"><label class="form-label">Tempat Lahir</label><input type="text" class="form-control" name="tempat_lahir" value="<?= e($pegawai['tempat_lahir']) ?>"></div>
                <div class="col-md-3"><label class="form-label">Tanggal Lahir</label><input type="date" class="form-control" name="tanggal_lahir" value="<?= e($pegawai['tanggal_lahir']) ?>"></div>
                <div class="col-md-3">
                    <label class="form-label">Agama</label>
                    <select class="form-select" name="agama">
                        <option value="">Pilih</option>
                        <?php foreach(['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $a): ?>
                        <option value="<?= $a ?>" <?= $pegawai['agama']===$a?'selected':'' ?>><?= $a ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select class="form-select" name="jenis_kelamin">
                        <option <?= $pegawai['jenis_kelamin']==='Pria'?'selected':'' ?>>Pria</option>
                        <option <?= $pegawai['jenis_kelamin']==='Wanita'?'selected':'' ?>>Wanita</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status Pernikahan</label>
                    <select class="form-select" name="status_pernikahan">
                        <?php foreach(['Menikah','Belum Menikah','Cerai'] as $s): ?>
                        <option <?= $pegawai['status_pernikahan']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Jumlah Keluarga</label><input type="number" class="form-control" name="jumlah_keluarga" min="0" value="<?= (int)$pegawai['jumlah_keluarga'] ?>"></div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-briefcase me-1"></i> Data Kepegawaian</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">NIP</label><input type="text" class="form-control" name="nip" value="<?= e($pegawai['nip']) ?>"></div>
                <div class="col-md-4">
                    <label class="form-label">Pangkat/Golongan</label>
                    <select class="form-select" name="pangkat_golongan">
                        <option value="">Pilih</option>
                        <?php foreach(['I/a','I/b','I/c','I/d','II/a','II/b','II/c','II/d','III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c','IV/d','IV/e'] as $p): ?>
                        <option <?= $pegawai['pangkat_golongan']===$p?'selected':'' ?>><?= $p ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status Kepegawaian</label>
                    <select class="form-select" name="status_kepegawaian">
                        <?php foreach(['PNS','CPNS','Honorer','Kontrak'] as $s): ?>
                        <option <?= $pegawai['status_kepegawaian']===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Pendidikan</label><input type="text" class="form-control" name="pendidikan" value="<?= e($pegawai['pendidikan']) ?>"></div>
                <div class="col-md-6"><label class="form-label">Jabatan</label><input type="text" class="form-control" name="jabatan" value="<?= e($pegawai['jabatan']) ?>"></div>
                <div class="col-md-6"><label class="form-label">Nomor Kartu Pegawai</label><input type="text" class="form-control" name="nomor_kartu_pegawai" value="<?= e($pegawai['nomor_kartu_pegawai']) ?>"></div>
                <div class="col-md-6"><label class="form-label">Alamat</label><textarea class="form-control" name="alamat_rumah" rows="2"><?= e($pegawai['alamat_rumah']) ?></textarea></div>
            </div>
        </div>
    </div>

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
                    <div class="form-text">Kosongkan jika tidak diubah.</div>
                    <?php if (!empty($pegawai[$field])): ?>
                    <div class="mt-1"><small><i class="bi bi-paperclip"></i> Saat ini: <a href="<?= e($pegawai[$field]) ?>" target="_blank"><?= e(basename($pegawai[$field])) ?></a></small></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <div class="col-md-6"><label class="form-label">Masa Berlaku STR</label><input type="date" class="form-control" name="masa_berlaku_str" value="<?= e($pegawai['masa_berlaku_str']) ?>"></div>
                <div class="col-md-6"><label class="form-label">Masa Berlaku SIP</label><input type="date" class="form-control" name="masa_berlaku_sip" value="<?= e($pegawai['masa_berlaku_sip']) ?>"></div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg me-1"></i> Update</button>
        <a href="detail_pegawai.php?id=<?= $id ?>" class="btn btn-secondary">Batal</a>
    </div>
</form>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
