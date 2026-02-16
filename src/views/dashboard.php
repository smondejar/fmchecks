<?php
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
require __DIR__ . '/layout/header.php';

// Get dashboard stats
$venueCount = count(Venue::all());
$areaCount = count(Area::all());
$openReports = Report::getOpenCount();
$criticalReports = Report::getCriticalCount();
$recentReports = Report::all(['status' => 'open']);
$recentReports = array_slice($recentReports, 0, 5);
?>

<div class="dashboard">
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

        <div class="stat-card">
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

    <?php if (!empty($recentReports)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h3>Recent Open Reports</h3>
            <a href="/reports" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Venue</th>
                        <th>Severity</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentReports as $report): ?>
                    <tr>
                        <td>#<?= $report['id'] ?></td>
                        <td><a href="/reports/<?= $report['id'] ?>"><?= htmlspecialchars($report['title']) ?></a></td>
                        <td><?= htmlspecialchars($report['venue_name']) ?></td>
                        <td><span class="badge badge-<?= $report['severity'] ?>"><?= ucfirst($report['severity']) ?></span></td>
                        <td><?= date('Y-m-d H:i', strtotime($report['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if (Permission::can('view', 'venues')): ?>
    <div class="quick-actions mt-4">
        <h3>Quick Actions</h3>
        <div class="action-buttons">
            <?php if (Permission::can('create', 'venues')): ?>
            <a href="/venues/create" class="btn btn-primary">New Venue</a>
            <?php endif; ?>
            <?php if (Permission::can('create', 'areas')): ?>
            <a href="/areas/create" class="btn btn-primary">New Area</a>
            <?php endif; ?>
            <?php if (Permission::can('create', 'reports')): ?>
            <a href="/reports/create" class="btn btn-warning">Create Report</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
