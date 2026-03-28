<?php
if (!file_exists(__DIR__ . '/includes/config.php')) {
    header('Location: /install/');
    exit;
}
require_once __DIR__ . '/includes/config.php';

// Host Validation for Resellers (Optimized)
$host = $_SERVER['HTTP_HOST'];
$main_domain = 'yourdomain.com'; // Should ideally be in config.php

if ($host !== $main_domain) {
    try {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $stmt = $db->prepare("SELECT user_id FROM reseller_settings WHERE custom_domain = ?");
        $stmt->bind_param("s", $host);
        $stmt->execute();
        $res = $stmt->get_result();
        $is_reseller_domain = ($res->num_rows > 0);
        $stmt->close();
        $db->close();
    } catch (mysqli_sql_exception $e) {
        header('Location: /install/');
        exit;
    }

    if (!$is_reseller_domain) {
        http_response_code(404);
        include __DIR__ . '/templates/error_404.php';
        exit;
    }
}

// Proceed to normal routing
require_once __DIR__ . '/includes/auth.php';
$auth = new Auth();
if ($auth->isLoggedIn()) {
    if ($auth->isAdmin()) {
        header('Location: /admin/index');
    } else {
        header('Location: /client/index');
    }
} else {
    if (strpos($_SERVER['REQUEST_URI'], '/admin') !== false) {
        header('Location: /admin/authorize');
    } else {
        header('Location: /login');
    }
}
exit;
