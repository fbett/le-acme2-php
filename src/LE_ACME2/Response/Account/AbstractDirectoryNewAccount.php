<?php

namespace LE_ACME2\Response\Account;

use LE_ACME2\Response\AbstractResponse;

abstract class AbstractDirectoryNewAccount extends AbstractResponse {

    public function getLocation() {

        $matches = $this->_preg_match_headerLine($this->_pattern_header_location);
        return trim($matches[1]);
    }
}