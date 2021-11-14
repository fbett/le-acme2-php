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
     * @return Struct\Challenge|null
     */
    public function getChallenge(string $type) : ?Struct\Challenge {

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
        }

        // There is not a challenge for a specific type, when the subject is already authorized by another
        // authorize type, f.e. when switching from http-01 to dns-01

        return null;
    }
}