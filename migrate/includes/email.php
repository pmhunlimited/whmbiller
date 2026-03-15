<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$autoload_path = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
} else {
    // Fallback if the path above is still not correct in some environments
    $fallback_path = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($fallback_path)) {
        require_once $fallback_path;
    }
}

require_once __DIR__ . '/config.php';

class Email {
    public static function send($to, $subject, $body) {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $settings = [];
        $res = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'smtp_%'");
        while($row = $res->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $settings['smtp_host'] ?? '';
            $mail->SMTPAuth   = true;
            $mail->Username   = $settings['smtp_user'] ?? '';
            $mail->Password   = $settings['smtp_pass'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $settings['smtp_port'] ?? 587;

            $mail->setFrom($settings['smtp_from'] ?? 'noreply@whmbiller.com', 'WHMBiller');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email failed: " . $mail->ErrorInfo);
            return false;
        }
    }
}
