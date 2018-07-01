<?php

namespace LE_ACME2\Request\Account;

use LE_ACME2\Account;
use LE_ACME2\Connector\Connector;
use LE_ACME2\Connector\Storage;
use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Response as Response;
use LE_ACME2\Utilities as Utilities;

abstract class AbstractLocation extends AbstractRequest {

    protected $_account;
    protected $_directoryNewAccountResponse;

    public function __construct(Account $account) {

        $this->_account = $account;
    }

    /**
     * @return \LE_ACME2\Connector\Struct\RawResponse
     * @throws \LE_ACME2\Exception\InvalidResponse
     * @throws \LE_ACME2\Exception\RateLimitReached
     */
    protected function _getRawResponse()
    {
        $connector = Connector::getInstance();
        $storage = Storage::getInstance();

        $payload = $this->_getPayload();

        $kid = Utilities\RequestSigner::KID(
            $payload,
            $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            $storage->getNewNonceResponse()->getNonce(),
            $this->_account->getKeyDirectoryPath()
        );

        $result = $connector->request(
            Connector::METHOD_POST,
            $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            $kid
        );

        return $result;
    }

    abstract protected function _getPayload();
}