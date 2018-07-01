<?php

namespace LE_ACME2\Request\Account;

use LE_ACME2\Response as Response;

class Deactivate extends AbstractLocation {

    protected function _getPayload() {

        return [
            'status' => 'deactivated',
        ];
    }

    /**
     * @return Response\AbstractResponse|Response\Account\Deactivate
     * @throws \LE_ACME2\Exception\InvalidResponse
     * @throws \LE_ACME2\Exception\RateLimitReached
     */
    public function getResponse() {

        return new Response\Account\Deactivate($this->_getRawResponse());
    }
}
