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
    created_at DATETIME DEFAULT (datetime('now'))
);
