<?php

namespace LE_ACME2\Connector;

use LE_ACME2\Request;
use LE_ACME2\Response;

use LE_ACME2\SingletonTrait;
use LE_ACME2\Cache;
use LE_ACME2\Utilities;
use LE_ACME2\Exception;

class Connector {

    use SingletonTrait;
    
    const METHOD_GET = 'GET';
    const METHOD_HEAD = 'HEAD';
    const METHOD_POST = 'POST';

    private function __construct() {}

    protected $_baseURL = 		 'https://acme-v02.api.letsencrypt.org';
    protected $_stagingBaseURL = 'https://acme-staging-v02.api.letsencrypt.org';

    protected $_useStagingServer = true;

    protected $_delayedResponseTime = 0;

    public function useStagingServer(bool $useStagingServer) {
        $this->_useStagingServer = $useStagingServer;
    }

    public function isUsingStagingServer() : bool {
        return $this->_useStagingServer;
    }

    public function getBaseURL() : string {
        return $this->_useStagingServer ? $this->_stagingBaseURL : $this->_baseURL;
    }

    /**
     * Delay the response to prevent bleaching rate limits
     */
    public function delayResponse(int $milliSeconds) : void {
        $this->_delayedResponseTime = $milliSeconds;
    }

    /**
     * Makes a Curl request.
     *
     * @param string	$method	The HTTP method to use. Accepting GET, POST and HEAD requests.
     * @param string 	$url 	The URL to make the request to.
     * @param string|null 	$data  	The body to attach to a POST request. Expected as a JSON encoded string.
     *
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\ServiceUnavailable
     */
    public function request(string $method, string $url, string $data = null) : RawResponse {

        Utilities\Event::getInstance()->trigger(Utilities\Event::EVENT_CONNECTOR_WILL_REQUEST, [
            'method' => $method,
            'url' => $url,
            'data' => $data,
        ]);
        Utilities\Logger::getInstance()->add(Utilities\Logger::LEVEL_INFO, 'will request from ' . $url, ['data' => $data]);

        $handle = curl_init();

        $headers = array(
            'Accept: application/json',
            'Content-Type: ' . ($method == self::METHOD_POST ? 'application/jose+json' : 'application/json') //  ACME draft-10, section 6.2
        );

        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_HEADER, true);

        switch ($method) {
            case self::METHOD_GET:
                break;
            case self::METHOD_POST:
                curl_setopt($handle, CURLOPT_POST, true);
                curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
                break;
            case self::METHOD_HEAD:
                curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'HEAD');
                curl_setopt($handle, CURLOPT_NOBODY, true);
                break;
            default:
                throw new \RuntimeException('HTTP request ' . $method . ' not supported.');
                break;
        }
        $response = curl_exec($handle);

        if($this->_delayedResponseTime > 0) {
            usleep($this->_delayedResponseTime * 1000);
        }

        if(curl_errno($handle)) {
            throw new \RuntimeException('Curl: ' . curl_error($handle));
        }

        $header_size = curl_getinfo($handle, CURLINFO_HEADER_SIZE);

        $rawResponse = RawResponse::createFrom($method, $url, $response, $header_size);

        Utilities\Logger::getInstance()->add(Utilities\Logger::LEVEL_INFO, self::class . ': response received', [get_class($rawResponse) => $rawResponse]);

        $this->_saveNewNonceFrom($rawResponse, $method);

        return $rawResponse;
    }

    /**
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\ServiceUnavailable
     */
    private function _saveNewNonceFrom(RawResponse $rawResponse, string $method) : void {

        try {
            $getNewNonceResponse = new Response\GetNewNonce($rawResponse);
            Cache\NewNonceResponse::getInstance()->set($getNewNonceResponse);

        } catch(Exception\InvalidResponse $e) {

            if($method == self::METHOD_POST) {
                $request = new Request\GetNewNonce();
                $getNewNonceResponse = $request->getResponse();
                Cache\NewNonceResponse::getInstance()->set($getNewNonceResponse);
            }
        }
    }
}