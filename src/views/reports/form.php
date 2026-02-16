<?php
$pageTitle = 'Create Report';
$currentPage = 'reports';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <div>
        <a href="/reports" class="breadcrumb-link">← Reports</a>
        <h1>Create Report</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/reports/store">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label for="venue_id">Venue <span class="text-danger">*</span></label>
                <select id="venue_id" name="venue_id" class="form-control" required>
                    <option value="">Select a venue</option>
                    <?php foreach ($venues as $venue): ?>
                    <option value="<?= $venue['id'] ?>"><?= htmlspecialchars($venue['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="title">Title <span class="text-danger">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required autofocus>
            </div>

            <div class="form-group">
                <label for="description">Description <span class="text-danger">*</span></label>
                <textarea id="description" name="description" class="form-control" rows="6" required></textarea>
            </div>

            <div class="form-group">
                <label for="severity">Severity</label>
                <select id="severity" name="severity" class="form-control">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Report</button>
                <a href="/reports" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
