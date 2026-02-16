<?php $pageTitle = 'Login'; ?>
<?php require __DIR__ . '/layout/header.php'; ?>

<div class="auth-card">
    <h2>Login</h2>
    <form method="POST" action="/login">
        <?= Csrf::field() ?>

        <div class="form-group">
            <label for="username">Username or Email</label>
            <input type="text" id="username" name="username" class="form-control" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>

    <?php if (Setting::get('allow_registration', '0') === '1'): ?>
    <p class="text-center mt-3">
        Don't have an account? <a href="/register">Register here</a>
    </p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
