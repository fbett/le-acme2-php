<?php

namespace LE_ACME2\Request\Authorization;

use LE_ACME2\Connector\Connector;
use LE_ACME2\Connector\Storage;
use LE_ACME2\Order;
use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Account;

use LE_ACME2\Response as Response;
use LE_ACME2\Utilities as Utilities;

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
     * @throws \LE_ACME2\Exception\InvalidResponse
     * @throws \LE_ACME2\Exception\RateLimitReached
     */
    public function getResponse() {

        $connector = Connector::getInstance();
        $storage = Storage::getInstance();

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
            Connector::METHOD_POST,
            $this->_challenge->url,
            $kid
        );

        return new Response\Authorization\Start($result);
    }
}