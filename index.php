<?php
/**
 * WHMBiller Root Index
 * Redirects or includes the main application from the migrate folder.
 */

$migrate_dir = __DIR__ . '/migrate';

if (!file_exists($migrate_dir . '/includes/config.php')) {
    // Not installed, go to installer
    header('Location: /migrate/install/');
    exit;
}

// If installed, load the main application index
require_once $migrate_dir . '/index.php';
