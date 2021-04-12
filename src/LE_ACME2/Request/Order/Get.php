<?php

namespace LE_ACME2\Request\Order;

use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Response;

use LE_ACME2\Connector;
use LE_ACME2\Cache;
use LE_ACME2\Exception;
use LE_ACME2\Utilities;

use LE_ACME2\Order;

class Get extends AbstractRequest {

    protected $_order;
    protected $_directoryNewOrderResponse;

    public function __construct(Order $order, Response\Order\AbstractDirectoryNewOrder $directoryNewOrderResponse) {

        $this->_order = $order;
        $this->_directoryNewOrderResponse = $directoryNewOrderResponse;
    }

    /**
     * @return Response\AbstractResponse|Response\Order\Get
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function getResponse() : Response\AbstractResponse {

        $kid = Utilities\RequestSigner::KID(
            null,
            Cache\DirectoryNewAccountResponse::getInstance()->get($this->_order->getAccount())->getLocation(),
            $this->_directoryNewOrderResponse->getLocation(),
            Cache\NewNonceResponse::getInstance()->get()->getNonce(),
            $this->_order->getAccount()->getKeyDirectoryPath()
        );

        $result = Connector\Connector::getInstance()->request(
            Connector\Connector::METHOD_POST,
            $this->_directoryNewOrderResponse->getLocation(),
            $kid
        );

        return new Response\Order\Get($result, $this->_directoryNewOrderResponse->getLocation());
    }
}