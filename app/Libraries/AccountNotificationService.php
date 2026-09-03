<?php

namespace App\Libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once APPPATH . 'ThirdParty/PHPMailer/Exception.php';
require_once APPPATH . 'ThirdParty/PHPMailer/PHPMailer.php';
require_once APPPATH . 'ThirdParty/PHPMailer/SMTP.php';

class AccountNotificationService
{
    private static function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'pharmedisync@gmail.com';
            $mail->Password   = 'wsavchtqkwuxrmaq';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->SMTPOptions = [
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true],
            ];

            $mail->setFrom('pharmedisync@gmail.com', 'Robin Rose Trading');
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            log_message('error', 'Account notification failed for ' . $toEmail . ': ' . $mail->ErrorInfo);
            return false;
        }
    }

    public static function notifyApproved(string $email, string $name, string $reference): void
    {
        self::send(
            $email,
            $name,
            'Your PharMediSync Account Has Been Approved',
            "Hi {$name},\n\nGood news — your application (Reference: {$reference}) has been reviewed and approved by Robin Rose Trading.\n\nYou can now log in at the Partner Gateway using the email and password you registered with.\n\n— Robin Rose Trading"
        );
    }

    public static function notifyRejected(string $email, string $name, string $reference, ?string $reason = null): void
    {
        $reasonLine = $reason ? "\n\nReason: {$reason}" : '';
        self::send(
            $email,
            $name,
            'Update on Your PharMediSync Application',
            "Hi {$name},\n\nWe've reviewed your application (Reference: {$reference}) and are unable to approve it at this time.{$reasonLine}\n\nIf you believe this is a mistake or have questions, please contact Robin Rose Trading directly.\n\n— Robin Rose Trading"
        );
    }
}