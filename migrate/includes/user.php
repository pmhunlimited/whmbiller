<?php
require_once __DIR__ . '/auth.php';

class User {
    private $db;

    public function __construct() {
        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    }

    public function register($username, $email, $password, $tos_accepted) {
        if (!$tos_accepted) {
            return "You must accept the Terms of Service and Privacy Policy.";
        }

        if (empty($username) || empty($email) || empty($password)) {
            return "All fields are required.";
        }

        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_pass);

        if ($stmt->execute()) {
            return true;
        } else {
            return "Error: " . $stmt->error;
        }
    }
}
