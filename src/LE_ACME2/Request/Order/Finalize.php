<?php

namespace LE_ACME2\Request\Order;

use LE_ACME2\Account;
use LE_ACME2\Order;
use LE_ACME2\Connector\Connector;
use LE_ACME2\Connector\Storage;
use LE_ACME2\Request\AbstractRequest;

use LE_ACME2\Utilities as Utilities;
use LE_ACME2\Response as Response;

class Finalize extends AbstractRequest {

    protected $_account;
    protected $_order;

    public function __construct(Account $account, Order $order) {

        $this->_account = $account;
        $this->_order = $order;
    }

    /**
     * @return Response\AbstractResponse|Response\Order\Finalize
     * @throws \LE_ACME2\Exception\InvalidResponse
     * @throws \LE_ACME2\Exception\RateLimitReached
     */
    public function getResponse() {

        $connector = Connector::getInstance();
        $storage = Storage::getInstance();

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
            Connector::METHOD_POST,
            $storage->getDirectoryNewOrderResponse($this->_account, $this->_order)->getFinalize(),
            $kid
        );

        return new Response\Order\Finalize($result);
    }
}