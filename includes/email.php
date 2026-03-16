<?php
// Use manual requiring for maximum robustness in all environments
$vendor_path = dirname(__DIR__) . '/vendor/phpmailer/phpmailer/src/';
require_once $vendor_path . 'Exception.php';
require_once $vendor_path . 'PHPMailer.php';
require_once $vendor_path . 'SMTP.php';

require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email {
    private static $settings_cache = null;

    private static function getSettings() {
        if (self::$settings_cache !== null) return self::$settings_cache;

        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $settings = [];
        $res = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'smtp_%'");
        while($row = $res->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $db->close();
        self::$settings_cache = $settings;
        return $settings;
    }

    public static function send($to, $subject, $body) {
        $settings = self::getSettings();
        if (empty($settings['smtp_host'])) return false;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $settings['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $settings['smtp_user'] ?? '';
            $mail->Password   = $settings['smtp_pass'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $settings['smtp_port'] ?? 587;

            // Strict timeouts to prevent login hangs
            $mail->Timeout    = 5;
            $mail->SMTPConnectTimeout = 3;

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
