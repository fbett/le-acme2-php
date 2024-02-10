<?php

namespace LE_ACME2\Request\Account;

use LE_ACME2\Response;

use LE_ACME2\Exception;

class GetData extends AbstractLocation {
    
    protected function _getPayload() : array {
        
        return [];
    }

    /**
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\ServiceUnavailable
     */
    public function getResponse() : Response\Account\GetData {
        
        return new Response\Account\GetData($this->_getRawResponse());
    }
}