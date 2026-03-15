<?php
require_once __DIR__ . '/includes/user.php';
$user_obj = new User();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = $user_obj->register($_POST['username'], $_POST['email'], $_POST['password'], isset($_POST['tos']));
    if ($res === true) {
        $success = "Registration successful! You can now <a href='/login'>login</a>.";
    } else {
        $error = $res;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { border-radius: 20px; padding: 40px; width: 100%; max-width: 450px; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center mb-4 fw-bold text-primary">Join WHMBiller</h3>
        <?php if($error): ?>
            <div class="alert alert-danger small"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert alert-success small"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="tos" class="form-check-input" id="tos" required>
                <label class="form-check-label small" for="tos">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Create Account</button>
        </form>
        <div class="text-center mt-3 small">
            Already have an account? <a href="/login">Sign In</a>
        </div>
    </div>
</body>
</html>
