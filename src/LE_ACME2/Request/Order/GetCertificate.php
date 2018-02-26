<?php

namespace LE_ACME2\Request\Order;

use LE_ACME2\Connector\Connector;
use LE_ACME2\Response as Response;

use LE_ACME2\Request\AbstractRequest;

class GetCertificate extends AbstractRequest {

    protected $_directoryNewOrderResponse;

    public function __construct(Response\Order\AbstractDirectoryNewOrder $directoryNewOrderResponse) {

        $this->_directoryNewOrderResponse = $directoryNewOrderResponse;
    }

    public function getResponse()
    {
        $connector = Connector::getInstance();

        $result = $connector->request(
            Connector::METHOD_GET,
            $this->_directoryNewOrderResponse->getCertificate()
        );
        return new Response\Order\GetCertificate($result);
    }

}