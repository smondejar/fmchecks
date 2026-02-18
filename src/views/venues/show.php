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

<!-- Plan Library -->
<?php if (Permission::can('edit', 'venues') || !empty($venuePlans)): ?>
<div class="card mb-4">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h3>Plan Library (<?= count($venuePlans) ?>)</h3>
        <?php if (Permission::can('edit', 'venues')): ?>
        <button type="button" class="btn btn-sm btn-primary" id="showUploadPlan">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:3px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Upload Plan
        </button>
        <?php endif; ?>
    </div>

    <?php if (Permission::can('edit', 'venues')): ?>
    <div id="uploadPlanForm" style="display:none;border-bottom:1px solid var(--gray-200);">
        <div class="card-body" style="padding-bottom:1rem;">
            <form method="POST" action="/venues/<?= $venue['id'] ?>/plans/store" enctype="multipart/form-data">
                <?= Csrf::field() ?>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label for="plan_name">Plan Name <span class="text-danger">*</span></label>
                        <input type="text" id="plan_name" name="plan_name" class="form-control"
                               placeholder="e.g. Ground Floor, Fire Escape Routes" required>
                    </div>
                    <div class="form-group" style="flex:2;">
                        <label for="plan_pdf">PDF File <span class="text-danger">*</span></label>
                        <input type="file" id="plan_pdf" name="plan_pdf" class="form-control" accept=".pdf" required>
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;gap:0.5rem;">
                        <button type="submit" class="btn btn-primary">Upload</button>
                        <button type="button" class="btn btn-secondary" id="cancelUploadPlan">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($venuePlans)): ?>
    <div class="card-body text-center">
        <p style="color:var(--gray-500);margin:0;">No plans in the library yet. Upload PDF floor plans to reuse them when creating areas.</p>
    </div>
    <?php else: ?>
    <div class="plan-library-grid">
        <?php foreach ($venuePlans as $plan): ?>
        <div class="plan-library-item">
            <div class="plan-library-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <div class="plan-library-info">
                <span class="plan-library-name"><?= htmlspecialchars($plan['name']) ?></span>
                <span class="plan-library-meta">Uploaded by <?= htmlspecialchars($plan['uploader_name']) ?> &middot; <?= date('Y-m-d', strtotime($plan['created_at'])) ?></span>
            </div>
            <div class="plan-library-actions">
                <a href="<?= htmlspecialchars($plan['pdf_path']) ?>" target="_blank" class="btn btn-sm btn-secondary" title="Preview PDF">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                <a href="/areas/create?venue_id=<?= $venue['id'] ?>&plan_id=<?= $plan['id'] ?>" class="btn btn-sm btn-primary" title="Create area from this plan">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Use
                </a>
                <?php if (Permission::can('edit', 'venues')): ?>
                <form method="POST" action="/venues/<?= $venue['id'] ?>/plans/<?= $plan['id'] ?>/delete"
                      onsubmit="return confirm('Remove this plan from the library?')" style="display:inline;">
                    <?= Csrf::field() ?>
                    <button type="submit" class="btn btn-sm btn-danger" title="Remove from library">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Areas -->
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

<style>
.plan-library-grid {
    display: flex;
    flex-direction: column;
    gap: 0;
}
.plan-library-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.875rem 1.25rem;
    border-top: 1px solid var(--gray-100);
}
.plan-library-item:first-child { border-top: none; }
.plan-library-icon {
    flex-shrink: 0;
    width: 44px;
    height: 44px;
    border-radius: var(--radius);
    background: var(--primary-light);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
}
.plan-library-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}
.plan-library-name {
    font-weight: 600;
    font-size: 0.9375rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.plan-library-meta {
    font-size: 0.75rem;
    color: var(--gray-500);
}
.plan-library-actions {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    flex-shrink: 0;
}
.dark-mode .plan-library-item { border-color: #334155; }
.dark-mode .plan-library-icon { background: rgba(37,99,235,0.15); color: #93c5fd; }
.dark-mode .plan-library-name { color: #e2e8f0; }
.dark-mode .plan-library-meta { color: #64748b; }
.dark-mode #uploadPlanForm { border-color: #334155; }
</style>

<script>
(function() {
    const showBtn   = document.getElementById('showUploadPlan');
    const cancelBtn = document.getElementById('cancelUploadPlan');
    const form      = document.getElementById('uploadPlanForm');
    if (showBtn && form) {
        showBtn.addEventListener('click', function() { form.style.display = ''; showBtn.style.display = 'none'; });
        cancelBtn.addEventListener('click', function() { form.style.display = 'none'; showBtn.style.display = ''; });
    }
})();
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
