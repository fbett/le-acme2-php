<?php

namespace LE_ACME2\Utilities;

use LE_ACME2\Account;

use LE_ACME2\Exception as Exception;

class Challenge {

    public static function buildAuthorizationKey($token, $digest) {

        return $token . '.' . $digest;
    }

    public static function getDigest(Account $account) {

        $privateKey = openssl_pkey_get_private(file_get_contents($account->getKeyDirectoryPath() . 'private.pem'));
        $details = openssl_pkey_get_details($privateKey);

        $header = array(
            "e" => Base64::UrlSafeEncode($details["rsa"]["e"]),
            "kty" => "RSA",
            "n" => Base64::UrlSafeEncode($details["rsa"]["n"])

        );
        return Base64::UrlSafeEncode(hash('sha256', json_encode($header), true));
    }

    public static function writeHTTPAuthorizationFile($directoryPath, Account $account, \LE_ACME2\Response\Authorization\Struct\Challenge $challenge) {

        $digest = self::getDigest($account);
        file_put_contents($directoryPath . $challenge->token,  self::buildAuthorizationKey($challenge->token, $digest));
    }

    /**
     * @param string $domain
     * @param Account $account
     * @param \LE_ACME2\Response\Authorization\Struct\Challenge $challenge
     * @return bool
     * @throws Exception\HTTPAuthorizationInvalid
     */
    public static function validateHTTPAuthorizationFile($domain, Account $account, \LE_ACME2\Response\Authorization\Struct\Challenge $challenge) {

        $digest = self::getDigest($account);

        $requestURL = 'http://' . $domain . '/.well-known/acme-challenge/' . $challenge->token;
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $requestURL);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);

        $result = !empty($response) && $response == self::buildAuthorizationKey($challenge->token, $digest);

        if(!$result) {

            throw new Exception\HTTPAuthorizationInvalid(
                'HTTP challenge for "' . $domain . '"": ' .
                $domain . '/.well-known/acme-challenge/' . $challenge->token .
                ' tested, found invalid. CURL response: ' . var_export($response, true)
            );
        }
        return true;
    }
}