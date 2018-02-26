<?php

namespace LE_ACME2\Request\Account;

use LE_ACME2\Response as Response;

class GetData extends AbstractLocation {
    
    protected function _getPayload() {
        
        return [];
    }

    public function getResponse() {
        
        return new Response\Account\GetData($this->_getRawResponse());
    }
}