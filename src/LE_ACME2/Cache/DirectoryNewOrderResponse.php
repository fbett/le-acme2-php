<?php
namespace LE_ACME2\Cache;

use LE_ACME2\Connector;
use LE_ACME2\Order;
use LE_ACME2\Response;
use LE_ACME2\Exception;
use LE_ACME2\SingletonTrait;

class DirectoryNewOrderResponse extends AbstractKeyValuableCache {

    use SingletonTrait;

    private const _FILE = 'DirectoryNewOrderResponse';

    private $_responses = [];

    public function get(Order $order): ?Response\Order\AbstractDirectoryNewOrder {

        $accountIdentifier = $this->_getObjectIdentifier($order->getAccount());
        $orderIdentifier = $this->_getObjectIdentifier($order);

        if(isset($this->_responses[$accountIdentifier][$orderIdentifier])) {
            return $this->_responses[ $accountIdentifier ][ $orderIdentifier ];
        }

        $cacheFile = $order->getKeyDirectoryPath() . self::_FILE;

        if(file_exists($cacheFile)) {

            $rawResponse = Connector\RawResponse::getFromString(file_get_contents($cacheFile));

            try {
                $directoryNewOrderResponse = new Response\Order\Create($rawResponse);
                $this->set($order, $directoryNewOrderResponse);

                return $directoryNewOrderResponse;

            } catch(Exception\AbstractException $e) {

                $this->set($order, null);
            }
        }
        return null;
    }

    public function set(Order $order, Response\Order\AbstractDirectoryNewOrder $response = null) : void {

        $accountIdentifier = $this->_getObjectIdentifier($order->getAccount());
        $orderIdentifier = $this->_getObjectIdentifier($order);

        $filePath = $order->getKeyDirectoryPath() . self::_FILE;

        if($response === null) {

            unset($this->_responses[$accountIdentifier][$orderIdentifier]);

            if(file_exists($filePath)) {
                unlink($filePath);
            }

            return;
        }

        $this->_responses[$accountIdentifier][$orderIdentifier] = $response;
        file_put_contents($filePath, $response->getRaw()->toString());
    }
}