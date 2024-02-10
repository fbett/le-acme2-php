<?php

namespace LE_ACME2\Request;

use LE_ACME2\Response;

use LE_ACME2\Connector\Connector;
use LE_ACME2\Exception;

class GetDirectory extends AbstractRequest {

    /**
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\ServiceUnavailable
     */
    public function getResponse() : Response\GetDirectory {

        $connector = Connector::getInstance();

        $result = $connector->request(
            Connector::METHOD_GET,
             $connector->getBaseURL() . '/directory'
        );
        return new Response\GetDirectory($result);
    }
}