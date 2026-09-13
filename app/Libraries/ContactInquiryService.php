<?php

namespace App\Libraries;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once APPPATH . 'ThirdParty/PHPMailer/Exception.php';
require_once APPPATH . 'ThirdParty/PHPMailer/PHPMailer.php';
require_once APPPATH . 'ThirdParty/PHPMailer/SMTP.php';

class ContactInquiryService
{
    private const BUSINESS_INBOX = 'pharmedisync@gmail.com'; // swap to Robin Rose's real inbox when ready

    private static function baseMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
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
        $mail->setFrom('pharmedisync@gmail.com', 'Robin Rose Trading Website');
        return $mail;
    }

    // Sent to the business — subject/body carries everything, and Reply-To is set to the
    // inquirer's own email, so Robin Rose can just hit Reply and it goes straight to them.
    public static function notifyBusiness(string $firstName, string $lastName, string $email, ?string $phone, ?string $institution, string $inquiryType, ?string $products, string $message): bool
    {
        try {
            $mail = self::baseMailer();
            $mail->addAddress(self::BUSINESS_INBOX, 'Robin Rose Trading');
            $mail->addReplyTo($email, "{$firstName} {$lastName}");
            $mail->Subject = "New Website Inquiry: {$inquiryType} — {$firstName} {$lastName}";

            $body = "New inquiry received from the Robin Rose Trading website.\n\n";
            $body .= "Name: {$firstName} {$lastName}\n";
            $body .= "Email: {$email}\n";
            $body .= "Phone: " . ($phone ?: 'Not provided') . "\n";
            $body .= "Institution: " . ($institution ?: 'Not provided') . "\n";
            $body .= "Inquiry Type: {$inquiryType}\n";
            if ($products) $body .= "Products Needed: {$products}\n";
            $body .= "\nMessage:\n{$message}\n\n";
            $body .= "— Reply directly to this email to respond to {$firstName}.";

            $mail->Body = $body;
            $mail->send();
            return true;
        } catch (Exception $e) {
            log_message('error', 'Contact inquiry notification failed: ' . $mail->ErrorInfo);
            return false;
        }
    }

    public static function confirmToInquirer(string $firstName, string $email, string $inquiryType): bool
    {
        try {
            $mail = self::baseMailer();
            $mail->addAddress($email, $firstName);
            $mail->Subject = "We've received your inquiry — Robin Rose Trading";
            $mail->Body = "Hi {$firstName},\n\nThank you for reaching out to Robin Rose Trading regarding: {$inquiryType}.\n\nWe've received your message and our team will get back to you as soon as possible.\n\nIf you need immediate assistance, feel free to call or Viber us at 09292379053.\n\n— Robin Rose Trading";
            $mail->send();
            return true;
        } catch (Exception $e) {
            log_message('error', 'Contact confirmation email failed: ' . $mail->ErrorInfo);
            return false;
        }
    }
}