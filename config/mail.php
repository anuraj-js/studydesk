<?php
// config/mail.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once(__DIR__ . "/../phpmailer/PHPMailer.php");
require_once(__DIR__ . "/../phpmailer/SMTP.php");
require_once(__DIR__ . "/../phpmailer/Exception.php");

// Load environment variables from .env
require_once(__DIR__ . "/env.php");

function sendResetEmail($toEmail, $toName, $resetLink) {
    $mail = new PHPMailer(true);

    try {
        // Server settings - loaded from .env
        $mail->isSMTP();
        $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USERNAME') ?: '';
        $mail->Password   = getenv('SMTP_PASSWORD') ?: '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = getenv('SMTP_PORT') ?: 587;

        // Recipients
        $mail->setFrom(getenv('SMTP_USERNAME') ?: 'studydesk.dev@gmail.com', 'StudyDesk');
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password - StudyDesk';
        $mail->Body = "
            <div style='font-family: Inter, Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0;'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <h2 style='color: #2563EB; font-weight: 700; margin: 0;'>StudyDesk</h2>
                    <p style='color: #64748B; margin: 4px 0 0;'>Reset Your Password</p>
                </div>
                <div style='padding: 10px 0;'>
                    <p style='color: #0F172A; font-size: 16px;'>Hello <strong>" . htmlspecialchars($toName) . "</strong>,</p>
                    <p style='color: #475569; font-size: 14px; line-height: 1.6;'>We received a request to reset your password. Click the button below to set a new password:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . $resetLink . "' style='background: #2563EB; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-block;'>Reset Password</a>
                    </div>
                    <p style='color: #64748B; font-size: 13px;'>Or copy this link: <br><a href='" . $resetLink . "' style='color: #2563EB; word-break: break-all;'>" . $resetLink . "</a></p>
                    <p style='color: #94A3B8; font-size: 13px; margin-top: 20px;'>This link will expire in <strong>1 hour</strong>.</p>
                    <p style='color: #94A3B8; font-size: 13px;'>If you didn't request this, you can safely ignore this email.</p>
                </div>
                <div style='text-align: center; padding-top: 20px; border-top: 1px solid #e2e8f0; margin-top: 10px;'>
                    <p style='color: #94A3B8; font-size: 12px;'>StudyDesk · Student Productivity App</p>
                </div>
            </div>
        ";
        $mail->AltBody = "Reset your password: " . $resetLink;

        $mail->send();
        return ['success' => true];
    } catch (Exception $e) {
        $errorMessage = $mail->ErrorInfo ?: $e->getMessage();
        error_log("Mail error: " . $errorMessage . " | To: " . $toEmail);
        return [
            'success' => false,
            'message' => 'Failed to send password reset email. Please try again later.'
        ];
    }
}
?>