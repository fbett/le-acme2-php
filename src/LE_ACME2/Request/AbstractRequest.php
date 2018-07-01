<?php

namespace LE_ACME2\Request;

use LE_ACME2\Exception as Exception;

use LE_ACME2\Response\AbstractResponse;

abstract class AbstractRequest {

    /**
     * @return AbstractResponse
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    abstract public function getResponse();

    /**
     * @param string $email
     * @return array
     */
    protected function _buildContactPayload($email) {

        $result = [
            'mailto:' . $email
        ];
        return $result;
    }
}