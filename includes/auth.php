<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';

class Auth {
    private $db;
    private $security;

    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->security = new Security();
    }

    public function login($username, $password) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        if ($this->security->isIPBlocked($ip)) {
            return "Your IP is currently blocked due to multiple failed login attempts.";
        }

        if ($this->security->isUserSuspended($username)) {
            return "This account has been suspended.";
        }

        if ($this->security->checkCountryBlock($ip)) {
            return "Access from your country is restricted.";
        }

        $stmt = $this->db->prepare("SELECT id, username, password, role, status FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $this->security->logLoginAttempt($username, $ip, 'success');
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                return true;
            }
        }

        $this->security->logLoginAttempt($username, $ip, 'failed');
        return "Invalid username or password.";
    }

    public function logout() {
        session_destroy();
        return true;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}
