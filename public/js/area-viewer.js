// Area Viewer - PDF Rendering and Check Point Management

let pdfDoc = null;
let pageNum = 1;
let scale = 1.0;
let canvas = null;
let ctx = null;
let isAddingCheckPoint = false;
let isDragging = false;
let draggedPoint = null;
let dragOffset = { x: 0, y: 0 };
let selectedPoint = null;

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

    // Handle canvas interactions
    canvas.addEventListener('mousedown', function(e) {
        if (isAddingCheckPoint) return;

        const rect = canvas.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const clickY = e.clientY - rect.top;

        // Check if clicking on a checkpoint
        const point = getCheckPointAtPosition(clickX, clickY);
        if (point) {
            if (e.shiftKey) {
                // Shift+click = edit properties
                showEditCheckPointModal(point);
            } else {
                // Regular click = start dragging
                isDragging = true;
                draggedPoint = point;
                const pointX = point.x_coord * canvas.width;
                const pointY = point.y_coord * canvas.height;
                dragOffset.x = clickX - pointX;
                dragOffset.y = clickY - pointY;
                canvas.style.cursor = 'grabbing';
            }
        }
    });

    canvas.addEventListener('mousemove', function(e) {
        const rect = canvas.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;

        if (isDragging && draggedPoint) {
            // Update position
            const newX = (mouseX - dragOffset.x) / canvas.width;
            const newY = (mouseY - dragOffset.y) / canvas.height;

            // Clamp to canvas bounds
            draggedPoint.x_coord = Math.max(0, Math.min(1, newX));
            draggedPoint.y_coord = Math.max(0, Math.min(1, newY));

            // Re-render
            renderPage(pageNum);
        } else if (!isAddingCheckPoint) {
            // Update cursor
            const point = getCheckPointAtPosition(mouseX, mouseY);
            canvas.style.cursor = point ? 'grab' : 'default';
        }
    });

    canvas.addEventListener('mouseup', function(e) {
        if (isDragging && draggedPoint) {
            // Save new position
            updateCheckPointPosition(draggedPoint);
            isDragging = false;
            draggedPoint = null;
            canvas.style.cursor = 'default';
        }
    });

    canvas.addEventListener('click', function(e) {
        if (isAddingCheckPoint) {
            const rect = canvas.getBoundingClientRect();
            const x = (e.clientX - rect.left) / canvas.width;
            const y = (e.clientY - rect.top) / canvas.height;
            showAddCheckPointModal(x, y);
        } else if (!isDragging) {
            const rect = canvas.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const clickY = e.clientY - rect.top;
            const point = getCheckPointAtPosition(clickX, clickY);
            if (point) {
                showCheckPointModal(point);
            }
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
    const radius = point.radius || 10;

    // Use custom color if set, otherwise determine by status
    let color = point.custom_colour;
    if (!color) {
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
                color = point.type_colour || '#9ca3af';
        }
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
    const fontSize = Math.max(8, Math.min(12, radius * 0.8));
    ctx.font = `bold ${fontSize}px sans-serif`;
    ctx.fillStyle = 'white';
    ctx.textAlign = 'center';
    ctx.fillText(point.reference, x, y + fontSize/3);
}

function getCheckPointAtPosition(clickX, clickY) {
    if (!checkPoints) return null;

    for (let point of checkPoints) {
        const x = point.x_coord * canvas.width;
        const y = point.y_coord * canvas.height;
        const radius = point.radius || 10;

        const distance = Math.sqrt((clickX - x) ** 2 + (clickY - y) ** 2);
        if (distance <= radius) {
            return point;
        }
    }
    return null;
}

function updateCheckPointPosition(point) {
    fetch('/areas/' + area.id + '/checkpoints/' + point.id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            x_coord: point.x_coord,
            y_coord: point.y_coord,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(result => {
        if (!result.success) {
            alert('Error updating position: ' + (result.error || 'Unknown error'));
            location.reload();
        }
    })
    .catch(error => {
        alert('Error updating position: ' + error.message);
        location.reload();
    });
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

    const canEdit = typeof Permission !== 'undefined' ? Permission.can('edit', 'checks') : canPerformChecks;

    modalBody.innerHTML = `
        <p><strong>Type:</strong> ${point.type_name}</p>
        <p><strong>Periodicity:</strong> ${point.periodicity}</p>
        <p><strong>Status:</strong> <span class="badge badge-${point.status}">${point.status}</span></p>
        <p><strong>${lastCheckInfo}</strong></p>
        ${point.notes ? '<p><strong>Notes:</strong><br>' + point.notes + '</p>' : ''}
        <p style="color: #6b7280; font-size: 0.875rem; margin-top: 1rem;">
            💡 <strong>Tip:</strong> Drag to move • Shift+Click to edit size/color
        </p>
        ${canPerformChecks ? `
            <div class="form-group mt-3">
                <button class="btn btn-success btn-block" onclick="performCheck(${point.id}, 'pass')">✓ Pass</button>
                <button class="btn btn-danger btn-block" onclick="performCheck(${point.id}, 'fail')">✗ Fail</button>
            </div>
        ` : ''}
    `;

    modal.classList.add('active');
}

function showEditCheckPointModal(point) {
    const modal = document.getElementById('checkPointModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');

    selectedPoint = point;
    modalTitle.textContent = 'Edit: ' + point.reference + ' - ' + point.label;

    const currentRadius = point.radius || 10;
    const currentColor = point.custom_colour || '';

    modalBody.innerHTML = `
        <div class="form-group">
            <label for="edit_radius">Size (radius in pixels)</label>
            <input type="range" id="edit_radius" min="5" max="30" value="${currentRadius}" class="form-control">
            <span id="radiusValue">${currentRadius}px</span>
        </div>
        <div class="form-group">
            <label for="edit_colour">Custom Color (leave empty to use type color)</label>
            <input type="color" id="edit_colour" value="${currentColor || '#2563eb'}" class="form-control">
            <label style="margin-top: 0.5rem;">
                <input type="checkbox" id="use_custom_colour" ${currentColor ? 'checked' : ''}> Use custom color
            </label>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" onclick="saveCheckPointEdit()">Save</button>
            <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        </div>
    `;

    // Update radius display
    document.getElementById('edit_radius').addEventListener('input', function(e) {
        document.getElementById('radiusValue').textContent = e.target.value + 'px';
        // Live preview
        if (selectedPoint) {
            selectedPoint.radius = parseInt(e.target.value);
            renderPage(pageNum);
        }
    });

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
        notes: document.getElementById('notes').value,
        csrf_token: csrfToken
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
    })
    .catch(error => {
        alert('Error adding checkpoint: ' + error.message);
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
        body: JSON.stringify({ status, notes, severity, csrf_token: csrfToken })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            closeModal();
            location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    })
    .catch(error => {
        alert('Error performing check: ' + error.message);
    });
}

function saveCheckPointEdit() {
    if (!selectedPoint) return;

    const radius = parseInt(document.getElementById('edit_radius').value);
    const useCustomColor = document.getElementById('use_custom_colour').checked;
    const customColor = useCustomColor ? document.getElementById('edit_colour').value : null;

    fetch('/areas/' + area.id + '/checkpoints/' + selectedPoint.id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            radius: radius,
            custom_colour: customColor,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            selectedPoint.radius = radius;
            selectedPoint.custom_colour = customColor;
            closeModal();
            renderPage(pageNum);
        } else {
            alert('Error: ' + (result.error || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error saving changes: ' + error.message);
    });
}

function closeModal() {
    const modal = document.getElementById('checkPointModal');
    modal.classList.remove('active');
    isAddingCheckPoint = false;
    selectedPoint = null;
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
