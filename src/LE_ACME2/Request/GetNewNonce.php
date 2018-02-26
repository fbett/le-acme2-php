<?php

namespace LE_ACME2\Request;

use LE_ACME2\Connector\Connector;
use LE_ACME2\Connector\Storage;
use LE_ACME2\Response as Response;

class GetNewNonce extends AbstractRequest {

    public function getResponse() {

        $connector = Connector::getInstance();
        $storage = Storage::getInstance();

        $result = $connector->request(
            Connector::METHOD_HEAD,
            $storage->getGetDirectoryResponse()->getNewNonce()
        );

        if(strpos($result['header'], "204 No Content") == false)
            throw new \RuntimeException('No new nonce.');

        return new Response\GetNewNonce($result);
    }
}