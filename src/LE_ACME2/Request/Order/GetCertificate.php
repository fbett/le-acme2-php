<?php

namespace LE_ACME2\Request\Order;

use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Response;

use LE_ACME2\Connector;
use LE_ACME2\Exception;
use LE_ACME2\Utilities;

use LE_ACME2\Account;

class GetCertificate extends AbstractRequest {

    protected $_account;
    protected $_directoryNewOrderResponse;

    public function __construct(Account $account, Response\Order\AbstractDirectoryNewOrder $directoryNewOrderResponse) {

        $this->_account = $account;
        $this->_directoryNewOrderResponse = $directoryNewOrderResponse;
    }

    /**
     * @return Response\AbstractResponse|Response\Order\GetCertificate
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function getResponse() : Response\AbstractResponse {

        $connector = Connector\Connector::getInstance();
        $storage = Connector\Storage::getInstance();

        $kid = Utilities\RequestSigner::KID(
            null,
            $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            $this->_directoryNewOrderResponse->getCertificate(),
            $storage->getNewNonceResponse()->getNonce(),
            $this->_account->getKeyDirectoryPath()
        );

        $result = $connector->request(
            Connector\Connector::METHOD_POST,
            $this->_directoryNewOrderResponse->getCertificate(),
            $kid
        );

        return new Response\Order\GetCertificate($result);
    }
}