<?php

namespace LE_ACME2\Request\Authorization;

use LE_ACME2\Request\AbstractRequest;

use LE_ACME2\Connector;
use LE_ACME2\Exception;
use LE_ACME2\Response;
use LE_ACME2\Utilities;

use LE_ACME2\Account;
use LE_ACME2\Order;

class Start extends AbstractRequest {

    protected $_account;
    protected $_order;
    protected $_challenge;

    public function __construct(Account $account, Order $order, Response\Authorization\Struct\Challenge $challenge) {

        $this->_account = $account;
        $this->_order = $order;
        $this->_challenge = $challenge;
    }

    /**
     * @return Response\AbstractResponse|Response\Authorization\Start
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\ExpiredAuthorization
     */
    public function getResponse() : Response\AbstractResponse {

        $connector = Connector\Connector::getInstance();
        $storage = Connector\Storage::getInstance();

        $payload = [
            'keyAuthorization' => Utilities\Challenge::buildAuthorizationKey($this->_challenge->token, Utilities\Challenge::getDigest($this->_account))
        ];

        $kid = Utilities\RequestSigner::KID(
            $payload,
            $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            $this->_challenge->url,
            $storage->getNewNonceResponse()->getNonce(),
            $this->_account->getKeyDirectoryPath()
        );

        $result = $connector->request(
            Connector\Connector::METHOD_POST,
            $this->_challenge->url,
            $kid
        );

        return new Response\Authorization\Start($result);
    }
}