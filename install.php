<?php
/**
 * install.php — Installation Wizard untuk SIM Kepegawaian RSUD Mimika
 *
 * Langkah:
 *  1. Cek koneksi MySQL
 *  2. Buat database
 *  3. Buat tabel (pegawai, users, logs)
 *  4. Insert admin default (bcrypt)
 *  5. Insert sample data
 *  6. Buat folder uploads + .htaccess
 *  7. Instruksi hapus file ini
 */

// Safety: hanya bisa dijalankan dari localhost
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
    die('<h1>⛔ Akses Ditolak</h1><p>Instalasi hanya bisa dijalankan dari localhost.</p>');
}

$step = $_GET['step'] ?? 1;
$messages = [];
$errors = [];

// ─── Step 1: Test Database Connection ───
if ($step == 1) {
    $host = $_POST['db_host'] ?? 'localhost';
    $user = $_POST['db_user'] ?? 'root';
    $pass = $_POST['db_pass'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $_SESSION['install_db_host'] = $host;
            $_SESSION['install_db_user'] = $user;
            $_SESSION['install_db_pass'] = $pass;
            header('Location: install.php?step=2');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Koneksi MySQL gagal: ' . $e->getMessage();
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Instalasi — SIM Kepegawaian RSUD Mimika</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <style>
            body { background: linear-gradient(135deg, #667eea, #764ba2); min-height: 100vh; display: flex; align-items: center; }
            .install-card { max-width: 500px; margin: auto; background: white; border-radius: 16px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
        </style>
    </head>
    <body>
    <div class="install-card">
        <h3 class="mb-4"><i class="bi bi-gear-fill me-2"></i> Instalasi SIM Kepegawaian</h3>
        <div class="progress mb-4" style="height: 8px;">
            <div class="progress-bar" style="width: 20%"></div>
        </div>
        <h5 class="mb-3">Step 1 — Koneksi Database</h5>
        <?php foreach ($errors as $err): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
        <?php endforeach; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Host MySQL</label>
                <input type="text" class="form-control" name="db_host" value="localhost" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="db_user" value="root" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="db_pass" placeholder="Kosongkan jika tidak ada password">
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-arrow-right me-1"></i> Lanjut
            </button>
        </form>
    </div>
    </body></html>
    <?php
    exit;
}

// ─── Steps 2-5: Run Installation ───
$host = $_SESSION['install_db_host'] ?? 'localhost';
$user = $_SESSION['install_db_user'] ?? 'root';
$pass = $_SESSION['install_db_pass'] ?? '';
$dbName = 'rsud_mimika_kepegawaian';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Step 2: Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $messages[] = '✅ Database berhasil dibuat.';
    $pdo->exec("USE `$dbName`");

    // Step 3: Create tables
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS pegawai (
        id INT PRIMARY KEY AUTO_INCREMENT,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        nama_lengkap VARCHAR(255) NOT NULL,
        tempat_lahir VARCHAR(100),
        tanggal_lahir DATE,
        agama VARCHAR(50),
        jenis_kelamin ENUM('Pria', 'Wanita'),
        nip VARCHAR(50) UNIQUE,
        pangkat_golongan VARCHAR(50),
        pendidikan VARCHAR(255),
        status_pernikahan VARCHAR(50),
        jabatan VARCHAR(255),
        status_kepegawaian VARCHAR(50),
        link_sk TEXT,
        jumlah_keluarga INT DEFAULT 0,
        alamat_rumah TEXT,
        link_ktp TEXT,
        link_kartu_keluarga TEXT,
        link_ijazah TEXT,
        link_str TEXT,
        masa_berlaku_str DATE,
        link_sip TEXT,
        masa_berlaku_sip DATE,
        nomor_kartu_pegawai VARCHAR(100),
        link_npwp TEXT,
        link_foto TEXT,
        link_akta_lahir TEXT,
        link_akta_nikah TEXT,
        link_skp TEXT,
        link_sk_kenaikan_pangkat TEXT,
        link_sk_jabatan TEXT,
        link_sk_mutasi TEXT,
        link_sk_pensiun TEXT,
        link_sertifikat TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $messages[] = '✅ Tabel `pegawai` berhasil dibuat.';

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        nama_lengkap VARCHAR(255),
        role ENUM('admin', 'operator', 'viewer') DEFAULT 'operator',
        remember_token VARCHAR(64) DEFAULT NULL,
        remember_token_expires DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $messages[] = '✅ Tabel `users` berhasil dibuat.';

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        action VARCHAR(100),
        table_name VARCHAR(100),
        record_id INT,
        description TEXT,
        ip_address VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $messages[] = '✅ Tabel `logs` berhasil dibuat.';

    // Step 4: Insert default admin
    $adminHash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', $adminHash, 'Administrator', 'admin']);
    $messages[] = '✅ User admin default berhasil dibuat (admin / admin123).';

    // Step 5: Insert sample data
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO pegawai (nama_lengkap, tempat_lahir, tanggal_lahir, agama, jenis_kelamin, nip,
            pangkat_golongan, pendidikan, status_pernikahan, jabatan, status_kepegawaian, jumlah_keluarga, alamat_rumah)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $samples = [
        ['Uji coba data', 'Nabire', '1990-01-31', 'Konghucu', 'Pria', '123123123', 'IV/a', 'S1 ilmu kesehatan masyarakat', 'Menikah', 'Staf', 'PNS', 2, 'DINAS'],
        ['Dr. Budi Santoso', 'Jakarta', '1980-05-15', 'Islam', 'Pria', '19800515001', 'IV/c', 'S2 Kedokteran', 'Menikah', 'Dokter Spesialis', 'PNS', 3, 'Jl. Sudirman No. 123'],
        ['Siti Rahayu', 'Surabaya', '1992-08-20', 'Islam', 'Wanita', '19920820001', 'III/b', 'D3 Keperawatan', 'Belum Menikah', 'Perawat', 'Honorer', 0, 'Jl. A. Yani No. 45'],
    ];
    $count = 0;
    foreach ($samples as $s) {
        $stmt->execute($s);
        $count++;
    }
    $messages[] = "✅ $count data sample pegawai berhasil ditambahkan.";

    // Step 6: Create uploads directory
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        file_put_contents($uploadDir . '/.gitkeep', '');
        file_put_contents($uploadDir . '/.htaccess', "# Protect uploads\n<FilesMatch \"\.(php|php3|php4|php5|phtml)$\">\n    Deny from all\n</FilesMatch>\nOptions -Indexes\n");
    }
    $messages[] = '✅ Folder uploads berhasil dibuat.';

} catch (PDOException $e) {
    $errors[] = 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalasi Selesai — SIM Kepegawaian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea, #764ba2); min-height: 100vh; display: flex; align-items: center; }
        .install-card { max-width: 600px; margin: auto; background: white; border-radius: 16px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    </style>
</head>
<body>
<div class="install-card">
    <h3 class="mb-4"><i class="bi bi-check-circle-fill text-success me-2"></i> Instalasi Selesai!</h3>
    <div class="progress mb-4" style="height: 8px;">
        <div class="progress-bar bg-success" style="width: 100%"></div>
    </div>

    <?php foreach ($messages as $msg): ?>
        <div class="alert alert-success py-2"><?= $msg ?></div>
    <?php endforeach; ?>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger py-2"><?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>

    <hr>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Penting:</strong> Hapus atau rename file <code>install.php</code> setelah instalasi selesai untuk keamanan.
    </div>

    <div class="mt-4 d-flex gap-2">
        <a href="login.php" class="btn btn-primary flex-fill">
            <i class="bi bi-box-arrow-in-right me-1"></i> Login Sekarang
        </a>
        <a href="#" onclick="if(confirm('Hapus install.php sekarang?')){fetch('install.php?delete=1').then(()=>location.reload())}" class="btn btn-danger">
            <i class="bi bi-trash me-1"></i> Hapus File Ini
        </a>
    </div>
</div>
</body></html>

<?php
// Handle self-delete
if (isset($_GET['delete'])) {
    @unlink(__FILE__);
    echo '<script>alert("install.php berhasil dihapus."); window.location.href="login.php";</script>';
    exit;
}
?>
