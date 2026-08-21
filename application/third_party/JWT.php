<?php
namespace Firebase\JWT;

class JWT {
    public static function encode($payload, $key, $alg = 'HS256') {
        $header = json_encode(['typ' => 'JWT', 'alg' => $alg]);
        $segments = [];
        $segments[] = static::urlsafeB64Encode($header);
        $segments[] = static::urlsafeB64Encode(json_encode($payload));
        $signing_input = implode('.', $segments);
        $signature = static::sign($signing_input, $key, $alg);
        $segments[] = static::urlsafeB64Encode($signature);
        return implode('.', $segments);
    }

    public static function decode($jwt, $key, $allowed_algs = ['HS256']) {
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            throw new \Exception('Wrong number of segments');
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        if (null === ($header = static::jsonDecode(static::urlsafeB64Decode($headb64)))) {
            throw new \Exception('Invalid header encoding');
        }
        if (null === $payload = static::jsonDecode(static::urlsafeB64Decode($bodyb64))) {
            throw new \Exception('Invalid claims encoding');
        }
        $sig = static::urlsafeB64Decode($cryptob64);
        if (empty($header->alg)) {
            throw new \Exception('Empty algorithm');
        }
        if (!static::verify("$headb64.$bodyb64", $sig, $key, $header->alg)) {
            throw new \Exception('Signature verification failed');
        }
        return $payload;
    }

    private static function urlsafeB64Encode($input) {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    private static function urlsafeB64Decode($input) {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    private static function sign($msg, $key, $alg) {
        if ($alg === 'HS256') {
            return hash_hmac('sha256', $msg, $key, true);
        }
        throw new \Exception('Algorithm not supported');
    }

    private static function verify($msg, $signature, $key, $alg) {
        if ($alg === 'HS256') {
            $hash = hash_hmac('sha256', $msg, $key, true);
            return hash_equals($signature, $hash);
        }
        throw new \Exception('Algorithm not supported');
    }

    private static function jsonDecode($input) {
        $obj = json_decode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            throw new \Exception('JSON decode error: ' . $errno);
        } else if ($obj === null && $input !== 'null') {
            throw new \Exception('Null result with non-null input');
        }
        return $obj;
    }
}