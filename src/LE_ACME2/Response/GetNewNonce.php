<?php

namespace LE_ACME2\Response;

class GetNewNonce extends AbstractResponse {

    protected $_pattern = '~Replay\-Nonce: (\S+)~i';

    public function isValid() {

        return preg_match($this->_pattern, $this->_raw['header'], $matches) === 1;
    }

    public function getNonce() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Nonce not valid. Check validation by calling isValid first');
        }

        preg_match($this->_pattern, $this->_raw['header'], $matches);
        return trim($matches[1]);
    }


}