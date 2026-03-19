<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SESSION['user_id'] ?? '') {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $connection->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $_POST['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login — Simplx CMS</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-box">
        <img src="simplx-logo.png" alt="Simplx" class="auth-logo">
        <h2>Sign in to your account</h2>

        <?php if ($error) { ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error) ?></div>
        <?php } ?>

        <form method="POST" action="login.php">
            <fieldset>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit" class="btn-login">Log In</button>
            </fieldset>
        </form>
    </div>
</div>
</body>
</html>
