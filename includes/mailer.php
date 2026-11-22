<?php
require_once __DIR__ . '/../libs/phpmailer/Exception.php';
require_once __DIR__ . '/../libs/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../libs/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Sends a password reset email.
 *
 * @param string $toEmail
 * @param string $toName
 * @param string $resetLink
 * @return bool
 */
function send_reset_email(string $toEmail, string $toName, string $resetLink): bool {
    $envPath = __DIR__ . '/../.env';
    if (!file_exists($envPath)) {
        error_log('Mailer Error: .env file missing.');
        return false;
    }
    
    $env = parse_ini_file($envPath);
    
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $env['MAIL_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $env['MAIL_USER'] ?? '';
        $mail->Password   = $env['MAIL_PASS'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $env['MAIL_PORT'] ?? 587;

        // Recipients
        $mail->setFrom($env['MAIL_FROM'] ?? 'no-reply@studydesk.com', $env['MAIL_FROM_NAME'] ?? 'StudyDesk');
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Your StudyDesk Password';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                <h2 style='color: #0F172A;'>Password Reset Request</h2>
                <p style='color: #64748B;'>Hello " . htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') . ",</p>
                <p style='color: #64748B;'>We received a request to reset your StudyDesk password. Click the button below to create a new password.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . "' style='background-color: #3B82F6; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;'>Reset Password</a>
                </div>
                <p style='color: #64748B; font-size: 12px;'>If you didn't request this, you can safely ignore this email. This link will expire in 1 hour.</p>
            </div>
        ";
        $mail->AltBody = "Hello " . $toName . ",\n\nClick this link to reset your password: " . $resetLink . "\n\nThis link expires in 1 hour.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}