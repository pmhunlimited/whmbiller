<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = $auth->login($_POST['username'], $_POST['password'], true);
    if ($res === true) {
        header('Location: /admin/index');
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
    <title>Admin Authorization - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1a202c; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { border-radius: 15px; padding: 40px; width: 100%; max-width: 400px; background: #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center mb-4 fw-bold text-danger">ADMIN LOGIN</h3>
        <?php if($error): ?>
            <div class="alert alert-danger small"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Admin Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-dark w-100 py-2">Authorize</button>
        </form>
    </div>
</body>
</html>
