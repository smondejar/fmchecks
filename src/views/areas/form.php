<?php
$isEdit = isset($area);
$pageTitle = $isEdit ? 'Edit Area' : 'Create Area';
$currentPage = 'areas';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <div>
        <a href="<?= $isEdit ? '/areas/' . $area['id'] : '/areas' ?>" class="breadcrumb-link">← Back</a>
        <h1><?= $isEdit ? 'Edit' : 'Create' ?> Area</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? '/areas/' . $area['id'] . '/update' : '/areas/store' ?>" enctype="multipart/form-data">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label for="venue_id">Venue <span class="text-danger">*</span></label>
                <select id="venue_id" name="venue_id" class="form-control" required>
                    <option value="">Select a venue</option>
                    <?php foreach ($venues as $venue): ?>
                    <option value="<?= $venue['id'] ?>"
                        <?= (isset($_GET['venue_id']) && $_GET['venue_id'] == $venue['id']) ||
                            ($isEdit && $area['venue_id'] == $venue['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($venue['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="area_name">Area Name <span class="text-danger">*</span></label>
                <input type="text" id="area_name" name="area_name" class="form-control"
                       value="<?= $isEdit ? htmlspecialchars($area['area_name']) : '' ?>" required>
            </div>

            <?php if (!$isEdit): ?>
            <div class="form-group">
                <label for="pdf_file">PDF Floor Plan <span class="text-danger">*</span></label>
                <input type="file" id="pdf_file" name="pdf_file" class="form-control" accept=".pdf" required>
                <small class="form-text">Upload a PDF floor plan or CAD drawing</small>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?> Area</button>
                <a href="<?= $isEdit ? '/areas/' . $area['id'] : '/areas' ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
