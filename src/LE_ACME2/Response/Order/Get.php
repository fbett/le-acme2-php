<?php

namespace LE_ACME2\Response\Order;

class Get extends AbstractDirectoryNewOrder {

    public function __construct($raw, $orderURL)
    {
        parent::__construct($raw);

        $this->_raw['header'] .= '\nLocation: ' . $orderURL; // Dirty fix: Header of response "Get" does not contain an order url, instead of response "Create"
    }
}