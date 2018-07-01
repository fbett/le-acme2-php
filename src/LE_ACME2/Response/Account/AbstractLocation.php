<?php

namespace LE_ACME2\Response\Account;

use LE_ACME2\Response\AbstractResponse;

abstract class AbstractLocation extends AbstractResponse {

    public function getId() {

        return $this->_raw->body['id'];
    }

    public function getKey() {

        return $this->_raw->body['key'];
    }

    public function getContact() {

        return $this->_raw->body['contact'];
    }

    public function getAgreement() {

        return $this->_raw->body['agreement'];
    }

    public function getInitialIP() {

        return $this->_raw->body['initialIp'];
    }

    public function getCreatedAt() {

        return $this->_raw->body['createdAt'];
    }

    public function getStatus() {

        return $this->_raw->body['status'];
    }
}