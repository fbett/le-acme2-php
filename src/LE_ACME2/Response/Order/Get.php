<?php

namespace LE_ACME2\Response\Order;

use LE_ACME2\Connector\Struct\RawResponse;

class Get extends AbstractDirectoryNewOrder {

    /**
     * Get constructor.
     *
     * @param RawResponse $raw
     * @param $orderURL
     * @throws \LE_ACME2\Exception\InvalidResponse
     * @throws \LE_ACME2\Exception\RateLimitReached
     */
    public function __construct(RawResponse $raw, $orderURL)
    {
        parent::__construct($raw);

        $this->_raw->header[] = 'Location: ' . $orderURL; // Dirty fix: Header of response "Get" does not contain an order url, instead of response "Create"
    }
}