<?php

namespace LE_ACME2\Response\Order;

use LE_ACME2\Response\AbstractResponse;
use LE_ACME2\Exception;
use LE_ACME2\Response\Order\Struct\OrderError;

abstract class AbstractOrder extends AbstractResponse {

    const STATUS_PENDING = 'pending';
    const STATUS_VALID = 'valid';
    const STATUS_READY = 'ready';
    const STATUS_INVALID = 'invalid';

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

    /**
     * @return array<string> Authorization urls
     */
    public function getAuthorizations() : array {
        return $this->_raw->body['authorizations'];
    }

    public function getFinalize() : string {
        return $this->_raw->body['finalize'];
    }

    public function getCertificate() : string {
        return $this->_raw->body['certificate'];
    }

    /**
     * @throws Exception\OrderStatusInvalid
     */
    protected function _isValid(): bool {

        if(!parent::_isValid()) {
            return false;
        }

        if(
            $this->getStatus() == AbstractOrder::STATUS_INVALID
        ) {
            throw new Exception\OrderStatusInvalid(
                '. Probably all authorizations have failed. ' . PHP_EOL .
                'Please see: ' . $this->getLocation() . PHP_EOL .
                'Continue by using $order->clear() after getting rid of the problem',
                $this,
            );
        }

        return true;
    }

    public function getError() : ?Struct\OrderError {

        if(
            !isset($this->_raw->body['error'])
            || !is_array($this->_raw->body['error'])
        ) {
            return null;
        }

        return OrderError::createFrom($this->_raw->body['error']);
    }
}