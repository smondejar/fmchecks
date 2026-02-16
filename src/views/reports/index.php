<?php
$pageTitle = 'Reports';
$currentPage = 'reports';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <h1>Reports</h1>
    <?php if (Permission::can('create', 'reports')): ?>
    <a href="/reports/create" class="btn btn-primary">Create Report</a>
    <?php endif; ?>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="/reports" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="venue_id">Venue</label>
                    <select id="venue_id" name="venue_id" class="form-control">
                        <option value="">All Venues</option>
                        <?php foreach ($venues as $v): ?>
                        <option value="<?= $v['id'] ?>" <?= ($_GET['venue_id'] ?? '') == $v['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="severity">Severity</label>
                    <select id="severity" name="severity" class="form-control">
                        <option value="">All</option>
                        <option value="low" <?= ($_GET['severity'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= ($_GET['severity'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="high" <?= ($_GET['severity'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                        <option value="critical" <?= ($_GET['severity'] ?? '') === 'critical' ? 'selected' : '' ?>>Critical</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All</option>
                        <option value="open" <?= ($_GET['status'] ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="in_progress" <?= ($_GET['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="resolved" <?= ($_GET['status'] ?? '') === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                        <option value="closed" <?= ($_GET['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="margin-top: 28px;">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (empty($reports)): ?>
<div class="card">
    <div class="card-body text-center">
        <p>No reports found.</p>
    </div>
</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Venue</th>
                <th>Severity</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reports as $report): ?>
            <tr>
                <td>#<?= $report['id'] ?></td>
                <td><a href="/reports/<?= $report['id'] ?>"><?= htmlspecialchars($report['title']) ?></a></td>
                <td><?= htmlspecialchars($report['venue_name']) ?></td>
                <td><span class="badge badge-<?= $report['severity'] ?>"><?= ucfirst($report['severity']) ?></span></td>
                <td><span class="badge badge-status-<?= $report['status'] ?>"><?= ucfirst(str_replace('_', ' ', $report['status'])) ?></span></td>
                <td><?= date('Y-m-d H:i', strtotime($report['created_at'])) ?></td>
                <td>
                    <a href="/reports/<?= $report['id'] ?>" class="btn btn-sm btn-primary">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
