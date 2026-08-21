<?php
/**
 * JSON Web Token implementation, based on this spec:
 * https://tools.ietf.org/html/rfc7519
 *
 * This class is a simplified version for CodeIgniter
 */

class JWT {
    /**
     * Decodes a JWT string into a PHP object.
     *
     * @param string      $jwt       The JWT
     * @param string|null $key       The secret key
     * @param array       $allowed_algs List of supported verification algorithms
     *
     * @return object The JWT's payload as a PHP object
     *
     * @throws Exception
     */
    public function decode($jwt, $key, $allowed_algs = array()) {
        $timestamp = time();
        
        if (empty($key)) {
            throw new Exception('Key may not be empty');
        }
        
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            throw new Exception('Wrong number of segments');
        }
        
        list($headb64, $bodyb64, $cryptob64) = $tks;
        
        if (null === ($header = $this->jsonDecode($this->urlsafeB64Decode($headb64)))) {
            throw new Exception('Invalid header encoding');
        }
        
        if (null === $payload = $this->jsonDecode($this->urlsafeB64Decode($bodyb64))) {
            throw new Exception('Invalid claims encoding');
        }
        
        if (false === ($sig = $this->urlsafeB64Decode($cryptob64))) {
            throw new Exception('Invalid signature encoding');
        }
        
        if (empty($header->alg)) {
            throw new Exception('Empty algorithm');
        }
        
        if (empty($allowed_algs)) {
            $allowed_algs = array('HS256', 'HS384', 'HS512', 'RS256');
        }
        
        if (!in_array($header->alg, $allowed_algs)) {
            throw new Exception('Algorithm not allowed');
        }
        
        // Check token expiry
        if (isset($payload->exp) && $payload->exp < $timestamp) {
            throw new Exception('Expired token');
        }
        
        // Check if token is valid
        if (!$this->verify("$headb64.$bodyb64", $sig, $key, $header->alg)) {
            throw new Exception('Signature verification failed');
        }
        
        return $payload;
    }

    /**
     * Converts and signs a PHP object or array into a JWT string.
     *
     * @param object|array $payload PHP object or array
     * @param string       $key     The secret key
     * @param string       $alg     The signing algorithm
     * @param array        $head    An array with header elements to attach
     *
     * @return string A signed JWT
     */
    public function encode($payload, $key, $alg = 'HS512', $keyId = null, $head = null) {
        $header = array('typ' => 'JWT', 'alg' => $alg);
        
        if ($keyId !== null) {
            $header['kid'] = $keyId;
        }
        
        if ($head && is_array($head)) {
            $header = array_merge($head, $header);
        }
        
        $segments = array();
        $segments[] = $this->urlsafeB64Encode($this->jsonEncode($header));
        $segments[] = $this->urlsafeB64Encode($this->jsonEncode($payload));
        $signing_input = implode('.', $segments);
        
        $signature = $this->sign($signing_input, $key, $alg);
        $segments[] = $this->urlsafeB64Encode($signature);
        
        return implode('.', $segments);
    }

    /**
     * Sign a string with a given key and algorithm.
     *
     * @param string $msg  The message to sign
     * @param string $key  The secret key
     * @param string $alg  The signing algorithm
     *
     * @return string An encrypted message
     */
    private function sign($msg, $key, $alg) {
        if ($alg === 'HS256') {
            return hash_hmac('sha256', $msg, $key, true);
        } elseif ($alg === 'HS384') {
            return hash_hmac('sha384', $msg, $key, true);
        } elseif ($alg === 'HS512') {
            return hash_hmac('sha512', $msg, $key, true);
        }
        
        throw new Exception('Algorithm not supported');
    }

    /**
     * Verify a signature with the message, key and method.
     *
     * @param string $msg       The original message
     * @param string $signature The original signature
     * @param string $key       The secret key
     * @param string $alg       The algorithm
     *
     * @return bool
     */
    private function verify($msg, $signature, $key, $alg) {
        if ($alg === 'HS256') {
            $hash = hash_hmac('sha256', $msg, $key, true);
        } elseif ($alg === 'HS384') {
            $hash = hash_hmac('sha384', $msg, $key, true);
        } elseif ($alg === 'HS512') {
            $hash = hash_hmac('sha512', $msg, $key, true);
        } else {
            throw new Exception('Algorithm not supported');
        }
        
        return hash_equals($signature, $hash);
    }

    /**
     * Decode a JSON string into a PHP object.
     *
     * @param string $input JSON string
     *
     * @return object Object representation of JSON string
     */
    private function jsonDecode($input) {
        $obj = json_decode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            throw new Exception('JSON decode error: ' . $errno);
        } elseif ($obj === null && $input !== 'null') {
            throw new Exception('Null result with non-null input');
        }
        return $obj;
    }

    /**
     * Encode a PHP object into a JSON string.
     *
     * @param object|array $input A PHP object or array
     *
     * @return string JSON representation of the PHP object or array
     */
    private function jsonEncode($input) {
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            throw new Exception('JSON encode error: ' . $errno);
        } elseif ($json === 'null' && $input !== null) {
            throw new Exception('Null result with non-null input');
        }
        return $json;
    }

    /**
     * Decode a string with URL-safe Base64.
     *
     * @param string $input A Base64 encoded string
     *
     * @return string A decoded string
     */
    private function urlsafeB64Decode($input) {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Encode a string with URL-safe Base64.
     *
     * @param string $input The string you want encoded
     *
     * @return string The base64 encode of what you passed in
     */
    private function urlsafeB64Encode($input) {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }
}