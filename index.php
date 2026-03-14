<?php
if (!file_exists(__DIR__ . '/includes/config.php')) {
    header('Location: /install/');
    exit;
}
require_once __DIR__ . '/includes/config.php';

// Host Validation for Resellers
$host = $_SERVER['HTTP_HOST'];
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
} catch (mysqli_sql_exception $e) {
    // If DB connection fails (e.g. after upload but before install), redirect to installer
    header('Location: /install/');
    exit;
}
$stmt = $db->prepare("SELECT user_id FROM reseller_settings WHERE custom_domain = ?");
$stmt->bind_param("s", $host);
$stmt->execute();
$res = $stmt->get_result();

$is_reseller_domain = ($res->num_rows > 0);
$main_domain = 'yourdomain.com'; // Should be in settings

if ($host !== $main_domain && !$is_reseller_domain) {
    http_response_code(404);
    include __DIR__ . '/templates/error_404.php';
    exit;
}

// Proceed to normal routing
header('Location: /user/index');
exit;
