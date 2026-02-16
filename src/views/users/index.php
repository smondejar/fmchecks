<?php
$pageTitle = 'Users';
$currentPage = 'users';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <h1>Users</h1>
    <?php if (Permission::can('create', 'users')): ?>
    <a href="/users/create" class="btn btn-primary">Add User</a>
    <?php endif; ?>
</div>

<?php if (empty($users)): ?>
<div class="card">
    <div class="card-body text-center">
        <p>No users found.</p>
    </div>
</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td>#<?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['full_name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><span class="badge badge-role-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span></td>
                <td><span class="badge badge-<?= $user['is_active'] ? 'success' : 'danger' ?>"><?= $user['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                <td>
                    <?php if (Permission::can('edit', 'users')): ?>
                    <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                    <?php endif; ?>
                    <?php if (Permission::can('delete', 'users') && $user['id'] != Auth::id()): ?>
                    <form method="POST" action="/users/<?= $user['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this user?')">
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
