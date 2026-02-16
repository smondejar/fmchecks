<?php

class VenueController
{
    public static function index(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('view', 'venues');

        $venues = Venue::all();
        require __DIR__ . '/../views/venues/index.php';
    }

    public static function show(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('view', 'venues');

        $venue = Venue::find($id);
        if (!$venue) {
            $_SESSION['flash_error'] = 'Venue not found';
            header('Location: /venues');
            exit;
        }

        $areas = Venue::getAreas($id);
        require __DIR__ . '/../views/venues/show.php';
    }

    public static function create(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('create', 'venues');

        require __DIR__ . '/../views/venues/form.php';
    }

    public static function store(): void
    {
        Auth::requireAuth();
        Permission::requirePerm('create', 'venues');
        Csrf::requireValidToken();

        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($name)) {
            $_SESSION['flash_error'] = 'Venue name is required';
            header('Location: /venues/create');
            exit;
        }

        $venueId = Venue::create([
            'name' => $name,
            'address' => $address,
            'notes' => $notes,
            'created_by' => Auth::id()
        ]);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Venue created successfully';
        header('Location: /venues/' . $venueId);
        exit;
    }

    public static function edit(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'venues');

        $venue = Venue::find($id);
        if (!$venue) {
            $_SESSION['flash_error'] = 'Venue not found';
            header('Location: /venues');
            exit;
        }

        require __DIR__ . '/../views/venues/form.php';
    }

    public static function update(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('edit', 'venues');
        Csrf::requireValidToken();

        $venue = Venue::find($id);
        if (!$venue) {
            $_SESSION['flash_error'] = 'Venue not found';
            header('Location: /venues');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (empty($name)) {
            $_SESSION['flash_error'] = 'Venue name is required';
            header('Location: /venues/' . $id . '/edit');
            exit;
        }

        Venue::update($id, [
            'name' => $name,
            'address' => $address,
            'notes' => $notes
        ]);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Venue updated successfully';
        header('Location: /venues/' . $id);
        exit;
    }

    public static function delete(int $id): void
    {
        Auth::requireAuth();
        Permission::requirePerm('delete', 'venues');
        Csrf::requireValidToken();

        Venue::delete($id);

        Csrf::regenerate();
        $_SESSION['flash_success'] = 'Venue deleted successfully';
        header('Location: /venues');
        exit;
    }
}
