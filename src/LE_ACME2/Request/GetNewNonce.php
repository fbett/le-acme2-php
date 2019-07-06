<?php

namespace LE_ACME2\Request;

use LE_ACME2\Response;

use LE_ACME2\Connector;
use LE_ACME2\Exception;

class GetNewNonce extends AbstractRequest {

    /**
     * @return Response\AbstractResponse|Response\GetNewNonce
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function getResponse() : Response\AbstractResponse {

        $connector = Connector\Connector::getInstance();
        $storage = Connector\Storage::getInstance();

        $result = $connector->request(
            Connector\Connector::METHOD_HEAD,
            $storage->getGetDirectoryResponse()->getNewNonce()
        );

        return new Response\GetNewNonce($result);
    }
}