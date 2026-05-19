<?php
/**
 * config.php - Konfigurasi Utama SIM Kepegawaian RSUD Mimika
 *
 * - Session management dengan secure settings
 * - Database connection (PDO)
 * - Helper functions (auth, CSRF, sanitization, flash messages)
 * - Password hashing dengan bcrypt
 */

// ─── Session Configuration ───
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', '86400');
    ini_set('session.cookie_httponly', '1');
    // Jangan set SameSite untuk localhost — bisa bikin CSRF gagal
    if (!empty($_SERVER['HTTPS'])) {
        ini_set('session.cookie_secure', '1');
        ini_set('session.cookie_samesite', 'Lax');
    }
    session_start();
    
    // Regenerate session ID jika baru (anti session fixation)
    if (empty($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
}

// ─── Database Configuration ───
// Untuk production, disarankan pakai environment variables atau .env
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'rsud_mimika_kepegawaian');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'admin123');

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Cek socket path untuk Termux
            $socket = '/data/data/com.termux/files/usr/var/run/mysqld.sock';
            $dsn = file_exists($socket)
                ? "mysql:unix_socket=$socket;dbname=" . $this->db_name . ";charset=utf8mb4"
                : "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";

            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES    => false,
                ]
            );
        } catch (PDOException $exception) {
            // Jangan tampilkan detail error di production
            if (defined('APP_DEBUG') && APP_DEBUG) {
                die("Connection error: " . $exception->getMessage());
            }
            die("Koneksi database gagal. Periksa konfigurasi.");
        }
        return $this->conn;
    }
}

// ─── CSRF Protection ───

/** Generate CSRF token jika belum ada */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/** Render hidden CSRF field untuk form */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

/** Validasi CSRF token dari form POST */
function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            setFlash('error', 'Token keamanan kadaluarsa. Silakan login ulang.');
            header('Location: login.php');
            exit;
        }
    }
}

// ─── Authentication Helpers ───

/** Cek apakah user sudah login */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/** Wajibkan login — redirect ke login.php jika belum */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

/** Cek apakah user adalah admin */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/** Wajibkan role admin */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        die('Akses ditolak. Hanya admin yang dapat mengakses halaman ini.');
    }
}

/** Wajibkan minimal operator (admin/operator) */
function requireOperator() {
    requireLogin();
    $role = $_SESSION['role'] ?? '';
    if (!in_array($role, ['admin', 'operator'])) {
        http_response_code(403);
        die('Akses ditolak. Minimum role: Operator.');
    }
}

// ─── Input Sanitization ───

/** Sanitize string output (XSS protection) */
function e($string) {
    return htmlspecialchars((string) $string, ENT_QUOTES, 'UTF-8');
}

/** Sanitize input string */
function sanitize($string) {
    return trim(strip_tags((string) $string));
}

/** Validasi dan sanitize tanggal */
function sanitizeDate($date) {
    if (empty($date)) return null;
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return ($d && $d->format('Y-m-d') === $date) ? $date : null;
}

/** Validasi integer positif */
function sanitizeInt($value, $min = 0) {
    $int = filter_var($value, FILTER_VALIDATE_INT);
    return ($int !== false && $int >= $min) ? $int : $min;
}

// ─── Flash Messages ───

/** Set flash message */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Get dan clear flash message */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/** Render flash alert HTML */
function renderFlash() {
    $flash = getFlash();
    if ($flash) {
        $type = $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'danger' : 'info');
        $icon = $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-triangle' : 'info-circle');
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo '<i class="bi bi-' . $icon . ' me-2"></i>' . e($flash['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
}

// ─── Upload Configuration ───
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_ALLOWED_TYPES', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']);

/** Validasi file upload */
function validateUpload($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'File tidak valid atau gagal upload.'];
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, UPLOAD_ALLOWED_TYPES)) {
        return ['valid' => false, 'error' => 'Tipe file tidak diizinkan. Hanya: ' . implode(', ', UPLOAD_ALLOWED_TYPES)];
    }
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return ['valid' => false, 'error' => 'Ukuran file melebihi batas maksimal 10MB.'];
    }
    return ['valid' => true, 'ext' => $ext];
}

/** Proses upload file ke folder tujuan */
function processUpload($file, $destinationDir) {
    $validation = validateUpload($file);
    if (!$validation['valid']) {
        return ['success' => false, 'error' => $validation['error']];
    }

    if (!is_dir($destinationDir)) {
        mkdir($destinationDir, 0755, true);
    }

    $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
    $sanizedName = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $originalName);
    $filename = $sanizedName . '_' . date('Ymd_His') . '_' . uniqid() . '.' . $validation['ext'];
    $destination = $destinationDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'path' => $destination];
    }

    return ['success' => false, 'error' => 'Gagal memindahkan file upload.'];
}

/** Hapus file lama jika ada */
function deleteOldFile($filePath) {
    if (!empty($filePath) && file_exists($filePath)) {
        @unlink($filePath);
    }
}

// ─── Logging Helper ───

/** Catat aktivitas ke tabel logs */
function logActivity($db, $userId, $action, $tableName = null, $recordId = null, $description = '') {
    try {
        $query = "INSERT INTO logs (user_id, action, table_name, record_id, description, ip_address)
                  VALUES (:user_id, :action, :table_name, :record_id, :description, :ip)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':user_id'     => $userId,
            ':action'      => $action,
            ':table_name'  => $tableName,
            ':record_id'   => $recordId,
            ':description' => $description,
            ':ip'          => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ]);
    } catch (PDOException $e) {
        // Jangan biarkan logging error mengganggu flow utama
        error_log('Failed to log activity: ' . $e->getMessage());
    }
}

// ─── Password Helpers ───

/** Hash password dengan bcrypt */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/** Verifikasi password */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/** Cek apakah password perlu di-rehash */
function needsPasswordRehash($hash) {
    return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
}
