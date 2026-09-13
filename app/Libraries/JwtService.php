<?php

namespace App\Libraries;

class JwtService
{
    // Keep this secret consistent across the app and never expose it publicly.
    // In a real production system this would live in .env, not hardcoded.
    private static string $secret = 'pharmedisync-2026-session-signing-key';

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function generate(int $userId, string $role, int $expiresInSeconds = 7200): string
    {
        $header = self::base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));

        $payload = self::base64UrlEncode(json_encode([
            'user_id' => $userId,
            'role'    => $role,
            'iat'     => time(),
            'exp'     => time() + $expiresInSeconds,
        ]));

        $signature = self::base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payload}", self::$secret, true)
        );

        return "{$header}.{$payload}.{$signature}";
    }

    // Not currently called anywhere in the app (auth still runs on PHP sessions),
    // but included so the token stored in user_sessions is genuinely verifiable, not decorative.
    public static function verify(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $payload, $signature] = $parts;
        $expectedSignature = self::base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payload}", self::$secret, true)
        );

        if (!hash_equals($expectedSignature, $signature)) return null;

        $decoded = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
        if (!$decoded || ($decoded['exp'] ?? 0) < time()) return null;

        return $decoded;
    }
}