<?php
$pageTitle = 'Venues';
$currentPage = 'venues';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <h1>Venues</h1>
    <?php if (Permission::can('create', 'venues')): ?>
    <a href="/venues/create" class="btn btn-primary">Add Venue</a>
    <?php endif; ?>
</div>

<?php if (empty($venues)): ?>
<div class="card">
    <div class="card-body text-center">
        <p>No venues yet.</p>
        <?php if (Permission::can('create', 'venues')): ?>
        <a href="/venues/create" class="btn btn-primary">Create Your First Venue</a>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="grid-layout">
    <?php foreach ($venues as $venue): ?>
    <div class="card venue-card">
        <div class="card-body">
            <h3><a href="/venues/<?= $venue['id'] ?>"><?= htmlspecialchars($venue['name']) ?></a></h3>
            <?php if ($venue['address']): ?>
            <p class="text-muted"><small>📍 <?= htmlspecialchars($venue['address']) ?></small></p>
            <?php endif; ?>
            <div class="venue-stats">
                <span class="badge"><?= $venue['area_count'] ?> Areas</span>
            </div>
            <?php if ($venue['notes']): ?>
            <p class="mt-2"><?= htmlspecialchars(substr($venue['notes'], 0, 100)) ?><?= strlen($venue['notes']) > 100 ? '...' : '' ?></p>
            <?php endif; ?>
            <div class="card-actions mt-3">
                <a href="/venues/<?= $venue['id'] ?>" class="btn btn-sm btn-primary">View</a>
                <?php if (Permission::can('edit', 'venues')): ?>
                <a href="/venues/<?= $venue['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                <?php endif; ?>
                <?php if (Permission::can('delete', 'venues')): ?>
                <form method="POST" action="/venues/<?= $venue['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this venue and all its areas?')">
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
