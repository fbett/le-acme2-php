<?php
namespace LE_ACME2;

use LE_ACME2\Request;
use LE_ACME2\Response;

use LE_ACME2\Utilities;
use LE_ACME2\Exception;

class Account extends AbstractKeyValuable {

    private $_email = NULL;

    public function __construct(string $email) {

        $this->_setEmail($email);

        Utilities\Logger::getInstance()->add(
            Utilities\Logger::LEVEL_DEBUG,
            static::class . '::' . __FUNCTION__ .
            ' email: "' . $email . '" ' .
            ' path: ' . $this->getKeyDirectoryPath()
        );
    }

    private function _setEmail(string $email) {

        $this->_email = $email;
        $this->_identifier = $this->_getAccountIdentifier($this);
    }

    public function getEmail() : string {

        return $this->_email;
    }

    /**
     * @throws Exception\AbstractException
     */
    public static function create(string $email) : Account {

        Utilities\Logger::getInstance()->add(
            Utilities\Logger::LEVEL_INFO,
            static::class . '::' . __FUNCTION__ .  ' email: "' . $email . '"'
        );

        $account = new self($email);
        $account->_initKeyDirectory();

        $request = new Request\Account\Create($account);

        try {
            $response = $request->getResponse();

            Cache\AccountResponse::getInstance()->set($account, $response);

            return $account;

        } catch(Exception\AbstractException $e) {

            $account->_clearKeyDirectory();
            throw $e;
        }
    }

    public static function exists(string $email) : bool {

        $account = new self($email);

        return  file_exists($account->getKeyDirectoryPath()) &&
                file_exists($account->getKeyDirectoryPath() . 'private.pem') &&
                file_exists($account->getKeyDirectoryPath() . 'public.pem');
    }

    public static function get(string $email) : Account {

        Utilities\Logger::getInstance()->add(
            Utilities\Logger::LEVEL_INFO,
            static::class . '::' . __FUNCTION__ .  ' email: "' . $email . '"'
        );

        $account = new self($email);

        if(!self::exists($email))
            throw new \RuntimeException('Keys not found - does this account exist?');

        return $account;
    }

    /**
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\ServiceUnavailable
     */
    public function getData() : Response\Account\GetData {

        $request = new Request\Account\GetData($this);
        return $request->getResponse();
    }

    /**
     * @throws Exception\RateLimitReached
     * @throws Exception\ServiceUnavailable
     */
    public function update(string $email) : bool {

        $request = new Request\Account\Update($this, $email);

        try {
            /* $response = */ $request->getResponse();

            $previousKeyDirectoryPath = $this->getKeyDirectoryPath();

            $this->_setEmail($email);

            if($previousKeyDirectoryPath != $this->getKeyDirectoryPath())
                rename($previousKeyDirectoryPath, $this->getKeyDirectoryPath());

            return true;

        } catch(Exception\InvalidResponse $e) {
            return false;
        }
    }

    /**
     * @throws Exception\RateLimitReached
     * @throws Exception\ServiceUnavailable
     */
    public function changeKeys() : bool {

        Utilities\KeyGenerator::RSA($this->getKeyDirectoryPath(), 'private-replacement.pem', 'public-replacement.pem');

        $request = new Request\Account\ChangeKeys($this);
        try {
            /* $response = */ $request->getResponse();

            unlink($this->getKeyDirectoryPath() . 'private.pem');
            unlink($this->getKeyDirectoryPath() . 'public.pem');
            rename($this->getKeyDirectoryPath() . 'private-replacement.pem', $this->getKeyDirectoryPath() . 'private.pem');
            rename($this->getKeyDirectoryPath() . 'public-replacement.pem', $this->getKeyDirectoryPath() . 'public.pem');
            return true;

        } catch(Exception\InvalidResponse $e) {

            return false;
        }
    }

    /**
     * @throws Exception\RateLimitReached
     * @throws Exception\ServiceUnavailable
     */
    public function deactivate() : bool {

        $request = new Request\Account\Deactivate($this);

        try {
            /* $response = */ $request->getResponse();

            return true;

        } catch(Exception\InvalidResponse $e) {
            return false;
        }
    }
}