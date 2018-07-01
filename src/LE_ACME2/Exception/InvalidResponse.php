<?php

namespace LE_ACME2\Exception;

use LE_ACME2\Connector\Struct\RawResponse;

class InvalidResponse extends AbstractException {

    public function __construct(RawResponse $raw) {

        parent::__construct("Invalid response received: " . var_export($raw, true));
    }
}