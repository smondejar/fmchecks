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
                <span class="logo-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
                <span class="logo-text">FM Checks</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="/dashboard" class="nav-item <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                </span>
                <span class="nav-label">Dashboard</span>
            </a>

            <?php if (Permission::can('view', 'venues')): ?>
            <a href="/venues" class="nav-item <?= ($currentPage ?? '') === 'venues' ? 'active' : '' ?>">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M3 7V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2M3 7h18M3 7v14M21 7v14M9 21v-6a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v6"/><rect x="7" y="10" width="3" height="3" rx=".5"/><rect x="14" y="10" width="3" height="3" rx=".5"/></svg>
                </span>
                <span class="nav-label">Venues</span>
            </a>
            <?php endif; ?>

            <?php if (Permission::can('view', 'areas')): ?>
            <a href="/areas" class="nav-item <?= ($currentPage ?? '') === 'areas' ? 'active' : '' ?>">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6l6-3 6 3 6-3v15l-6 3-6-3-6 3V6z"/><path d="M9 3v15"/><path d="M15 6v15"/></svg>
                </span>
                <span class="nav-label">Areas</span>
            </a>
            <?php endif; ?>

            <?php if (Permission::can('view', 'check_types')): ?>
            <a href="/check-types" class="nav-item <?= ($currentPage ?? '') === 'check_types' ? 'active' : '' ?>">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10l9.29 9.29a1 1 0 0 0 1.42 0l6.58-6.58a1 1 0 0 0 0-1.42L12 2z"/><circle cx="7" cy="7" r="1" fill="currentColor"/></svg>
                </span>
                <span class="nav-label">Check Types</span>
            </a>
            <?php endif; ?>

            <div class="nav-divider"></div>

            <?php if (Permission::can('view', 'checks')): ?>
            <a href="/checks" class="nav-item <?= ($currentPage ?? '') === 'checks' ? 'active' : '' ?>">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>
                </span>
                <span class="nav-label">Check Logs</span>
            </a>
            <?php endif; ?>

            <?php if (Permission::can('view', 'reports')): ?>
            <a href="/reports" class="nav-item <?= ($currentPage ?? '') === 'reports' ? 'active' : '' ?>">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>
                </span>
                <span class="nav-label">Reports</span>
            </a>
            <?php endif; ?>

            <?php if (Permission::can('view', 'reports')): ?>
            <a href="/analytics" class="nav-item <?= ($currentPage ?? '') === 'analytics' ? 'active' : '' ?>">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 21H3"/><path d="M3 3v18"/><rect x="7" y="10" width="3" height="11" rx="1"/><rect x="13" y="5" width="3" height="16" rx="1"/><rect x="19" y="13" width="3" height="8" rx="1" transform="translate(-1 0)"/></svg>
                </span>
                <span class="nav-label">Analytics</span>
            </a>
            <?php endif; ?>

            <?php if (Permission::can('view', 'users') || Permission::can('view', 'settings')): ?>
            <div class="nav-divider"></div>
            <?php endif; ?>

            <?php if (Permission::can('view', 'users')): ?>
            <a href="/users" class="nav-item <?= ($currentPage ?? '') === 'users' ? 'active' : '' ?>">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <span class="nav-label">Users</span>
            </a>
            <?php endif; ?>

            <?php if (Permission::can('view', 'settings')): ?>
            <a href="/settings" class="nav-item <?= ($currentPage ?? '') === 'settings' ? 'active' : '' ?>">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                </span>
                <span class="nav-label">Settings</span>
            </a>
            <?php endif; ?>

            <div class="nav-divider"></div>
            <a href="/help" class="nav-item <?= ($currentPage ?? '') === 'help' ? 'active' : '' ?>">
                <span class="nav-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </span>
                <span class="nav-label">Help</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <button class="btn-icon" id="darkModeToggle" title="Toggle dark mode">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </button>
            <a href="/logout" class="btn btn-sm btn-danger btn-block">Logout</a>
        </div>
    </aside>

    <!-- Mobile backdrop (closes sidebar when tapped) -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Main Content Area -->
    <div class="main-wrapper">
        <!-- Top Bar -->
        <header class="topbar">
            <button class="sidebar-toggle" id="mobileSidebarToggle" aria-label="Open menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
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
