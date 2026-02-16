<?php

class ReportController
{
    public static function index(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('view', 'reports');

        $filters = [
            'venue_id' => $_GET['venue_id'] ?? null,
            'area_id' => $_GET['area_id'] ?? null,
            'severity' => $_GET['severity'] ?? null,
            'status' => $_GET['status'] ?? null
        ];

        $reports = Report::all($filters);
        $venues = Venue::all();
        require __DIR__ . '/../views/reports/index.php';
    }

    public static function show(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('view', 'reports');

        $report = Report::find($id);
        if (!$report) {
            $_SESSION['flash_error'] = 'Report not found';
            header('Location: /reports');
            exit;
        }

        $users = User::all();
        require __DIR__ . '/../views/reports/show.php';
    }

    public static function create(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('create', 'reports');

        $venues = Venue::all();
        require __DIR__ . '/../views/reports/form.php';
    }

    public static function store(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('create', 'reports');
        Csrf::requireValidToken();

        $venueId = (int) ($_POST['venue_id'] ?? 0);
        $areaId = !empty($_POST['area_id']) ? (int) $_POST['area_id'] : null;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $severity = $_POST['severity'] ?? 'medium';

        if (empty($venueId) || empty($title) || empty($description)) {
            $_SESSION['flash_error'] = 'Venue, title, and description are required';
            header('Location: /reports/create');
            exit;
        }

        $reportId = Report::create([
            'venue_id' => $venueId,
            'area_id' => $areaId,
            'title' => $title,
            'description' => $description,
            'severity' => $severity,
            'status' => 'open',
            'created_by' => Auth::id()
        ]);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Report created successfully';
        header('Location: /reports/' . $reportId);
        exit;
    }

    public static function update(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'reports');
        Csrf::requireValidToken();

        $report = Report::find($id);
        if (!$report) {
            $_SESSION['flash_error'] = 'Report not found';
            header('Location: /reports');
            exit;
        }

        $data = [];

        if (isset($_POST['status'])) {
            $data['status'] = $_POST['status'];
        }

        if (isset($_POST['severity'])) {
            $data['severity'] = $_POST['severity'];
        }

        if (isset($_POST['assigned_to'])) {
            $data['assigned_to'] = !empty($_POST['assigned_to']) ? (int) $_POST['assigned_to'] : null;
        }

        Report::update($id, $data);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Report updated successfully';
        header('Location: /reports/' . $id);
        exit;
    }

    public static function resolve(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'reports');
        Csrf::requireValidToken();

        $report = Report::find($id);
        if (!$report) {
            $_SESSION['flash_error'] = 'Report not found';
            header('Location: /reports');
            exit;
        }

        $notes = trim($_POST['resolution_notes'] ?? '');

        if (empty($notes)) {
            $_SESSION['flash_error'] = 'Resolution notes are required';
            header('Location: /reports/' . $id);
            exit;
        }

        Report::resolve($id, Auth::id(), $notes);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Report resolved successfully';
        header('Location: /reports/' . $id);
        exit;
    }

    public static function delete(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('delete', 'reports');
        Csrf::requireValidToken();

        Report::delete($id);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Report deleted successfully';
        header('Location: /reports');
        exit;
    }
}
