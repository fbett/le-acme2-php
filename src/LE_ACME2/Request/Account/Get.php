<?php

namespace LE_ACME2\Request\Account;

use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Response;

use LE_ACME2\Connector;
use LE_ACME2\Exception;
use LE_ACME2\Utilities;

use LE_ACME2\Account;

class Get extends AbstractRequest {

    protected $_account;

    public function __construct(Account $account) {
        $this->_account = $account;
    }

    /**
     * @return Response\AbstractResponse|Response\Account\Get
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function getResponse() : Response\AbstractResponse {

        $connector = Connector\Connector::getInstance();
        $storage = Connector\Storage::getInstance();

        $payload = [
            'onlyReturnExisting' => true,
        ];

        $jwk = Utilities\RequestSigner::JWKString(
            $payload,
            $storage->getGetDirectoryResponse()->getNewAccount(),
            $storage->getNewNonceResponse()->getNonce(),
            $this->_account->getKeyDirectoryPath()
        );

        $result = $connector->request(
            Connector\Connector::METHOD_POST,
            $storage->getGetDirectoryResponse()->getNewAccount(),
            $jwk
        );

        return new Response\Account\Get($result);
    }
}