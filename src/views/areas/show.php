<?php
$pageTitle = htmlspecialchars($area['area_name']);
$currentPage = 'areas';
$usePdfJs = true;
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <div>
        <a href="/venues/<?= $area['venue_id'] ?>" class="breadcrumb-link">← <?= htmlspecialchars($area['venue_name']) ?></a>
        <h1><?= htmlspecialchars($area['area_name']) ?></h1>
    </div>
    <div>
        <?php if (Permission::can('edit', 'areas')): ?>
        <a href="/areas/<?= $area['id'] ?>/edit" class="btn btn-secondary">Edit</a>
        <?php endif; ?>
    </div>
</div>

<div class="area-viewer">
    <div class="area-controls">
        <button class="btn btn-sm" id="zoomIn">🔍+</button>
        <button class="btn btn-sm" id="zoomOut">🔍-</button>
        <button class="btn btn-sm" id="zoomReset">Reset</button>
        <?php if (Permission::can('create', 'checks') && !empty($checkTypes)): ?>
        <button class="btn btn-sm btn-primary" id="addCheckPoint">Add Check Point</button>
        <?php endif; ?>
    </div>

    <div class="canvas-container">
        <canvas id="pdfCanvas"></canvas>
    </div>

    <?php if (!empty($checkPoints)): ?>
    <div class="checkpoint-legend">
        <h4>Check Points (<?= count($checkPoints) ?>)</h4>
        <div class="legend-items">
            <div class="legend-item"><span class="status-dot status-ok"></span> Up to date</div>
            <div class="legend-item"><span class="status-dot status-due-soon"></span> Due soon</div>
            <div class="legend-item"><span class="status-dot status-overdue"></span> Overdue</div>
            <div class="legend-item"><span class="status-dot status-never"></span> Never checked</div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Check Point Modal -->
<div class="modal" id="checkPointModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Check Point</h3>
            <button class="btn-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Dynamic content -->
        </div>
    </div>
</div>

<script>
const area = <?= json_encode($area) ?>;
const checkPoints = <?= json_encode($checkPoints) ?>;
const checkTypes = <?= json_encode($checkTypes) ?>;
const canPerformChecks = <?= Permission::can('create', 'checks') ? 'true' : 'false' ?>;
const csrfToken = '<?= $_SESSION['csrf_token'] ?>';
</script>
<script src="/js/area-viewer.js"></script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
