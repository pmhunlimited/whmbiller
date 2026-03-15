<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (isset($_POST['action'])) {
    if ($_POST['action'] === 'suspend') {
        $id = (int)$_POST['user_id'];
        $db->query("UPDATE users SET status='suspended' WHERE id=$id");
    } elseif ($_POST['action'] === 'unsuspend') {
        $id = (int)$_POST['user_id'];
        $db->query("UPDATE users SET status='active' WHERE id=$id");
    }
}

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Management - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fc; }
        .card { border-radius: 15px; border: none; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people text-primary"></i> Client Management</h2>
        <a href="/admin/index" class="btn btn-secondary">Back</a>
    </div>

    <div class="card p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><span class="badge bg-info"><?php echo ucfirst($user['role']); ?></span></td>
                        <td>
                            <span class="badge <?php echo $user['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <?php if($user['status'] === 'active'): ?>
                                    <input type="hidden" name="action" value="suspend">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Suspend</button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="unsuspend">
                                    <button type="submit" class="btn btn-sm btn-outline-success">Unsuspend</button>
                                <?php endif; ?>
                            </form>
                            <a href="#" class="btn btn-sm btn-outline-primary">Edit</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
