<?php
namespace App\Services;
// JWT Service for StrikeCircle
// Usage: JWTService::encode($claims, $ttlSeconds), JWTService::decode($jwt)

class JWTService {
    private static string $algo = 'HS256';
    private static ?string $secret = null;

    private static function getSecret(): string {
        if (!self::$secret) {
            self::$secret = \App\Config\Env::get('JWT_SECRET', 'changeme');
        }
        return self::$secret;
    }

    private static function getIssuer(): string {
        return \App\Config\Env::get('JWT_ISS', 'https://strikecircle.local');
    }
    private static function getAudience(): string {
        return \App\Config\Env::get('JWT_AUD', 'strikecircle_app');
    }
    private static function uuidv4(): string {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function encode(array $claims, int $ttlSeconds = 900): string {
        $now = time();
        $payload = array_merge([
            'iss' => self::getIssuer(),
            'aud' => self::getAudience(),
            'iat' => $now,
            'exp' => $now + $ttlSeconds,
            'jti' => self::uuidv4(),
        ], $claims);
        $header = ['alg' => self::$algo, 'typ' => 'JWT'];
        $segments = [
            self::base64url(json_encode($header, JSON_UNESCAPED_SLASHES)),
            self::base64url(json_encode($payload, JSON_UNESCAPED_SLASHES))
        ];
        $signing_input = implode('.', $segments);
        $signature = self::sign($signing_input, self::getSecret());
        $segments[] = self::base64url($signature);
        return implode('.', $segments);
    }

    public static function decode(string $jwt): array|false {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return false;
        [$header64, $payload64, $sig64] = $parts;
        $header = json_decode(self::base64url_decode($header64), true) ?: [];
        $payload = json_decode(self::base64url_decode($payload64), true) ?: [];
        $signature = self::base64url_decode($sig64);
        $valid = hash_equals(self::sign("$header64.$payload64", self::getSecret()), $signature);
        if (!$valid) return false;
        if (isset($payload['exp']) && time() > (int)$payload['exp']) return false;
        return $payload;
    }

    private static function sign(string $input, string $key): string {
        return hash_hmac('sha256', $input, $key, true);
    }
    private static function base64url(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    private static function base64url_decode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
