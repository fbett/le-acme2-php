<?php

namespace LE_ACME2\Response\Order;

use LE_ACME2\Response\AbstractResponse;

abstract class AbstractDirectoryNewOrder extends AbstractResponse {

    const STATUS_PENDING = 'pending';
    const STATUS_VALID = 'valid';
    const STATUS_READY = 'ready';

    public function getLocation() : string {

        $matches = $this->_preg_match_headerLine($this->_pattern_header_location);
        return trim($matches[1]);
    }

    public function getStatus() : string {
        return $this->_raw->body['status'];
    }

    public function getExpires() : string {
        return $this->_raw->body['expires'];
    }

    public function getIdentifiers() : array {
        return $this->_raw->body['identifiers'];
    }

    public function getAuthorizations() : array {
        return $this->_raw->body['authorizations'];
    }

    public function getFinalize() : string {
        return $this->_raw->body['finalize'];
    }

    public function getCertificate() : string {
        return $this->_raw->body['certificate'];
    }
}