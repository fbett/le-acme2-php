<?php
namespace LE_ACME2\Cache;

use LE_ACME2\Connector;
use LE_ACME2\Order;
use LE_ACME2\Request;
use LE_ACME2\Response;
use LE_ACME2\Exception;
use LE_ACME2\Utilities;
use LE_ACME2\SingletonTrait;

class DirectoryNewOrderResponse extends AbstractKeyValuableCache {

    use SingletonTrait;

    private const _FILE = 'DirectoryNewOrderResponse';

    private $_responses = [];

    /**
     * @param Order $order
     * @return Response\Order\AbstractDirectoryNewOrder
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function get(Order $order): Response\Order\AbstractDirectoryNewOrder {

        $accountIdentifier = $this->_getObjectIdentifier($order->getAccount());
        $orderIdentifier = $this->_getObjectIdentifier($order);

        if(isset($this->_responses[$accountIdentifier][$orderIdentifier])) {
            return $this->_responses[ $accountIdentifier ][ $orderIdentifier ];
        }

        $cacheFile = $order->getKeyDirectoryPath() . self::_FILE;

        if(file_exists($cacheFile)) {

            $rawResponse = Connector\RawResponse::getFromString(file_get_contents($cacheFile));

            $directoryNewOrderResponse = null;

            try {
                $directoryNewOrderResponse = new Response\Order\Create($rawResponse);
            } catch(Exception\AbstractException $e) {
                $this->set($order, null);
            }

            if($directoryNewOrderResponse !== null) {

                if(
                    $directoryNewOrderResponse->getStatus() != Response\Order\AbstractDirectoryNewOrder::STATUS_VALID
                ) {

                    Utilities\Logger::getInstance()->add(
                        Utilities\Logger::LEVEL_DEBUG,
                        get_class() . '::' . __FUNCTION__ . ' (cache did not satisfy, status "' . $directoryNewOrderResponse->getStatus() . '")'
                    );
                    $this->set($order, null);

                } else {

                    Utilities\Logger::getInstance()->add(
                        Utilities\Logger::LEVEL_DEBUG,
                        get_class() . '::' . __FUNCTION__ .  ' (from cache, status "' . $directoryNewOrderResponse->getStatus() . '")'
                    );

                    $this->_responses[$accountIdentifier][$orderIdentifier] = $directoryNewOrderResponse;

                    return $directoryNewOrderResponse;
                }
            }
        }

        $request = new Request\Order\Get($order->getAccount(), $order);
        $response = $request->getResponse();

        $this->set($order, $response);

        Utilities\Logger::getInstance()->add(
            Utilities\Logger::LEVEL_INFO,
            get_class() . '::' . __FUNCTION__ .  ' (status: "' . $response->getStatus() . '")'
        );

        return $response;
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