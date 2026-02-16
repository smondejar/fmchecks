<?php
$pageTitle = htmlspecialchars($venue['name']);
$currentPage = 'venues';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <div>
        <a href="/venues" class="breadcrumb-link">← Venues</a>
        <h1><?= htmlspecialchars($venue['name']) ?></h1>
    </div>
    <div>
        <?php if (Permission::can('edit', 'venues')): ?>
        <a href="/venues/<?= $venue['id'] ?>/edit" class="btn btn-secondary">Edit</a>
        <?php endif; ?>
        <?php if (Permission::can('create', 'areas')): ?>
        <a href="/areas/create?venue_id=<?= $venue['id'] ?>" class="btn btn-primary">Add Area</a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h3>Venue Details</h3>
    </div>
    <div class="card-body">
        <?php if ($venue['address']): ?>
        <p><strong>Address:</strong><br><?= htmlspecialchars($venue['address']) ?></p>
        <?php endif; ?>
        <?php if ($venue['notes']): ?>
        <p><strong>Notes:</strong><br><?= nl2br(htmlspecialchars($venue['notes'])) ?></p>
        <?php endif; ?>
        <p><strong>Created:</strong> <?= date('Y-m-d H:i', strtotime($venue['created_at'])) ?> by <?= htmlspecialchars($venue['creator_name']) ?></p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Areas (<?= count($areas) ?>)</h3>
    </div>
    <?php if (empty($areas)): ?>
    <div class="card-body text-center">
        <p>No areas in this venue yet.</p>
        <?php if (Permission::can('create', 'areas')): ?>
        <a href="/areas/create?venue_id=<?= $venue['id'] ?>" class="btn btn-primary">Add First Area</a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Area Name</th>
                    <th>Check Points</th>
                    <th>Uploaded By</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($areas as $area): ?>
                <tr>
                    <td><a href="/areas/<?= $area['id'] ?>"><?= htmlspecialchars($area['area_name']) ?></a></td>
                    <td><?= $area['checkpoint_count'] ?></td>
                    <td><?= htmlspecialchars($area['uploader_name']) ?></td>
                    <td><?= date('Y-m-d', strtotime($area['created_at'])) ?></td>
                    <td>
                        <a href="/areas/<?= $area['id'] ?>" class="btn btn-sm btn-primary">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
