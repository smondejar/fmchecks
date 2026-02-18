<?php

class AnalyticsController
{
    public static function index(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('view', 'reports');

        $range = $_GET['range'] ?? '30';
        $venueId = $_GET['venue_id'] ?? null;

        // Build date_from from range selection
        $dateFrom = null;
        if ($range !== 'all') {
            $days = (int) $range;
            $dateFrom = date('Y-m-d', strtotime("-{$days} days"));
        }

        $filters = [
            'date_from' => $dateFrom,
            'date_to'   => null,
            'venue_id'  => $venueId ?: null,
        ];

        $data = CheckLog::analyticsData($filters);
        $venues = Venue::all();

        $pageTitle = 'Analytics';
        $currentPage = 'analytics';
        require __DIR__ . '/../views/analytics/index.php';
    }
}
