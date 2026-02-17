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
            <div class="form-group">
                <label for="pdf_file">PDF Floor Plan <span class="text-danger">*</span></label>
                <input type="file" id="pdf_file" name="pdf_file" class="form-control" accept=".pdf" required>
                <small class="form-text">Upload a PDF floor plan or CAD drawing. You can crop it to focus on the relevant area.</small>
            </div>

            <!-- PDF Crop UI (shown after file is selected) -->
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
                        <!-- Crop overlay -->
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

                <!-- Hidden crop coordinate inputs -->
                <input type="hidden" name="crop_x" id="crop_x" value="0">
                <input type="hidden" name="crop_y" id="crop_y" value="0">
                <input type="hidden" name="crop_w" id="crop_w" value="1">
                <input type="hidden" name="crop_h" id="crop_h" value="1">
            </div>
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
});
</script>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
