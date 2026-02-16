<?php
$pageTitle = 'Report #' . $report['id'];
$currentPage = 'reports';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <div>
        <a href="/reports" class="breadcrumb-link">← Reports</a>
        <h1>Report #<?= $report['id'] ?></h1>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h3><?= htmlspecialchars($report['title']) ?></h3>
        <span class="badge badge-<?= $report['severity'] ?>"><?= ucfirst($report['severity']) ?></span>
        <span class="badge badge-status-<?= $report['status'] ?>"><?= ucfirst(str_replace('_', ' ', $report['status'])) ?></span>
    </div>
    <div class="card-body">
        <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($report['description'])) ?></p>
        <p><strong>Venue:</strong> <?= htmlspecialchars($report['venue_name']) ?></p>
        <?php if ($report['area_name']): ?>
        <p><strong>Area:</strong> <a href="/areas/<?= $report['area_id'] ?>"><?= htmlspecialchars($report['area_name']) ?></a></p>
        <?php endif; ?>
        <?php if ($report['checkpoint_ref']): ?>
        <p><strong>Check Point:</strong> <?= htmlspecialchars($report['checkpoint_ref']) ?> - <?= htmlspecialchars($report['checkpoint_label']) ?></p>
        <?php endif; ?>
        <p><strong>Created:</strong> <?= date('Y-m-d H:i', strtotime($report['created_at'])) ?> by <?= htmlspecialchars($report['creator_name']) ?></p>
        <?php if ($report['assigned_name']): ?>
        <p><strong>Assigned to:</strong> <?= htmlspecialchars($report['assigned_name']) ?></p>
        <?php endif; ?>
        <?php if ($report['resolved_at']): ?>
        <p><strong>Resolved:</strong> <?= date('Y-m-d H:i', strtotime($report['resolved_at'])) ?> by <?= htmlspecialchars($report['resolver_name']) ?></p>
        <p><strong>Resolution Notes:</strong><br><?= nl2br(htmlspecialchars($report['resolution_notes'])) ?></p>
        <?php endif; ?>
    </div>
</div>

<?php if (Permission::can('edit', 'reports') && $report['status'] !== 'resolved' && $report['status'] !== 'closed'): ?>
<div class="card mb-4">
    <div class="card-header">
        <h3>Update Report</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/reports/<?= $report['id'] ?>/update">
            <?= Csrf::field() ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="severity">Severity</label>
                    <select id="severity" name="severity" class="form-control">
                        <option value="low" <?= $report['severity'] === 'low' ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= $report['severity'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="high" <?= $report['severity'] === 'high' ? 'selected' : '' ?>>High</option>
                        <option value="critical" <?= $report['severity'] === 'critical' ? 'selected' : '' ?>>Critical</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="open" <?= $report['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="in_progress" <?= $report['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="assigned_to">Assign To</label>
                    <select id="assigned_to" name="assigned_to" class="form-control">
                        <option value="">Unassigned</option>
                        <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= $report['assigned_to'] == $user['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['full_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="margin-top: 28px;">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Resolve Report</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/reports/<?= $report['id'] ?>/resolve">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label for="resolution_notes">Resolution Notes <span class="text-danger">*</span></label>
                <textarea id="resolution_notes" name="resolution_notes" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-success">Mark as Resolved</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
