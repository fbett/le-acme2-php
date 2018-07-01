<?php

namespace LE_ACME2\Exception;

class HTTPAuthorizationInvalid extends AbstractException {

    public function __construct($domain, $token, $response) {

        parent::__construct(
            'HTTP challenge for "' . $domain . '"": ' .
            $domain . '/.well-known/acme-challenge/' . $token .
            ' tested, found invalid. CURL response: ' . var_export($response, true)
        );
    }
}