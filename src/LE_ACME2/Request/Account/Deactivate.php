<?php

namespace LE_ACME2\Request\Account;

use LE_ACME2\Response;

use LE_ACME2\Exception;

class Deactivate extends AbstractLocation {

    protected function _getPayload() : array {

        return [
            'status' => 'deactivated',
        ];
    }

    /**
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\ServiceUnavailable
     */
    public function getResponse() : Response\Account\Deactivate {

        return new Response\Account\Deactivate($this->_getRawResponse());
    }
}
