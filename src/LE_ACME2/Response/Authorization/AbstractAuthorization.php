<?php

namespace LE_ACME2\Response\Authorization;

use LE_ACME2\Response\AbstractResponse;

use LE_ACME2\Connector\Struct\RawResponse;
use LE_ACME2\Exception;

class AbstractAuthorization extends AbstractResponse {

    /**
     * AbstractAuthorization constructor.
     * @param RawResponse $raw
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\ExpiredAuthorization
     */
    public function __construct(RawResponse $raw) {
        parent::__construct($raw);
    }

    /**
     * @return bool
     * @throws Exception\ExpiredAuthorization
     */
    protected function _isValid() : bool {

        if($this->_preg_match_headerLine('/^HTTP.* 404 .*$/i') !== null) {
            if(
                is_array($this->_raw->body) &&
                isset($this->_raw->body['status']) && $this->_raw->body['status'] == '404' &&
                isset($this->_raw->body['detail']) && $this->_raw->body['detail'] == 'Expired authorization'
            ) {
                throw new Exception\ExpiredAuthorization($this->_raw);
            }
        }

        return parent::_isValid();
    }
}