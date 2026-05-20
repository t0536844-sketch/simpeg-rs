<?php
/**
 * includes/layout.php — Shared layout components
 *
 * Usage:
 *   $pageTitle = 'Dashboard';
 *   require_once __DIR__ . '/includes/layout.php';
 *   // Output: header, sidebar, main-content opening
 *   // ... page content ...
 *   // require_once __DIR__ . '/includes/layout_footer.php';
 */

if (!isset($pageTitle)) $pageTitle = 'SIM Kepegawaian';
if (!isset($activePage)) $activePage = '';
if (!isset($breadcrumb)) $breadcrumb = [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — Sistem Kepegawaian RSUD Mimika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --sidebar-width: 260px;
            --bg-body: #f4f6f9;
            --bg-card: #ffffff;
            --text-body: #212529;
            --text-muted: #6c757d;
            --border-color: rgba(0,0,0,0.08);
            --shadow-card: 0 1px 3px rgba(0,0,0,0.08);
        }

        /* Dark mode variables */
        [data-theme="dark"] {
            --bg-body: #1a1d23;
            --bg-card: #252830;
            --text-body: #e4e6eb;
            --text-muted: #a0a3a8;
            --border-color: rgba(255,255,255,0.08);
            --shadow-card: 0 1px 3px rgba(0,0,0,0.3);
        }

        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; background: var(--bg-body); color: var(--text-body); }
        .main-content { background: var(--bg-body); }
        .card { background: var(--bg-card); box-shadow: var(--shadow-card); }
        .card-header.bg-white { background: var(--bg-card) !important; border-bottom: 1px solid var(--border-color); }
        .table { color: var(--text-body); }
        .table > :not(caption) > * > * { border-bottom-color: var(--border-color); }
        .text-muted { color: var(--text-muted) !important; }
        .breadcrumb-item a { color: var(--primary); }
        .sidebar-user { background: rgba(255,255,255,0.12); }

        /* Dark mode form adjustments */
        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select {
            background-color: #2d3038;
            border-color: #3d4048;
            color: #e4e6eb;
        }
        [data-theme="dark"] .form-control:focus,
        [data-theme="dark"] .form-select:focus {
            background-color: #2d3038;
            border-color: var(--primary);
            color: #e4e6eb;
        }
        [data-theme="dark"] .modal-content {
            background: #252830;
            color: #e4e6eb;
        }
        [data-theme="dark"] .table-hover tbody tr:hover {
            background-color: rgba(255,255,255,0.04);
        }
        [data-theme="dark"] .pagination .page-link {
            background-color: #252830;
            border-color: #3d4048;
            color: #e4e6eb;
        }
        [data-theme="dark"] .pagination .active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        [data-theme="dark"] .border { border-color: var(--border-color) !important; }
        [data-theme="dark"] .alert-info { background-color: #1e3a5f; color: #93c5fd; border-color: #1e3a5f; }
        [data-theme="dark"] .alert-warning { background-color: #4a3b1e; color: #fde68a; border-color: #4a3b1e; }
        [data-theme="dark"] .alert-success { background-color: #1e4a2e; color: #86efac; border-color: #1e4a2e; }
        [data-theme="dark"] .alert-danger { background-color: #4a1e1e; color: #fca5a5; border-color: #4a1e1e; }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            padding: 24px 0;
            z-index: 1050;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        .sidebar-brand {
            text-align: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            margin-bottom: 8px;
        }
        .sidebar-brand h4 { margin: 0; font-weight: 700; }
        .sidebar-brand small { opacity: 0.8; }
        .sidebar-user {
            padding: 12px 16px;
            margin: 8px 12px;
            background: rgba(255,255,255,0.12);
            border-radius: 50px;
            text-align: center;
            font-size: 0.85rem;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 10px 20px;
            margin: 2px 10px;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar .nav-link.active { background: rgba(255,255,255,0.2); color: #fff; font-weight: 600; }
        .sidebar .nav-link i { width: 20px; }

        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 24px;
            background: #f4f6f9;
            min-height: 100vh;
        }

        /* Cards */
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .stat-card {
            border-radius: 12px;
            padding: 20px;
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); }

        /* Mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
        }
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 12px;
            left: 12px;
            z-index: 1060;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            width: 44px;
            height: 44px;
            font-size: 1.2rem;
        }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0; padding-top: 70px; }
            .sidebar-toggle { display: flex; align-items: center; justify-content: center; }
        }

        /* Print */
        @media print {
            .sidebar, .sidebar-toggle, .no-print { display: none !important; }
            .main-content { margin: 0; padding: 0; }
        }
    </style>
    <?php if (isset($extraCSS)): ?><?= $extraCSS ?><?php endif; ?>
</head>
<body>

<!-- Mobile toggle -->
<button class="sidebar-toggle" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button>

<!-- Overlay -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h4>RSUD MIMIKA</h4>
        <small>Sistem Kepegawaian</small>
    </div>
    <div class="sidebar-user">
        <i class="bi bi-person-circle me-1"></i>
        <?= e($_SESSION['nama_lengkap'] ?? 'User') ?>
        <span class="badge bg-light text-dark ms-1"><?= e($_SESSION['role'] ?? '?') ?></span>
    </div>
    <nav class="nav flex-column mt-3">
        <a class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a class="nav-link <?= $activePage === 'pegawai' ? 'active' : '' ?>" href="pegawai.php">
            <i class="bi bi-people"></i> Data Pegawai
        </a>
        <?php if (in_array($_SESSION['role'] ?? '', ['admin', 'operator'])): ?>
        <a class="nav-link <?= $activePage === 'tambah' ? 'active' : '' ?>" href="tambah_pegawai.php">
            <i class="bi bi-person-plus"></i> Tambah Pegawai
        </a>
        <a class="nav-link <?= $activePage === 'import' ? 'active' : '' ?>" href="import.php">
            <i class="bi bi-upload"></i> Import Data
        </a>
        <?php endif; ?>
        <a class="nav-link <?= $activePage === 'export' ? 'active' : '' ?>" href="export.php">
            <i class="bi bi-download"></i> Export Data
        </a>
        <a class="nav-link <?= $activePage === 'laporan' ? 'active' : '' ?>" href="laporan.php">
            <i class="bi bi-file-earmark-text"></i> Laporan
        </a>
        <?php if (isAdmin()): ?>
        <hr class="text-white-50 mx-3">
        <a class="nav-link" href="#" id="darkModeToggle">
            <i class="bi bi-moon-fill" id="darkModeIcon"></i> <span id="darkModeLabel">Mode Gelap</span>
        </a>
        <a class="nav-link <?= $activePage === 'users' ? 'active' : '' ?>" href="users.php">
            <i class="bi bi-shield-lock"></i> Manajemen User
        </a>
        <a class="nav-link <?= $activePage === 'logs' ? 'active' : '' ?>" href="logs.php">
            <i class="bi bi-clock-history"></i> Audit Logs
        </a>
        <?php endif; ?>
        <div class="mt-auto pt-4"></div>
        <a class="nav-link text-danger" href="logout.php">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>
</aside>

<!-- Main content wrapper -->
<div class="main-content">
    <?php if (!empty($breadcrumb) || !empty($pageTitle)): ?>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-1"><?= e($pageTitle) ?></h2>
            <?php if (!empty($breadcrumb)): ?>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <?php foreach ($breadcrumb as $crumb): ?>
                    <li class="breadcrumb-item <?= $crumb['active'] ? 'active' : '' ?>">
                        <?php if (!$crumb['active']): ?><a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a>
                        <?php else: ?><?= e($crumb['label']) ?><?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <?php endif; ?>
        </div>
        <?php if (isset($headerActions)): ?>
        <div><?= $headerActions ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php renderFlash(); ?>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('show');
    document.querySelector('.sidebar-overlay').classList.toggle('show');
}

// Dark mode toggle
(function() {
    var theme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', theme);
    updateDarkModeIcon(theme);
    
    document.getElementById('darkModeToggle').addEventListener('click', function(e) {
        e.preventDefault();
        var current = document.documentElement.getAttribute('data-theme');
        var next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        updateDarkModeIcon(next);
    });

    function updateDarkModeIcon(theme) {
        var icon = document.getElementById('darkModeIcon');
        var label = document.getElementById('darkModeLabel');
        if (theme === 'dark') {
            icon.className = 'bi bi-sun-fill';
            label.textContent = 'Mode Terang';
        } else {
            icon.className = 'bi bi-moon-fill';
            label.textContent = 'Mode Gelap';
        }
    }
})();
</script>
