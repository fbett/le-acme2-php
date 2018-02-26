<?php

namespace LE_ACME2\Request;

abstract class AbstractRequest {

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