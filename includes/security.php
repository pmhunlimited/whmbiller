<?php
require_once __DIR__ . '/config.php';

class Security {
    private $db;

    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }

    public function logLoginAttempt($username, $ip, $status) {
        $stmt = $this->db->prepare("INSERT INTO login_logs (username, ip_address, status) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $ip, $status);
        $stmt->execute();

        if ($status === 'failed') {
            $this->handleFailedAttempt($username, $ip);
        } else {
            $this->handleSuccessfulLogin($username, $ip);
        }
    }

    private function getSetting($key, $default) {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $stmt->bind_result($val);
        if ($stmt->fetch()) {
            return $val;
        }
        return $default;
    }

    private function handleFailedAttempt($username, $ip) {
        $max_user_failures = $this->getSetting('bf_max_user_failures', 5);
        $max_ip_failures = $this->getSetting('bf_max_ip_failures', 10);
        $bf_period = $this->getSetting('bf_period', 15);

        // Track IP failures
        $stmt = $this->db->prepare("INSERT INTO ip_protection (ip_address, failed_attempts) VALUES (?, 1) ON DUPLICATE KEY UPDATE failed_attempts = failed_attempts + 1");
        $stmt->bind_param("s", $ip);
        $stmt->execute();

        // Check if IP should be blocked
        $stmt = $this->db->prepare("SELECT failed_attempts FROM ip_protection WHERE ip_address = ?");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $stmt->bind_result($failures);
        $stmt->fetch();
        $stmt->close();

        if ($failures >= $max_ip_failures) {
            $this->blockIP($ip, '1 day');
        }

        // Track User failures and suspend if user exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM login_logs WHERE username = ? AND status = 'failed' AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
            $stmt->bind_param("si", $username, $bf_period);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count >= $max_user_failures) {
                if ($username === 'admin' || $username === 'administrator') {
                    if ($this->getSetting('bf_lock_admin', '0') === '0') {
                        return; // Don't lock admin unless explicitly allowed
                    }
                }
                $stmt = $this->db->prepare("UPDATE users SET status = 'suspended' WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();

                // Send notification
                require_once __DIR__ . '/email.php';
                Email::send('admin@whmbiller.com', 'Brute Force User Detected', "User $username has been suspended due to too many failed login attempts from IP $ip.");
            }
        } else {
            // User doesn't exist, block IP
            $this->blockIP($ip, '1 day');
        }
    }

    private function handleSuccessfulLogin($username, $ip) {
        // Increment successful sessions and unban IP if it was blocked
        $stmt = $this->db->prepare("INSERT INTO ip_protection (ip_address, successful_sessions, failed_attempts, status, block_until) VALUES (?, 1, 0, 'none', NULL) ON DUPLICATE KEY UPDATE successful_sessions = successful_sessions + 1, failed_attempts = 0, status = IF(status = 'blacklist', 'none', status), block_until = NULL");
        $stmt->bind_param("s", $ip);
        $stmt->execute();

        // Whitelist after 5 sessions
        $stmt = $this->db->prepare("UPDATE ip_protection SET status = 'whitelist' WHERE ip_address = ? AND successful_sessions >= 5 AND status = 'none'");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
    }


    public function blockIP($ip, $duration) {
        $block_until = date('Y-m-d H:i:s', strtotime('+' . $duration));
        $stmt = $this->db->prepare("INSERT INTO ip_protection (ip_address, status, block_until) VALUES (?, 'blacklist', ?) ON DUPLICATE KEY UPDATE status = 'blacklist', block_until = ?");
        $stmt->bind_param("sss", $ip, $block_until, $block_until);
        $stmt->execute();
    }

    public function isIPBlocked($ip) {
        $stmt = $this->db->prepare("SELECT status, block_until FROM ip_protection WHERE ip_address = ?");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            if ($row['status'] === 'blacklist') {
                if (strtotime($row['block_until']) > time()) {
                    return true;
                } else {
                    // Block expired
                    $this->db->query("UPDATE ip_protection SET status = 'none', block_until = NULL, failed_attempts = 0 WHERE ip_address = '$ip'");
                }
            }
        }
        return false;
    }

    public function isUserSuspended($username) {
        $stmt = $this->db->prepare("SELECT status FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($status);
        if ($stmt->fetch()) {
            return $status === 'suspended';
        }
        return false;
    }

    public function checkCountryBlock($ip) {
        // Simple country lookup (in real app would use GeoIP)
        // For now, assume a placeholder that works with our country_protection table
        $country_code = 'US'; // Example
        $stmt = $this->db->prepare("SELECT status FROM country_protection WHERE country_code = ?");
        $stmt->bind_param("s", $country_code);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            if ($row['status'] === 'blacklisted') {
                return true;
            }
        }
        return false;
    }

    public function isWhitelisted($ip) {
        $stmt = $this->db->prepare("SELECT status FROM ip_protection WHERE ip_address = ?");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $stmt->bind_result($status);
        if ($stmt->fetch()) {
            return $status === 'whitelist';
        }
        return false;
    }
}
