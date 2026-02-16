<?php
$isEdit = isset($venue);
$pageTitle = $isEdit ? 'Edit Venue' : 'Create Venue';
$currentPage = 'venues';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <div>
        <a href="<?= $isEdit ? '/venues/' . $venue['id'] : '/venues' ?>" class="breadcrumb-link">← Back</a>
        <h1><?= $isEdit ? 'Edit' : 'Create' ?> Venue</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? '/venues/' . $venue['id'] . '/update' : '/venues/store' ?>">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label for="name">Venue Name <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name" class="form-control"
                       value="<?= $isEdit ? htmlspecialchars($venue['name']) : '' ?>" required autofocus>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" rows="3"><?= $isEdit ? htmlspecialchars($venue['address']) : '' ?></textarea>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="4"><?= $isEdit ? htmlspecialchars($venue['notes']) : '' ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?> Venue</button>
                <a href="<?= $isEdit ? '/venues/' . $venue['id'] : '/venues' ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
