<?php

class CheckController
{
    public static function index(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('view', 'checks');

        $filters = [
            'venue_id'  => $_GET['venue_id'] ?? null,
            'area_id'   => $_GET['area_id'] ?? null,
            'status'    => $_GET['status'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to'   => $_GET['date_to'] ?? null,
        ];

        $checkLogs = CheckLog::all($filters);
        $venues = Venue::all();
        require __DIR__ . '/../views/checks/index.php';
    }

    public static function export(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('view', 'checks');

        $filters = [
            'venue_id'  => $_GET['venue_id'] ?? null,
            'area_id'   => $_GET['area_id'] ?? null,
            'status'    => $_GET['status'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to'   => $_GET['date_to'] ?? null,
        ];

        $rows = CheckLog::allForExport($filters);

        $filename = 'check-logs-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Date', 'Venue', 'Area', 'Reference', 'Label', 'Type', 'Status', 'Performed By', 'Notes']);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['id'],
                $row['performed_at'],
                $row['venue_name'] ?? '',
                $row['area_name'] ?? '',
                $row['reference'] ?? '',
                $row['label'] ?? '',
                $row['type_name'] ?? '',
                $row['status'],
                $row['performer_name'] ?? '',
                $row['notes'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }

    public static function perform(int $checkPointId): void
    {
        Auth::requireAuth();
        Permission::requirePerm('create', 'checks');
        Csrf::requireValidToken();

        $checkPoint = CheckPoint::find($checkPointId);
        if (!$checkPoint) {
            http_response_code(404);
            echo json_encode(['error' => 'Checkpoint not found']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['status']) || !in_array($data['status'], ['pass', 'fail'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid status']);
            exit;
        }

        // Create check log
        $logId = CheckLog::create([
            'check_point_id' => $checkPointId,
            'performed_by' => Auth::id(),
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'photo_path' => $data['photo_path'] ?? null
        ]);

        $reportId = null;

        // If failed and user wants to create a report
        if ($data['status'] === 'fail' && ($data['create_report'] ?? true)) {
            $reportTitle = $data['report_title'] ?? 'Check failure: ' . $checkPoint['reference'] . ' - ' . $checkPoint['label'];
            $reportDescription = $data['report_description'] ?? $data['notes'] ?? 'Check failed';

            $reportId = Report::create([
                'check_log_id' => $logId,
                'check_point_id' => $checkPointId,
                'venue_id' => $checkPoint['venue_id'],
                'area_id' => $checkPoint['area_id'],
                'title' => $reportTitle,
                'description' => $reportDescription,
                'severity' => $data['severity'] ?? 'medium',
                'status' => 'open',
                'created_by' => Auth::id()
            ]);
        }

        echo json_encode([
            'success' => true,
            'log_id' => $logId,
            'report_id' => $reportId
        ]);
        exit;
    }
}
