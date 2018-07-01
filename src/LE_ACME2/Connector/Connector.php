<?php

namespace LE_ACME2\Connector;

use LE_ACME2\Request as Request;
use LE_ACME2\Response as Response;
use LE_ACME2\Utilities as Utilities;
use LE_ACME2\Exception as Exception;

class Connector {
    
    const METHOD_GET = 'GET';
    const METHOD_HEAD = 'HEAD';
    const METHOD_POST = 'POST';

    private static $_instance = NULL;
    
    public static function getInstance() {
        
        if(self::$_instance === NULL) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected $_baseURL = 		 'https://acme-v02.api.letsencrypt.org';
    protected $_stagingBaseURL = 'https://acme-staging-v02.api.letsencrypt.org';

    protected $_useStagingServer = true;

    /**
     * @param bool $useStagingServer
     */
    public function useStagingServer($useStagingServer) {

        $this->_useStagingServer = $useStagingServer;
    }

    public function isUsingStagingServer() {

        return $this->_useStagingServer;
    }

    public function getBaseURL() {
        return $this->_useStagingServer ? $this->_stagingBaseURL : $this->_baseURL;
    }

    /**
     * Makes a Curl request.
     *
     * @param string	$method	The HTTP method to use. Accepting GET, POST and HEAD requests.
     * @param string 	$url 	The URL to make the request to.
     * @param string 	$data  	The body to attach to a POST request. Expected as a JSON encoded string.
     *
     * @return Struct\RawResponse
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function request($method, $url, $data = null) {

        Utilities\Logger::getInstance()->add(Utilities\Logger::LEVEL_INFO, 'will request from ' . $url, $data);

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

        if(curl_errno($handle)) {
            throw new \RuntimeException('Curl: ' . curl_error($handle));
        }

        $header_size = curl_getinfo($handle, CURLINFO_HEADER_SIZE);

        $rawResponse = new Struct\RawResponse();
        $rawResponse->init($method, $url, $response, $header_size);

        Utilities\Logger::getInstance()->add(Utilities\Logger::LEVEL_INFO, self::class . ': response received', $rawResponse);


        try {
            $getNewNonceResponse = new Response\GetNewNonce($rawResponse);
            Storage::getInstance()->setNewNonceResponse($getNewNonceResponse);

        } catch(Exception\InvalidResponse $e) {

            if($method == self::METHOD_POST) {
                $request = new Request\GetNewNonce();
                Storage::getInstance()->setNewNonceResponse($request->getResponse());
            }
        }

        return $rawResponse;
    }

}