<?php

namespace App\Libraries;

use App\Models\UserModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once APPPATH . 'ThirdParty/PHPMailer/Exception.php';
require_once APPPATH . 'ThirdParty/PHPMailer/PHPMailer.php';
require_once APPPATH . 'ThirdParty/PHPMailer/SMTP.php';

class PasswordResetService
{
    private static function sendMail(string $toEmail, string $toName, string $subject, string $body): bool
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
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];

            $mail->setFrom('pharmedisync@gmail.com', 'PharMediSync');
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            log_message('error', 'PasswordReset email failed for ' . $toEmail . ': ' . $mail->ErrorInfo);
            return false;
        }
    }

    public static function requestReset(string $email): void
    {
        $model = new UserModel();
        $user = $model->where('email', $email)->first();
        if (!$user) return;

        $db = \Config\Database::connect();
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $db->table('password_resets')->insert([
            'user_id'    => $user['user_id'],
            'code'       => $code,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+15 minutes')),
        ]);

        self::sendMail(
            $user['email'],
            $user['full_name'],
            'Your Login Verification Code',
            "Hi {$user['full_name']},\n\nYour verification code is: {$code}\n\nThis code expires in 15 minutes. Use it to verify your identity and regain access to your account — you can then change your password from your account settings.\n\nIf you didn't request this, you can safely ignore this email.\n\n— PharMediSync, Robin Rose Trading"
        );
    }

    public static function verifyCode(string $email, string $code): array
    {
        $model = new UserModel();
        $user = $model->where('email', $email)->first();
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid request.'];
        }

        $db = \Config\Database::connect();
        $reset = $db->table('password_resets')
            ->where('user_id', $user['user_id'])
            ->where('code', $code)
            ->where('is_used', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->orderBy('reset_id', 'DESC')
            ->get()->getRow();

        if (!$reset) {
            return ['success' => false, 'message' => 'That code is invalid or has expired. Please request a new one.'];
        }

        $db->table('password_resets')->where('reset_id', $reset->reset_id)->update(['is_used' => 1]);

        return ['success' => true, 'user' => $user];
    }
}