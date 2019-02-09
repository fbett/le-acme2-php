<?php

namespace LE_ACME2\Request\Order;

use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Response;

use LE_ACME2\Connector;
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
    public function getResponse()
    {
        $connector = Connector\Connector::getInstance();
        $storage = Connector\Storage::getInstance();

        $kid = Utilities\RequestSigner::KID(
            null,
            $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            $storage->getDirectoryNewOrderResponse($this->_account, $this->_order)->getLocation(),
            $storage->getNewNonceResponse()->getNonce(),
            $this->_account->getKeyDirectoryPath()
        );

        $result = $connector->request(
            Connector\Connector::METHOD_POST,
            $storage->getDirectoryNewOrderResponse($this->_account, $this->_order)->getLocation(),
            $kid
        );

        return new Response\Order\Get($result, $storage->getDirectoryNewOrderResponse($this->_account, $this->_order)->getLocation());
    }
}