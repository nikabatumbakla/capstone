<?php

namespace App\Libraries;

class SessionTrackerService
{
    public static function recordLogin(int $userId, string $role, \CodeIgniter\HTTP\IncomingRequest $request): void
    {
        $db = \Config\Database::connect();
        $sessionId = session_id() ?: bin2hex(random_bytes(20));

        $jwt = JwtService::generate($userId, $role, 7200);

        $db->table('user_sessions')->insert([
            'session_id'  => $sessionId,
            'user_id'     => $userId,
            'device_info' => substr((string) $request->getUserAgent(), 0, 255),
            'ip_address'  => $request->getIPAddress(),
            'jwt_token'   => $jwt,
            'expires_at'  => date('Y-m-d H:i:s', strtotime('+2 hours')),
        ]);
    }

    public static function touchActivity(): void
    {
        $sessionId = session_id();
        if (!$sessionId) return;

        $db = \Config\Database::connect();
        $db->table('user_sessions')->where('session_id', $sessionId)->update([
            'last_active' => date('Y-m-d H:i:s'),
            'expires_at'  => date('Y-m-d H:i:s', strtotime('+2 hours')),
        ]);
    }

    public static function endSession(): void
    {
        $sessionId = session_id();
        if (!$sessionId) return;

        $db = \Config\Database::connect();
        $db->table('user_sessions')->where('session_id', $sessionId)->delete();
    }
}