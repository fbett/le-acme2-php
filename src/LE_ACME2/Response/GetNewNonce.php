<?php

namespace LE_ACME2\Response;

class GetNewNonce extends AbstractResponse {

    protected $_pattern = '/^Replay\-Nonce: (\S+)$/i';

    protected function _isValid() {

        return $this->_preg_match_headerLine($this->_pattern) !== null;
    }

    public function getNonce() {

        $matches = $this->_preg_match_headerLine($this->_pattern);
        return trim($matches[1]);
    }


}