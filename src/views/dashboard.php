<?php
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
require __DIR__ . '/layout/header.php';

// Get dashboard stats
$pdo = Database::connect();

// Venues and areas (for quick access widget)
$venues = Venue::all();
$areas = Area::all();

// Reports stats
$openReports = Report::getOpenCount();
$criticalReports = Report::getCriticalCount();
$recentReports = Report::all(['status' => 'open']);
$recentReports = array_slice($recentReports, 0, 5);

// Checkpoint stats - overdue and due soon
$stmt = $pdo->query("
    SELECT
        cp.*,
        ct.name as type_name,
        ct.colour as type_colour,
        a.area_name,
        v.name as venue_name,
        a.id as area_id,
        v.id as venue_id,
        (SELECT performed_at FROM check_logs WHERE check_point_id = cp.id ORDER BY performed_at DESC LIMIT 1) as last_check
    FROM check_points cp
    LEFT JOIN check_types ct ON cp.check_type_id = ct.id
    LEFT JOIN areas a ON cp.area_id = a.id
    LEFT JOIN venues v ON a.venue_id = v.id
    ORDER BY last_check ASC
");
$allCheckpoints = $stmt->fetchAll();

$overdueChecks = [];
$dueSoonChecks = [];
$upcomingChecks = [];

foreach ($allCheckpoints as $cp) {
    $status = CheckPoint::getStatus($cp['id'], $cp['periodicity']);
    $cp['status'] = $status;

    if ($status === 'overdue') {
        $overdueChecks[] = $cp;
    } elseif ($status === 'due_soon') {
        $dueSoonChecks[] = $cp;
    } elseif ($status === 'never') {
        $upcomingChecks[] = $cp;
    }
}

$overdueCount = count($overdueChecks);
$dueSoonCount = count($dueSoonChecks);
$neverCheckedCount = count($upcomingChecks);

// Limit lists
$overdueChecks = array_slice($overdueChecks, 0, 10);
$dueSoonChecks = array_slice($dueSoonChecks, 0, 10);
$upcomingChecks = array_slice($upcomingChecks, 0, 5);
?>

<div class="dashboard">
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card <?= $overdueCount > 0 ? 'stat-critical' : '' ?>">
            <div class="stat-icon stat-icon-danger">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="stat-info">
                <h3><?= $overdueCount ?></h3>
                <p>Overdue Checks</p>
            </div>
        </div>

        <div class="stat-card <?= $dueSoonCount > 0 ? 'stat-warning' : '' ?>">
            <div class="stat-icon stat-icon-warning">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="stat-info">
                <h3><?= $dueSoonCount ?></h3>
                <p>Due Soon</p>
            </div>
        </div>

        <div class="stat-card <?= $openReports > 0 ? 'stat-warning' : '' ?>">
            <div class="stat-icon stat-icon-info">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>
            </div>
            <div class="stat-info">
                <h3><?= $openReports ?></h3>
                <p>Open Reports</p>
            </div>
        </div>

        <div class="stat-card <?= $criticalReports > 0 ? 'stat-critical' : '' ?>">
            <div class="stat-icon stat-icon-danger">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            </div>
            <div class="stat-info">
                <h3><?= $criticalReports ?></h3>
                <p>Critical Issues</p>
            </div>
        </div>
    </div>

    <!-- Overdue Checks Alert -->
    <?php if (!empty($overdueChecks)): ?>
    <div class="alert alert-danger mt-4">
        <h4><?= $overdueCount ?> Overdue Check<?= $overdueCount != 1 ? 's' : '' ?></h4>
        <div class="overdue-list">
            <?php foreach ($overdueChecks as $cp): ?>
            <div class="overdue-item">
                <a href="/areas/<?= $cp['area_id'] ?>">
                    <strong><?= htmlspecialchars($cp['reference']) ?></strong> - <?= htmlspecialchars($cp['label']) ?>
                </a>
                <span class="location"><?= htmlspecialchars($cp['venue_name']) ?> › <?= htmlspecialchars($cp['area_name']) ?></span>
                <span class="badge badge-overdue"><?= ucfirst($cp['periodicity']) ?></span>
                <?php if ($cp['last_check']): ?>
                <span class="last-check">Last: <?= date('M j', strtotime($cp['last_check'])) ?></span>
                <?php else: ?>
                <span class="last-check">Never checked</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Due Soon Checks -->
    <?php if (!empty($dueSoonChecks)): ?>
    <div class="alert alert-warning mt-4">
        <h4><?= $dueSoonCount ?> Check<?= $dueSoonCount != 1 ? 's' : '' ?> Due Soon</h4>
        <div class="due-soon-list">
            <?php foreach ($dueSoonChecks as $cp): ?>
            <div class="due-item">
                <a href="/areas/<?= $cp['area_id'] ?>">
                    <strong><?= htmlspecialchars($cp['reference']) ?></strong> - <?= htmlspecialchars($cp['label']) ?>
                </a>
                <span class="location"><?= htmlspecialchars($cp['venue_name']) ?> › <?= htmlspecialchars($cp['area_name']) ?></span>
                <span class="badge badge-due-soon"><?= ucfirst($cp['periodicity']) ?></span>
                <?php if ($cp['last_check']): ?>
                <span class="last-check">Last: <?= date('M j', strtotime($cp['last_check'])) ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Access to Venues/Areas -->
    <?php if (!empty($venues)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h3>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:0.375rem;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                Quick Access
            </h3>
        </div>
        <div class="venue-grid">
            <?php
            $venueColors = ['vc-blue','vc-violet','vc-emerald','vc-sky','vc-amber','vc-rose'];
            foreach ($venues as $vi => $venue):
                $venueAreas = array_filter($areas, fn($a) => $a['venue_id'] == $venue['id']);
                $vc = $venueColors[$vi % count($venueColors)];
            ?>
            <div class="venue-card <?= $vc ?>">
                <h4><a href="/venues/<?= $venue['id'] ?>"><?= htmlspecialchars($venue['name']) ?></a></h4>
                <?php if (!empty($venueAreas)): ?>
                <div class="area-links">
                    <?php foreach ($venueAreas as $area): ?>
                    <a href="/areas/<?= $area['id'] ?>" class="area-link">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6l6-3 6 3 6-3v15l-6 3-6-3-6 3V6z"/><path d="M9 3v15"/><path d="M15 6v15"/></svg>
                        <?= htmlspecialchars($area['area_name']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">No areas yet</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Open Reports -->
    <?php if (!empty($recentReports)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h3>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:0.375rem;"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>
                Recent Issues
            </h3>
            <a href="/reports" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="report-list">
            <?php foreach ($recentReports as $report): ?>
            <div class="report-item">
                <a href="/reports/<?= $report['id'] ?>">
                    <strong>#<?= $report['id'] ?></strong> - <?= htmlspecialchars($report['title']) ?>
                </a>
                <div class="report-meta">
                    <span class="location"><?= htmlspecialchars($report['venue_name']) ?></span>
                    <span class="badge badge-<?= $report['severity'] ?>"><?= ucfirst($report['severity']) ?></span>
                    <span class="date"><?= date('M j, H:i', strtotime($report['created_at'])) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="quick-actions-grid mt-4">
        <?php if (Permission::can('create', 'reports')): ?>
        <a href="/reports/create" class="action-card action-warning">
            <div class="action-icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <div class="action-label">Report Issue</div>
        </a>
        <?php endif; ?>

        <?php if (Permission::can('view', 'reports')): ?>
        <a href="/reports" class="action-card action-info">
            <div class="action-icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>
            </div>
            <div class="action-label">View Reports</div>
        </a>
        <?php endif; ?>

    </div>
</div>

<style>
/* ── Stats grid ──────────────────────────────────── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.875rem;
    margin-bottom: 1rem;
}

.stat-card {
    background: white;
    border-radius: var(--radius);
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.875rem;
    box-shadow: var(--shadow);
    border-left: 3px solid transparent;
}

.stat-card.stat-critical { border-left-color: var(--danger); }
.stat-card.stat-warning  { border-left-color: var(--warning); }

/* SVG icon container — matches sidebar logo-icon style */
.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.stat-icon-danger  { background: #fee2e2; color: var(--danger); }
.stat-icon-warning { background: #fef3c7; color: var(--warning); }
.stat-icon-info    { background: #dbeafe; color: var(--primary); }
.stat-icon-success { background: #dcfce7; color: var(--success); }

.stat-info h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.1;
}

.stat-info p {
    margin: 0;
    font-size: 0.75rem;
    color: var(--gray-500);
    margin-top: 0.125rem;
}

/* ── Overdue / Due soon alert lists ─────────────── */
.overdue-list, .due-soon-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.overdue-item, .due-item {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 0.75rem;
    background: white;
    border-radius: 6px;
    font-size: 0.8125rem;
}

.overdue-item strong { color: var(--danger); }

.location {
    color: var(--gray-500);
    font-size: 0.75rem;
}

.last-check {
    color: var(--gray-500);
    font-size: 0.75rem;
    margin-left: auto;
}

/* ── Venue / area quick access ───────────────────── */
.venue-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 0.875rem;
    padding: 1rem;
}

.venue-card {
    padding: 0.875rem;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius);
    background: white;
}

.venue-card h4 {
    margin: 0 0 0.625rem 0;
    font-size: 0.875rem;
    font-weight: 600;
}

.area-links {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.area-link {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.4375rem 0.625rem;
    background: var(--gray-50);
    border-radius: 6px;
    font-size: 0.8125rem;
    text-decoration: none;
    color: var(--gray-700);
    transition: background 0.15s, color 0.15s;
}

.area-link:hover {
    background: var(--primary-light);
    color: var(--primary);
}

/* ── Venue card colour palette ───────────────────── */
/* Each variant: subtle bg tint + left border accent + matching link hover */
.venue-card.vc-blue    { background: #eff6ff; border-color: #bfdbfe; border-left: 3px solid #3b82f6; }
.venue-card.vc-violet  { background: #f5f3ff; border-color: #ddd6fe; border-left: 3px solid #8b5cf6; }
.venue-card.vc-emerald { background: #ecfdf5; border-color: #a7f3d0; border-left: 3px solid #10b981; }
.venue-card.vc-sky     { background: #f0f9ff; border-color: #bae6fd; border-left: 3px solid #0ea5e9; }
.venue-card.vc-amber   { background: #fffbeb; border-color: #fde68a; border-left: 3px solid #f59e0b; }
.venue-card.vc-rose    { background: #fff1f2; border-color: #fecdd3; border-left: 3px solid #f43f5e; }

.venue-card.vc-blue    h4 a { color: #1d4ed8; }
.venue-card.vc-violet  h4 a { color: #6d28d9; }
.venue-card.vc-emerald h4 a { color: #047857; }
.venue-card.vc-sky     h4 a { color: #0284c7; }
.venue-card.vc-amber   h4 a { color: #b45309; }
.venue-card.vc-rose    h4 a { color: #be123c; }

.venue-card.vc-blue    .area-link { background: #dbeafe; color: #1e40af; }
.venue-card.vc-violet  .area-link { background: #ede9fe; color: #5b21b6; }
.venue-card.vc-emerald .area-link { background: #d1fae5; color: #065f46; }
.venue-card.vc-sky     .area-link { background: #e0f2fe; color: #0369a1; }
.venue-card.vc-amber   .area-link { background: #fef3c7; color: #92400e; }
.venue-card.vc-rose    .area-link { background: #ffe4e6; color: #9f1239; }

.venue-card.vc-blue    .area-link:hover { background: #bfdbfe; }
.venue-card.vc-violet  .area-link:hover { background: #ddd6fe; }
.venue-card.vc-emerald .area-link:hover { background: #a7f3d0; }
.venue-card.vc-sky     .area-link:hover { background: #bae6fd; }
.venue-card.vc-amber   .area-link:hover { background: #fde68a; }
.venue-card.vc-rose    .area-link:hover { background: #fecdd3; }

/* Dark mode variants */
.dark-mode .venue-card.vc-blue    { background: rgba(59,130,246,0.1);  border-color: rgba(59,130,246,0.3);  border-left-color: #3b82f6; }
.dark-mode .venue-card.vc-violet  { background: rgba(139,92,246,0.1);  border-color: rgba(139,92,246,0.3);  border-left-color: #8b5cf6; }
.dark-mode .venue-card.vc-emerald { background: rgba(16,185,129,0.1);  border-color: rgba(16,185,129,0.3);  border-left-color: #10b981; }
.dark-mode .venue-card.vc-sky     { background: rgba(14,165,233,0.1);  border-color: rgba(14,165,233,0.3);  border-left-color: #0ea5e9; }
.dark-mode .venue-card.vc-amber   { background: rgba(245,158,11,0.1);  border-color: rgba(245,158,11,0.3);  border-left-color: #f59e0b; }
.dark-mode .venue-card.vc-rose    { background: rgba(244,63,94,0.1);   border-color: rgba(244,63,94,0.3);   border-left-color: #f43f5e; }

.dark-mode .venue-card.vc-blue    h4 a { color: #93c5fd; }
.dark-mode .venue-card.vc-violet  h4 a { color: #c4b5fd; }
.dark-mode .venue-card.vc-emerald h4 a { color: #6ee7b7; }
.dark-mode .venue-card.vc-sky     h4 a { color: #7dd3fc; }
.dark-mode .venue-card.vc-amber   h4 a { color: #fcd34d; }
.dark-mode .venue-card.vc-rose    h4 a { color: #fda4af; }

.dark-mode .venue-card.vc-blue    .area-link { background: rgba(59,130,246,0.15);  color: #93c5fd; }
.dark-mode .venue-card.vc-violet  .area-link { background: rgba(139,92,246,0.15);  color: #c4b5fd; }
.dark-mode .venue-card.vc-emerald .area-link { background: rgba(16,185,129,0.15);  color: #6ee7b7; }
.dark-mode .venue-card.vc-sky     .area-link { background: rgba(14,165,233,0.15);  color: #7dd3fc; }
.dark-mode .venue-card.vc-amber   .area-link { background: rgba(245,158,11,0.15);  color: #fcd34d; }
.dark-mode .venue-card.vc-rose    .area-link { background: rgba(244,63,94,0.15);   color: #fda4af; }

.dark-mode .venue-card.vc-blue    .area-link:hover { background: rgba(59,130,246,0.28); }
.dark-mode .venue-card.vc-violet  .area-link:hover { background: rgba(139,92,246,0.28); }
.dark-mode .venue-card.vc-emerald .area-link:hover { background: rgba(16,185,129,0.28); }
.dark-mode .venue-card.vc-sky     .area-link:hover { background: rgba(14,165,233,0.28); }
.dark-mode .venue-card.vc-amber   .area-link:hover { background: rgba(245,158,11,0.28); }
.dark-mode .venue-card.vc-rose    .area-link:hover { background: rgba(244,63,94,0.28); }

/* ── Recent reports list ─────────────────────────── */
.report-list {
    padding: 0.75rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.report-item {
    padding: 0.625rem 0.75rem;
    border: 1px solid var(--gray-200);
    border-radius: 6px;
    background: white;
    font-size: 0.8125rem;
}

.report-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.375rem;
    font-size: 0.75rem;
    color: var(--gray-500);
}

/* ── Quick action cards ──────────────────────────── */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 0.875rem;
}

.action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.25rem 1rem;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    text-decoration: none;
    color: var(--gray-700);
    transition: transform 0.15s, box-shadow 0.15s;
    border-top: 3px solid transparent;
    gap: 0.625rem;
}

.action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.action-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: var(--gray-100);
}

.action-label {
    font-weight: 600;
    font-size: 0.8125rem;
    text-align: center;
    color: var(--gray-700);
}

.action-primary { border-top-color: var(--primary); }
.action-primary .action-icon { background: #dbeafe; color: var(--primary); }

.action-warning { border-top-color: var(--warning); }
.action-warning .action-icon { background: #fef3c7; color: var(--warning); }

.action-info { border-top-color: #0ea5e9; }
.action-info .action-icon { background: #e0f2fe; color: #0284c7; }

.action-success { border-top-color: var(--success); }
.action-success .action-icon { background: #dcfce7; color: var(--success); }

/* ── Dark mode overrides ─────────────────────────── */
.dark-mode .stat-card          { background: #1e293b; }
.dark-mode .stat-card.stat-critical { background: rgba(220,38,38,0.08); }
.dark-mode .stat-card.stat-warning  { background: rgba(217,119,6,0.08); }
.dark-mode .stat-icon-danger   { background: rgba(220,38,38,0.2);  color: #f87171; }
.dark-mode .stat-icon-warning  { background: rgba(217,119,6,0.2);  color: #fbbf24; }
.dark-mode .stat-icon-info     { background: rgba(37,99,235,0.2);   color: #60a5fa; }
.dark-mode .stat-icon-success  { background: rgba(22,163,74,0.2);   color: #4ade80; }
.dark-mode .stat-info h3       { color: #f1f5f9; }
.dark-mode .stat-info p        { color: #64748b; }

.dark-mode .overdue-item,
.dark-mode .due-item           { background: #0f172a; color: #cbd5e1; }
.dark-mode .overdue-item a,
.dark-mode .due-item a         { color: #e2e8f0; }

/* Base dark venue card — coloured variants override this below */
.dark-mode .venue-card         { background: #0f172a; border-color: #334155; }
.dark-mode .venue-card h4 a    { color: #e2e8f0; }

.dark-mode .area-link          { background: #1e293b; color: #94a3b8; }
.dark-mode .area-link:hover    { background: rgba(37,99,235,0.2); color: #60a5fa; }

.dark-mode .report-item        { background: #0f172a; border-color: #334155; color: #cbd5e1; }
.dark-mode .report-item a      { color: #e2e8f0; }

.dark-mode .action-card        { background: #1e293b; color: #e2e8f0; }
.dark-mode .action-label       { color: #e2e8f0; }
.dark-mode .action-warning .action-icon { background: rgba(217,119,6,0.2);  color: #fbbf24; }
.dark-mode .action-info .action-icon    { background: rgba(14,165,233,0.2); color: #38bdf8; }
.dark-mode .action-primary .action-icon { background: rgba(37,99,235,0.2);  color: #60a5fa; }
.dark-mode .action-success .action-icon { background: rgba(22,163,74,0.2);  color: #4ade80; }

/* ── Responsive ──────────────────────────────────── */
@media (max-width: 768px) {
    .stats-grid           { grid-template-columns: repeat(2, 1fr); }
    .venue-grid           { grid-template-columns: 1fr; padding: 0.75rem; }
    .quick-actions-grid   { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 400px) {
    .stats-grid           { grid-template-columns: 1fr; }
}
</style>

<?php require __DIR__ . '/layout/footer.php'; ?>
