<?php
namespace LE_ACME2\Utilities;

use LE_ACME2\Exception;

class ChallengeHTTP {

    /**
     * @throws Exception\HTTPAuthorizationInvalid
     */
    public static function fetch(string $domain, string $token) : string {

        $requestURL = 'http://' . $domain . '/.well-known/acme-challenge/' . $token;
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $requestURL);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($handle);

        if(curl_errno($handle)) {

            throw new Exception\HTTPAuthorizationInvalid(
                'Error while testing HTTP challenge for "' . $domain . '"": ' .
                $domain . '/.well-known/acme-challenge/' . $token . PHP_EOL .
                '- CURL error: ' . curl_error($handle)
            );
        }

        $statusCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if(substr($statusCode, 0, 1) != 2) {
            throw new Exception\HTTPAuthorizationInvalid(
                'Error while testing HTTP challenge for "' . $domain . '"": ' .
                $domain . '/.well-known/acme-challenge/' . $token .
                ' - unexpected status code:  ' . $statusCode
            );
        }

        if($response === false) {
            throw new Exception\HTTPAuthorizationInvalid(
                'HTTP challenge for "' . $domain . '"": ' .
                $domain . '/.well-known/acme-challenge/' . $token .
                ' tested, found invalid. CURL response: ' . var_export($response, true)
            );
        }

        return $response;
    }
}