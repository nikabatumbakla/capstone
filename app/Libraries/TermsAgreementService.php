<?php

namespace App\Libraries;

use Config\Legal;

class TermsAgreementService
{

    public static function hasAgreedToCurrent(array $user): bool
    {
        $legal = new Legal();
        return $user['terms_version'] === $legal->currentVersion && !empty($user['terms_agreed_at']);
    }

    public static function recordAgreement(int $userId, string $ipAddress, string $userAgent): void
    {
        $legal = new Legal();
        $db = \Config\Database::connect();

        $db->table('users')->where('user_id', $userId)->update([
            'terms_agreed_at' => date('Y-m-d H:i:s'),
            'terms_version'   => $legal->currentVersion,
        ]);

        $db->table('terms_agreement_log')->insert([
            'user_id'       => $userId,
            'terms_version' => $legal->currentVersion,
            'ip_address'    => $ipAddress,
            'user_agent'    => $userAgent,
        ]);
    }
}