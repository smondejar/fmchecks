<?php
$pageTitle = 'Settings';
$currentPage = 'settings';
require __DIR__ . '/../layout/header.php';
?>

<div class="page-header">
    <h1>System Settings</h1>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/settings/update">
            <?= Csrf::field() ?>

            <?php
            $settingsMap = [];
            foreach ($settings as $setting) {
                $settingsMap[$setting['setting_key']] = $setting['setting_value'];
            }
            ?>

            <div class="form-group">
                <label for="site_name">Site Name</label>
                <input type="text" id="site_name" name="site_name" class="form-control"
                       value="<?= htmlspecialchars($settingsMap['site_name'] ?? 'FM Checks') ?>">
            </div>

            <div class="form-group">
                <label for="site_timezone">Timezone</label>
                <select id="site_timezone" name="site_timezone" class="form-control">
                    <option value="UTC" <?= ($settingsMap['site_timezone'] ?? 'UTC') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                    <option value="America/New_York" <?= ($settingsMap['site_timezone'] ?? '') === 'America/New_York' ? 'selected' : '' ?>>America/New_York</option>
                    <option value="America/Chicago" <?= ($settingsMap['site_timezone'] ?? '') === 'America/Chicago' ? 'selected' : '' ?>>America/Chicago</option>
                    <option value="America/Los_Angeles" <?= ($settingsMap['site_timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' ?>>America/Los_Angeles</option>
                    <option value="Europe/London" <?= ($settingsMap['site_timezone'] ?? '') === 'Europe/London' ? 'selected' : '' ?>>Europe/London</option>
                </select>
            </div>

            <div class="form-group">
                <label for="check_reminder_hours">Check Reminder Hours</label>
                <input type="number" id="check_reminder_hours" name="check_reminder_hours" class="form-control"
                       value="<?= htmlspecialchars($settingsMap['check_reminder_hours'] ?? '24') ?>">
                <small class="form-text">Hours before check due to show amber warning</small>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="allow_registration"
                           <?= ($settingsMap['allow_registration'] ?? '0') === '1' ? 'checked' : '' ?>>
                    Allow Public Registration
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
