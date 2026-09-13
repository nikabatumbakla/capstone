<?php

namespace App\Libraries;

class AdminNotifier
{
    public static function newRegistration(string $applicantType, string $applicantName, string $reference): void
    {
        $db = \Config\Database::connect();
        $rows = $db->table('store_settings')->get()->getResultArray();
        $storeInfo = [];
        foreach ($rows as $row) $storeInfo[$row['setting_key']] = $row['setting_value'];

        $adminEmail = $storeInfo['store_email'] ?? null;
        if (!$adminEmail) return; // no admin email on file — skip silently, never block registration over this

        $reviewUrl = base_url('admin/management/user-management');
        $subject = "New {$applicantType} Registration Pending Review — {$reference}";

        $body = "
            <div style='font-family: Arial, sans-serif; max-width:560px; margin:auto; color:#1a0505;'>
                <div style='background:#4a0000; padding:20px; text-align:center;'>
                    <h2 style='color:#fff; margin:0;'>PharMediSync</h2>
                    <p style='color:#f0d0d0; margin:4px 0 0; font-size:12px;'>Robin Rose Trading — Partner Gateway</p>
                </div>
                <div style='padding:24px; border:1px solid #eee; border-top:none;'>
                    <p>Dear Administrator,</p>
                    <p>A new <strong>{$applicantType}</strong> account has submitted a registration application through the Partner Gateway and is now awaiting your review.</p>
                    <table style='width:100%; font-size:13px; margin:16px 0; border-collapse:collapse;'>
                        <tr><td style='padding:6px 0; color:#666;'>Applicant Name</td><td style='padding:6px 0; font-weight:bold;'>{$applicantName}</td></tr>
                        <tr><td style='padding:6px 0; color:#666;'>Account Type</td><td style='padding:6px 0; font-weight:bold;'>{$applicantType}</td></tr>
                        <tr><td style='padding:6px 0; color:#666;'>Reference Number</td><td style='padding:6px 0; font-weight:bold;'>{$reference}</td></tr>
                        <tr><td style='padding:6px 0; color:#666;'>Submitted</td><td style='padding:6px 0; font-weight:bold;'>" . date('F d, Y — g:i A') . "</td></tr>
                    </table>
                    <p>Please log in to the PharMediSync Admin Portal to review the submitted details and approve or decline this application.</p>
                    <div style='text-align:center; margin:28px 0;'>
                        <a href='{$reviewUrl}' style='background:#4a0000; color:#fff; text-decoration:none; padding:12px 28px; border-radius:30px; font-weight:bold; font-size:13px;'>Review Application</a>
                    </div>
                    <p style='font-size:11px; color:#999;'>This is an automated notification from PharMediSync. Please do not reply directly to this email.</p>
                </div>
            </div>";

        try {
            $email = \Config\Services::email();
            $email->setTo($adminEmail);
            $email->setSubject($subject);
            $email->setMessage($body);
            $email->send();
        } catch (\Throwable $e) {
            log_message('error', 'AdminNotifier failed: ' . $e->getMessage());
        }
    }
}