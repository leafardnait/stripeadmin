<?php

namespace Magekc\PayCools\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Signature extends AbstractHelper
{
    
    /**
     * Build canonical string for signing
     *
     * Rules (typical for payment gateways):
     *  - Exclude the 'sign' field itself
     *  - Flatten nested arrays/objects into JSON strings
     *  - Remove null/empty values
     *  - Sort parameters alphabetically by key
     *  - Concatenate as key=value pairs joined by '&'
     *
     * @param array $params
     * @return string
     */
    public function buildSignString(array $params): string
    {
        // 1. Remove existing 'sign' field if present
        unset($params['sign']);

        // 2. Flatten arrays/objects into JSON strings
        foreach ($params as $k => $v) {
            if (is_array($v)) {
                // Use JSON_UNESCAPED_SLASHES to avoid escaping '/'
                // Use JSON_UNESCAPED_UNICODE if gateway requires raw UTF-8
                $params[$k] = json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }

        // 3. Remove null or empty values
        $params = array_filter($params, fn($v) => $v !== null && $v !== '');

        // 4. Sort parameters alphabetically by key
        ksort($params);

        // 5. Build key=value pairs and join with '&'
        $pairs = [];
        foreach ($params as $key => $value) {
            $pairs[] = $key . '=' . $value;
        }

        return implode('&', $pairs);
    }


    /**
     * RSA2 Sign (SHA256withRSA)
     *
     * @param string $plainText
     * @param string $privateKeyPem
     * @return string|null
     */
    public function sign(string $plainText, string $privateKeyPem): ?string
    {
        try {
            $privateKey = $this->formatPrivateKey($privateKeyPem);
            $key = openssl_pkey_get_private($privateKey);
        if (!$key) {
            die("Invalid private key!\n");
        }

            // 3. Sign the string using SHA256 (RSA2)
            openssl_sign($plainText, $signature, $key, OPENSSL_ALGO_SHA256);
            // 4. Base64 encode
            $signatureBase64 = base64_encode($signature);

            return $signatureBase64;

        } catch (\Exception $e) {
            return "";
        }
    }

    /**
     * RSA2 Verify
     *
     * @param string $plainText
     * @param string $signature
     * @param string $publicKey
     * @return bool
     */
    public function verify(string $plainText, string $signature, string $publicKey): bool
    {
        try {
            $publicKey = $this->formatPublicKey($publicKey);
            
            $res = openssl_pkey_get_public($publicKey);
            if (!$res) {
                return false;
            }
            
            $result = openssl_verify(
                $plainText,
                base64_decode($signature),
                $res,
                OPENSSL_ALGO_SHA256
            );
            

            return $result === 1;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Format Private Key (PKCS8 compatible)
     */
    public function formatPrivateKey(string $key): string
    {
        $privateKey = str_replace(["\r", "\n"], '', $key);
        $privateKey = "-----BEGIN PRIVATE KEY-----\n" . chunk_split($privateKey, 64, "\n") . "-----END PRIVATE KEY-----\n";
        return $privateKey;
    }

    /**
     * Format Public Key
     */
    public function formatPublicKey(string $key): string
    {
        $publicKey = str_replace(["\r", "\n"], '', $key);
        $publicKey = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($publicKey, 64, "\n") . "-----END PUBLIC KEY-----\n";
        return $publicKey;
    }
}