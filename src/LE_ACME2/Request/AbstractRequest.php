<?php

namespace LE_ACME2\Request;

use LE_ACME2\Response\AbstractResponse;

use LE_ACME2\Exception;

abstract class AbstractRequest {

    /**
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\ServiceUnavailable
     */
    abstract public function getResponse() : AbstractResponse;

    protected function _buildContactPayload(string $email) : array {

        $result = [
            'mailto:' . $email
        ];
        return $result;
    }
}