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

    protected Order $_order;
    private string $location;

    public function __construct(Order $order, string $location) {

        $this->_order = $order;
        $this->location = $location;
    }

    /**
     * @return Response\AbstractResponse|Response\Order\Get
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function getResponse() : Response\AbstractResponse {

        $kid = Utilities\RequestSigner::KID(
            null,
            Cache\AccountResponse::getInstance()->get($this->_order->getAccount())->getLocation(),
            $this->location,
            Cache\NewNonceResponse::getInstance()->get()->getNonce(),
            $this->_order->getAccount()->getKeyDirectoryPath()
        );

        $result = Connector\Connector::getInstance()->request(
            Connector\Connector::METHOD_POST,
            $this->location,
            $kid
        );

        return new Response\Order\Get($result, $this->location);
    }
}