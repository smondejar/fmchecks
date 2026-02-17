<!DOCTYPE html>
<html lang="en" class="<?= isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === '1' ? 'dark-mode' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'FM Checks' ?> - Facilities Management</title>
    <link rel="stylesheet" href="/css/style.css">
    <?php if (isset($usePdfJs) && $usePdfJs): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>
    <?php endif; ?>
</head>
<body>
    <?php if (Auth::check()): ?>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <span class="logo-icon">✓</span>
                <span class="logo-text">FM Checks</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="/dashboard" class="nav-item <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span>
                <span class="nav-label">Dashboard</span>
            </a>

            <?php if (Permission::can('view', 'venues')): ?>
            <a href="/venues" class="nav-item <?= ($currentPage ?? '') === 'venues' ? 'active' : '' ?>">
                <span class="nav-icon">🏢</span>
                <span class="nav-label">Venues</span>
            </a>
            <?php endif; ?>

            <?php if (Permission::can('view', 'areas')): ?>
            <a href="/areas" class="nav-item <?= ($currentPage ?? '') === 'areas' ? 'active' : '' ?>">
                <span class="nav-icon">📐</span>
                <span class="nav-label">Areas</span>
            </a>
            <?php endif; ?>

            <?php if (Permission::can('view', 'check_types')): ?>
            <a href="/check-types" class="nav-item <?= ($currentPage ?? '') === 'check_types' ? 'active' : '' ?>">
                <span class="nav-icon">🏷️</span>
                <span class="nav-label">Check Types</span>
            </a>
            <?php endif; ?>

            <div class="nav-divider"></div>

            <?php if (Permission::can('view', 'checks')): ?>
            <a href="/checks" class="nav-item <?= ($currentPage ?? '') === 'checks' ? 'active' : '' ?>">
                <span class="nav-icon">📝</span>
                <span class="nav-label">Check Logs</span>
            </a>
            <?php endif; ?>

            <?php if (Permission::can('view', 'reports')): ?>
            <a href="/reports" class="nav-item <?= ($currentPage ?? '') === 'reports' ? 'active' : '' ?>">
                <span class="nav-icon">📋</span>
                <span class="nav-label">Reports</span>
            </a>
            <?php endif; ?>

            <?php if (Permission::can('view', 'users') || Permission::can('view', 'settings')): ?>
            <div class="nav-divider"></div>
            <?php endif; ?>

            <?php if (Permission::can('view', 'users')): ?>
            <a href="/users" class="nav-item <?= ($currentPage ?? '') === 'users' ? 'active' : '' ?>">
                <span class="nav-icon">👥</span>
                <span class="nav-label">Users</span>
            </a>
            <?php endif; ?>

            <?php if (Permission::can('view', 'settings')): ?>
            <a href="/settings" class="nav-item <?= ($currentPage ?? '') === 'settings' ? 'active' : '' ?>">
                <span class="nav-icon">⚙️</span>
                <span class="nav-label">Settings</span>
            </a>
            <?php endif; ?>

            <div class="nav-divider"></div>
            <a href="/help" class="nav-item <?= ($currentPage ?? '') === 'help' ? 'active' : '' ?>">
                <span class="nav-icon">❓</span>
                <span class="nav-label">Help</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <button class="btn-icon" id="darkModeToggle" title="Toggle dark mode">🌓</button>
            <a href="/logout" class="btn btn-sm btn-danger btn-block">Logout</a>
        </div>
    </aside>

    <!-- Mobile backdrop (closes sidebar when tapped) -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Main Content Area -->
    <div class="main-wrapper">
        <!-- Top Bar -->
        <header class="topbar">
            <button class="sidebar-toggle" id="mobileSidebarToggle">☰</button>
            <div class="topbar-left">
                <h1 class="page-heading"><?= $pageTitle ?? 'FM Checks' ?></h1>
            </div>
            <div class="topbar-right">
                <div class="user-badge">
                    <span class="user-name"><?= htmlspecialchars(Auth::user()['full_name']) ?></span>
                    <span class="user-role"><?= htmlspecialchars(Auth::role()) ?></span>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="content">
            <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['flash_error'] ?>
            </div>
            <?php unset($_SESSION['flash_error']); endif; ?>
    <?php else: ?>
    <!-- Guest Layout (Login/Register) -->
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-header">
                <h1>FM Checks</h1>
                <p>Facilities Management System</p>
            </div>
    <?php endif; ?>
