<?php

namespace LE_ACME2\Authorizer;

use LE_ACME2\Order;
use LE_ACME2\Request as Request;
use LE_ACME2\Response as Response;
use LE_ACME2\Utilities as Utilities;
use LE_ACME2\Exception as Exception;

class HTTP extends AbstractAuthorizer {

    protected static $_directoryPath = null;

    public static function setDirectoryPath($directoryPath) {

        if(!file_exists($directoryPath)) {
            throw new \RuntimeException('HTTP authorization directory path does not exist');
        }

        self::$_directoryPath = realpath($directoryPath) . DIRECTORY_SEPARATOR;
    }

    public function shouldStartAuthorization() {

        foreach($this->_authorizationResponses as $response) {

            $challenge = $response->getChallenge(Order::CHALLENGE_TYPE_HTTP);
            if($challenge->status == Response\Authorization\Struct\Challenge::STATUS_PENDING) {

                Utilities\Logger::getInstance()->add(
                    Utilities\Logger::LEVEL_DEBUG,
                    get_class() . '::' . __FUNCTION__ . ' "Pending challenge found',
                    $challenge
                );

                return true;
            }
        }
        return false;
    }

    /**
     * @throws Exception\HTTPAuthorizationInvalid
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function progress() {

        if(!$this->_hasValidAuthorizationResponses())
            return;

        $existsNotValidChallenges = false;

        foreach($this->_authorizationResponses as $authorizationResponse) {

            $challenge = $authorizationResponse->getChallenge(Order::CHALLENGE_TYPE_HTTP);

            if($challenge->status == Response\Authorization\Struct\Challenge::STATUS_PENDING) {

                Utilities\Logger::getInstance()->add(
                    Utilities\Logger::LEVEL_DEBUG,
                    get_class() . '::' . __FUNCTION__ . ' "Non valid challenge found',
                    $challenge
                );

                $existsNotValidChallenges = true;

                Utilities\Challenge::writeHTTPAuthorizationFile(self::$_directoryPath, $this->_account, $challenge);
                if(Utilities\Challenge::validateHTTPAuthorizationFile($authorizationResponse->getIdentifier()->value, $this->_account, $challenge)) {

                    $request = new Request\Authorization\Start($this->_account, $this->_order, $challenge);
                    /* $response = */ $request->getResponse();
                } else {

                    Utilities\Logger::getInstance()->add(Utilities\Logger::LEVEL_INFO, 'Could not validate HTTP Authorization file');
                }
            }
            else if($challenge->status == Response\Authorization\Struct\Challenge::STATUS_PROGRESSING) {

                // Should come back later
                $existsNotValidChallenges = true;
            }
            else if($challenge->status == Response\Authorization\Struct\Challenge::STATUS_VALID) {

            }
            else if($challenge->status == Response\Authorization\Struct\Challenge::STATUS_INVALID) {
                throw new Exception\HTTPAuthorizationInvalid(
                    'Received status "' . Response\Authorization\Struct\Challenge::STATUS_INVALID . '" while challenge should be verified'
                );
            }
            else {

                throw new \RuntimeException('Challenge status "' . $challenge->status . '" is not implemented');
            }
        }

        $this->_finished = !$existsNotValidChallenges;
    }
}