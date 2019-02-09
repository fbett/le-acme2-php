<?php

namespace LE_ACME2\Response\Authorization;

use LE_ACME2\Response\Authorization\Struct;

class Get extends AbstractAuthorization {

    public function getIdentifier() {

        return new Struct\Identifier($this->_raw->body['identifier']['type'], $this->_raw->body['identifier']['value']);
    }

    public function getStatus() {

        return $this->_raw->body['status'];
    }

    public function getExpires() {

        return $this->_raw->body['expires'];
    }

    /**+
     * @return array
     */
    public function getChallenges() {

        return $this->_raw->body['challenges'];
    }

    /**
     * @param $type
     * @return Struct\Challenge
     */
    public function getChallenge($type) {

        foreach($this->getChallenges() as $challenge) {

            if($type == $challenge['type'])
                return new Struct\Challenge($challenge['type'], $challenge['status'], $challenge['url'], $challenge['token']);
        }
        throw new \RuntimeException('No challenge found with given type');
    }
}