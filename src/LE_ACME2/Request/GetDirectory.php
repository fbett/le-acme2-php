<?php

namespace LE_ACME2\Request;

use LE_ACME2\Connector\Connector;
use LE_ACME2\Response as Response;

class GetDirectory extends AbstractRequest {

    /**
     * @return Response\AbstractResponse|Response\GetDirectory
     * @throws \LE_ACME2\Exception\InvalidResponse
     * @throws \LE_ACME2\Exception\RateLimitReached
     */
    public function getResponse() {

        $connector = Connector::getInstance();

        $result = $connector->request(
            Connector::METHOD_GET,
             $connector->getBaseURL() . '/directory'
        );
        return new Response\GetDirectory($result);
    }
}