<?php

namespace LE_ACME2\Request\Authorization;

use LE_ACME2\Account;
use LE_ACME2\Connector\Connector;
use LE_ACME2\Connector\Storage;
use LE_ACME2\Request\AbstractRequest;

use LE_ACME2\Response;
use LE_ACME2\Utilities;

class Get extends AbstractRequest {

    protected $_account;
    protected $_authorizationURL;

    public function __construct(Account $account, $authorizationURL) {

        $this->_account = $account;
        $this->_authorizationURL = $authorizationURL;
    }

    /**
     * @return Response\AbstractResponse|Response\Authorization\Get
     * @throws \LE_ACME2\Exception\InvalidResponse
     * @throws \LE_ACME2\Exception\RateLimitReached
     */
    public function getResponse() {

        $connector = Connector::getInstance();
        $storage = Storage::getInstance();

        $kid = Utilities\RequestSigner::KID(
            null,
            $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            $this->_authorizationURL,
            $storage->getNewNonceResponse()->getNonce(),
            $this->_account->getKeyDirectoryPath()
        );

        $result = $connector->request(
            Connector::METHOD_POST,
            $this->_authorizationURL,
            $kid
        );

        return new Response\Authorization\Get($result);
    }
}