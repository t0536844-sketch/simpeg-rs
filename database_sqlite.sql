-- SQLite-compatible schema for SIM Kepegawaian
-- Auto-initialized on first run when USE_SQLITE=true

CREATE TABLE IF NOT EXISTS pegawai (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    timestamp DATETIME DEFAULT (datetime('now')),
    nama_lengkap TEXT NOT NULL,
    tempat_lahir TEXT,
    tanggal_lahir TEXT,
    agama TEXT,
    jenis_kelamin TEXT,
    nip TEXT UNIQUE,
    pangkat_golongan TEXT,
    pendidikan TEXT,
    status_pernikahan TEXT,
    jabatan TEXT,
    status_kepegawaian TEXT,
    link_sk TEXT,
    jumlah_keluarga INTEGER DEFAULT 0,
    alamat_rumah TEXT,
    link_ktp TEXT,
    link_kartu_keluarga TEXT,
    link_ijazah TEXT,
    link_str TEXT,
    masa_berlaku_str TEXT,
    link_sip TEXT,
    masa_berlaku_sip TEXT,
    nomor_kartu_pegawai TEXT,
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
    created_at DATETIME DEFAULT (datetime('now')),
    updated_at DATETIME DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    nama_lengkap TEXT,
    role TEXT DEFAULT 'operator',
    remember_token TEXT,
    remember_token_expires TEXT,
    created_at DATETIME DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT,
    table_name TEXT,
    record_id INTEGER,
    description TEXT,
    ip_address TEXT,
    created_at DATETIME DEFAULT (datetime('now')),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Default admin user (password: admin123, bcrypt hash)
INSERT INTO users (username, password, nama_lengkap, role)
SELECT 'admin', '$2y$12$UCLvTpnKl1Y3nPu4v.zQYuKoppkmUEjwaPYtlE/JVVk.i.3BZtCAe', 'Administrator', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');

-- Sample pegawai data
INSERT INTO pegawai (
    nama_lengkap, tempat_lahir, tanggal_lahir, agama, jenis_kelamin, nip,
    pangkat_golongan, pendidikan, status_pernikahan, jabatan, status_kepegawaian,
    link_sk, jumlah_keluarga, alamat_rumah, link_ktp, link_kartu_keluarga,
    link_ijazah, link_str, masa_berlaku_str, link_sip, masa_berlaku_sip,
    nomor_kartu_pegawai, link_npwp, link_foto
) SELECT
    'Uji coba data', 'Nabire', '1990-01-31', 'Konghucu', 'Pria', '123123123',
    'IV/a', 'S1 ilmu kesehatan masyarakat', 'Menikah', 'Staf', 'PNS',
    'https://drive.google.com/open?id=1aYrA86pYxZ9fkAOWtfqCoy6QSGXUGXUGX-', 2, 'DINAS',
    'https://drive.google.com/open?id=1TPoRqoVz030dfZYj8MmSvl3SkBvvp99C',
    'https://drive.google.com/open?id=1mVRR9FYy07Hl4CdWZ8Fw10K-naEU4ViP',
    'https://drive.google.com/open?id=1fo2_zcH1Y62RLEKteIzTIplHplbyvftN',
    'https://drive.google.com/open?id=1O3RaQ9J7OsFQP0AOih3bLC48cf2uiRu5', '2026-03-04',
    'https://drive.google.com/open?id=147PjKg7uMco3jdTBc1ZgWJwJwJwJwJwJw', '2026-03-13',
    '123123123', 'https://drive.google.com/open?id=1G6xGTQch8jON-m8l9TJNaiTXCNwv_JIW',
    'https://drive.google.com/open?id=1KtuNgnxGpTm1XELZ4Q85NSrerKQ8o47G'
WHERE NOT EXISTS (SELECT 1 FROM pegawai WHERE nip = '123123123');
