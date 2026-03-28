<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/hooks.php';

class Security {
    private $db;

    public function __construct($db_connection = null) {
        if ($db_connection) {
            $this->db = $db_connection;
        } else {
            $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        }
    }

    public function logLoginAttempt($username, $ip, $status) {
        // Log in tblgatewaylog or a dedicated login logs table
        // For simplicity, we'll use a standard login log logic here but update table names
        $stmt = $this->db->prepare("INSERT INTO login_logs (username, ip_address, status) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $username, $ip, $status);
            $stmt->execute();
            $stmt->close();
        }

        if ($status === 'failed') {
            $this->handleFailedAttempt($username, $ip);
        } else {
            $this->handleSuccessfulLogin($username, $ip);
        }
    }

    private function getSetting($key, $default) {
        $stmt = $this->db->prepare("SELECT value FROM tblconfiguration WHERE setting = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $stmt->bind_result($val);
        $found = $stmt->fetch();
        $stmt->close();
        return $found ? $val : $default;
    }

    private function handleFailedAttempt($username, $ip) {
        $max_user_failures = (int)$this->getSetting('bf_max_user_failures', 5);
        $max_ip_failures = (int)$this->getSetting('bf_max_ip_failures', 10);

        // Track IP failures in tblipblocks
        $stmt = $this->db->prepare("INSERT INTO tblipblocks (ip_address, failed_attempts) VALUES (?, 1) ON DUPLICATE KEY UPDATE failed_attempts = failed_attempts + 1");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $stmt->close();

        // Check if IP should be blocked
        $stmt = $this->db->prepare("SELECT failed_attempts FROM tblipblocks WHERE ip_address = ?");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $stmt->bind_result($failures);
        $stmt->fetch();
        $stmt->close();

        if ($failures >= $max_ip_failures) {
            $this->blockIP($ip, '1 day');
        }

        // Track User failures in tblclients
        $stmt = $this->db->prepare("SELECT id FROM tblclients WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user_found = $stmt->fetch();
        $stmt->close();

        if ($user_found) {
            // WHMCS handles status in tblclients.status
            // We'll increment failures here (might need an extra column for granular control)
        } else {
            $this->blockIP($ip, '1 day');
        }
    }

    private function handleSuccessfulLogin($username, $ip) {
        $stmt = $this->db->prepare("INSERT INTO tblipblocks (ip_address, successful_sessions, failed_attempts, status, block_until) VALUES (?, 1, 0, 'none', NULL) ON DUPLICATE KEY UPDATE successful_sessions = successful_sessions + 1, failed_attempts = 0, status = IF(status = 'blacklist', 'none', status), block_until = NULL");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $stmt->close();

        $stmt = $this->db->prepare("UPDATE tblipblocks SET status = 'whitelist' WHERE ip_address = ? AND successful_sessions >= 5 AND status = 'none'");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $stmt->close();
    }

    public function blockIP($ip, $duration) {
        $block_until = date('Y-m-d H:i:s', strtotime('+' . $duration));
        $stmt = $this->db->prepare("INSERT INTO tblipblocks (ip_address, status, block_until) VALUES (?, 'blacklist', ?) ON DUPLICATE KEY UPDATE status = 'blacklist', block_until = ?");
        $stmt->bind_param("sss", $ip, $block_until, $block_until);
        $stmt->execute();
        $stmt->close();
    }

    public function isIPBlocked($ip) {
        $stmt = $this->db->prepare("SELECT status, block_until FROM tblipblocks WHERE ip_address = ?");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $stmt->bind_result($status, $block_until);
        $found = $stmt->fetch();
        $stmt->close();

        if ($found && $status === 'blacklist') {
            if (strtotime($block_until) > time()) {
                return true;
            } else {
                $this->db->query("UPDATE tblipblocks SET status = 'none', block_until = NULL, failed_attempts = 0 WHERE ip_address = '$ip'");
            }
        }
        return false;
    }

    public function isUserSuspended($username) {
        $stmt = $this->db->prepare("SELECT status FROM tblclients WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($status);
        $found = $stmt->fetch();
        $stmt->close();
        return ($found && strtolower($status) === 'inactive');
    }

    public function checkCountryBlock($ip) {
        return false; // Mock
    }

    public function isWhitelisted($ip) {
        $stmt = $this->db->prepare("SELECT status FROM tblipblocks WHERE ip_address = ?");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $stmt->bind_result($status);
        $found = $stmt->fetch();
        $stmt->close();
        return ($found && $status === 'whitelist');
    }
}
