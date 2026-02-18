<?php
$pageTitle = 'Help';
$currentPage = 'help';
require __DIR__ . '/layout/header.php';
?>

<div class="page-header">
    <h1>Help &amp; Documentation</h1>
</div>

<style>
.help-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.help-section { margin-bottom: 1.5rem; }
.help-section:last-child { margin-bottom: 0; }
.help-section h3 {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 0.75rem;
    color: var(--gray-800);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.help-section h3 .h-icon {
    width: 28px; height: 28px;
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.h-icon-blue    { background: #dbeafe; color: #2563eb; }
.h-icon-green   { background: #dcfce7; color: #16a34a; }
.h-icon-amber   { background: #fef3c7; color: #d97706; }
.h-icon-violet  { background: #ede9fe; color: #7c3aed; }
.h-icon-sky     { background: #e0f2fe; color: #0284c7; }
.h-icon-rose    { background: #ffe4e6; color: #e11d48; }
.h-icon-slate   { background: #f1f5f9; color: #475569; }

.help-section ol,
.help-section ul { margin: 0; padding-left: 1.25rem; }
.help-section li { margin-bottom: 0.4rem; font-size: 0.9rem; line-height: 1.5; }
.help-section p  { font-size: 0.9rem; line-height: 1.6; margin: 0 0 0.5rem; }

.status-list { list-style: none; padding: 0; }
.status-list li {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.375rem 0;
    font-size: 0.9rem;
}
.status-dot {
    width: 14px; height: 14px;
    border-radius: 50%;
    flex-shrink: 0;
}
.dot-green  { background: #16a34a; }
.dot-amber  { background: #d97706; }
.dot-red    { background: #dc2626; }
.dot-grey   { background: #9ca3af; }

.role-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.role-table th, .role-table td { padding: 0.5rem 0.75rem; text-align: left; border-bottom: 1px solid var(--gray-100); }
.role-table th { font-weight: 600; color: var(--gray-600); background: var(--gray-50); }
.role-table td:first-child { font-weight: 500; }

.shortcut-row { display: flex; justify-content: space-between; align-items: center; padding: 0.375rem 0; font-size: 0.875rem; border-bottom: 1px solid var(--gray-100); }
.shortcut-row:last-child { border-bottom: none; }
kbd {
    display: inline-block;
    padding: 0.15rem 0.45rem;
    font-family: monospace;
    font-size: 0.78rem;
    background: var(--gray-100);
    border: 1px solid var(--gray-300);
    border-radius: 4px;
    color: var(--gray-700);
}

/* Dark mode */
.dark-mode .help-section h3 { color: #e2e8f0; }
.dark-mode .h-icon-blue   { background: rgba(37,99,235,0.15);  color: #93c5fd; }
.dark-mode .h-icon-green  { background: rgba(22,163,74,0.15);  color: #86efac; }
.dark-mode .h-icon-amber  { background: rgba(217,119,6,0.15);  color: #fcd34d; }
.dark-mode .h-icon-violet { background: rgba(124,58,237,0.15); color: #c4b5fd; }
.dark-mode .h-icon-sky    { background: rgba(2,132,199,0.15);  color: #7dd3fc; }
.dark-mode .h-icon-rose   { background: rgba(225,29,72,0.15);  color: #fda4af; }
.dark-mode .h-icon-slate  { background: rgba(71,85,105,0.2);   color: #94a3b8; }
.dark-mode .role-table th  { background: #1e293b; color: #94a3b8; border-color: #334155; }
.dark-mode .role-table td  { border-color: #334155; color: #e2e8f0; }
.dark-mode .shortcut-row   { border-color: #334155; color: #e2e8f0; }
.dark-mode kbd { background: #334155; border-color: #475569; color: #cbd5e1; }
</style>

<!-- Quick-start steps -->
<div class="card mb-4">
    <div class="card-header"><h3 style="margin:0;">Getting Started</h3></div>
    <div class="card-body">
        <ol style="margin:0;padding-left:1.25rem;line-height:2;">
            <li><strong>Create a Venue</strong> — add the building or site (Venues → New Venue)</li>
            <li><strong>Build a Plan Library</strong> — upload reusable PDF floor plans to the venue so you don't need to re-upload the same PDF for every area</li>
            <li><strong>Create Areas</strong> — either upload a fresh PDF or pick one from the venue's Plan Library. Crop the plan to the relevant section if needed</li>
            <li><strong>Calibrate the Plan</strong> — draw a reference line of known length to set the pixel-to-metre scale</li>
            <li><strong>Define Check Types</strong> — create categories such as Electrical, Fire Safety, Plumbing, HVAC (Check Types in the sidebar)</li>
            <li><strong>Add Check Points</strong> — click the plan to place coloured circles; set the reference code, label, type, and how often each check is required (periodicity)</li>
            <li><strong>Perform Checks</strong> — staff open an area, tap a circle, and mark it Pass or Fail. Failures automatically create a report entry</li>
            <li><strong>Monitor &amp; Export</strong> — review the dashboard, dig into Check Logs (with CSV export), and use the Analytics page for trends</li>
        </ol>
    </div>
</div>

<div class="help-grid">

    <!-- Check point status -->
    <div class="card">
        <div class="card-header"><h3 style="margin:0;">Check Point Status Colours</h3></div>
        <div class="card-body">
            <ul class="status-list">
                <li><span class="status-dot dot-green"></span><div><strong>Green</strong> — completed within the current period</div></li>
                <li><span class="status-dot dot-amber"></span><div><strong>Amber</strong> — due within the next 24 hours</div></li>
                <li><span class="status-dot dot-red"></span><div><strong>Red</strong> — overdue (past the period deadline)</div></li>
                <li><span class="status-dot dot-grey"></span><div><strong>Grey</strong> — never checked</div></li>
            </ul>
        </div>
    </div>

    <!-- Periodicity -->
    <div class="card">
        <div class="card-header"><h3 style="margin:0;">Periodicity Options</h3></div>
        <div class="card-body help-section">
            <p>Each check point has a frequency that determines when it turns amber or red:</p>
            <ul>
                <li><strong>Daily</strong> — must be checked every day</li>
                <li><strong>Weekly</strong> — once per 7-day window</li>
                <li><strong>Monthly</strong> — once per calendar month</li>
                <li><strong>Quarterly</strong> — once per 3-month period</li>
                <li><strong>Annually</strong> — once per 12-month period</li>
            </ul>
        </div>
    </div>

    <!-- Plan library -->
    <div class="card">
        <div class="card-header"><h3 style="margin:0;">Venue Plan Library</h3></div>
        <div class="card-body help-section">
            <p>Each venue has a <strong>Plan Library</strong> — a store of reusable PDF floor plans.</p>
            <ul>
                <li>Upload a plan once to the library; reuse it for as many areas as needed</li>
                <li>When creating a new area, switch to <em>Use from library</em> and pick a plan</li>
                <li>Each area gets its own independent copy, so cropping or deleting an area never affects the library</li>
                <li>Click <strong>Use</strong> on any library plan to jump straight to the Create Area form with that plan pre-selected</li>
            </ul>
        </div>
    </div>

    <!-- Reports -->
    <div class="card">
        <div class="card-header"><h3 style="margin:0;">Reports Register</h3></div>
        <div class="card-body help-section">
            <p>Every failed check creates a report automatically. Reports can also be added manually.</p>
            <ul>
                <li>Assign reports to users for follow-up</li>
                <li>Track status: <em>Open → In Progress → Resolved → Closed</em></li>
                <li>Set severity: Low / Medium / High / Critical</li>
                <li>Add resolution notes when closing</li>
            </ul>
        </div>
    </div>

    <!-- Check logs & export -->
    <div class="card">
        <div class="card-header"><h3 style="margin:0;">Check Logs &amp; CSV Export</h3></div>
        <div class="card-body help-section">
            <p>The <strong>Check Logs</strong> page shows a full audit trail of every check performed.</p>
            <ul>
                <li>Filter by venue, status (Pass/Fail), or date range</li>
                <li>Click <strong>Export CSV</strong> to download all matching rows (no row limit) — the active filters carry over to the export</li>
                <li>Exported columns: ID, Date, Venue, Area, Reference, Label, Type, Status, Performed By, Notes</li>
            </ul>
        </div>
    </div>

    <!-- Analytics -->
    <div class="card">
        <div class="card-header"><h3 style="margin:0;">Analytics</h3></div>
        <div class="card-body help-section">
            <p>The <strong>Analytics</strong> page gives a visual overview of check performance.</p>
            <ul>
                <li>Summary cards: total checks, pass count, fail count, pass rate %, active check points</li>
                <li>Daily activity chart — stacked bar (green = pass, red = fail) for the selected period</li>
                <li>Failures by check type and by area — horizontal bar charts</li>
                <li>Top 10 most-failed check points — ranked table</li>
                <li>Filter by time range (7 / 30 / 90 days or all time) and by venue</li>
            </ul>
        </div>
    </div>

</div>

<!-- User roles -->
<div class="card mb-4">
    <div class="card-header"><h3 style="margin:0;">User Roles &amp; Permissions</h3></div>
    <div class="card-body" style="padding:0;">
        <table class="role-table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>What they can do</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Admin</td>
                    <td>Full access — all CRUD on everything, user management, system settings</td>
                </tr>
                <tr>
                    <td>Manager</td>
                    <td>Create and manage venues, plan libraries, areas, check points, check types, and reports. View users.</td>
                </tr>
                <tr>
                    <td>Staff</td>
                    <td>View venues, areas, and plans. Perform checks (pass/fail). View reports.</td>
                </tr>
                <tr>
                    <td>Viewer</td>
                    <td>Read-only access to everything — cannot create, edit, or perform checks.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Tips -->
<div class="card mb-4">
    <div class="card-header"><h3 style="margin:0;">Tips &amp; Shortcuts</h3></div>
    <div class="card-body">
        <div class="shortcut-row"><span>Toggle dark mode</span><span>Dark mode button in the sidebar footer</span></div>
        <div class="shortcut-row"><span>Zoom the area plan</span><span>Zoom In / Zoom Out buttons, or scroll while holding <kbd>Ctrl</kbd></span></div>
        <div class="shortcut-row"><span>Reset plan zoom</span><span>Reset button in the area toolbar</span></div>
        <div class="shortcut-row"><span>Crop a plan</span><span>Crop button → drag handles → Save Crop</span></div>
        <div class="shortcut-row"><span>Quick-fail a check</span><span>Click a check point circle → Fail → enter notes → Submit</span></div>
        <div class="shortcut-row"><span>Jump to area from library</span><span>Venue page → Plan Library → Use button</span></div>
        <div class="shortcut-row"><span>Export filtered logs</span><span>Apply filters on Check Logs → Export CSV (filters carry over)</span></div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <p style="margin:0;font-size:0.9rem;color:var(--gray-500);">For technical support, please contact your system administrator.</p>
    </div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
