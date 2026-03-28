<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }

    public function __destruct() {
        if ($this->db) $this->db->close();
    }

    public function login($username, $password, $admin_only = false) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $security = new Security($this->db);

        if ($security->isIPBlocked($ip)) return "Your IP is currently blocked.";
        if ($security->isUserSuspended($username)) return "This account has been suspended.";

        $stmt = $this->db->prepare("SELECT id, username, email, password, role, status FROM tblclients WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user) {
            if ($admin_only && !in_array($user['role'], ['admin', 'staff'])) return "Access denied.";

            if (password_verify($password, $user['password'])) {
                if ($user['role'] === 'admin') {
                    if (!$security->isWhitelisted($ip)) {
                        require_once __DIR__ . '/email.php';
                        Email::send($user['email'], 'Admin Login Notification', "New login from IP: $ip.");
                    }
                }
                $security->logLoginAttempt($username, $ip, 'success');
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                return true;
            }
        }
        $security->logLoginAttempt($username, $ip, 'failed');
        return "Invalid username or password.";
    }

    public function logout() {
        session_destroy();
        return true;
    }

    public function isLoggedIn() { return isset($_SESSION['user_id']); }
    public function isAdmin() { return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; }
}
