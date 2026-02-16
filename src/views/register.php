<?php $pageTitle = 'Register'; ?>
<?php require __DIR__ . '/layout/header.php'; ?>

<div class="auth-card">
    <h2>Register</h2>
    <form method="POST" action="/register">
        <?= Csrf::field() ?>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control" required autofocus>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password_confirm">Confirm Password</label>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Register</button>
    </form>

    <p class="text-center mt-3">
        Already have an account? <a href="/login">Login here</a>
    </p>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
