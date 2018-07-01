<?php

namespace LE_ACME2\Request\Account;

use LE_ACME2\Connector\Storage;
use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Response as Response;
use LE_ACME2\Utilities as Utilities;

use LE_ACME2\Account;
use LE_ACME2\Connector\Connector;

class Get extends AbstractRequest {

    protected $_account;

    public function __construct(Account $account) {

        $this->_account = $account;
    }

    /**
     * @return Response\AbstractResponse|Response\Account\Get
     * @throws \LE_ACME2\Exception\InvalidResponse
     * @throws \LE_ACME2\Exception\RateLimitReached
     */
    public function getResponse()
    {
        $connector = Connector::getInstance();
        $storage = Storage::getInstance();

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
            Connector::METHOD_POST,
            $storage->getGetDirectoryResponse()->getNewAccount(),
            $jwk
        );

        return new Response\Account\Get($result);
    }
}