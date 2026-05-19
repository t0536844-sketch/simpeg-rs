# SIM Kepegawaian — RSUD Mimika

Sistem Informasi Manajemen Kepegawaian untuk RSUD Mimika. Aplikasi web berbasis PHP untuk mengelola data pegawai, dokumen, laporan, dan audit trail.

## ✨ Fitur

- 🔐 **Autentikasi & Role** — Login dengan session-based auth, 3 role (Admin, Operator, Viewer)
- 👥 **CRUD Pegawai** — Tambah, edit, hapus, dan lihat detail pegawai lengkap
- 📂 **Manajemen Dokumen** — Upload & simpan dokumen pegawai (SK, KTP, Ijazah, STR, SIP, dll) dengan struktur folder otomatis
- 📊 **Dashboard** — Statistik pegawai, grafik distribusi gender & status kepegawaian (Chart.js)
- 🔍 **Pencarian & Filter** — Cari berdasarkan nama, NIP, jabatan; filter berdasarkan status kepegawaian
- 📤 **Import/Export CSV** — Import data dari file CSV, export seluruh data pegawai
- 📄 **Laporan** — Laporan dengan filter tanggal, status, jabatan, agama + grafik interaktif
- 📝 **Audit Trail** — Catatan lengkap aktivitas user (login, logout, CRUD) dengan IP address
- 👤 **Manajemen User** — Tambah, edit, hapus user, reset password (khusus Admin)
- 🖨️ **Cetak & Print** — Support cetak laporan dan detail pegawai
- 📱 **Responsive** — Tampilan responsif dengan Bootstrap 5

## 🛠️ Teknologi

| Komponen | Teknologi |
|----------|-----------|
| Backend | PHP 8+ dengan PDO |
| Database | MySQL / MariaDB |
| Frontend | Bootstrap 5, Bootstrap Icons, Chart.js, DataTables |
| Security | Session regeneration, Prepared Statements, XSS Protection, CSRF Token |

## 📋 Persyaratan

- PHP 8.0 atau lebih baru
- MySQL 5.7+ / MariaDB 10.3+
- Web Server (Apache/Nginx) atau PHP built-in server
- Ekstensi PHP: `pdo_mysql`, `mbstring`, `json`, `session`

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/t0536844-sketch/sim-kepegawaian.git
cd sim-kepegawaian
```

### 2. Setup Database

```bash
mysql -u root -p < database.sql
```

Atau melalui phpMyAdmin:
1. Buka phpMyAdmin
2. Import file `database.sql`

### 3. Konfigurasi Aplikasi

Edit file `config.php` dan sesuaikan koneksi database:

```php
private $host = "localhost";
private $db_name = "rsud_mimika_kepegawaian";
private $username = "root";
private $password = ""; // Sesuaikan dengan password MySQL kamu
```

### 4. Buat Folder Uploads

```bash
mkdir -p uploads
chmod 755 uploads
```

### 5. Jalankan Aplikasi

**Opsi A — PHP Built-in Server:**
```bash
php -S localhost:8000
```

**Opsi B — XAMPP / Apache:**
1. Copy folder `sim-kepegawaian` ke `htdocs`
2. Buka `http://localhost/sim-kepegawaian/login.php`

## 🔑 Default Login

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |

> ⚠️ **PENTING:** Segera ganti password default setelah login pertama kali.

## 📁 Struktur Folder

```
sim-kepegawaian/
├── config.php              # Konfigurasi database & helper functions
├── login.php               # Halaman login
├── logout.php              # Proses logout
├── dashboard.php           # Dashboard utama
├── pegawai.php             # Data pegawai (tabel + filter)
├── tambah_pegawai.php      # Form tambah pegawai + upload dokumen
├── edit_pegawai.php        # Form edit pegawai
├── detail_pegawai.php      # Detail pegawai
├── hapus_pegawai.php       # Konfirmasi hapus pegawai
├── import.php              # Import data CSV
├── export.php              # Export data ke CSV
├── laporan.php             # Laporan dengan filter & grafik
├── users.php               # Manajemen user (Admin only)
├── logs.php                # Audit logs (Admin only)
├── database.sql            # Skema database
├── template_import.csv     # Template CSV untuk import
└── uploads/                # Folder upload dokumen
```

## 🗃️ Struktur Database

- **`pegawai`** — Data lengkap pegawai (40+ kolom termasuk dokumen)
- **`users`** — Akun pengguna dengan role-based access
- **`logs`** — Audit trail aktivitas user

## 🔒 Keamanan

- Password di-hash dengan `bcrypt` (`password_hash()`)
- Prepared statements untuk semua query (anti SQL injection)
- XSS protection dengan `htmlspecialchars()`
- Session regeneration setelah login
- CSRF token di semua form POST
- Validasi file upload (tipe & ukuran)
- Folder uploads dilindungi `.htaccess`

## 📝 Lisensi

Proyek ini dibuat untuk kebutuhan internal RSUD Mimika.

## 👨‍💻 Developer

Dikembangkan untuk RSUD Mimika — Sistem Kepegawaian Digital
