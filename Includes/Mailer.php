<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/config.php';

class MailManager {
    
    // Send a general notification email
    public static function send($to, $subject, $message) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            // Support both 465 (SSL) and 587 (TLS)
            $mail->SMTPSecure = (MAIL_PORT == 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;
            $mail->Timeout    = 1.5; // Set SMTP connection timeout to 1.5 seconds

            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            
            $htmlMessage = "
            <div style='font-family: Arial, sans-serif; color: #334155; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                <div style='text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 20px;'>
                    <h2 style='color: #1e3a8a; margin: 0;'>SCRMS Notification</h2>
                </div>
                <div style='line-height: 1.6;'>
                    $message
                </div>
                <div style='margin-top: 30px; padding-top: 15px; border-top: 1px solid #f1f5f9; font-size: 12px; color: #94a3b8; text-align: center;'>
                    &copy; " . date('Y') . " SCRMS. This is an automated message.
                </div>
            </div>
            ";
            
            $mail->Body = $htmlMessage;
            $mail->AltBody = strip_tags($message);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    // Send a general notification email asynchronously using a background process
    public static function sendAsync($to, $subject, $message) {
        $payload = [
            'to' => $to,
            'subject' => $subject,
            'message' => $message
        ];

        $dir = __DIR__ . '/temp_mail_queue';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $file = $dir . '/mail_job_' . microtime(true) . '_' . uniqid() . '.json';
        file_put_contents($file, json_encode($payload));

        $backgroundScript = __DIR__ . '/background-mailer.php';

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd = "start /B " . PHP_BINARY . " " . escapeshellarg($backgroundScript) . " " . escapeshellarg($file) . " > NUL 2> NUL";
            pclose(popen($cmd, "r"));
        } else {
            exec(PHP_BINARY . " " . escapeshellarg($backgroundScript) . " " . escapeshellarg($file) . " > /dev/null 2>&1 &");
        }
        return true;
    }

    // Notify user about a status change
    public static function notifyStatusChange($email, $complaintTitle, $statusLabel, $message = '') {
        $subject = "Update on your complaint: $complaintTitle";
        $body = "
            <p>Dear User,</p>
            <p>There has been a status update for your complaint: <strong>$complaintTitle</strong>.</p>
            <p><strong>New Status:</strong> $statusLabel</p>
            " . (!empty($message) ? "<p><strong>Remark from Admin:</strong> $message</p>" : "") . "
            <p>You can log in to your dashboard to see more details.</p>
        ";
        return self::sendAsync($email, $subject, $body);
    }

    // Notify Admin about a new complaint
    public static function notifyNewComplaint($email, $complaintTitle, $categoryLabel) {
        $subject = "New Complaint Filed: $complaintTitle";
        $body = "
            <p>Hello Admin,</p>
            <p>A new complaint has been filed in the category: <strong>$categoryLabel</strong>.</p>
            <p><strong>Title:</strong> $complaintTitle</p>
            <p>Please log in to the admin panel to review and address this complaint.</p>
        ";
        return self::sendAsync($email, $subject, $body);
    }
}
