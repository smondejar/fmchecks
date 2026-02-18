<?php

class VenuePlanController
{
    public static function upload(int $venueId): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'venues');
        Csrf::requireValidToken();

        $venue = Venue::find($venueId);
        if (!$venue) {
            $_SESSION['flash_error'] = 'Venue not found';
            header('Location: /venues');
            exit;
        }

        $name = trim($_POST['plan_name'] ?? '');
        if (empty($name)) {
            $_SESSION['flash_error'] = 'Plan name is required';
            header('Location: /venues/' . $venueId);
            exit;
        }

        if (!isset($_FILES['plan_pdf']) || $_FILES['plan_pdf']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'PDF file is required';
            header('Location: /venues/' . $venueId);
            exit;
        }

        $file = $_FILES['plan_pdf'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mimeType !== 'application/pdf') {
            $_SESSION['flash_error'] = 'Only PDF files are allowed';
            header('Location: /venues/' . $venueId);
            exit;
        }

        $filename = uniqid('vplan_') . '.pdf';
        $uploadPath = __DIR__ . '/../../public/uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $_SESSION['flash_error'] = 'Failed to upload file';
            header('Location: /venues/' . $venueId);
            exit;
        }

        VenuePlan::create([
            'venue_id'    => $venueId,
            'name'        => $name,
            'pdf_path'    => '/uploads/' . $filename,
            'uploaded_by' => Auth::id(),
        ]);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Plan "' . htmlspecialchars($name) . '" added to the library';
        header('Location: /venues/' . $venueId);
        exit;
    }

    public static function delete(int $venueId, int $planId): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'venues');
        Csrf::requireValidToken();

        $plan = VenuePlan::find($planId);
        if (!$plan || $plan['venue_id'] !== $venueId) {
            $_SESSION['flash_error'] = 'Plan not found';
            header('Location: /venues/' . $venueId);
            exit;
        }

        $pdfPath = VenuePlan::delete($planId);

        // Remove the physical file if it exists and is not used by any area
        if ($pdfPath) {
            $fullPath = __DIR__ . '/../../public' . $pdfPath;
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Plan removed from library';
        header('Location: /venues/' . $venueId);
        exit;
    }
}
