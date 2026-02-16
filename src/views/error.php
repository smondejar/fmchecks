<?php
$pageTitle = 'Error';
require __DIR__ . '/layout/header.php';
?>

<div class="card">
    <div class="card-body text-center">
        <h1 class="error-code"><?= $errorCode ?? '500' ?></h1>
        <h2><?= $errorTitle ?? 'An Error Occurred' ?></h2>
        <p><?= $errorMessage ?? 'Something went wrong. Please try again later.' ?></p>
        <a href="/dashboard" class="btn btn-primary mt-3">Go to Dashboard</a>
    </div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
