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

        $csr = Utilities\Certificate::generateCSR($this->_order);

        if(preg_match('~-----BEGIN\sCERTIFICATE\sREQUEST-----(.*)-----END\sCERTIFICATE\sREQUEST-----~s', $csr, $matches))
            $csr = $matches[1];

        $csr = trim(Utilities\Base64::UrlSafeEncode(base64_decode($csr)));

        $payload = [
            'csr' => $csr
        ];

        $kid = Utilities\RequestSigner::KID(
            $payload,
            Cache\DirectoryNewAccountResponse::getInstance()->get($this->_account)->getLocation(),
            Cache\DirectoryNewOrderResponse::getInstance()->get($this->_order)->getFinalize(),
            Cache\NewNonceResponse::getInstance()->get()->getNonce(),
            $this->_account->getKeyDirectoryPath()
        );

        $result = Connector\Connector::getInstance()->request(
            Connector\Connector::METHOD_POST,
            Cache\DirectoryNewOrderResponse::getInstance()->get($this->_order)->getFinalize(),
            $kid
        );

        return new Response\Order\Finalize($result);
    }
}