<?php

namespace LE_ACME2\Response\Authorization;

use LE_ACME2\Response\AbstractResponse;

use LE_ACME2\Connector\RawResponse;
use LE_ACME2\Exception;

class AbstractAuthorization extends AbstractResponse {

    // Status from RFC 8555 (7.1.4), version: March 2019

    const STATUS_PENDING = 'pending';
    const STATUS_VALID = 'valid';
    const STATUS_INVALID = 'invalid';
    const STATUS_DEACTIVATED = 'deactivated';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REVOKED = 'revoked';

    /**
     * AbstractAuthorization constructor.
     *
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\ExpiredAuthorization
     * @throws Exception\ServiceUnavailable
     */
    public function __construct(RawResponse $raw) {
        parent::__construct($raw);
    }

    /**
     * @throws Exception\ExpiredAuthorization
     */
    protected function _isValid() : bool {

        if($this->_preg_match_headerLine('/^HTTP\/.* 404/i') !== null) {
            throw new Exception\ExpiredAuthorization();
        }

        return parent::_isValid();
    }
}