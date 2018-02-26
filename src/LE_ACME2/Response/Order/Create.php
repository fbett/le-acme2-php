<?php

namespace LE_ACME2\Response\Order;

class Create extends AbstractDirectoryNewOrder {

    public function isValid() {

        return strpos($this->_raw['header'], "201 Created") !== false || strpos($this->_raw['header'], "200 OK") !== false; // Second part is needed, if a saved request "Get" is loaded
    }
}