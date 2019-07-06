<?php

namespace LE_ACME2\Request\Order;

use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Response;

use LE_ACME2\Connector;
use LE_ACME2\Exception;
use LE_ACME2\Utilities;

use LE_ACME2\Account;
use LE_ACME2\Order;

class Finalize extends AbstractRequest {

    protected $_account;
    protected $_order;

    public function __construct(Account $account, Order $order) {

        $this->_account = $account;
        $this->_order = $order;
    }

    /**
     * @return Response\AbstractResponse|Response\Order\Finalize
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function getResponse() : Response\AbstractResponse {

        $connector = Connector\Connector::getInstance();
        $storage = Connector\Storage::getInstance();

        $csr = Utilities\Certificate::generateCSR($this->_order);

        if(preg_match('~-----BEGIN\sCERTIFICATE\sREQUEST-----(.*)-----END\sCERTIFICATE\sREQUEST-----~s', $csr, $matches))
            $csr = $matches[1];

        $csr = trim(Utilities\Base64::UrlSafeEncode(base64_decode($csr)));

        $payload = [
            'csr' => $csr
        ];

        $kid = Utilities\RequestSigner::KID(
            $payload,
            $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            $storage->getDirectoryNewOrderResponse($this->_account, $this->_order)->getFinalize(),
            $storage->getNewNonceResponse()->getNonce(),
            $this->_account->getKeyDirectoryPath()
        );

        $result = $connector->request(
            Connector\Connector::METHOD_POST,
            $storage->getDirectoryNewOrderResponse($this->_account, $this->_order)->getFinalize(),
            $kid
        );

        return new Response\Order\Finalize($result);
    }
}