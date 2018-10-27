<?php

namespace LE_ACME2\Request\Order;

use LE_ACME2\Account;
use LE_ACME2\Connector\Connector;
use LE_ACME2\Connector\Storage;
use LE_ACME2\Response;
use LE_ACME2\Utilities;

use LE_ACME2\Request\AbstractRequest;

class GetCertificate extends AbstractRequest {

    protected $_account;
    protected $_directoryNewOrderResponse;

    public function __construct(Account $account, Response\Order\AbstractDirectoryNewOrder $directoryNewOrderResponse) {

        $this->_account = $account;
        $this->_directoryNewOrderResponse = $directoryNewOrderResponse;
    }

    /**
     * @return Response\AbstractResponse|Response\Order\GetCertificate
     * @throws \LE_ACME2\Exception\InvalidResponse
     * @throws \LE_ACME2\Exception\RateLimitReached
     */
    public function getResponse()
    {
        $connector = Connector::getInstance();
        $storage = Storage::getInstance();

        $kid = Utilities\RequestSigner::KID(
            null,
            $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            $this->_directoryNewOrderResponse->getCertificate(),
            $storage->getNewNonceResponse()->getNonce(),
            $this->_account->getKeyDirectoryPath()
        );

        $result = $connector->request(
            Connector::METHOD_POST,
            $this->_directoryNewOrderResponse->getCertificate(),
            $kid
        );

        return new Response\Order\GetCertificate($result);
    }

}