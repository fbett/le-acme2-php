<?php

namespace LE_ACME2\Exception;

use LE_ACME2\Connector\RawResponse;

class InvalidResponse extends AbstractException {

    private $_rawResponse;

    public function __construct(RawResponse $rawResponse) {

        $this->_rawResponse = $rawResponse;

        parent::__construct("Invalid response received: " . var_export($rawResponse, true));
    }

    public function getRawResponse() : RawResponse {
        return $this->_rawResponse;
    }
}