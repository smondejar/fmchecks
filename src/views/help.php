<?php
$pageTitle = 'Help';
$currentPage = 'help';
require __DIR__ . '/layout/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Help & Documentation</h2>
    </div>
    <div class="card-body">
        <h3>FM Checks - Facilities Management System</h3>
        <p>Welcome to FM Checks, a comprehensive system for managing periodic safety and maintenance checks across your facilities.</p>

        <h4>Getting Started</h4>
        <ol>
            <li><strong>Create Venues</strong> - Add your buildings or sites</li>
            <li><strong>Upload Area Plans</strong> - Upload PDF floor plans for each area</li>
            <li><strong>Calibrate Plans</strong> - Set the scale by drawing a reference line</li>
            <li><strong>Define Check Types</strong> - Create categories (Electrical, Fire, Plumbing, etc.)</li>
            <li><strong>Add Check Points</strong> - Place check points on your plans</li>
            <li><strong>Perform Checks</strong> - Staff can check off items on mobile or desktop</li>
        </ol>

        <h4>Check Point Status Colors</h4>
        <ul>
            <li><strong class="text-success">Green</strong> - Check completed within period</li>
            <li><strong class="text-warning">Amber</strong> - Due soon (within 24 hours)</li>
            <li><strong class="text-danger">Red</strong> - Overdue</li>
            <li><strong class="text-muted">Grey</strong> - Never checked</li>
        </ul>

        <h4>User Roles</h4>
        <ul>
            <li><strong>Admin</strong> - Full system access</li>
            <li><strong>Manager</strong> - Can manage venues, areas, checks, and reports</li>
            <li><strong>Staff</strong> - Can perform checks and view reports</li>
            <li><strong>Viewer</strong> - Read-only access</li>
        </ul>

        <h4>Support</h4>
        <p>For technical support or questions, please contact your system administrator.</p>
    </div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
