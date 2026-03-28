<?php
session_start();
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

function checkExtensions($exts) {
    $results = [];
    foreach ($exts as $ext) {
        $results[$ext] = extension_loaded($ext);
    }
    return $results;
}

$required_exts = ['mysqli', 'curl', 'openssl', 'mbstring', 'gd'];
$ext_results = checkExtensions($required_exts);
$php_version = phpversion();
$php_ok = version_compare($php_version, '8.0.0', '>=');
$exts_ok = !in_array(false, $ext_results);

if ($step === 1) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WHMBiller Installer - Stage 1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .stat-card { border-radius: 12px; padding: 20px; background: #fff; margin-bottom: 20px; border: 1px solid #eee; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card p-4">
                <h2 class="text-center mb-4">Welcome to WHMBiller</h2>
                <p class="text-muted text-center">Stage 1: System Requirements Check</p>

                <div class="stat-card">
                    <h5>PHP Version</h5>
                    <p>Required: 8.0+, Current: <?php echo $php_version; ?>
                    <?php echo $php_ok ? '<span class="text-success">✔</span>' : '<span class="text-danger">✘</span>'; ?></p>
                </div>

                <div class="stat-card">
                    <h5>PHP Extensions</h5>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($ext_results as $ext => $ok): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo $ext; ?>
                            <?php echo $ok ? '<span class="text-success">✔</span>' : '<span class="text-danger">✘</span>'; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="text-center mt-4">
                    <?php if ($php_ok && $exts_ok): ?>
                        <a href="?step=2" class="btn btn-primary px-5">Continue to Stage 2</a>
                    <?php else: ?>
                        <div class="alert alert-danger">Please resolve the requirements above to continue.</div>
                        <button onclick="window.location.reload();" class="btn btn-secondary">Retry</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php
} elseif ($step === 2) {
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $host = $_POST['db_host'];
        $user = $_POST['db_user'];
        $pass = $_POST['db_pass'];
        $name = $_POST['db_name'];

        try {
            $conn = new mysqli($host, $user, $pass, $name);
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }

            // Execute Schema
            $schema = file_get_contents(__DIR__ . '/schema.sql');
            if ($conn->multi_query($schema)) {
                do {
                    if ($res = $conn->store_result()) {
                        $res->free();
                    }
                } while ($conn->more_results() && $conn->next_result());
            } else {
                throw new Exception("Error executing schema: " . $conn->error);
            }

            // Save config
            $config_content = "<?php\ndefine('DB_HOST', '$host');\ndefine('DB_USER', '$user');\ndefine('DB_PASS', '$pass');\ndefine('DB_NAME', '$name');\n";
            file_put_contents(__DIR__ . '/../includes/config.php', $config_content);

            header('Location: ?step=3');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WHMBiller Installer - Stage 2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4">
                <h2 class="text-center mb-4">Database Configuration</h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">DB Host</label>
                        <input type="text" name="db_host" class="form-control" value="localhost" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">DB User</label>
                        <input type="text" name="db_user" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">DB Password</label>
                        <input type="password" name="db_pass" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">DB Name</label>
                        <input type="text" name="db_name" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Test & Install Schema</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php
} elseif ($step === 3) {
    require_once __DIR__ . '/../includes/config.php';
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user = $_POST['admin_user'];
        $email = $_POST['admin_email'];
        $pass = password_hash($_POST['admin_pass'], PASSWORD_DEFAULT);

        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }

            $stmt = $conn->prepare("INSERT INTO tblclients (username, email, password, role) VALUES (?, ?, ?, 'admin')");
            if (!$stmt) {
                throw new Exception("Preparation failed: " . $conn->error);
            }
            $stmt->bind_param("sss", $user, $email, $pass);
            if (!$stmt->execute()) {
                throw new Exception("Error creating admin account: " . $stmt->error);
            }

            header('Location: ?step=4');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WHMBiller Installer - Stage 3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4">
                <h2 class="text-center mb-4">Admin Account Setup</h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Admin Username</label>
                        <input type="text" name="admin_user" class="form-control" value="admin" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Admin Email</label>
                        <input type="email" name="admin_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Admin Password</label>
                        <input type="password" name="admin_pass" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Admin Account</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php
} elseif ($step === 4) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WHMBiller Installer - Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card p-5 text-center">
                <div class="mb-4">
                    <span class="display-1 text-success">✔</span>
                </div>
                <h2 class="mb-4">Congratulations!</h2>
                <p class="lead mb-4">WHMBiller has been successfully installed.</p>

                <div class="alert alert-warning text-start">
                    <h5>Important Next Steps:</h5>
                    <ul>
                        <li><strong>Security:</strong> Please delete the <code>install/</code> directory immediately.</li>
                        <li><strong>Admin Access:</strong> You can now log in to the admin panel using the credentials you created.</li>
                        <li><strong>Configuration:</strong> Set up your cron jobs to automate billing and provisioning tasks.</li>
                    </ul>
                </div>

                <div class="mt-4">
                    <a href="/admin/index" class="btn btn-primary btn-lg px-5">Go to Admin Panel</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php
}
?>
