<?php
require_once __DIR__ . '/includes/auth.php';
$auth = new Auth();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = $auth->login($_POST['username'], $_POST['password']);
    if ($res === true) {
        header('Location: /client/index');
        exit;
    } else {
        $error = $res;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { border-radius: 20px; padding: 40px; width: 100%; max-width: 400px; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center mb-4 fw-bold text-primary">WHMBiller</h3>
        <?php if($error): ?>
            <div class="alert alert-danger small"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Sign In</button>
        </form>
        <div class="text-center mt-3 small">
            Don't have an account? <a href="/register">Register</a>
        </div>
    </div>
</body>
</html>
