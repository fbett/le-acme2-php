<?php

namespace LE_ACME2\Response\Authorization;

use LE_ACME2\Response\AbstractResponse;
use LE_ACME2\Response\Authorization\Struct\Challenge;
use LE_ACME2\Response\Authorization\Struct\Identifier;

class Get extends AbstractResponse {

    public function getIdentifier() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Could not get from authorization url');
        }

        return new Identifier($this->_raw['body']['identifier']['type'], $this->_raw['body']['identifier']['value']);
    }

    public function getStatus() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Could not get from authorization url');
        }

        return $this->_raw['body']['status'];
    }

    public function getExpires() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Could not get from authorization url');
        }

        return $this->_raw['body']['expires'];
    }

    /**+
     * @return array
     */
    public function getChallenges() {

        if(!$this->isValid()) {
            throw new \RuntimeException('Could not get from authorization url');
        }

        return $this->_raw['body']['challenges'];
    }

    /**
     * @param $type
     * @return Challenge
     */
    public function getChallenge($type) {

        foreach($this->getChallenges() as $challenge) {

            if($type == $challenge['type'])
                return new Challenge($challenge['type'], $challenge['status'], $challenge['url'], $challenge['token']);
        }
        throw new \RuntimeException('No challenge found with given type');
    }
}