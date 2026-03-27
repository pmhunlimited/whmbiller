<?php
require_once __DIR__ . '/../includes/auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /login');
    exit;
}
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$user_id = $_SESSION['user_id'];

$res = $db->query("SELECT * FROM tblhosting WHERE userid = $user_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Services - WHMBiller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
    <h2>My Services (WHMCS Style)</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Domain</th>
                <th>Status</th>
                <th>Next Due Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $res->fetch_assoc()): ?>
            <tr>
                <td>Hosting</td>
                <td><?php echo htmlspecialchars($row['domain']); ?></td>
                <td><span class="badge bg-primary"><?php echo $row['domainstatus']; ?></span></td>
                <td><?php echo $row['nextduedate']; ?></td>
            </tr>
            <?php endwhile; ?>
            <?php if($res->num_rows == 0): ?>
            <tr><td colspan="4" class="text-center">No active services.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
