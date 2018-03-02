<?php

namespace LE_ACME2\Response;

class GetNewNonce extends AbstractResponse {

    protected $_pattern = '/^Replay\-Nonce: (\S+)$/i';

    public function isValid() {

        return $this->_preg_match_headerLine($this->_pattern) !== null;
    }

    public function getNonce() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Nonce not valid. Check validation by calling isValid first');
        }

        $matches = $this->_preg_match_headerLine($this->_pattern);
        return trim($matches[1]);
    }


}