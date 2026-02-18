<?php
namespace App\Services;
// JWT Service for StrikeCircle
// Usage: JWTService::encode($payload), JWTService::decode($jwt)
use App\Config\env;

class JWTService {
    private static $algo = 'HS256';
    private static $secret;

    private static function getSecret() {
        if (!self::$secret) {
            use App\Config\env;
            self::$secret = env('JWT_SECRET', 'changeme');
        }
        return self::$secret;
    }

    public static function encode($payload, $exp = 900) {
        $header = ['alg' => self::$algo, 'typ' => 'JWT'];
        $payload['exp'] = time() + $exp;
        $segments = [
            self::base64url(json_encode($header)),
            self::base64url(json_encode($payload))
        ];
        $signing_input = implode('.', $segments);
        $signature = self::sign($signing_input, self::getSecret());
        $segments[] = self::base64url($signature);
        return implode('.', $segments);
    }

    public static function decode($jwt) {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return false;
        [$header64, $payload64, $sig64] = $parts;
        $header = json_decode(self::base64url_decode($header64), true);
        $payload = json_decode(self::base64url_decode($payload64), true);
        $signature = self::base64url_decode($sig64);
        $valid = hash_equals(self::sign("$header64.$payload64", self::getSecret()), $signature);
        if (!$valid) return false;
        if (isset($payload['exp']) && time() > $payload['exp']) return false;
        return $payload;
    }

    private static function sign($input, $key) {
        return hash_hmac('sha256', $input, $key, true);
    }
    private static function base64url($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    private static function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/')); 
    }
}
