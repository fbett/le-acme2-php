<?php

namespace LE_ACME2\Request\Order;

use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Response;

use LE_ACME2\Connector;
use LE_ACME2\Cache;
use LE_ACME2\Exception;
use LE_ACME2\Utilities;

use LE_ACME2\Account;
use LE_ACME2\Order;

class Get extends AbstractRequest {

    protected $_account;
    protected $_order;

    public function __construct(Account $account, Order $order) {

        $this->_account = $account;
        $this->_order = $order;
    }

    /**
     * @return Response\AbstractResponse|Response\Order\Get
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function getResponse() : Response\AbstractResponse {

        $kid = Utilities\RequestSigner::KID(
            null,
            Cache\DirectoryNewAccountResponse::getInstance()->get($this->_account)->getLocation(),
            Cache\DirectoryNewOrderResponse::getInstance()->get($this->_order)->getLocation(),
            Cache\NewNonceResponse::getInstance()->get()->getNonce(),
            $this->_account->getKeyDirectoryPath()
        );

        $result = Connector\Connector::getInstance()->request(
            Connector\Connector::METHOD_POST,
            Cache\DirectoryNewOrderResponse::getInstance()->get($this->_order)->getLocation(),
            $kid
        );

        return new Response\Order\Get($result, Cache\DirectoryNewOrderResponse::getInstance()->get($this->_order)->getLocation());
    }
}