<?php

namespace LE_ACME2\Response;

class GetDirectory extends AbstractResponse {

    public function getKeyChange() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Received response is invalid');
        }

        return $this->_raw['body']['keyChange'];
    }

    public function getNewAccount() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Received response is invalid');
        }

        return $this->_raw['body']['newAccount'];
    }

    public function getNewNonce() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Received response is invalid');
        }

        return $this->_raw['body']['newNonce'];
    }

    public function getNewOrder() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Received response is invalid');
        }

        return $this->_raw['body']['newOrder'];
    }

    public function getRevokeCert() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Received response is invalid');
        }

        return $this->_raw['body']['revokeCert'];
    }
}