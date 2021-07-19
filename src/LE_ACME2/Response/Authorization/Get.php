<?php

namespace LE_ACME2\Response\Authorization;

use LE_ACME2\Response\Authorization\Struct;

class Get extends AbstractAuthorization {

    public function getIdentifier() : Struct\Identifier {

        return new Struct\Identifier(
            $this->_raw->body['identifier']['type'],
            $this->_raw->body['identifier']['value']
        );
    }

    public function getStatus() : string {
        return $this->_raw->body['status'];
    }

    public function getExpires() : string {
        return $this->_raw->body['expires'];
    }

    public function getChallenges() : array {
        return $this->_raw->body['challenges'];
    }

    /**
     * @param string $type
     * @return Struct\Challenge
     */
    public function getChallenge(string $type) : Struct\Challenge {

        $foundTypes = [];

        foreach($this->getChallenges() as $challenge) {

            if($type == $challenge['type']) {

                $error = null;
                if(isset($challenge[ 'error' ]) && $challenge[ 'error' ] != "") {
                    $error = new Struct\ChallengeError(
                        $challenge[ 'error' ][ 'type' ],
                        $challenge[ 'error' ][ 'detail' ],
                        $challenge[ 'error' ][ 'status' ],
                    );
                }

                return new Struct\Challenge(
                    $challenge[ 'type' ],
                    $challenge[ 'status' ],
                    $challenge[ 'url' ],
                    $challenge[ 'token' ],
                    $error,
                );
            }
            $foundTypes[] = $challenge['type'];
        }

        throw new \RuntimeException(
            'No challenge found with given type. Found types: ' . var_export($foundTypes, true)
        );
    }
}