<?php

namespace LE_ACME2\Request\Order;

use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Response;

use LE_ACME2\Connector;
use LE_ACME2\Cache;
use LE_ACME2\Exception;
use LE_ACME2\Utilities;

use LE_ACME2\Order;

class Finalize extends AbstractRequest {

    protected $_order;
    protected $_directoryNewOrderResponse;

    public function __construct(Order $order, Response\Order\AbstractDirectoryNewOrder $directoryNewOrderResponse) {

        $this->_order = $order;
        $this->_directoryNewOrderResponse = $directoryNewOrderResponse;
    }

    /**
     * @return Response\AbstractResponse|Response\Order\Finalize
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function getResponse() : Response\AbstractResponse {

        $csr = Utilities\Certificate::generateCSR($this->_order);

        if(preg_match('~-----BEGIN\sCERTIFICATE\sREQUEST-----(.*)-----END\sCERTIFICATE\sREQUEST-----~s', $csr, $matches))
            $csr = $matches[1];

        $csr = trim(Utilities\Base64::UrlSafeEncode(base64_decode($csr)));

        $payload = [
            'csr' => $csr
        ];

        $kid = Utilities\RequestSigner::KID(
            $payload,
            Cache\DirectoryNewAccountResponse::getInstance()->get($this->_order->getAccount())->getLocation(),
            $this->_directoryNewOrderResponse->getFinalize(),
            Cache\NewNonceResponse::getInstance()->get()->getNonce(),
            $this->_order->getAccount()->getKeyDirectoryPath()
        );

        $result = Connector\Connector::getInstance()->request(
            Connector\Connector::METHOD_POST,
            $this->_directoryNewOrderResponse->getFinalize(),
            $kid
        );

        return new Response\Order\Finalize($result);
    }
}