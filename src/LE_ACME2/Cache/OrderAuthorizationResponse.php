<?php
namespace LE_ACME2\Cache;

use LE_ACME2\Connector;
use LE_ACME2\Order;
use LE_ACME2\Request;
use LE_ACME2\Response;
use LE_ACME2\Exception;
use LE_ACME2\Utilities;
use LE_ACME2\SingletonTrait;

class OrderAuthorizationResponse extends AbstractKeyValuableCache {

    use SingletonTrait;

    private const _FILE_prefix = 'CacheAuthorizationResponse';

    private $_responses = [];

    private function _getCacheFilePath(Order $order, string $authorizationUrl) : string {
        return $order->getKeyDirectoryPath() . self::_FILE_prefix . '-' . md5($authorizationUrl);
    }

    /**
     * @param Order $order
     * @param string $authorizationUrl
     * @return Response\Authorization\Get
     * @throws Exception\ExpiredAuthorization
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function get(Order $order, string $authorizationUrl, string $challengeType): Response\Authorization\Get {

        $accountIdentifier = $this->_getObjectIdentifier($order->getAccount());
        $orderIdentifier = $this->_getObjectIdentifier($order);

        if(!isset($this->_responses[$accountIdentifier][$orderIdentifier])) {
            $this->_responses[$accountIdentifier][$orderIdentifier] = [];
        }

        if(array_key_exists($authorizationUrl, $this->_responses[$accountIdentifier][$orderIdentifier])) {
            return $this->_responses[ $accountIdentifier ][ $orderIdentifier ][ $authorizationUrl ];
        }
        $this->_responses[ $accountIdentifier ][ $orderIdentifier ][ $authorizationUrl ] = null;

        $cacheFile = $this->_getCacheFilePath($order, $authorizationUrl);

        if(file_exists($cacheFile)) {

            $rawResponse = Connector\RawResponse::getFromString(file_get_contents($cacheFile));

            $response = new Response\Authorization\Get($rawResponse);
            if(
                ($challenge = $response->getChallenge($challengeType)) &&
                $challenge->status == Response\Authorization\Struct\Challenge::STATUS_PROGRESSING
            ) {

                Utilities\Logger::getInstance()->add(
                    Utilities\Logger::LEVEL_DEBUG,
                    get_class() . '::' . __FUNCTION__ . ' (cache did not satisfy, status "' . $response->getStatus() . '")'
                );

                $request = new Request\Authorization\Get($order->getAccount(), $authorizationUrl);
                $this->set($order, $authorizationUrl, $response = $request->getResponse());
                return $response;
            }

            Utilities\Logger::getInstance()->add(
                Utilities\Logger::LEVEL_DEBUG,
                get_class() . '::' . __FUNCTION__ .  ' (from cache, status "' . $response->getStatus() . '")'
            );

            $this->_responses[$accountIdentifier][$orderIdentifier][$authorizationUrl] = $response;

            return $response;
        }

        $request = new Request\Authorization\Get($order->getAccount(), $authorizationUrl);
        $this->set($order, $authorizationUrl, $response = $request->getResponse());
        return $response;
    }

    public function set(Order $order, string $authorizationUrl, Response\Authorization\Get $response = null) : void {

        $accountIdentifier = $this->_getObjectIdentifier($order->getAccount());
        $orderIdentifier = $this->_getObjectIdentifier($order);

        $filePath = $this->_getCacheFilePath($order, $authorizationUrl);

        if($response === null) {

            unset($this->_responses[$accountIdentifier][$orderIdentifier][$authorizationUrl]);

            if(file_exists($filePath)) {
                unlink($filePath);
            }

            return;
        }

        $this->_responses[$accountIdentifier][$orderIdentifier][$authorizationUrl] = $response;
        file_put_contents($filePath, $response->getRaw()->toString());
    }

    /**
     * Clear the cache, when the next response could be different:
     * - Order has ended (certificate received)
     * - Authorization::Start
     *
     * @param Order $order
     * @throws Exception\ExpiredAuthorization
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function clear(Order $order) : void {

        $orderResponse = OrderResponse::getInstance()->get($order);
        foreach($orderResponse->getAuthorizations() as $authorization) {
            $this->set($order, $authorization, null);
        }
    }
}