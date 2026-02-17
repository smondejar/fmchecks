<?php

class AreaController
{
    public static function index(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('view', 'areas');

        $areas = Area::all();
        require __DIR__ . '/../views/areas/index.php';
    }

    public static function show(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('view', 'areas');

        $area = Area::find($id);
        if (!$area) {
            $_SESSION['flash_error'] = 'Area not found';
            header('Location: /areas');
            exit;
        }

        $checkPoints = CheckPoint::findByArea($id);
        $checkTypes = CheckType::all();

        // Add status to each checkpoint
        foreach ($checkPoints as &$point) {
            $point['status'] = CheckPoint::getStatus($point['id'], $point['periodicity']);
            $point['last_check'] = CheckPoint::getLastCheck($point['id']);
        }

        require __DIR__ . '/../views/areas/show.php';
    }

    public static function create(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('create', 'areas');

        $venues = Venue::all();
        require __DIR__ . '/../views/areas/form.php';
    }

    public static function store(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('create', 'areas');
        Csrf::requireValidToken();

        $venueId = (int) ($_POST['venue_id'] ?? 0);
        $areaName = trim($_POST['area_name'] ?? '');

        if (empty($venueId) || empty($areaName)) {
            $_SESSION['flash_error'] = 'Venue and area name are required';
            header('Location: /areas/create');
            exit;
        }

        // Handle PDF upload
        if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'PDF file is required';
            header('Location: /areas/create');
            exit;
        }

        $file = $_FILES['pdf_file'];

        // Validate PDF
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mimeType !== 'application/pdf') {
            $_SESSION['flash_error'] = 'Only PDF files are allowed';
            header('Location: /areas/create');
            exit;
        }

        // Generate unique filename
        $filename = uniqid('area_') . '.pdf';
        $uploadPath = __DIR__ . '/../../public/uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $_SESSION['flash_error'] = 'Failed to upload file';
            header('Location: /areas/create');
            exit;
        }

        $areaId = Area::create([
            'venue_id' => $venueId,
            'area_name' => $areaName,
            'pdf_path' => '/uploads/' . $filename,
            'uploaded_by' => Auth::id()
        ]);

        // Save crop if provided and not the full page
        $cropX = (float) ($_POST['crop_x'] ?? 0);
        $cropY = (float) ($_POST['crop_y'] ?? 0);
        $cropW = (float) ($_POST['crop_w'] ?? 1);
        $cropH = (float) ($_POST['crop_h'] ?? 1);
        if ($cropX > 0 || $cropY > 0 || $cropW < 1 || $cropH < 1) {
            Area::updateCrop($areaId, ['x' => $cropX, 'y' => $cropY, 'w' => $cropW, 'h' => $cropH]);
        }

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Area created successfully. Now calibrate the plan.';
        header('Location: /areas/' . $areaId);
        exit;
    }

    public static function edit(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'areas');

        $area = Area::find($id);
        if (!$area) {
            $_SESSION['flash_error'] = 'Area not found';
            header('Location: /areas');
            exit;
        }

        $venues = Venue::all();
        require __DIR__ . '/../views/areas/form.php';
    }

    public static function update(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'areas');
        Csrf::requireValidToken();

        $area = Area::find($id);
        if (!$area) {
            $_SESSION['flash_error'] = 'Area not found';
            header('Location: /areas');
            exit;
        }

        $venueId = (int) ($_POST['venue_id'] ?? 0);
        $areaName = trim($_POST['area_name'] ?? '');

        if (empty($venueId) || empty($areaName)) {
            $_SESSION['flash_error'] = 'Venue and area name are required';
            header('Location: /areas/' . $id . '/edit');
            exit;
        }

        Area::update($id, [
            'venue_id' => $venueId,
            'area_name' => $areaName
        ]);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Area updated successfully';
        header('Location: /areas/' . $id);
        exit;
    }

    public static function delete(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('delete', 'areas');
        Csrf::requireValidToken();

        Area::delete($id);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Area deleted successfully';
        header('Location: /areas');
        exit;
    }

    public static function calibrate(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'areas');
        Csrf::requireValidToken();

        $area = Area::find($id);
        if (!$area) {
            http_response_code(404);
            echo json_encode(['error' => 'Area not found']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['x1'], $data['y1'], $data['x2'], $data['y2'], $data['distance_m'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid calibration data']);
            exit;
        }

        Area::updateCalibration($id, [
            'x1' => $data['x1'],
            'y1' => $data['y1'],
            'x2' => $data['x2'],
            'y2' => $data['y2'],
            'distance_m' => $data['distance_m']
        ]);

        echo json_encode(['success' => true]);
        exit;
    }

    public static function saveCrop(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'areas');
        Csrf::requireValidToken();

        $area = Area::find($id);
        if (!$area) {
            http_response_code(404);
            echo json_encode(['error' => 'Area not found']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['x'], $data['y'], $data['w'], $data['h'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid crop data']);
            exit;
        }

        Area::updateCrop($id, [
            'x' => max(0, min(1, (float) $data['x'])),
            'y' => max(0, min(1, (float) $data['y'])),
            'w' => max(0.01, min(1, (float) $data['w'])),
            'h' => max(0.01, min(1, (float) $data['h']))
        ]);

        echo json_encode(['success' => true]);
        exit;
    }

    public static function addCheckPoint(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('create', 'checks');
        Csrf::requireValidToken();

        try {
            $area = Area::find($id);
            if (!$area) {
                http_response_code(404);
                echo json_encode(['error' => 'Area not found']);
                exit;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['reference'], $data['label'], $data['check_type_id'], $data['x_coord'], $data['y_coord'], $data['periodicity'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid checkpoint data']);
                exit;
            }

            $checkPointId = CheckPoint::create([
                'area_id' => $id,
                'reference' => $data['reference'],
                'label' => $data['label'],
                'check_type_id' => $data['check_type_id'],
                'x_coord' => $data['x_coord'],
                'y_coord' => $data['y_coord'],
                'periodicity' => $data['periodicity'],
                'notes' => $data['notes'] ?? null,
                'radius' => $data['radius'] ?? 10,
                'custom_colour' => $data['custom_colour'] ?? null
            ]);

            $checkPoint = CheckPoint::find($checkPointId);
            echo json_encode(['success' => true, 'checkpoint' => $checkPoint]);
        } catch (Exception $e) {
            error_log('Checkpoint creation error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Database error. Please run migration: /migrate.php?secret=fmchecks-migrate-2026']);
        }
        exit;
    }

    public static function updateCheckPoint(int $areaId, int $checkPointId): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'checks');
        Csrf::requireValidToken();

        $checkPoint = CheckPoint::find($checkPointId);
        if (!$checkPoint || $checkPoint['area_id'] != $areaId) {
            http_response_code(404);
            echo json_encode(['error' => 'Checkpoint not found']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Update with existing data, only changing what's provided
        $updateData = [
            'reference' => $data['reference'] ?? $checkPoint['reference'],
            'label' => $data['label'] ?? $checkPoint['label'],
            'check_type_id' => $data['check_type_id'] ?? $checkPoint['check_type_id'],
            'x_coord' => $data['x_coord'] ?? $checkPoint['x_coord'],
            'y_coord' => $data['y_coord'] ?? $checkPoint['y_coord'],
            'periodicity' => $data['periodicity'] ?? $checkPoint['periodicity'],
            'notes' => $data['notes'] ?? $checkPoint['notes'],
            'radius' => $data['radius'] ?? $checkPoint['radius'] ?? 10,
            'custom_colour' => $data['custom_colour'] ?? $checkPoint['custom_colour']
        ];

        CheckPoint::update($checkPointId, $updateData);

        echo json_encode(['success' => true]);
        exit;
    }
}
