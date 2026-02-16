<?php
$pageTitle = 'Check Types';
$currentPage = 'check_types';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <h1>Check Types</h1>
    <?php if (Permission::can('create', 'check_types')): ?>
    <a href="/check-types/create" class="btn btn-primary">Add Check Type</a>
    <?php endif; ?>
</div>

<?php if (empty($checkTypes)): ?>
<div class="card">
    <div class="card-body text-center">
        <p>No check types yet.</p>
        <?php if (Permission::can('create', 'check_types')): ?>
        <a href="/check-types/create" class="btn btn-primary">Create Your First Check Type</a>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="grid-layout">
    <?php foreach ($checkTypes as $type): ?>
    <div class="card check-type-card">
        <div class="card-body">
            <div class="check-type-icon" style="background-color: <?= htmlspecialchars($type['colour']) ?>">
                <?= $type['icon'] ?: '✓' ?>
            </div>
            <h3><?= htmlspecialchars($type['name']) ?></h3>
            <p class="text-muted">Colour: <?= htmlspecialchars($type['colour']) ?></p>
            <div class="card-actions mt-3">
                <?php if (Permission::can('edit', 'check_types')): ?>
                <a href="/check-types/<?= $type['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                <?php endif; ?>
                <?php if (Permission::can('delete', 'check_types')): ?>
                <form method="POST" action="/check-types/<?= $type['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this check type?')">
                    <?= Csrf::field() ?>
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
