<?php

namespace LE_ACME2\Request\Authorization;

use LE_ACME2\Connector\Connector;
use LE_ACME2\Request\AbstractRequest;

use LE_ACME2\Response as Response;

class Get extends AbstractRequest {

    protected $_authorizationURL;

    public function __construct($authorizationURL) {

        $this->_authorizationURL = $authorizationURL;
    }

    public function getResponse() {

        $connector = Connector::getInstance();

        $result = $connector->request(
            Connector::METHOD_GET,
            $this->_authorizationURL
        );

        return new Response\Authorization\Get($result);
    }
}