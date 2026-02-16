// Area Viewer - PDF Rendering and Check Point Management

let pdfDoc = null;
let pageNum = 1;
let scale = 1.0;
let canvas = null;
let ctx = null;
let isAddingCheckPoint = false;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    canvas = document.getElementById('pdfCanvas');
    if (!canvas) return;

    ctx = canvas.getContext('2d');

    // Load PDF
    if (typeof area !== 'undefined' && area.pdf_path) {
        loadPDF(area.pdf_path);
    }

    // Setup controls
    setupControls();

    // Render checkpoints
    if (typeof checkPoints !== 'undefined' && checkPoints.length > 0) {
        renderCheckPoints();
    }
});

function loadPDF(url) {
    pdfjsLib.getDocument(url).promise.then(function(pdf) {
        pdfDoc = pdf;
        renderPage(pageNum);
    });
}

function renderPage(num) {
    pdfDoc.getPage(num).then(function(page) {
        const viewport = page.getViewport({ scale: scale });
        canvas.width = viewport.width;
        canvas.height = viewport.height;

        const renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };

        page.render(renderContext).promise.then(function() {
            renderCheckPoints();
        });
    });
}

function setupControls() {
    const zoomIn = document.getElementById('zoomIn');
    const zoomOut = document.getElementById('zoomOut');
    const zoomReset = document.getElementById('zoomReset');
    const addCheckPoint = document.getElementById('addCheckPoint');

    if (zoomIn) {
        zoomIn.addEventListener('click', function() {
            scale += 0.2;
            renderPage(pageNum);
        });
    }

    if (zoomOut) {
        zoomOut.addEventListener('click', function() {
            if (scale > 0.4) {
                scale -= 0.2;
                renderPage(pageNum);
            }
        });
    }

    if (zoomReset) {
        zoomReset.addEventListener('click', function() {
            scale = 1.0;
            renderPage(pageNum);
        });
    }

    if (addCheckPoint) {
        addCheckPoint.addEventListener('click', function() {
            isAddingCheckPoint = !isAddingCheckPoint;
            addCheckPoint.textContent = isAddingCheckPoint ? 'Cancel' : 'Add Check Point';
            addCheckPoint.classList.toggle('btn-danger', isAddingCheckPoint);
            canvas.style.cursor = isAddingCheckPoint ? 'crosshair' : 'default';
        });
    }

    // Handle canvas clicks
    canvas.addEventListener('click', function(e) {
        const rect = canvas.getBoundingClientRect();
        const x = (e.clientX - rect.left) / canvas.width;
        const y = (e.clientY - rect.top) / canvas.height;

        if (isAddingCheckPoint) {
            showAddCheckPointModal(x, y);
        } else {
            checkCheckPointClick(e.clientX - rect.left, e.clientY - rect.top);
        }
    });
}

function renderCheckPoints() {
    if (!checkPoints || checkPoints.length === 0) return;

    checkPoints.forEach(function(point) {
        drawCheckPoint(point);
    });
}

function drawCheckPoint(point) {
    const x = point.x_coord * canvas.width;
    const y = point.y_coord * canvas.height;
    const radius = 10;

    // Determine color based on status
    let color;
    switch (point.status) {
        case 'ok':
            color = '#16a34a';
            break;
        case 'due_soon':
            color = '#d97706';
            break;
        case 'overdue':
            color = '#dc2626';
            break;
        default:
            color = '#9ca3af';
    }

    // Draw circle
    ctx.beginPath();
    ctx.arc(x, y, radius, 0, 2 * Math.PI);
    ctx.fillStyle = color;
    ctx.fill();
    ctx.strokeStyle = 'white';
    ctx.lineWidth = 2;
    ctx.stroke();

    // Draw reference label
    ctx.font = 'bold 10px sans-serif';
    ctx.fillStyle = 'white';
    ctx.textAlign = 'center';
    ctx.fillText(point.reference, x, y + 4);
}

function checkCheckPointClick(clickX, clickY) {
    if (!checkPoints) return;

    for (let point of checkPoints) {
        const x = point.x_coord * canvas.width;
        const y = point.y_coord * canvas.height;
        const radius = 10;

        const distance = Math.sqrt((clickX - x) ** 2 + (clickY - y) ** 2);
        if (distance <= radius) {
            showCheckPointModal(point);
            return;
        }
    }
}

function showCheckPointModal(point) {
    const modal = document.getElementById('checkPointModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');

    modalTitle.textContent = point.reference + ' - ' + point.label;

    let lastCheckInfo = 'Never checked';
    if (point.last_check) {
        lastCheckInfo = 'Last checked: ' + new Date(point.last_check.performed_at).toLocaleString();
        lastCheckInfo += ' (' + point.last_check.status + ')';
    }

    modalBody.innerHTML = `
        <p><strong>Type:</strong> ${point.type_name}</p>
        <p><strong>Periodicity:</strong> ${point.periodicity}</p>
        <p><strong>Status:</strong> <span class="badge badge-${point.status}">${point.status}</span></p>
        <p><strong>${lastCheckInfo}</strong></p>
        ${point.notes ? '<p><strong>Notes:</strong><br>' + point.notes + '</p>' : ''}
        ${canPerformChecks ? `
            <div class="form-group mt-3">
                <button class="btn btn-success btn-block" onclick="performCheck(${point.id}, 'pass')">✓ Pass</button>
                <button class="btn btn-danger btn-block" onclick="performCheck(${point.id}, 'fail')">✗ Fail</button>
            </div>
        ` : ''}
    `;

    modal.classList.add('active');
}

function showAddCheckPointModal(x, y) {
    const modal = document.getElementById('checkPointModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');

    modalTitle.textContent = 'Add Check Point';

    let typesOptions = '';
    checkTypes.forEach(function(type) {
        typesOptions += `<option value="${type.id}">${type.name}</option>`;
    });

    modalBody.innerHTML = `
        <form id="addCheckPointForm">
            <div class="form-group">
                <label for="reference">Reference Code *</label>
                <input type="text" id="reference" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="label">Label *</label>
                <input type="text" id="label" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="check_type_id">Check Type *</label>
                <select id="check_type_id" class="form-control" required>
                    ${typesOptions}
                </select>
            </div>
            <div class="form-group">
                <label for="periodicity">Periodicity *</label>
                <select id="periodicity" class="form-control" required>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly" selected>Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="annually">Annually</option>
                </select>
            </div>
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" class="form-control" rows="3"></textarea>
            </div>
            <input type="hidden" id="x_coord" value="${x}">
            <input type="hidden" id="y_coord" value="${y}">
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Check Point</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    `;

    document.getElementById('addCheckPointForm').addEventListener('submit', function(e) {
        e.preventDefault();
        addCheckPointToArea();
    });

    modal.classList.add('active');
}

function addCheckPointToArea() {
    const data = {
        reference: document.getElementById('reference').value,
        label: document.getElementById('label').value,
        check_type_id: document.getElementById('check_type_id').value,
        periodicity: document.getElementById('periodicity').value,
        x_coord: document.getElementById('x_coord').value,
        y_coord: document.getElementById('y_coord').value,
        notes: document.getElementById('notes').value
    };

    fetch('/areas/' + area.id + '/add-checkpoint', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            closeModal();
            location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    });
}

function performCheck(checkPointId, status) {
    let notes = '';
    let severity = 'medium';

    if (status === 'fail') {
        notes = prompt('Enter failure notes:');
        if (!notes) return;

        const severityChoice = prompt('Severity? (low/medium/high/critical)', 'medium');
        if (severityChoice) severity = severityChoice;
    }

    fetch('/checks/' + checkPointId + '/perform', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status, notes, severity })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            closeModal();
            location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    });
}

function closeModal() {
    const modal = document.getElementById('checkPointModal');
    modal.classList.remove('active');
    isAddingCheckPoint = false;
    const addBtn = document.getElementById('addCheckPoint');
    if (addBtn) {
        addBtn.textContent = 'Add Check Point';
        addBtn.classList.remove('btn-danger');
    }
    if (canvas) {
        canvas.style.cursor = 'default';
    }
}

// Close modal when clicking outside
window.addEventListener('click', function(e) {
    const modal = document.getElementById('checkPointModal');
    if (e.target === modal) {
        closeModal();
    }
});
