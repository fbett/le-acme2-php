<?php

namespace LE_ACME2\Exception;

class RateLimitReached extends AbstractException {

    public function __construct() {

        parent::__construct("Invalid response received: rate limit reached");
    }
}