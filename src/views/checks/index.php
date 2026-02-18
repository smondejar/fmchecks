<?php
$pageTitle = 'Check Logs';
$currentPage = 'checks';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <h1>Check Logs</h1>
    <a href="/analytics" class="btn btn-secondary btn-sm">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:4px"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>
        Analytics
    </a>
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
                    <label for="date_from">From</label>
                    <input type="date" id="date_from" name="date_from" class="form-control"
                           value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="date_to">To</label>
                    <input type="date" id="date_to" name="date_to" class="form-control"
                           value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                </div>
                <div class="form-group" style="display:flex;align-items:flex-end;gap:0.5rem;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <?php
                    $exportParams = http_build_query(array_filter([
                        'venue_id'  => $_GET['venue_id'] ?? '',
                        'status'    => $_GET['status'] ?? '',
                        'date_from' => $_GET['date_from'] ?? '',
                        'date_to'   => $_GET['date_to'] ?? '',
                    ]));
                    ?>
                    <a href="/checks/export<?= $exportParams ? '?' . $exportParams : '' ?>" class="btn btn-secondary">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:4px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export CSV
                    </a>
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
                <td><?= htmlspecialchars($log['reference'] ?? '') ?> - <?= htmlspecialchars($log['label'] ?? '') ?></td>
                <td><?= htmlspecialchars($log['area_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($log['venue_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($log['type_name'] ?? '') ?></td>
                <td><span class="badge badge-<?= $log['status'] ?>"><?= ucfirst($log['status']) ?></span></td>
                <td><?= htmlspecialchars($log['performer_name'] ?? '') ?></td>
                <td><?= date('Y-m-d H:i', strtotime($log['performed_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
