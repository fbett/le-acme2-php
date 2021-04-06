<?php

namespace LE_ACME2\Connector;

use LE_ACME2\AbstractKeyValuable;
use LE_ACME2\Request;
use LE_ACME2\Response;

use LE_ACME2\Exception;

use LE_ACME2\Account;
use LE_ACME2\Order;

class Storage {

    private static $_instance = NULL;

    public static function getInstance() : self {

        if(self::$_instance === NULL) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected $_getDirectoryResponse = NULL;
    protected $_getNewNonceResponse = NULL;

    protected $_directoryNewAccountResponses = [];
    protected $_directoryNewOrderResponses = [];

    /**
     * @return Response\GetDirectory
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function getGetDirectoryResponse() : Response\GetDirectory {

        if($this->_getDirectoryResponse === NULL) {

            $cacheFile = Account::getCommonKeyDirectoryPath() . 'DirectoryResponse';

            if(file_exists($cacheFile) && filemtime($cacheFile) > strtotime('-2 days')) {

                $rawResponse = Struct\RawResponse::getFromString(file_get_contents($cacheFile));

                try {
                    return $this->_getDirectoryResponse = new Response\GetDirectory($rawResponse);

                } catch(Exception\AbstractException $e) {
                    unlink($cacheFile);
                }
            }

            $request = new Request\GetDirectory();
            $this->_getDirectoryResponse = $request->getResponse();
            file_put_contents($cacheFile, $this->_getDirectoryResponse->getRaw()->toString());
        }

        return $this->_getDirectoryResponse;
    }

    public function setGetDirectoryResponse(Response\GetDirectory $response) {
        $this->_getDirectoryResponse = $response;
    }

    /**
     * @return Response\GetNewNonce
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function getNewNonceResponse() : Response\GetNewNonce {

        if($this->_getNewNonceResponse === NULL) {
            $request = new Request\GetNewNonce();
            $this->_getNewNonceResponse = $request->getResponse();
        }
        return $this->_getNewNonceResponse;
    }

    public function setNewNonceResponse(Response\GetNewNonce $response) {
        $this->_getNewNonceResponse = $response;
    }

    protected function _getObjectIdentifier(AbstractKeyValuable $object) : string {
        return $object->getKeyDirectoryPath();
    }

    public function getDirectoryNewAccountResponse(Account $account)
    : ?Response\Account\AbstractDirectoryNewAccount {

        $accountIdentifier = $this->_getObjectIdentifier($account);

        if(isset($this->_directoryNewAccountResponses[$accountIdentifier]))
            return $this->_directoryNewAccountResponses[$accountIdentifier];

        $cacheFile = $account->getKeyDirectoryPath() . 'DirectoryNewAccountResponse';

        if(file_exists($cacheFile) && filemtime($cacheFile) > strtotime('-7 days')) {

            $rawResponse = Struct\RawResponse::getFromString(file_get_contents($cacheFile));

            try {
                $directoryNewAccountResponse = new Response\Account\Create($rawResponse);
                $this->_directoryNewAccountResponses[$accountIdentifier] = $directoryNewAccountResponse;
                return $directoryNewAccountResponse;

            } catch(Exception\AbstractException $e) {
                unlink($cacheFile);
            }
        }
        return null;
    }

    public function setDirectoryNewAccountResponse(Account $account,
                                                   Response\Account\AbstractDirectoryNewAccount $response
    ) {
        $this->_directoryNewAccountResponses[$this->_getObjectIdentifier($account)] = $response;
        file_put_contents(
            $account->getKeyDirectoryPath() . 'DirectoryNewAccountResponse',
            $response->getRaw()->toString()
        );
    }

    public function getDirectoryNewOrderResponse(Account $account, Order $order)
    : ?Response\Order\AbstractDirectoryNewOrder {

        $accountIdentifier = $this->_getObjectIdentifier($account);
        $orderIdentifier = $this->_getObjectIdentifier($order);

        if(isset($this->_directoryNewOrderResponses[$accountIdentifier][$orderIdentifier]))
            return $this->_directoryNewOrderResponses[$accountIdentifier][$orderIdentifier];

        $cacheFile = $order->getKeyDirectoryPath() . 'DirectoryNewOrderResponse';

        if(file_exists($cacheFile)) {

            $rawResponse = Struct\RawResponse::getFromString(file_get_contents($cacheFile));

            try {
                $directoryNewOrderResponse = new Response\Order\Create($rawResponse);

                $this->_directoryNewOrderResponses[$accountIdentifier][$orderIdentifier] = $directoryNewOrderResponse;
                return $directoryNewOrderResponse;

            } catch(Exception\AbstractException $e) {

                unlink($cacheFile);
            }
        }
        return null;
    }

    public function setDirectoryNewOrderResponse(Account $account, Order $order,
                                                 Response\Order\AbstractDirectoryNewOrder $response
    ) {
        $this->_directoryNewOrderResponses[$this->_getObjectIdentifier($account)][$this->_getObjectIdentifier($order)] = $response;
        file_put_contents(
            $order->getKeyDirectoryPath() . 'DirectoryNewOrderResponse',
            $response->getRaw()->toString()
        );
    }

    public function purgeDirectoryNewOrderResponse(Account $account, Order $order) {

        unset($this->_directoryNewOrderResponses[$this->_getObjectIdentifier($account)][$this->_getObjectIdentifier($order)]);

        if(file_exists($order->getKeyDirectoryPath() . 'DirectoryNewOrderResponse')) {
            unlink($order->getKeyDirectoryPath() . 'DirectoryNewOrderResponse');
        }
    }
}