-- Database: rsud_mimika_kepegawaian
CREATE DATABASE IF NOT EXISTS rsud_mimika_kepegawaian;
USE rsud_mimika_kepegawaian;

-- Tabel utama pegawai
CREATE TABLE pegawai (
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
);

-- Tabel users untuk login
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(255),
    role ENUM('admin', 'operator', 'viewer') DEFAULT 'operator',
    remember_token VARCHAR(64) DEFAULT NULL,
    remember_token_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel logs untuk audit trail
CREATE TABLE logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100),
    table_name VARCHAR(100),
    record_id INT,
    description TEXT,
    ip_address VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert admin default
-- Password: admin123 (hashed dengan bcrypt)
-- Untuk generate hash baru: php -r "echo password_hash('password_anda', PASSWORD_BCRYPT, ['cost' => 12]);"
INSERT INTO users (username, password, nama_lengkap, role)
VALUES ('admin', '$2y$12$UCLvTpnKl1Y3nPu4v.zQYuKoppkmUEjwaPYtlE/JVVk.i.3BZtCAe', 'Administrator', 'admin');

-- Insert sample data
INSERT INTO pegawai (
    nama_lengkap, tempat_lahir, tanggal_lahir, agama, jenis_kelamin, nip,
    pangkat_golongan, pendidikan, status_pernikahan, jabatan, status_kepegawaian,
    link_sk, jumlah_keluarga, alamat_rumah, link_ktp, link_kartu_keluarga,
    link_ijazah, link_str, masa_berlaku_str, link_sip, masa_berlaku_sip,
    nomor_kartu_pegawai, link_npwp, link_foto
) VALUES (
    'Uji coba data', 'Nabire', '1990-01-31', 'Konghucu', 'Pria', '123123123',
    'IV/a', 'S1 ilmu kesehatan masyarakat', 'Menikah', 'Staf', 'PNS',
    'https://drive.google.com/open?id=1aYrA86pYxZ9fkAOWtfqCoy6QSGXUGiw-', 2, 'DINAS',
    'https://drive.google.com/open?id=1TPoRqoVz030dfZYj8MmSvl3SkBvvp99C',
    'https://drive.google.com/open?id=1mVRR9FYy07Hl4CdWZ8Fw10K-naEU4ViP',
    'https://drive.google.com/open?id=1fo2_zcH1Y62RLEKteIzTIplHplbyvftN',
    'https://drive.google.com/open?id=1O3RaQ9J7OsFQP0AOih3bLC48cf2uiRu5', '2026-03-04',
    'https://drive.google.com/open?id=147PjKg7uMco3jdTBc1ZgWJwZB7M3i7D6', '2026-03-13',
    '123123123', 'https://drive.google.com/open?id=1G6xGTQch8jON-m8l9TJNaiTXCNwv_JIW',
    'https://drive.google.com/open?id=1KtuNgnxGpTm1XELZ4Q85NSrerKQ8o47G'
);