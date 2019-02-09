<?php

namespace LE_ACME2\Response\Order;

use LE_ACME2\Connector\Struct\RawResponse;
use LE_ACME2\Exception;

class Get extends AbstractDirectoryNewOrder {

    /**
     * Get constructor.
     *
     * @param RawResponse $raw
     * @param $orderURL
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function __construct(RawResponse $raw, $orderURL)
    {
        parent::__construct($raw);

        $this->_raw->header[] = 'Location: ' . $orderURL; // Dirty fix: Header of response "Get" does not contain an order url, instead of response "Create"
    }
}