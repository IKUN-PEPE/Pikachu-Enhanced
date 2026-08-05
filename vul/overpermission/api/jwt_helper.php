<?php
// 一个极简的不安全的 JWT 辅助类，用于教学演示
class JWTHelper {
    // 故意设置的一个弱密钥，容易被破解
    public static $secret = "123456";

    public static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }

    public static function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $base64UrlHeader = self::base64url_encode($header);
        $base64UrlPayload = self::base64url_encode($payload);
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret, true);
        $base64UrlSignature = self::base64url_encode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function decode($jwt) {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header64, $payload64, $sign64) = $parts;
        
        $header = json_decode(self::base64url_decode($header64), true);
        $payload = json_decode(self::base64url_decode($payload64), true);
        
        // 【漏洞点】不仅密钥弱，还支持 alg: none 的绕过漏洞
        if (isset($header['alg']) && strtolower($header['alg']) === 'none') {
            // 如果头部声明了 alg: none，则完全跳过签名校验！（历史著名漏洞 CVE-2015-9256）
            return $payload;
        }
        
        $validSignature = hash_hmac('sha256', $header64 . "." . $payload64, self::$secret, true);
        $validSign64 = self::base64url_encode($validSignature);
        
        if ($sign64 === $validSign64) {
            return $payload;
        }
        
        return false;
    }
}
