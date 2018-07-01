<?php

namespace LE_ACME2\Response;

class GetDirectory extends AbstractResponse {

    public function getKeyChange() {

        return $this->_raw->body['keyChange'];
    }

    public function getNewAccount() {

        return $this->_raw->body['newAccount'];
    }

    public function getNewNonce() {

        return $this->_raw->body['newNonce'];
    }

    public function getNewOrder() {

        return $this->_raw->body['newOrder'];
    }

    public function getRevokeCert() {

        return $this->_raw->body['revokeCert'];
    }
}