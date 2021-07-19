<?php
namespace LE_ACME2Tests\Connector;

class RawResponse extends \LE_ACME2\Connector\RawResponse {

    const HEADER_200 = 'HTTP/1.1 200 OK';

    public static function createDummyFrom(string $header, string $response): \LE_ACME2\Connector\RawResponse {

        return \LE_ACME2\Connector\RawResponse::createFrom(
            'UNKOWN',
            'http://dummy.org',
            $header . $response,
            strlen($header)
        );
    }
}