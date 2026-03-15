<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_notifications') {
        $login_notif = isset($_POST['login_notifications']) ? 1 : 0;
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $key = "user_notif_login_" . $user_id;
        $val = (string)$login_notif;
        $stmt->bind_param("sss", $key, $val, $val);
        $stmt->execute();
        $msg = "Profile updated successfully!";
    }
}

$stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
$key = "user_notif_login_" . $user_id;
$stmt->bind_param("s", $key);
$stmt->execute();
$stmt->bind_result($login_notif);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f4f7f6; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Profile</h2>
        <a href="/client/index" class="btn btn-secondary">Back</a>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-4 text-center mb-4">
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['username']; ?>&size=128" class="rounded-circle mx-auto mb-3" alt="avatar">
                <h4><?php echo htmlspecialchars($_SESSION['username']); ?></h4>
                <p class="text-muted">Client Account</p>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card p-4 mb-4">
                <h5>Account Security</h5>
                <form method="POST">
                    <input type="hidden" name="action" value="update_notifications">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Login Notifications</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="login_notifications" id="loginNotif" <?php echo $login_notif === '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="loginNotif">Receive an email whenever someone logs into your account.</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Two-Factor Authentication (2FA)</label>
                        <p class="small text-muted">Enhanced security by requiring a code from your mobile device.</p>
                        <button type="button" class="btn btn-outline-primary btn-sm">Setup 2FA</button>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>

            <div class="card p-4">
                <h5>Update Password</h5>
                <form>
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-outline-secondary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
