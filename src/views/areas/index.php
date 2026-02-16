<?php
$pageTitle = 'Areas';
$currentPage = 'areas';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <h1>Areas</h1>
    <?php if (Permission::can('create', 'areas')): ?>
    <a href="/areas/create" class="btn btn-primary">Add Area</a>
    <?php endif; ?>
</div>

<?php if (empty($areas)): ?>
<div class="card">
    <div class="card-body text-center">
        <p>No areas yet.</p>
        <?php if (Permission::can('create', 'areas')): ?>
        <a href="/areas/create" class="btn btn-primary">Create Your First Area</a>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Area Name</th>
                <th>Venue</th>
                <th>Uploaded By</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($areas as $area): ?>
            <tr>
                <td><a href="/areas/<?= $area['id'] ?>"><?= htmlspecialchars($area['area_name']) ?></a></td>
                <td><?= htmlspecialchars($area['venue_name']) ?></td>
                <td><?= htmlspecialchars($area['uploader_name']) ?></td>
                <td><?= date('Y-m-d', strtotime($area['created_at'])) ?></td>
                <td>
                    <a href="/areas/<?= $area['id'] ?>" class="btn btn-sm btn-primary">View</a>
                    <?php if (Permission::can('edit', 'areas')): ?>
                    <a href="/areas/<?= $area['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                    <?php endif; ?>
                    <?php if (Permission::can('delete', 'areas')): ?>
                    <form method="POST" action="/areas/<?= $area['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this area?')">
                        <?= Csrf::field() ?>
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
