<?php

namespace LE_ACME2\Exception;

use LE_ACME2\Connector\RawResponse;

class InvalidResponse extends AbstractException {

    const RESPONSE_STATUS_TYPE_ERROR_SERVER_INTERNAL = 'urn:ietf:params:acme:error:serverInternal';
    const RESPONSE_STATUS_TYPE_ERROR_MALFORMED = 'urn:ietf:params:acme:error:malformed';

    private $_rawResponse;
    private $_responseStatus;
    private $_responseStatusType;

    public function __construct(RawResponse $rawResponse, string $responseStatus = null) {

        $this->_rawResponse = $rawResponse;
        $this->_responseStatus = $responseStatus;

        if($responseStatus === '') {
            $responseStatus = 'Unknown response status';
        }

        if(isset($this->_rawResponse->body['type'])) {
            $responseStatus = $this->_responseStatusType = $this->_rawResponse->body['type'];
        }

        if(isset($this->_rawResponse->body['detail'])) {
            $responseStatus .= ' - ' . $this->_rawResponse->body['detail'];
        }

        parent::__construct('Invalid response received: ' . $responseStatus);
    }

    public function getRawResponse() : RawResponse {
        return $this->_rawResponse;
    }

    public function getResponseStatus() : ?string {
        return $this->_responseStatus;
    }
    
    public function getResponseStatusType() : ?string {
        return $this->_responseStatusType;
    }

    public function getResponseStatusDetail() : ?string {

        if(isset($this->_rawResponse->body['detail'])) {
            return $this->_rawResponse->body['detail'];
        }
        return null;
    }
}