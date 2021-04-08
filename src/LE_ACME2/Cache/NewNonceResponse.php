<?php
namespace LE_ACME2\Cache;

use LE_ACME2\SingletonTrait;

use LE_ACME2\Exception;
use LE_ACME2\Request;
use LE_ACME2\Response;

class NewNonceResponse {

    use SingletonTrait;

    private function __construct() {}

    private $_response = null;

    /**
     * @return Response\GetNewNonce
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function get() : Response\GetNewNonce {

        if($this->_response === NULL) {
            $request = new Request\GetNewNonce();
            $this->set($request->getResponse());
        }

        return $this->_response;
    }

    public function set(Response\GetNewNonce $response) : void {
        $this->_response = $response;
    }
}