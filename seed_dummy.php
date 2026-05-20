<?php
/**
 * Dummy data seeder — seed 30 pegawai + 3 user tambahan
 * Jalankan via: php seed_dummy.php (atau visit /seed_dummy.php di HF Spaces)
 */
require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $database = new Database();
    $db = $database->getConnection();

    // Check pegawai count
    $count = $db->query("SELECT COUNT(*) FROM pegawai")->fetchColumn();
    echo "Existing pegawai: $count\n";

    // Check user count
    $uCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "Existing users: $uCount\n\n";

    // ─── 30 Dummy Pegawai ───
    $pegawai = [
        // — Dokter Spesialis (PNS) —
        ['Budi Santoso', 'Timika', '1985-03-15', 'Islam', 'Pria', '198503151234001001', 'IV/b', 'S2 Spesialis Penyakit Dalam', 'Menikah', 'Dokter Spesialis Penyakit Dalam', 'PNS', 'Kadaluarsa 2024', 3, 'Jl. Cendrawasih No. 12, Timika'],
        ['Anita Wijaya', 'Jayapura', '1988-07-22', 'Kristen', 'Wanita', '198807221234002001', 'IV/a', 'S2 Spesialis Anak', 'Menikah', 'Dokter Spesialis Anak', 'PNS', 'Segera 2026-06', 2, 'Jl. Diponegoro No. 5, Timika'],
        ['Rizky Pratama', 'Makassar', '1990-01-30', 'Islam', 'Pria', '199001301234003001', 'III/d', 'S2 Spesialis Bedah', 'Menikah', 'Dokter Spesialis Bedah', 'PNS', 'Masih berlaku 2028', 1, 'Jl. Ahmad Yani No. 78, Timika'],
        ['Siti Nurhaliza', 'Palembang', '1987-11-12', 'Islam', 'Wanita', '198711121234004001', 'IV/a', 'S2 Spesialis Kandungan', 'Menikah', 'Dokter Spesialis Obstetri Ginekologi', 'PNS', 'Kritis 2026-05-25', 4, 'Jl. Sudirman No. 33, Timika'],
        ['Ferdinand Lestaluhu', 'Ambon', '1992-06-08', 'Kristen Protestan', 'Pria', '199206081234005001', 'III/c', 'S2 Spesialis Jantung', 'Menikah', 'Dokter Spesialis Jantung dan Pembuluh Darah', 'PNS', 'Masih berlaku 2027', 2, 'Jl. Merdeka No. 10, Timika'],

        // — Dokter Umum (Honorer) —
        ['Maria Korwa', 'Wamena', '1995-09-18', 'Katolik', 'Wanita', 'HON001', '', 'S1 Kedokteran Umum', 'Belum Menikah', 'Dokter Umum', 'Honorer', 'Berlaku 2026-12', 0, 'Jl. Kartini No. 21, Timika'],
        ['Ahmad Fauzi', 'Nabire', '1993-04-25', 'Islam', 'Pria', 'HON002', '', 'S1 Kedokteran Umum', 'Menikah', 'Dokter Umum IGD', 'Honorer', 'Berlaku 2027-03', 1, 'Jl. Pahlawan No. 8, Timika'],
        ['Yuliana Kogoya', 'Timika', '1996-12-03', 'Kristen', 'Wanita', 'HON003', '', 'S1 Kedokteran Umum', 'Belum Menikah', 'Dokter Umum Poli', 'Honorer', 'Berlaku 2026-09', 0, 'Jl. Kasuari No. 15, Timika'],

        // — Perawat (PNS + Honorer) —
        ['Dewi Sartika', 'Surabaya', '1991-08-14', 'Islam', 'Wanita', '199108141234006001', 'III/b', 'D3 Keperawatan', 'Menikah', 'Kepala Ruangan ICU', 'PNS', 'Berlaku 2027', 2, 'Jl. Gatot Subroto No. 44, Timika'],
        ['I Wayan Sudarma', 'Denpasar', '1989-02-28', 'Hindu', 'Pria', '198902281234007001', 'III/a', 'S1 Keperawatan', 'Menikah', 'Perawat Pelaksana', 'PNS', 'Berlaku 2026', 3, 'Jl. Melati No. 6, Timika'],
        ['Kristina Ohee', 'Jayapura', '1997-05-17', 'Kristen', 'Wanita', 'HON004', '', 'D3 Keperawatan', 'Belum Menikah', 'Perawat IGD', 'Honorer', 'Berlaku 2026-08', 0, 'Jl. Anggrek No. 19, Timika'],
        ['Rahmat Hidayat', 'Bogor', '1994-10-05', 'Islam', 'Pria', 'HON005', '', 'S1 Keperawatan', 'Menikah', 'Perawat OK', 'Honorer', 'Berlaku 2027-01', 1, 'Jl. Mawar No. 3, Timika'],
        ['Selvina Pigai', 'Timika', '1998-01-22', 'Kristen', 'Wanita', 'HON006', '', 'D3 Keperawatan', 'Belum Menikah', 'Perawat Anak', 'Honorer', 'Berlaku 2026-11', 0, 'Jl. Kenanga No. 27, Timika'],

        // — Bidan (PNS + Honorer) —
        ['Nur Aisyah', 'Makassar', '1990-06-19', 'Islam', 'Wanita', '199006191234008001', 'III/c', 'D3 Kebidanan', 'Menikah', 'Bidan Koordinator', 'PNS', 'Berlaku 2027', 2, 'Jl. Dahlia No. 11, Timika'],
        ['Theresia Murib', 'Timika', '1995-03-30', 'Katolik', 'Wanita', 'HON007', '', 'D3 Kebidanan', 'Belum Menikah', 'Bidan Poli KIA', 'Honorer', 'Kadaluarsa 2024', 0, 'Jl. Flamboyan No. 9, Timika'],
        ['Fitri Handayani', 'Semarang', '1993-11-08', 'Islam', 'Wanita', 'HON008', '', 'S1 Kebidanan', 'Menikah', 'Bidan Pelaksana', 'Honorer', 'Berlaku 2026-07', 1, 'Jl. Cempaka No. 14, Timika'],

        // — Apoteker (PNS + Honorer) —
        ['Andi Firmansyah', 'Makassar', '1988-04-11', 'Islam', 'Pria', '198804111234009001', 'III/b', 'S1 Farmasi', 'Menikah', 'Apoteker Instalasi Farmasi', 'PNS', 'Berlaku 2027', 2, 'Jl. Teratai No. 22, Timika'],
        ['Rina Susanti', 'Yogyakarta', '1996-08-23', 'Kristen', 'Wanita', 'HON009', '', 'S1 Farmasi', 'Belum Menikah', 'Apoteker Pelaksana', 'Honorer', 'Berlaku 2026-10', 0, 'Jl. Melati No. 5, Timika'],

        // — Tenaga Administrasi (PNS + Honorer) —
        ['Hendra Gunawan', 'Timika', '1986-12-01', 'Islam', 'Pria', '198612011234010001', 'III/d', 'S1 Administrasi', 'Menikah', 'Kepala Sub Bagian Kepegawaian', 'PNS', '-', 3, 'Jl. Cenderawasih No. 31, Timika'],
        ['Ruslan Abdullah', 'Nabire', '1991-07-16', 'Islam', 'Pria', 'HON010', '', 'SMA', 'Menikah', 'Staff Administrasi', 'Honorer', '-', 2, 'Jl. Merak No. 7, Timika'],
        ['Lisbeth Wanma', 'Timika', '1994-02-14', 'Kristen', 'Wanita', 'HON011', '', 'D3 Administrasi', 'Belum Menikah', 'Staff Keuangan', 'Honorer', '-', 0, 'Jl. Rajawali No. 18, Timika'],
        ['Putri Ayu Lestari', 'Jakarta', '1997-09-29', 'Islam', 'Wanita', 'HON012', '', 'S1 Manajemen', 'Belum Menikah', 'Staff Umum', 'Honorer', '-', 0, 'Jl. Garuda No. 25, Timika'],

        // — Teknis / Penunjang Medis (Honorer) —
        ['Yohanes Wambrauw', 'Timika', '1992-05-03', 'Kristen', 'Pria', 'HON013', '', 'D3 Teknologi Laboratorium', 'Menikah', 'Petugas Laboratorium', 'Honorer', 'Berlaku 2026-12', 2, 'Jl. Nuri No. 4, Timika'],
        ['Sri Wahyuni', 'Medan', '1995-11-27', 'Islam', 'Wanita', 'HON014', '', 'D3 Radiologi', 'Belum Menikah', 'Petugas Radiologi', 'Honorer', 'Berlaku 2027-02', 0, 'Jl. Kakatua No. 16, Timika'],
        ['Agus Setiawan', 'Solo', '1990-08-08', 'Islam', 'Pria', 'HON015', '', 'SMA', 'Menikah', 'Petugas Ambulans', 'Honorer', '-', 3, 'Jl. Elang No. 20, Timika'],
        ['Mega Puspitasari', 'Bandung', '1998-04-12', 'Islam', 'Wanita', 'HON016', '', 'D3 Gizi', 'Belum Menikah', 'Petugas Gizi', 'Honorer', 'Berlaku 2026-06', 0, 'Jl. Merpati No. 13, Timika'],
        ['Daniel Kalami', 'Timika', '1993-07-07', 'Katolik', 'Pria', 'HON017', '', 'SMA', 'Menikah', 'Petugas Kebersihan', 'Honorer', '-', 2, 'Jl. Kasuari No. 2, Timika'],
        ['Novita Sari', 'Balikpapan', '1996-01-19', 'Kristen', 'Wanita', 'HON018', '', 'D3 Rekam Medis', 'Belum Menikah', 'Petugas Rekam Medis', 'Honorer', 'Berlaku 2027-04', 0, 'Jl. Cempaka No. 30, Timika'],

        // — Manajemen —
        ['dr. Hartono, M.Kes', 'Surabaya', '1980-05-10', 'Islam', 'Pria', '198005101234011001', 'IV/c', 'S2 Manajemen Kesehatan', 'Menikah', 'Direktur RSUD Mimika', 'PNS', 'Berlaku 2028', 4, 'Jl. Dr. Soetomo No. 1, Timika'],
        ['Ir. Maria Theresia, M.M.', 'Manado', '1983-09-25', 'Katolik', 'Wanita', '198309251234012001', 'IV/a', 'S2 Manajemen', 'Menikah', 'Kepala Bagian Umum', 'PNS', '-', 3, 'Jl. Pattimura No. 45, Timika'],
    ];

    $inserted = 0;
    $skipped = 0;
    $now = date('Y-m-d H:i:s');
    $baseDate = date('Y-m-d H:i:s');

    $stmt = $db->prepare("
        INSERT OR IGNORE INTO pegawai (
            nama_lengkap, tempat_lahir, tanggal_lahir, agama, jenis_kelamin,
            nip, pangkat_golongan, pendidikan, status_pernikahan, jabatan,
            status_kepegawaian, masa_berlaku_str, jumlah_keluarga, alamat_rumah,
            created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($pegawai as $p) {
        $stmt->execute([
            $p[0],  $p[1],  $p[2],  $p[3],  $p[4],
            $p[5],  $p[6],  $p[7],  $p[8],  $p[9],
            $p[10], $p[11], $p[12], $p[13], $now, $now
        ]);
        if ($stmt->rowCount() > 0) {
            $inserted++;
        } else {
            $skipped++;
        }
    }

    echo "═══════════════════════════════════════\n";
    echo "  PEGAWAI: Inserted: $inserted, Skipped (duplicate): $skipped\n";

    // ─── Additional Users ───
    $users = [
        ['operator', '$2y$12$UCLvTpnKl1Y3nPu4v.zQYuKoppkmUEjwaPYtlE/JVVk.i.3BZtCAe', 'Operator Kepegawaian', 'operator'],
        ['viewer',   '$2y$12$UCLvTpnKl1Y3nPu4v.zQYuKoppkmUEjwaPYtlE/JVVk.i.3BZtCAe', 'Viewer Dashboard',    'viewer'],
    ];

    $uInserted = 0;
    $uStmt = $db->prepare("INSERT OR IGNORE INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
    foreach ($users as $u) {
        $uStmt->execute($u);
        if ($uStmt->rowCount() > 0) $uInserted++;
    }

    echo "  USERS: Inserted: $uInserted (operator & viewer, password: admin123)\n";

    // ─── Summary ───
    $totalPegawai = $db->query("SELECT COUNT(*) FROM pegawai")->fetchColumn();
    $totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalPNS = $db->query("SELECT COUNT(*) FROM pegawai WHERE status_kepegawaian = 'PNS'")->fetchColumn();
    $totalHonorer = $db->query("SELECT COUNT(*) FROM pegawai WHERE status_kepegawaian = 'Honorer'")->fetchColumn();

    echo "\n═══════════════════════════════════════\n";
    echo "  TOTAL SEKARANG:\n";
    echo "  Pegawai: $totalPegawai (PNS: $totalPNS, Honorer: $totalHonorer)\n";
    echo "  Users: $totalUsers\n";
    echo "═══════════════════════════════════════\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
