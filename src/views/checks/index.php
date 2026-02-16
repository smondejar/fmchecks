<?php
$pageTitle = 'Check Logs';
$currentPage = 'checks';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <h1>Check Logs</h1>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="/checks" class="filter-form">
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
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="">All</option>
                        <option value="pass" <?= ($_GET['status'] ?? '') === 'pass' ? 'selected' : '' ?>>Pass</option>
                        <option value="fail" <?= ($_GET['status'] ?? '') === 'fail' ? 'selected' : '' ?>>Fail</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="margin-top: 28px;">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (empty($checkLogs)): ?>
<div class="card">
    <div class="card-body text-center">
        <p>No check logs found.</p>
    </div>
</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Check Point</th>
                <th>Area</th>
                <th>Venue</th>
                <th>Type</th>
                <th>Status</th>
                <th>Performed By</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($checkLogs as $log): ?>
            <tr>
                <td>#<?= $log['id'] ?></td>
                <td><?= htmlspecialchars($log['reference']) ?> - <?= htmlspecialchars($log['label']) ?></td>
                <td><?= htmlspecialchars($log['area_name']) ?></td>
                <td><?= htmlspecialchars($log['venue_name']) ?></td>
                <td><?= htmlspecialchars($log['type_name']) ?></td>
                <td><span class="badge badge-<?= $log['status'] ?>"><?= ucfirst($log['status']) ?></span></td>
                <td><?= htmlspecialchars($log['performer_name']) ?></td>
                <td><?= date('Y-m-d H:i', strtotime($log['performed_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
