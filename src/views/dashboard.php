<?php
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
require __DIR__ . '/layout/header.php';

// Get dashboard stats
$pdo = Database::connect();

// Venues and areas
$venues = Venue::all();
$areas = Area::all();
$venueCount = count($venues);
$areaCount = count($areas);

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
        <div class="stat-card">
            <div class="stat-icon">🏢</div>
            <div class="stat-info">
                <h3><?= $venueCount ?></h3>
                <p>Venues</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📐</div>
            <div class="stat-info">
                <h3><?= $areaCount ?></h3>
                <p>Areas</p>
            </div>
        </div>

        <div class="stat-card <?= $overdueCount > 0 ? 'stat-critical' : '' ?>">
            <div class="stat-icon">⚠️</div>
            <div class="stat-info">
                <h3><?= $overdueCount ?></h3>
                <p>Overdue Checks</p>
            </div>
        </div>

        <div class="stat-card <?= $dueSoonCount > 0 ? 'stat-warning' : '' ?>">
            <div class="stat-icon">⏰</div>
            <div class="stat-info">
                <h3><?= $dueSoonCount ?></h3>
                <p>Due Soon</p>
            </div>
        </div>

        <div class="stat-card <?= $openReports > 0 ? 'stat-warning' : '' ?>">
            <div class="stat-icon">📋</div>
            <div class="stat-info">
                <h3><?= $openReports ?></h3>
                <p>Open Reports</p>
            </div>
        </div>

        <div class="stat-card <?= $criticalReports > 0 ? 'stat-critical' : '' ?>">
            <div class="stat-icon">🚨</div>
            <div class="stat-info">
                <h3><?= $criticalReports ?></h3>
                <p>Critical Issues</p>
            </div>
        </div>
    </div>

    <!-- Overdue Checks Alert -->
    <?php if (!empty($overdueChecks)): ?>
    <div class="alert alert-danger mt-4">
        <h4>⚠️ <?= $overdueCount ?> Overdue Check<?= $overdueCount != 1 ? 's' : '' ?></h4>
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
        <h4>⏰ <?= $dueSoonCount ?> Check<?= $dueSoonCount != 1 ? 's' : '' ?> Due Soon</h4>
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
            <h3>📍 Quick Access</h3>
        </div>
        <div class="venue-grid">
            <?php foreach ($venues as $venue):
                $venueAreas = array_filter($areas, fn($a) => $a['venue_id'] == $venue['id']);
            ?>
            <div class="venue-card">
                <h4><a href="/venues/<?= $venue['id'] ?>"><?= htmlspecialchars($venue['name']) ?></a></h4>
                <?php if (!empty($venueAreas)): ?>
                <div class="area-links">
                    <?php foreach ($venueAreas as $area): ?>
                    <a href="/areas/<?= $area['id'] ?>" class="area-link">
                        📐 <?= htmlspecialchars($area['area_name']) ?>
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
            <h3>📋 Recent Issues</h3>
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
        <?php if (Permission::can('create', 'checks')): ?>
        <a href="/checks" class="action-card action-primary">
            <div class="action-icon">✓</div>
            <div class="action-label">Perform Checks</div>
        </a>
        <?php endif; ?>

        <?php if (Permission::can('create', 'reports')): ?>
        <a href="/reports/create" class="action-card action-warning">
            <div class="action-icon">📝</div>
            <div class="action-label">Report Issue</div>
        </a>
        <?php endif; ?>

        <?php if (Permission::can('view', 'reports')): ?>
        <a href="/reports" class="action-card action-info">
            <div class="action-icon">📊</div>
            <div class="action-label">View Reports</div>
        </a>
        <?php endif; ?>

        <?php if (Permission::can('create', 'venues')): ?>
        <a href="/venues/create" class="action-card action-success">
            <div class="action-icon">➕</div>
            <div class="action-label">New Venue</div>
        </a>
        <?php endif; ?>
    </div>
</div>

<style>
/* Dashboard mobile-optimized styles */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.stat-card {
    background: white;
    border-radius: var(--radius);
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: var(--shadow);
}

.stat-card.stat-critical {
    background: #fef2f2;
    border-left: 3px solid var(--danger);
}

.stat-card.stat-warning {
    background: #fef3c7;
    border-left: 3px solid var(--warning);
}

.stat-icon {
    font-size: 2rem;
}

.stat-info h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
}

.stat-info p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.overdue-list, .due-soon-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-top: 1rem;
}

.overdue-item, .due-item {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: white;
    border-radius: var(--radius);
    font-size: 0.9rem;
}

.overdue-item strong, .due-item strong {
    color: var(--danger);
}

.location {
    color: var(--gray-600);
    font-size: 0.875rem;
}

.last-check {
    color: var(--gray-500);
    font-size: 0.875rem;
    margin-left: auto;
}

.venue-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
    padding: 1rem;
}

.venue-card {
    padding: 1rem;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius);
}

.venue-card h4 {
    margin: 0 0 0.75rem 0;
}

.area-links {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.area-link {
    padding: 0.5rem;
    background: var(--gray-50);
    border-radius: var(--radius);
    font-size: 0.875rem;
    text-decoration: none;
    color: var(--gray-800);
    transition: background 0.2s;
}

.area-link:hover {
    background: var(--primary-light);
}

.report-list {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.report-item {
    padding: 0.75rem;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius);
}

.report-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
    font-size: 0.875rem;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
}

.action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.5rem 1rem;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    text-decoration: none;
    color: var(--gray-800);
    transition: transform 0.2s, box-shadow 0.2s;
}

.action-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.action-icon {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.action-label {
    font-weight: 600;
    font-size: 0.875rem;
    text-align: center;
}

.action-primary { border-top: 3px solid var(--primary); }
.action-warning { border-top: 3px solid var(--warning); }
.action-info { border-top: 3px solid #0ea5e9; }
.action-success { border-top: 3px solid var(--success); }

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .venue-grid {
        grid-template-columns: 1fr;
    }

    .quick-actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<?php require __DIR__ . '/layout/footer.php'; ?>
