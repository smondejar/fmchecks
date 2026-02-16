<?php
$isEdit = isset($user);
$pageTitle = $isEdit ? 'Edit User' : 'Create User';
$currentPage = 'users';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <div>
        <a href="/users" class="breadcrumb-link">← Users</a>
        <h1><?= $isEdit ? 'Edit' : 'Create' ?> User</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? '/users/' . $user['id'] . '/update' : '/users/store' ?>">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label for="username">Username <span class="text-danger">*</span></label>
                <input type="text" id="username" name="username" class="form-control"
                       value="<?= $isEdit ? htmlspecialchars($user['username']) : '' ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="email">Email <span class="text-danger">*</span></label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= $isEdit ? htmlspecialchars($user['email']) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="full_name">Full Name <span class="text-danger">*</span></label>
                <input type="text" id="full_name" name="full_name" class="form-control"
                       value="<?= $isEdit ? htmlspecialchars($user['full_name']) : '' ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password <?= $isEdit ? '(leave blank to keep current)' : '<span class="text-danger">*</span>' ?></label>
                <input type="password" id="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?>>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" class="form-control">
                    <?php foreach ($roles as $r): ?>
                    <option value="<?= $r ?>" <?= ($isEdit && $user['role'] === $r) ? 'selected' : '' ?>>
                        <?= ucfirst($r) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" <?= ($isEdit && $user['is_active']) || !$isEdit ? 'checked' : '' ?>>
                    Active
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?> User</button>
                <a href="/users" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
