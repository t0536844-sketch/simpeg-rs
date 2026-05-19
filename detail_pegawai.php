<?php
// detail_pegawai.php — Detail lengkap pegawai
require_once 'config.php';
requireLogin();

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

$pageTitle = 'Detail Pegawai';
$activePage = 'pegawai';
$breadcrumb = [
    ['label' => 'Data Pegawai', 'url' => 'pegawai.php'],
    ['label' => e($pegawai['nama_lengkap']), 'active' => true],
];
$headerActions = '
    <a href="edit_pegawai.php?id=' . $pegawai['id'] . '" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit</a>
    <button onclick="window.print()" class="btn btn-success no-print"><i class="bi bi-printer"></i> Cetak</button>
';
require __DIR__ . '/includes/layout.php';
?>

<!-- Profile Header -->
<div class="card mb-4" style="background:linear-gradient(135deg,var(--primary),var(--secondary));color:white">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto text-center">
                <?php if (!empty($pegawai['link_foto']) && file_exists($pegawai['link_foto'])): ?>
                    <img src="<?= e($pegawai['link_foto']) ?>" class="rounded-circle" style="width:100px;height:100px;object-fit:cover">
                <?php else: ?>
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:100px;height:100px">
                        <i class="bi bi-person" style="font-size:48px;color:var(--primary)"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col">
                <h3 class="mb-1"><?= e($pegawai['nama_lengkap']) ?></h3>
                <div class="row g-3 mt-2">
                    <div class="col-auto"><strong>NIP:</strong> <?= e($pegawai['nip'] ?: '-') ?></div>
                    <div class="col-auto"><strong>Jabatan:</strong> <?= e($pegawai['jabatan'] ?: '-') ?></div>
                    <div class="col-auto"><strong>Status:</strong>
                        <span class="badge bg-<?= $pegawai['status_kepegawaian']==='PNS'?'success':'warning' ?>">
                            <?= e($pegawai['status_kepegawaian']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Data Pribadi -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-person me-1"></i> Data Pribadi</h6></div>
            <div class="card-body">
                <div class="row g-2">
                    <?php
                    $fields = [
                        'Tempat Lahir' => $pegawai['tempat_lahir'],
                        'Tanggal Lahir' => !empty($pegawai['tanggal_lahir']) ? date('d F Y', strtotime($pegawai['tanggal_lahir'])) : '-',
                        'Agama' => $pegawai['agama'],
                        'Jenis Kelamin' => $pegawai['jenis_kelamin'],
                        'Status Pernikahan' => $pegawai['status_pernikahan'],
                        'Jumlah Keluarga' => ($pegawai['jumlah_keluarga'] ?? 0) . ' orang',
                    ];
                    foreach ($fields as $label => $val): ?>
                    <div class="col-6">
                        <small class="text-muted"><?= $label ?></small>
                        <p class="mb-1 fw-semibold"><?= e($val ?: '-') ?></p>
                    </div>
                    <?php endforeach; ?>
                    <div class="col-12"><small class="text-muted">Alamat</small><p class="mb-0 fw-semibold"><?= nl2br(e($pegawai['alamat_rumah'] ?: '-')) ?></p></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Kepegawaian -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-briefcase me-1"></i> Data Kepegawaian</h6></div>
            <div class="card-body">
                <div class="row g-2">
                    <?php
                    $fields2 = [
                        'Pangkat/Golongan' => $pegawai['pangkat_golongan'],
                        'Pendidikan' => $pegawai['pendidikan'],
                        'Nomor Kartu Pegawai' => $pegawai['nomor_kartu_pegawai'],
                        'Tanggal Masuk' => date('d F Y', strtotime($pegawai['created_at'])),
                    ];
                    foreach ($fields2 as $label => $val): ?>
                    <div class="col-6">
                        <small class="text-muted"><?= $label ?></small>
                        <p class="mb-1 fw-semibold"><?= e($val ?: '-') ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Masa Berlaku -->
        <div class="card mt-3">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-calendar-check me-1"></i> Masa Berlaku</h6></div>
            <div class="card-body">
                <div class="row g-2">
                    <?php
                    foreach (['masa_berlaku_str' => 'STR', 'masa_berlaku_sip' => 'SIP'] as $field => $label):
                        $val = $pegawai[$field];
                        $expired = false;
                        $warning = false;
                        if (!empty($val)) {
                            $days = floor((strtotime($val) - time()) / 86400);
                            if ($days <= 0) $expired = true;
                            elseif ($days < 30) $warning = true;
                        }
                    ?>
                    <div class="col-6">
                        <small class="text-muted"><?= $label ?></small>
                        <p class="mb-0 fw-semibold <?= $expired ? 'text-danger' : ($warning ? 'text-warning' : 'text-success') ?>">
                            <?= !empty($val) ? date('d F Y', strtotime($val)) : '-' ?>
                            <?php if ($expired): ?><span class="badge bg-danger ms-1">Kadaluarsa</span><?php endif; ?>
                            <?php if ($warning): ?><span class="badge bg-warning ms-1">Segera habis</span><?php endif; ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dokumen -->
<div class="card mt-3">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-folder me-1"></i> Dokumen</h6></div>
    <div class="card-body">
        <div class="row g-2">
            <?php
            $docs = [
                'KTP' => $pegawai['link_ktp'], 'Kartu Keluarga' => $pegawai['link_kartu_keluarga'],
                'Ijazah' => $pegawai['link_ijazah'], 'STR' => $pegawai['link_str'], 'SIP' => $pegawai['link_sip'],
                'NPWP' => $pegawai['link_npwp'], 'Akta Lahir' => $pegawai['link_akta_lahir'],
                'Akta Nikah' => $pegawai['link_akta_nikah'], 'SK' => $pegawai['link_sk'],
                'SKP' => $pegawai['link_skp'], 'SK Kenaikan Pangkat' => $pegawai['link_sk_kenaikan_pangkat'],
                'SK Jabatan' => $pegawai['link_sk_jabatan'], 'SK Mutasi' => $pegawai['link_sk_mutasi'],
                'SK Pensiun' => $pegawai['link_sk_pensiun'], 'Sertifikat' => $pegawai['link_sertifikat'],
            ];
            $hasDocs = false;
            foreach ($docs as $name => $link):
                if (!empty($link)): $hasDocs = true; ?>
            <div class="col-auto">
                <a href="serve_file.php?file=<?= e($link) ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-file-earmark me-1"></i><?= e($name) ?>
                </a>
            </div>
            <?php endif; endforeach; ?>
            <?php if (!$hasDocs): ?>
            <div class="col-12 text-center text-muted py-3"><i class="bi bi-file-earmark-x" style="font-size:32px"></i><p class="mt-2">Belum ada dokumen diunggah.</p></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
