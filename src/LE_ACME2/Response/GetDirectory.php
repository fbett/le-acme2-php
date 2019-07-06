<?php

namespace LE_ACME2\Response;

class GetDirectory extends AbstractResponse {

    public function getKeyChange() : string {
        return $this->_raw->body['keyChange'];
    }

    public function getNewAccount() : string {
        return $this->_raw->body['newAccount'];
    }

    public function getNewNonce() : string {
        return $this->_raw->body['newNonce'];
    }

    public function getNewOrder() : string {
        return $this->_raw->body['newOrder'];
    }

    public function getRevokeCert() : string {
        return $this->_raw->body['revokeCert'];
    }
}