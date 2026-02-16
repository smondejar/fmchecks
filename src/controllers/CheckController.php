<?php

class CheckController
{
    public static function index(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('view', 'checks');

        $filters = [
            'venue_id' => $_GET['venue_id'] ?? null,
            'area_id' => $_GET['area_id'] ?? null,
            'status' => $_GET['status'] ?? null
        ];

        $checkLogs = CheckLog::all($filters);
        $venues = Venue::all();
        require __DIR__ . '/../views/checks/index.php';
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

        // If failed, create a report
        if ($data['status'] === 'fail') {
            $reportId = Report::create([
                'check_log_id' => $logId,
                'check_point_id' => $checkPointId,
                'venue_id' => $checkPoint['venue_id'],
                'area_id' => $checkPoint['area_id'],
                'title' => 'Check failure: ' . $checkPoint['reference'] . ' - ' . $checkPoint['label'],
                'description' => $data['notes'] ?? 'Check failed',
                'severity' => $data['severity'] ?? 'medium',
                'status' => 'open',
                'created_by' => Auth::id()
            ]);
        }

        echo json_encode([
            'success' => true,
            'log_id' => $logId,
            'report_id' => $reportId ?? null
        ]);
        exit;
    }
}
