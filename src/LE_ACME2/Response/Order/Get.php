<?php

namespace LE_ACME2\Response\Order;

use LE_ACME2\Connector\RawResponse;
use LE_ACME2\Exception;

class Get extends AbstractOrder {

    /**
     * Get constructor.
     *
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\ServiceUnavailable
     */
    public function __construct(RawResponse $raw, string $orderURL) {

        // Dirty fix: Header of response "Get" does not contain an order url, instead of response "Create"
        // Is needed on production server, not on staging server - tested: 12.04.2021
        $raw->header[] = 'Location: ' . $orderURL;

        parent::__construct($raw);
    }
}