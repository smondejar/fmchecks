<?php
$isEdit = isset($area);
$pageTitle = $isEdit ? 'Edit Area' : 'Create Area';
$currentPage = 'areas';
$usePdfJs = !$isEdit; // Only load PDF.js for the upload form
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
        <form method="POST" action="<?= $isEdit ? '/areas/' . $area['id'] . '/update' : '/areas/store' ?>" enctype="multipart/form-data" id="areaForm">
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
            <?php
            $allPlansJson       = json_encode($allVenuePlans ?? []);
            $preselectedPlanId  = (int)($_GET['plan_id'] ?? 0);
            $preselectedPlan    = null;
            if ($preselectedPlanId && !empty($allVenuePlans)) {
                foreach ($allVenuePlans as $plans) {
                    foreach ($plans as $p) {
                        if ($p['id'] === $preselectedPlanId) { $preselectedPlan = $p; break 2; }
                    }
                }
            }
            $useLibrary = (bool)$preselectedPlan;
            ?>

            <!-- Floor plan source tabs -->
            <div class="form-group">
                <label>Floor Plan Source</label>
                <div class="plan-source-tabs">
                    <button type="button" class="plan-source-tab <?= $useLibrary ? '' : 'active' ?>" data-source="upload">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        Upload new PDF
                    </button>
                    <button type="button" class="plan-source-tab <?= $useLibrary ? 'active' : '' ?>" data-source="library">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Use from library
                    </button>
                </div>
            </div>

            <!-- Source: upload new PDF -->
            <div id="sourceUpload" <?= $useLibrary ? 'style="display:none"' : '' ?>>
                <div class="form-group">
                    <label for="pdf_file">PDF Floor Plan <span class="text-danger">*</span></label>
                    <input type="file" id="pdf_file" name="pdf_file" class="form-control" accept=".pdf">
                    <small class="form-text">Upload a PDF floor plan or CAD drawing. You can crop it to focus on the relevant area.</small>
                </div>
                <div id="cropSection" style="display:none;" class="crop-section">
                    <div class="form-group">
                        <label>Crop Plan (optional)</label>
                        <p class="form-text">Drag the handles to select the area of the PDF you want to show. Leave as-is to use the full page.</p>
                    </div>
                    <div class="crop-wrapper">
                        <div class="crop-toolbar">
                            <span id="cropDimensions" class="crop-dimensions"></span>
                            <button type="button" class="btn btn-sm btn-secondary" id="resetCrop">Reset to Full Page</button>
                        </div>
                        <div class="crop-canvas-container" id="cropCanvasContainer">
                            <canvas id="cropCanvas"></canvas>
                            <div class="crop-overlay" id="cropOverlay">
                                <div class="crop-box" id="cropBox">
                                    <div class="crop-handle crop-handle-nw" data-handle="nw"></div>
                                    <div class="crop-handle crop-handle-ne" data-handle="ne"></div>
                                    <div class="crop-handle crop-handle-sw" data-handle="sw"></div>
                                    <div class="crop-handle crop-handle-se" data-handle="se"></div>
                                    <div class="crop-handle crop-handle-n"  data-handle="n"></div>
                                    <div class="crop-handle crop-handle-s"  data-handle="s"></div>
                                    <div class="crop-handle crop-handle-w"  data-handle="w"></div>
                                    <div class="crop-handle crop-handle-e"  data-handle="e"></div>
                                    <div class="crop-move-zone" data-handle="move"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="crop_x" id="crop_x" value="0">
                    <input type="hidden" name="crop_y" id="crop_y" value="0">
                    <input type="hidden" name="crop_w" id="crop_w" value="1">
                    <input type="hidden" name="crop_h" id="crop_h" value="1">
                </div>
            </div>

            <!-- Source: use from plan library -->
            <div id="sourceLibrary" <?= $useLibrary ? '' : 'style="display:none"' ?>>
                <input type="hidden" name="plan_id" id="plan_id_input" value="<?= $useLibrary ? $preselectedPlan['id'] : '' ?>">
                <div id="libraryPlans" class="library-plan-picker"></div>
                <p id="libraryEmpty" class="form-text" style="display:none;">
                    No plans in this venue's library yet.
                    <a href="#" id="switchToUpload">Upload a new PDF instead.</a>
                </p>
                <p id="libraryNoVenue" class="form-text" <?= $useLibrary ? 'style="display:none"' : '' ?>>
                    Select a venue above to see its stored plans.
                </p>
            </div>

            <script>
            // All venue plans passed from controller
            var allVenuePlans = <?= $allPlansJson ?>;
            var preselectedPlanId = <?= $preselectedPlanId ?>;
            </script>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?> Area</button>
                <a href="<?= $isEdit ? '/areas/' . $area['id'] : '/areas' ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.crop-section { margin-top: 1rem; }

.crop-wrapper {
    border: 1px solid var(--gray-300);
    border-radius: var(--radius);
    overflow: hidden;
    background: var(--gray-100);
}

.crop-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0.75rem;
    background: var(--gray-200);
    border-bottom: 1px solid var(--gray-300);
    font-size: 0.875rem;
}

.crop-dimensions {
    color: var(--gray-700);
    font-family: monospace;
}

.crop-canvas-container {
    position: relative;
    overflow: auto;
    max-height: 70vh;
    display: inline-block;
    width: 100%;
    text-align: center;
    background: #888;
}

#cropCanvas {
    display: block;
    margin: 0 auto;
}

.crop-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

/* Dark semi-transparent masks outside crop area */
.crop-overlay::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.5);
    pointer-events: none;
}

.crop-box {
    position: absolute;
    border: 2px solid #fff;
    box-shadow:
        0 0 0 9999px rgba(0,0,0,0.5),
        0 0 0 1px rgba(0,0,0,0.3);
    cursor: move;
    pointer-events: all;
}

/* Grid lines inside crop box */
.crop-box::before,
.crop-box::after {
    content: '';
    position: absolute;
    background: rgba(255,255,255,0.3);
}
.crop-box::before {
    top: 33.3%; left: 0; right: 0; height: 1px;
    box-shadow: 0 calc(33.3% / 1 * 1) 0 rgba(255,255,255,0.3);
}
.crop-box::after {
    left: 33.3%; top: 0; bottom: 0; width: 1px;
    box-shadow: calc(33.3% / 1 * 1) 0 0 rgba(255,255,255,0.3);
}

.crop-move-zone {
    position: absolute;
    inset: 10px;
    cursor: move;
}

/* Handles */
.crop-handle {
    position: absolute;
    width: 14px;
    height: 14px;
    background: #fff;
    border: 2px solid var(--primary);
    border-radius: 2px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    pointer-events: all;
}
.crop-handle-nw { top:-7px; left:-7px; cursor:nw-resize; }
.crop-handle-ne { top:-7px; right:-7px; cursor:ne-resize; }
.crop-handle-sw { bottom:-7px; left:-7px; cursor:sw-resize; }
.crop-handle-se { bottom:-7px; right:-7px; cursor:se-resize; }
.crop-handle-n  { top:-7px; left:50%; transform:translateX(-50%); cursor:n-resize; }
.crop-handle-s  { bottom:-7px; left:50%; transform:translateX(-50%); cursor:s-resize; }
.crop-handle-w  { left:-7px; top:50%; transform:translateY(-50%); cursor:w-resize; }
.crop-handle-e  { right:-7px; top:50%; transform:translateY(-50%); cursor:e-resize; }
</style>

<?php if (!$isEdit): ?>
<style>
.plan-source-tabs {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.25rem;
}
.plan-source-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 1rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius);
    background: var(--gray-50);
    color: var(--gray-600);
    font-size: 0.875rem;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s, color 0.15s;
}
.plan-source-tab.active {
    border-color: var(--primary);
    background: var(--primary-light);
    color: var(--primary);
    font-weight: 500;
}
.library-plan-picker {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}
.library-plan-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius);
    cursor: pointer;
    background: #fff;
    transition: border-color 0.15s, background 0.15s;
}
.library-plan-option:hover { border-color: var(--primary); background: var(--primary-light); }
.library-plan-option.selected { border-color: var(--primary); background: var(--primary-light); }
.library-plan-option input[type=radio] { accent-color: var(--primary); }
.library-plan-option-name { font-weight: 500; font-size: 0.9375rem; }
.library-plan-option-preview { margin-left: auto; }
.dark-mode .plan-source-tab { background: #1e293b; border-color: #334155; color: #94a3b8; }
.dark-mode .plan-source-tab.active { background: rgba(37,99,235,0.15); border-color: #3b82f6; color: #93c5fd; }
.dark-mode .library-plan-option { background: #1e293b; border-color: #334155; color: #e2e8f0; }
.dark-mode .library-plan-option.selected,
.dark-mode .library-plan-option:hover { border-color: #3b82f6; background: rgba(37,99,235,0.12); }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput  = document.getElementById('pdf_file');
    const cropSection = document.getElementById('cropSection');
    const cropCanvas  = document.getElementById('cropCanvas');
    const cropBox     = document.getElementById('cropBox');
    const cropOverlay = document.getElementById('cropOverlay');
    const container   = document.getElementById('cropCanvasContainer');
    const dimLabel    = document.getElementById('cropDimensions');
    const resetBtn    = document.getElementById('resetCrop');

    // Hidden inputs
    const inX = document.getElementById('crop_x');
    const inY = document.getElementById('crop_y');
    const inW = document.getElementById('crop_w');
    const inH = document.getElementById('crop_h');

    let pdfPage = null;
    let canvasW = 0, canvasH = 0;
    // Crop in canvas pixels
    let crop = { x: 0, y: 0, w: 0, h: 0 };

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file || file.type !== 'application/pdf') return;

        cropSection.style.display = 'block';

        const reader = new FileReader();
        reader.onload = function(e) {
            pdfjsLib.getDocument({ data: e.target.result }).promise.then(function(pdf) {
                pdf.getPage(1).then(function(page) {
                    pdfPage = page;
                    renderCropPreview();
                });
            });
        };
        reader.readAsArrayBuffer(file);
    });

    function renderCropPreview() {
        const viewport = pdfPage.getViewport({ scale: 1.5 });
        cropCanvas.width  = viewport.width;
        cropCanvas.height = viewport.height;
        canvasW = viewport.width;
        canvasH = viewport.height;

        const ctx = cropCanvas.getContext('2d');
        pdfPage.render({ canvasContext: ctx, viewport }).promise.then(function() {
            resetCropToFull();
        });
    }

    function resetCropToFull() {
        crop = { x: 0, y: 0, w: canvasW, h: canvasH };
        updateCropBox();
        updateHiddenInputs();
    }

    resetBtn.addEventListener('click', resetCropToFull);

    function updateCropBox() {
        // Position the cropBox absolutely relative to the container
        const contRect = container.getBoundingClientRect();
        const canvRect = cropCanvas.getBoundingClientRect();
        const offsetX  = canvRect.left - contRect.left + container.scrollLeft;
        const offsetY  = canvRect.top  - contRect.top  + container.scrollTop;

        cropBox.style.left   = (offsetX + crop.x) + 'px';
        cropBox.style.top    = (offsetY + crop.y) + 'px';
        cropBox.style.width  = crop.w + 'px';
        cropBox.style.height = crop.h + 'px';

        // Dimensions label (pixels relative to canvas — show as % for clarity)
        const pct = v => Math.round(v * 100);
        dimLabel.textContent =
            `x:${pct(crop.x/canvasW)}% y:${pct(crop.y/canvasH)}%  ` +
            `${pct(crop.w/canvasW)}% × ${pct(crop.h/canvasH)}%`;
    }

    function updateHiddenInputs() {
        inX.value = (crop.x / canvasW).toFixed(8);
        inY.value = (crop.y / canvasH).toFixed(8);
        inW.value = (crop.w / canvasW).toFixed(8);
        inH.value = (crop.h / canvasH).toFixed(8);
    }

    // ----- Drag logic -----
    let dragging = false;
    let handle   = null;
    let startMouse = { x: 0, y: 0 };
    let startCrop  = { x: 0, y: 0, w: 0, h: 0 };
    const MIN = 30; // minimum crop size in canvas pixels

    cropBox.addEventListener('mousedown', startDrag);
    cropBox.addEventListener('touchstart', startDrag, { passive: false });

    function startDrag(e) {
        e.preventDefault();
        dragging = true;
        handle = (e.target.dataset.handle) || 'move';
        const pos = getEventPos(e);
        startMouse = pos;
        startCrop  = { ...crop };
        document.addEventListener('mousemove', onDrag);
        document.addEventListener('touchmove',  onDrag,  { passive: false });
        document.addEventListener('mouseup',   stopDrag);
        document.addEventListener('touchend',  stopDrag);
    }

    function onDrag(e) {
        if (!dragging) return;
        e.preventDefault();
        const pos = getEventPos(e);
        const dx  = pos.x - startMouse.x;
        const dy  = pos.y - startMouse.y;
        let { x, y, w, h } = startCrop;

        switch (handle) {
            case 'move':
                x = Math.max(0, Math.min(canvasW - w, x + dx));
                y = Math.max(0, Math.min(canvasH - h, y + dy));
                break;
            case 'se': w = Math.max(MIN, Math.min(canvasW - x, w + dx)); h = Math.max(MIN, Math.min(canvasH - y, h + dy)); break;
            case 'sw': { const nw = Math.max(MIN, Math.min(startCrop.x + startCrop.w, w - dx)); x = startCrop.x + startCrop.w - nw; w = nw; h = Math.max(MIN, Math.min(canvasH - y, h + dy)); break; }
            case 'ne': w = Math.max(MIN, Math.min(canvasW - x, w + dx)); { const nh = Math.max(MIN, Math.min(startCrop.y + startCrop.h, h - dy)); y = startCrop.y + startCrop.h - nh; h = nh; break; }
            case 'nw': { const nw = Math.max(MIN, Math.min(startCrop.x + startCrop.w, w - dx)); x = startCrop.x + startCrop.w - nw; w = nw; const nh = Math.max(MIN, Math.min(startCrop.y + startCrop.h, h - dy)); y = startCrop.y + startCrop.h - nh; h = nh; break; }
            case 'n':  { const nh = Math.max(MIN, Math.min(startCrop.y + startCrop.h, h - dy)); y = startCrop.y + startCrop.h - nh; h = nh; break; }
            case 's':  h = Math.max(MIN, Math.min(canvasH - y, h + dy)); break;
            case 'w':  { const nw = Math.max(MIN, Math.min(startCrop.x + startCrop.w, w - dx)); x = startCrop.x + startCrop.w - nw; w = nw; break; }
            case 'e':  w = Math.max(MIN, Math.min(canvasW - x, w + dx)); break;
        }

        crop = { x, y, w, h };
        updateCropBox();
        updateHiddenInputs();
    }

    function stopDrag() {
        dragging = false;
        document.removeEventListener('mousemove', onDrag);
        document.removeEventListener('touchmove',  onDrag);
        document.removeEventListener('mouseup',   stopDrag);
        document.removeEventListener('touchend',  stopDrag);
    }

    function getEventPos(e) {
        const touch = e.touches ? e.touches[0] : e;
        const contRect = container.getBoundingClientRect();
        return {
            x: touch.clientX - contRect.left + container.scrollLeft,
            y: touch.clientY - contRect.top  + container.scrollTop
        };
    }

    window.addEventListener('resize', function() {
        if (pdfPage) updateCropBox();
    });

    // -------- Plan source tabs & library picker --------
    var venueSelect  = document.getElementById('venue_id');
    var srcUpload    = document.getElementById('sourceUpload');
    var srcLibrary   = document.getElementById('sourceLibrary');
    var planIdInput  = document.getElementById('plan_id_input');
    var libraryPlans = document.getElementById('libraryPlans');
    var libraryEmpty = document.getElementById('libraryEmpty');
    var libraryNoVenue = document.getElementById('libraryNoVenue');
    var fileInput    = document.getElementById('pdf_file');
    var tabs         = document.querySelectorAll('.plan-source-tab');
    var activeSource = srcLibrary && srcLibrary.style.display !== 'none' ? 'library' : 'upload';

    function switchSource(src) {
        activeSource = src;
        tabs.forEach(function(t) { t.classList.toggle('active', t.dataset.source === src); });
        if (srcUpload) srcUpload.style.display  = src === 'upload'  ? '' : 'none';
        if (srcLibrary) srcLibrary.style.display = src === 'library' ? '' : 'none';
        if (fileInput) fileInput.required = src === 'upload';
        if (src === 'library') refreshLibraryPlans();
    }

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() { switchSource(tab.dataset.source); });
    });

    var switchToUploadLink = document.getElementById('switchToUpload');
    if (switchToUploadLink) {
        switchToUploadLink.addEventListener('click', function(e) { e.preventDefault(); switchSource('upload'); });
    }

    function refreshLibraryPlans() {
        if (!libraryPlans) return;
        var vid = venueSelect ? parseInt(venueSelect.value, 10) : 0;
        var plans = (vid && allVenuePlans[vid]) ? allVenuePlans[vid] : [];

        libraryPlans.innerHTML = '';
        if (!vid) {
            if (libraryNoVenue) libraryNoVenue.style.display = '';
            if (libraryEmpty)   libraryEmpty.style.display   = 'none';
            return;
        }
        if (libraryNoVenue) libraryNoVenue.style.display = 'none';

        if (plans.length === 0) {
            if (libraryEmpty) libraryEmpty.style.display = '';
            return;
        }
        if (libraryEmpty) libraryEmpty.style.display = 'none';

        var currentPid = planIdInput ? parseInt(planIdInput.value, 10) : 0;
        if (!currentPid && preselectedPlanId) currentPid = preselectedPlanId;

        plans.forEach(function(plan) {
            var div = document.createElement('div');
            div.className = 'library-plan-option' + (plan.id === currentPid ? ' selected' : '');
            div.innerHTML =
                '<input type="radio" name="_plan_radio" value="' + plan.id + '"' + (plan.id === currentPid ? ' checked' : '') + '>' +
                '<span class="library-plan-option-name">' + escapeHtml(plan.name) + '</span>' +
                '<span class="library-plan-option-preview"><a href="' + escapeHtml(plan.pdf_path) + '" target="_blank" ' +
                  'class="btn btn-sm btn-secondary" onclick="event.stopPropagation()">Preview</a></span>';
            div.addEventListener('click', function() {
                document.querySelectorAll('.library-plan-option').forEach(function(el) { el.classList.remove('selected'); });
                div.classList.add('selected');
                div.querySelector('input[type=radio]').checked = true;
                if (planIdInput) planIdInput.value = plan.id;
            });
            libraryPlans.appendChild(div);
            if (plan.id === currentPid && planIdInput) planIdInput.value = plan.id;
        });
    }

    function escapeHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    if (venueSelect) {
        venueSelect.addEventListener('change', function() {
            if (activeSource === 'library') refreshLibraryPlans();
        });
    }

    // Initialise on load
    switchSource(activeSource);
});
</script>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
