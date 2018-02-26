<?php

namespace LE_ACME2\Response;

abstract class AbstractResponse {

    protected $_raw = NULL;

    public function __construct($raw) {

        $this->_raw = $raw;
    }

    public function isValid() {

        return strpos($this->_raw['header'], "200 OK") !== false;
    }

    public function getRaw() {

        return $this->_raw;
    }
}