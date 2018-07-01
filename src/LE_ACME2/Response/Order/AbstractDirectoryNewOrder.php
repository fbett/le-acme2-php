<?php

namespace LE_ACME2\Response\Order;

use LE_ACME2\Response\AbstractResponse;
use LE_ACME2\Utilities\Logger;

abstract class AbstractDirectoryNewOrder extends AbstractResponse {

    const STATUS_PENDING = 'pending';
    const STATUS_VALID = 'valid';
    const STATUS_READY = 'ready';

    public function getLocation() {

        $matches = $this->_preg_match_headerLine($this->_pattern_header_location);
        return trim($matches[1]);
    }

    public function getStatus() {

        return $this->_raw->body['status'];
    }

    public function getExpires() {
        
        return $this->_raw->body['expires'];
    }

    /**
     * @return array()
     */
    public function getIdentifiers() {
        
        return $this->_raw->body['identifiers'];
    }

    /**
     * @return array
     */
    public function getAuthorizations() {
        
        return $this->_raw->body['authorizations'];
    }

    public function getFinalize() {
        
        return $this->_raw->body['finalize'];
    }

    public function getCertificate() {
        
        return $this->_raw->body['certificate'];
    }
}