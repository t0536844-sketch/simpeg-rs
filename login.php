<?php
// login.php — Autentikasi user dengan bcrypt + Remember Me
require_once 'config.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

// Cek Remember Me cookie
if (!isLoggedIn() && isset($_COOKIE['remember_token'])) {
    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare("SELECT * FROM users WHERE remember_token = ? AND remember_token_expires > datetime('now')");
    $stmt->execute([$_COOKIE['remember_token']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['login_time'] = time();

        logActivity($db, $user['id'], 'LOGIN', null, null, 'Auto-login via Remember Me');
        header("Location: dashboard.php");
        exit();
    } else {
        // Cookie tidak valid — hapus
        setcookie('remember_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
    }
}

$error = '';
$lockout_remaining = 0;

// ─── Rate Limiting Check ───
$rate_file = __DIR__ . '/.login_attempts';
$max_attempts = 5;
$lockout_duration = 900; // 15 menit

function readLoginAttempts() {
    global $rate_file;
    if (!file_exists($rate_file)) return [];
    $data = @json_decode(file_get_contents($rate_file), true);
    return is_array($data) ? $data : [];
}

function writeLoginAttempts($data) {
    global $rate_file;
    @file_put_contents($rate_file, json_encode($data), LOCK_EX);
}

function checkLoginLockout($ip) {
    global $max_attempts, $lockout_duration, $lockout_remaining;
    $attempts = readLoginAttempts();
    $ipKey = 'ip_' . md5($ip);
    $now = time();

    if (isset($attempts[$ipKey])) {
        $ipData = $attempts[$ipKey];
        $windowStart = $now - $lockout_duration;

        // Hapus percobaan yang sudah lewat 15 menit
        $ipData['attempts'] = array_filter($ipData['attempts'], fn($t) => $t > $windowStart);

        if (count($ipData['attempts']) >= $max_attempts) {
            $lastAttempt = max($ipData['attempts']);
            $lockout_remaining = ceil(($lastAttempt + $lockout_duration - $now) / 60);
            return true;
        }

        $attempts[$ipKey] = $ipData;
    }

    writeLoginAttempts($attempts);
    return false;
}

function recordFailedLogin($ip) {
    global $max_attempts, $rate_file;
    $attempts = readLoginAttempts();
    $ipKey = 'ip_' . md5($ip);
    $now = time();

    if (!isset($attempts[$ipKey])) {
        $attempts[$ipKey] = ['attempts' => []];
    }

    $attempts[$ipKey]['attempts'][] = $now;
    writeLoginAttempts($attempts);
}

function clearLoginAttempts($ip) {
    $attempts = readLoginAttempts();
    $ipKey = 'ip_' . md5($ip);
    unset($attempts[$ipKey]);
    writeLoginAttempts($attempts);
}

// Cek apakah IP sedang di-lockout
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && checkLoginLockout($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
    $error = 'Terlalu banyak percobaan login gagal. Silakan coba lagi dalam ' . $lockout_remaining . ' menit.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi CSRF
    verify_csrf();

    // Cek lockout
    if (checkLoginLockout($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
        $error = 'Terlalu banyak percobaan login gagal. Silakan coba lagi dalam ' . $lockout_remaining . ' menit.';
    } else {
        $database = new Database();
        $db = $database->getConnection();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($username) || empty($password)) {
            $error = 'Username dan password wajib diisi!';
        } else {
            $query = "SELECT * FROM users WHERE username = :username";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verifikasi password dengan bcrypt
                // Support backward: cek SHA256 lama kalau bcrypt gagal
                $passwordValid = false;
                if (verifyPassword($password, $user['password'])) {
                    $passwordValid = true;
                } elseif ($user['password'] === hash('sha256', $password)) {
                    // Migration: upgrade dari SHA256 ke bcrypt
                    $newHash = hashPassword($password);
                    $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt->execute([$newHash, $user['id']]);
                    $passwordValid = true;
                }

                if ($passwordValid) {
                    // Clear failed attempts on successful login
                    clearLoginAttempts($_SERVER['REMOTE_ADDR'] ?? 'unknown');

                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                    $_SESSION['login_time'] = time();

                    // Remember Me — set cookie 30 hari
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                        $stmt = $db->prepare("UPDATE users SET remember_token = ?, remember_token_expires = ? WHERE id = ?");
                        $stmt->execute([$token, $expires, $user['id']]);
                        setcookie('remember_token', $token, strtotime('+30 days'), '/', '', isset($_SERVER['HTTPS']), true);
                    }

                    logActivity($db, $user['id'], 'LOGIN', null, null, 'User login');

                    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    header("Location: $proto://$host/dashboard.php");
                    exit();
                } else {
                    recordFailedLogin($_SERVER['REMOTE_ADDR'] ?? 'unknown');
                    $remaining = $max_attempts - (count(readLoginAttempts()['ip_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown')]['attempts'] ?? []));
                    $error = 'Password salah! Sisa percobaan: ' . max(0, $remaining);
                }
            } else {
                recordFailedLogin($_SERVER['REMOTE_ADDR'] ?? 'unknown');
                $remaining = $max_attempts - (count(readLoginAttempts()['ip_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown')]['attempts'] ?? []));
                $error = 'Username tidak ditemukan! Sisa percobaan: ' . max(0, $remaining);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sistem Kepegawaian RSUD Mimika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 420px;
            padding: 40px 32px;
        }
        .logo {
            text-align: center;
            padding-bottom: 24px;
            font-weight: 700;
            font-size: 26px;
            color: #667eea;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 24px;
        }
        .logo small {
            display: block;
            font-size: 14px;
            font-weight: 400;
            color: #888;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            RSUD MIMIKA
            <small>Sistem Kepegawaian</small>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i><?php echo e($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['logout'])): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle me-2"></i> Anda berhasil logout.
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?= isset($_POST['username']) ? e($_POST['username']) : '' ?>"
                       required autofocus autocomplete="username">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password"
                           required autocomplete="current-password">
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </button>
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    </script>
</body>
</html>
