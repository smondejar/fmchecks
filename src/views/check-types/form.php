<?php
$isEdit = isset($checkType);
$pageTitle = $isEdit ? 'Edit Check Type' : 'Create Check Type';
$currentPage = 'check_types';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <div>
        <a href="/check-types" class="breadcrumb-link">← Check Types</a>
        <h1><?= $isEdit ? 'Edit' : 'Create' ?> Check Type</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? '/check-types/' . $checkType['id'] . '/update' : '/check-types/store' ?>">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label for="name">Name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" class="form-control"
                       value="<?= $isEdit ? htmlspecialchars($checkType['name']) : '' ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="colour">Colour</label>
                <input type="color" id="colour" name="colour" class="form-control"
                       value="<?= $isEdit ? htmlspecialchars($checkType['colour']) : '#2563eb' ?>">
            </div>

            <div class="form-group">
                <label for="icon">Icon (Emoji)</label>
                <input type="text" id="icon" name="icon" class="form-control"
                       value="<?= $isEdit ? htmlspecialchars($checkType['icon']) : '' ?>" placeholder="⚡">
                <small class="form-text">Optional: Enter an emoji to represent this check type</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?> Check Type</button>
                <a href="/check-types" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
