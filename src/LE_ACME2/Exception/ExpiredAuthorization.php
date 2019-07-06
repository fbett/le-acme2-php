<?php

namespace LE_ACME2\Exception;

use LE_ACME2\Connector\Struct\RawResponse;

class ExpiredAuthorization extends AbstractException {

    public function __construct(RawResponse $raw) {
        parent::__construct("Expired authorization received: " . var_export($raw, true));
    }
}