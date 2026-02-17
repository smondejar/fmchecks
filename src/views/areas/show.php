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
        <?php if (Permission::can('edit', 'areas')): ?>
        <button class="btn btn-sm btn-secondary" id="adjustCrop">✂️ Crop</button>
        <?php endif; ?>
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

<!-- Crop Modal -->
<div class="modal" id="cropModal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3>✂️ Adjust Crop</h3>
            <button class="btn-close" onclick="closeCropModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p class="form-text" style="margin-bottom:0.75rem;">Drag the handles to select the visible area of the plan. The crop is applied immediately for all users.</p>
            <div class="crop-toolbar">
                <span id="cropDimLive" style="font-family:monospace;font-size:0.875rem;color:var(--gray-600);"></span>
                <div>
                    <button type="button" class="btn btn-sm btn-secondary" id="resetCropBtn">Reset to Full Page</button>
                    <button type="button" class="btn btn-sm btn-primary" id="saveCropBtn">Save Crop</button>
                </div>
            </div>
            <div class="crop-canvas-container" id="liveCropContainer" style="margin-top:0.5rem;">
                <canvas id="liveCropCanvas"></canvas>
                <div class="crop-overlay">
                    <div class="crop-box" id="liveCropBox">
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
    </div>
</div>

<style>
/* Reuse the crop UI styles from form.php */
.modal-large { max-width: 90vw; }
.crop-canvas-container { position:relative; overflow:auto; max-height:65vh; display:inline-block; width:100%; text-align:center; background:#888; border-radius:var(--radius); }
.crop-overlay { position:absolute; top:0; left:0; right:0; bottom:0; pointer-events:none; }
.crop-overlay::before { content:''; position:absolute; inset:0; background:rgba(0,0,0,0.5); pointer-events:none; }
.crop-box { position:absolute; border:2px solid #fff; box-shadow:0 0 0 9999px rgba(0,0,0,0.5),0 0 0 1px rgba(0,0,0,0.3); cursor:move; pointer-events:all; }
.crop-toolbar { display:flex; justify-content:space-between; align-items:center; gap:0.5rem; padding:0.5rem 0; flex-wrap:wrap; }
.crop-handle { position:absolute; width:14px; height:14px; background:#fff; border:2px solid var(--primary); border-radius:2px; box-shadow:0 1px 3px rgba(0,0,0,0.3); pointer-events:all; }
.crop-handle-nw{top:-7px;left:-7px;cursor:nw-resize;} .crop-handle-ne{top:-7px;right:-7px;cursor:ne-resize;}
.crop-handle-sw{bottom:-7px;left:-7px;cursor:sw-resize;} .crop-handle-se{bottom:-7px;right:-7px;cursor:se-resize;}
.crop-handle-n{top:-7px;left:50%;transform:translateX(-50%);cursor:n-resize;} .crop-handle-s{bottom:-7px;left:50%;transform:translateX(-50%);cursor:s-resize;}
.crop-handle-w{left:-7px;top:50%;transform:translateY(-50%);cursor:w-resize;} .crop-handle-e{right:-7px;top:50%;transform:translateY(-50%);cursor:e-resize;}
.crop-move-zone{position:absolute;inset:10px;cursor:move;}
</style>

<script>
const area = <?= json_encode($area) ?>;
const checkPoints = <?= json_encode($checkPoints) ?>;
const checkTypes = <?= json_encode($checkTypes) ?>;
const canPerformChecks = <?= Permission::can('create', 'checks') ? 'true' : 'false' ?>;
const csrfToken = '<?= $_SESSION['csrf_token'] ?>';
</script>
<script src="/js/area-viewer.js"></script>
<script>
// Live crop adjustment on area show page
(function() {
    const adjustBtn = document.getElementById('adjustCrop');
    if (!adjustBtn) return;

    const cropModal   = document.getElementById('cropModal');
    const liveCropCanvas = document.getElementById('liveCropCanvas');
    const liveCropBox = document.getElementById('liveCropBox');
    const container   = document.getElementById('liveCropContainer');
    const dimLabel    = document.getElementById('cropDimLive');
    const resetBtn    = document.getElementById('resetCropBtn');
    const saveBtn     = document.getElementById('saveCropBtn');

    let cropCanvasW = 0, cropCanvasH = 0;
    let liveCrop = { x: 0, y: 0, w: 0, h: 0 };
    let cropRendered = false;

    adjustBtn.addEventListener('click', function() {
        cropModal.classList.add('active');
        if (!cropRendered) renderCropCanvas();
    });

    window.closeCropModal = function() {
        cropModal.classList.remove('active');
    };

    window.addEventListener('click', function(e) {
        if (e.target === cropModal) closeCropModal();
    });

    function renderCropCanvas() {
        pdfjsLib.getDocument(area.pdf_path).promise.then(function(pdf) {
            pdf.getPage(1).then(function(page) {
                const vp = page.getViewport({ scale: 1.5 });
                liveCropCanvas.width  = vp.width;
                liveCropCanvas.height = vp.height;
                cropCanvasW = vp.width;
                cropCanvasH = vp.height;
                page.render({ canvasContext: liveCropCanvas.getContext('2d'), viewport: vp }).promise.then(function() {
                    cropRendered = true;
                    // Init crop box from stored values or full page
                    liveCrop = {
                        x: (area.crop_x || 0) * cropCanvasW,
                        y: (area.crop_y || 0) * cropCanvasH,
                        w: (area.crop_w || 1) * cropCanvasW,
                        h: (area.crop_h || 1) * cropCanvasH
                    };
                    updateLiveCropBox();
                });
            });
        });
    }

    function updateLiveCropBox() {
        const contRect = container.getBoundingClientRect();
        const canvRect = liveCropCanvas.getBoundingClientRect();
        const ox = canvRect.left - contRect.left + container.scrollLeft;
        const oy = canvRect.top  - contRect.top  + container.scrollTop;
        liveCropBox.style.left   = (ox + liveCrop.x) + 'px';
        liveCropBox.style.top    = (oy + liveCrop.y) + 'px';
        liveCropBox.style.width  = liveCrop.w + 'px';
        liveCropBox.style.height = liveCrop.h + 'px';
        const pct = v => Math.round(v * 100);
        dimLabel.textContent = `x:${pct(liveCrop.x/cropCanvasW)}% y:${pct(liveCrop.y/cropCanvasH)}%  ${pct(liveCrop.w/cropCanvasW)}% × ${pct(liveCrop.h/cropCanvasH)}%`;
    }

    resetBtn.addEventListener('click', function() {
        liveCrop = { x: 0, y: 0, w: cropCanvasW, h: cropCanvasH };
        updateLiveCropBox();
    });

    saveBtn.addEventListener('click', function() {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving…';
        const payload = {
            x: liveCrop.x / cropCanvasW,
            y: liveCrop.y / cropCanvasH,
            w: liveCrop.w / cropCanvasW,
            h: liveCrop.h / cropCanvasH,
            csrf_token: csrfToken
        };
        fetch('/areas/' + area.id + '/crop', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(result => {
            if (result.success) {
                closeCropModal();
                location.reload();
            } else {
                alert('Error: ' + (result.error || 'Unknown'));
            }
        })
        .catch(err => alert('Error: ' + err.message))
        .finally(() => { saveBtn.disabled = false; saveBtn.textContent = 'Save Crop'; });
    });

    // Drag logic
    let dragging = false, handle = null;
    let startMouse = { x:0, y:0 }, startCrop = { x:0, y:0, w:0, h:0 };
    const MIN = 30;

    liveCropBox.addEventListener('mousedown', startDrag);
    liveCropBox.addEventListener('touchstart', startDrag, { passive: false });

    function startDrag(e) {
        e.preventDefault();
        dragging = true;
        handle = e.target.dataset.handle || 'move';
        startMouse = getPos(e);
        startCrop  = { ...liveCrop };
        document.addEventListener('mousemove', onDrag);
        document.addEventListener('touchmove', onDrag, { passive: false });
        document.addEventListener('mouseup',  stopDrag);
        document.addEventListener('touchend', stopDrag);
    }

    function onDrag(e) {
        if (!dragging) return;
        e.preventDefault();
        const pos = getPos(e);
        const dx = pos.x - startMouse.x, dy = pos.y - startMouse.y;
        let { x, y, w, h } = startCrop;
        switch (handle) {
            case 'move': x = Math.max(0,Math.min(cropCanvasW-w,x+dx)); y = Math.max(0,Math.min(cropCanvasH-h,y+dy)); break;
            case 'se': w=Math.max(MIN,Math.min(cropCanvasW-x,w+dx)); h=Math.max(MIN,Math.min(cropCanvasH-y,h+dy)); break;
            case 'sw': { const nw=Math.max(MIN,Math.min(startCrop.x+startCrop.w,w-dx)); x=startCrop.x+startCrop.w-nw; w=nw; h=Math.max(MIN,Math.min(cropCanvasH-y,h+dy)); break; }
            case 'ne': w=Math.max(MIN,Math.min(cropCanvasW-x,w+dx)); { const nh=Math.max(MIN,Math.min(startCrop.y+startCrop.h,h-dy)); y=startCrop.y+startCrop.h-nh; h=nh; break; }
            case 'nw': { const nw=Math.max(MIN,Math.min(startCrop.x+startCrop.w,w-dx)); x=startCrop.x+startCrop.w-nw; w=nw; const nh=Math.max(MIN,Math.min(startCrop.y+startCrop.h,h-dy)); y=startCrop.y+startCrop.h-nh; h=nh; break; }
            case 'n': { const nh=Math.max(MIN,Math.min(startCrop.y+startCrop.h,h-dy)); y=startCrop.y+startCrop.h-nh; h=nh; break; }
            case 's': h=Math.max(MIN,Math.min(cropCanvasH-y,h+dy)); break;
            case 'w': { const nw=Math.max(MIN,Math.min(startCrop.x+startCrop.w,w-dx)); x=startCrop.x+startCrop.w-nw; w=nw; break; }
            case 'e': w=Math.max(MIN,Math.min(cropCanvasW-x,w+dx)); break;
        }
        liveCrop = { x, y, w, h };
        updateLiveCropBox();
    }

    function stopDrag() {
        dragging = false;
        document.removeEventListener('mousemove', onDrag);
        document.removeEventListener('touchmove', onDrag);
        document.removeEventListener('mouseup',  stopDrag);
        document.removeEventListener('touchend', stopDrag);
    }

    function getPos(e) {
        const t = e.touches ? e.touches[0] : e;
        const r = container.getBoundingClientRect();
        return { x: t.clientX - r.left + container.scrollLeft, y: t.clientY - r.top + container.scrollTop };
    }
})();
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
