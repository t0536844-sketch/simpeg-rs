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

// Compute notification count for sidebar badge
$notifCount = 0;
if (isLoggedIn()) {
    try {
        require_once __DIR__ . '/notifikasi_helper.php';
        $notifDb = (new Database())->getConnection();
        $allNotif = getExpiryNotifications($notifDb);
        $notifCounts = countExpiryBySeverity($allNotif);
        $notifCount = $notifCounts['total'];
    } catch (Exception $e) {
        // Silent fail — sidebar still renders
    }
}
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
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --sidebar-width: 260px;
            --bg-body: #f0f2f5;
            --bg-card: #ffffff;
            --text-body: #1e293b;
            --text-muted: #94a3b8;
            --border-color: rgba(0,0,0,0.06);
            --shadow-card: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
            --shadow-hover: 0 10px 25px -5px rgba(0,0,0,0.12);
            --gradient-1: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            --gradient-2: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
            --gradient-3: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
            --gradient-4: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        }

        /* Dark mode variables */
        [data-theme="dark"] {
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --text-body: #e2e8f0;
            --text-muted: #64748b;
            --border-color: rgba(255,255,255,0.06);
            --shadow-card: 0 4px 6px -1px rgba(0,0,0,0.3);
            --shadow-hover: 0 10px 25px -5px rgba(0,0,0,0.5);
        }

        body { font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif; background: var(--bg-body); color: var(--text-body); }
        .main-content { background: var(--bg-body); }

        /* Card styling */
        .card {
            background: var(--bg-card);
            border-radius: 16px;
            box-shadow: var(--shadow-card);
            border: 1px solid var(--border-color);
            transition: box-shadow 0.3s, transform 0.3s;
        }
        .card:hover {
            box-shadow: var(--shadow-hover);
        }
        .card-header.bg-white {
            background: var(--bg-card) !important;
            border-bottom: 1px solid var(--border-color);
            padding: 16px 20px;
        }
        .card-body { padding: 20px; }

        /* Table styling */
        .table { color: var(--text-body); }
        .table > thead > tr > th {
            border-bottom: 2px solid var(--primary);
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table > :not(caption) > * > * { border-bottom-color: var(--border-color); }
        .text-muted { color: var(--text-muted) !important; }
        .breadcrumb-item a { color: var(--primary); }
        .sidebar-user { background: rgba(255,255,255,0.12); }

        /* Badge styling */
        .badge { padding: 4px 10px; font-weight: 500; border-radius: 6px; letter-spacing: 0.3px; }

        /* Dark mode form adjustments */
        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select {
            background-color: #334155;
            border-color: #475569;
            color: #e2e8f0;
        }
        [data-theme="dark"] .form-control:focus,
        [data-theme="dark"] .form-select:focus {
            background-color: #334155;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.25);
            color: #e2e8f0;
        }
        [data-theme="dark"] .modal-content {
            background: #1e293b;
            color: #e2e8f0;
        }
        [data-theme="dark"] .table-hover tbody tr:hover {
            background-color: rgba(255,255,255,0.04);
        }
        [data-theme="dark"] .pagination .page-link {
            background-color: #1e293b;
            border-color: #475569;
            color: #e2e8f0;
        }
        [data-theme="dark"] .pagination .active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        [data-theme="dark"] .border { border-color: var(--border-color) !important; }
        [data-theme="dark"] .alert-info { background-color: #0c4a6e; color: #7dd3fc; border-color: #0c4a6e; }
        [data-theme="dark"] .alert-warning { background-color: #422006; color: #fde68a; border-color: #422006; }
        [data-theme="dark"] .alert-success { background-color: #052e16; color: #86efac; border-color: #052e16; }
        [data-theme="dark"] .alert-danger { background-color: #450a0a; color: #fca5a5; border-color: #450a0a; }

        /* Sidebar */
        .sidebar {
            background: linear-gradient(180deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%);
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
            box-shadow: 4px 0 20px rgba(79,70,229,0.3);
        }
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 30% 20%, rgba(255,255,255,0.08) 0%, transparent 50%);
            pointer-events: none;
        }
        .sidebar-brand {
            text-align: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            margin-bottom: 8px;
            position: relative;
        }
        .sidebar-brand h4 {
            margin: 0; font-weight: 800;
            background: linear-gradient(90deg, #fff 0%, #e0e7ff 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .sidebar-brand small { opacity: 0.85; }
        .sidebar-user {
            padding: 12px 16px; margin: 8px 12px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border-radius: 12px; text-align: center; font-size: 0.85rem; position: relative;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8); padding: 10px 20px; margin: 2px 10px;
            border-radius: 10px; transition: all 0.25s ease; font-size: 0.9rem; position: relative; overflow: hidden;
        }
        .sidebar .nav-link::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
            background: white; border-radius: 0 3px 3px 0; transform: scaleY(0); transition: transform 0.25s ease;
        }
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.15); color: #fff; transform: translateX(4px); }
        .sidebar .nav-link:hover::before { transform: scaleY(1); }
        .sidebar .nav-link.active { background: rgba(255,255,255,0.25); color: #fff; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
        .sidebar .nav-link.active::before { transform: scaleY(1); }
        .sidebar .nav-link i { width: 20px; margin-right: 8px; }

        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width); padding: 24px; background: var(--bg-body); min-height: 100vh;
        }
        .main-content::before {
            content: ''; position: fixed; top: 0; right: 0; width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(99,102,241,0.04) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
        }
        .page-header { position: relative; z-index: 1; }

        /* Cards */
        .card { border: none; border-radius: 16px; box-shadow: var(--shadow-card); transition: box-shadow 0.3s ease, transform 0.3s ease; }
        .stat-card {
            border-radius: 16px; padding: 24px; color: white; position: relative; overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-6px); box-shadow: 0 15px 30px rgba(0,0,0,0.2); }
        .stat-card::before {
            content: ''; position: absolute; top: -30%; right: -15%; width: 120px; height: 120px;
            background: rgba(255,255,255,0.15); border-radius: 50%;
        }
        .stat-card::after {
            content: ''; position: absolute; bottom: -20%; right: 10%; width: 80px; height: 80px;
            background: rgba(255,255,255,0.08); border-radius: 50%;
        }
        .stat-card .stat-number { font-size: 2rem; font-weight: 800; position: relative; z-index: 1; line-height: 1.2; }
        .stat-card .stat-label { font-size: 0.85rem; opacity: 0.9; position: relative; z-index: 1; letter-spacing: 0.5px; }
        .stat-card .stat-icon { font-size: 2.5rem; opacity: 0.25; position: absolute; bottom: 15px; right: 15px; z-index: 1; }

        /* Gradient stat cards */
        .bg-gradient-primary { background: var(--gradient-1); }
        .bg-gradient-info { background: var(--gradient-2); }
        .bg-gradient-success { background: var(--gradient-3); }
        .bg-gradient-warning { background: var(--gradient-4); }

        /* Buttons */
        .btn-primary {
            background: var(--gradient-1); border: none; border-radius: 10px; padding: 8px 20px; font-weight: 500;
            transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(99,102,241,0.3);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(99,102,241,0.4); }
        .btn-success {
            background: var(--gradient-3); border: none; border-radius: 10px; padding: 8px 20px; font-weight: 500;
            transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }
        .btn-success:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16,185,129,0.4); }
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
        }
        .sidebar-toggle {
            display: none; position: fixed; top: 12px; left: 12px; z-index: 1060;
            background: var(--gradient-1); color: white; border: none; border-radius: 10px;
            width: 44px; height: 44px; font-size: 1.2rem; box-shadow: 0 4px 12px rgba(99,102,241,0.4);
        }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0; padding-top: 70px; }
            .sidebar-toggle { display: flex; align-items: center; justify-content: center; }
        }

        /* Collapsible sidebar (desktop) */
        .sidebar-collapsed .sidebar { transform: translateX(-100%); }
        .sidebar-collapsed .main-content { margin-left: 0 !important; }
        .sidebar-expand-btn {
            position: fixed;
            top: 12px;
            left: 12px;
            z-index: 1055;
            width: 44px;
            height: 44px;
            background: var(--gradient-1);
            color: white;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(99,102,241,0.4);
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.25s ease;
            display: none;
        }
        .sidebar-collapsed .sidebar-expand-btn { display: flex !important; }
        .sidebar-expand-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99,102,241,0.5);
        }

        /* Print */
        @media print {
            .sidebar, .sidebar-toggle, .sidebar-expand-btn, .no-print { display: none !important; }
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
            <i class="bi bi-download"></i> Export CSV
        </a>
        <a class="nav-link <?= $activePage === 'export_pdf' ? 'active' : '' ?>" href="export_pdf.php">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>
        <a class="nav-link <?= $activePage === 'notifikasi' ? 'active' : '' ?>" href="notifikasi.php">
            <i class="bi bi-bell-fill"></i> Notifikasi
            <?php if ($notifCount > 0): ?>
                <span class="badge bg-danger ms-1"><?= $notifCount ?></span>
            <?php endif; ?>
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
        <hr class="text-white-50 mx-3">
        <a class="nav-link text-danger" href="logout.php">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
        <a class="nav-link" href="#" id="sidebarCollapse" title="Sembunyikan sidebar">
            <i class="bi bi-layout-sidebar-inset"></i> <span>Perkecil Menu</span>
        </a>
    </nav>
</aside>

<!-- Expand sidebar button (visible when collapsed) -->
<button class="sidebar-expand-btn" id="sidebarExpand" title="Tampilkan sidebar">
    <i class="bi bi-layout-sidebar"></i>
</button>

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
                    <?php foreach ($breadcrumb as $crumb):
                        $isActive = $crumb['active'] ?? false;
                        $url = $crumb['url'] ?? '#';
                    ?>
                    <li class="breadcrumb-item <?= $isActive ? 'active' : '' ?>">
                        <?php if (!$isActive): ?><a href="<?= e($url) ?>"><?= e($crumb['label']) ?></a>
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

// Sidebar collapse/expand (desktop)
(function() {
    var body = document.body;
    var sidebarState = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarState) {
        body.classList.add('sidebar-collapsed');
    }

    var collapseBtn = document.getElementById('sidebarCollapse');
    var expandBtn = document.getElementById('sidebarExpand');

    if (collapseBtn) {
        collapseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.add('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', 'true');
        });
    }

    if (expandBtn) {
        expandBtn.addEventListener('click', function() {
            body.classList.remove('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', 'false');
        });
    }
})();
</script>
