<?php

namespace LE_ACME2\Request\Account;

use LE_ACME2\Request\AbstractRequest;

use LE_ACME2\Connector;
use LE_ACME2\Utilities;
use LE_ACME2\Exception;

use LE_ACME2\Account;

abstract class AbstractLocation extends AbstractRequest {

    protected $_account;
    protected $_directoryNewAccountResponse;

    public function __construct(Account $account) {
        $this->_account = $account;
    }

    /**
     * @return Connector\Struct\RawResponse
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    protected function _getRawResponse() : Connector\Struct\RawResponse {

        $connector = Connector\Connector::getInstance();
        $storage = Connector\Storage::getInstance();

        $payload = $this->_getPayload();

        $kid = Utilities\RequestSigner::KID(
            $payload,
            $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            $storage->getNewNonceResponse()->getNonce(),
            $this->_account->getKeyDirectoryPath()
        );

        $result = $connector->request(
            Connector\Connector::METHOD_POST,
            $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            $kid
        );

        return $result;
    }

    abstract protected function _getPayload() : array;
}