<?php

namespace LE_ACME2\Response\Account;

use LE_ACME2\Response\AbstractResponse;

abstract class AbstractDirectoryNewAccount extends AbstractResponse {

    public function getLocation() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Could not get or create account');
        }

        preg_match('~Location: (\S+)~i', $this->_raw['header'], $matches);
        return trim($matches[1]);
    }
}