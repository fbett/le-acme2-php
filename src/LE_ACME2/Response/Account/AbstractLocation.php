<?php

namespace LE_ACME2\Response\Account;

use LE_ACME2\Response\AbstractResponse;

abstract class AbstractLocation extends AbstractResponse {

    public function getId() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Received response is invalid');
        }

        return $this->_raw['body']['id'];
    }

    public function getKey() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Received response is invalid');
        }

        return $this->_raw['body']['key'];
    }

    public function getContact() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Received response is invalid');
        }

        return $this->_raw['body']['contact'];
    }

    public function getAgreement() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Received response is invalid');
        }

        return $this->_raw['body']['agreement'];
    }

    public function getInitialIP() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Received response is invalid');
        }

        return $this->_raw['body']['initialIp'];
    }

    public function getCreatedAt() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Received response is invalid');
        }

        return $this->_raw['body']['createdAt'];
    }

    public function getStatus() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Received response is invalid');
        }

        return $this->_raw['body']['status'];
    }
}