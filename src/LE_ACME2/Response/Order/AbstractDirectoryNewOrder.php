<?php

namespace LE_ACME2\Response\Order;

use LE_ACME2\Response\AbstractResponse;
use LE_ACME2\Utilities\Logger;

abstract class AbstractDirectoryNewOrder extends AbstractResponse {

    const STATUS_PENDING = 'pending';
    const STATUS_VALID = 'valid';

    public function getLocation() {

        if(!$this->isValid()) {
            var_dump($this->_raw);
            throw new \RuntimeException('Could not get or create order');
        }

        preg_match('~Location: (\S+)~i', $this->_raw['header'], $matches);
        if(!isset($matches[1])) {
            Logger::getInstance()->add(null, "test", $this->_raw);
        }
        return trim($matches[1]);
    }

    public function getStatus() {

        if(!$this->isValid())
            throw new \RuntimeException('Could not get or create order');

        return $this->_raw['body']['status'];
    }

    public function getExpires() {

        if(!$this->isValid())
            throw new \RuntimeException('Could not get or create order');

        return $this->_raw['body']['expires'];
    }

    /**
     * @return array()
     */
    public function getIdentifiers() {

        if(!$this->isValid())
            throw new \RuntimeException('Could not get or create order');

        return $this->_raw['body']['identifiers'];
    }

    /**
     * @return array
     */
    public function getAuthorizations() {

        if(!$this->isValid())
            throw new \RuntimeException('Could not get or create order');

        return $this->_raw['body']['authorizations'];
    }

    public function getFinalize() {

        if(!$this->isValid())
            throw new \RuntimeException('Could not get or create order');

        return $this->_raw['body']['finalize'];
    }

    public function getCertificate() {

        if(!$this->isValid())
            throw new \RuntimeException('Could not get or create order');

        return $this->_raw['body']['certificate'];
    }
}