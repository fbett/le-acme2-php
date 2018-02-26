<?php

namespace LE_ACME2\Request\Account;

use LE_ACME2\Response as Response;

class Deactivate extends AbstractLocation {

    protected function _getPayload() {

        return [
            'status' => 'deactivated',
        ];
    }

    public function getResponse() {

        return new Response\Account\Deactivate($this->_getRawResponse());
    }
}
