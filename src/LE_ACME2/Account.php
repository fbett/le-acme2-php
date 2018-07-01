<?php

namespace LE_ACME2;

use LE_ACME2\Connector\Storage;
use LE_ACME2\Request as Request;
use LE_ACME2\Response as Response;
use LE_ACME2\Utilities as Utilities;
use LE_ACME2\Exception as Exception;

class Account extends AbstractKeyValuable {

    private $_email = NULL;

    public function __construct($email) {

        $this->setEmail($email);
    }

    public function setEmail($email) {

        $this->_email = $email;
        $this->_identifier = $this->_getAccountIdentifier($this);
    }

    public function getEmail() {

        return $this->_email;
    }

    /**
     * @param string $email
     * @return Account|null
     * @throws Exception\AbstractException
     */
    public static function create($email) {

        $account = new self($email);
        $account->_initKeyDirectory();

        $request = new Request\Account\Create($account);

        try {
            $response = $request->getResponse();

            Storage::getInstance()->setDirectoryNewAccountResponse($account, $response);
            Utilities\Logger::getInstance()->add(
                Utilities\Logger::LEVEL_INFO,
                get_class() . '::' . __FUNCTION__ .  ' "' . $email . '"'
            );

            return $account;

        } catch(Exception\AbstractException $e) {

            $account->_clearKeyDirectory();
            throw $e;
        }
    }

    public static function exists($email) {

        $account = new self($email);

        return  file_exists($account->getKeyDirectoryPath()) &&
                file_exists($account->getKeyDirectoryPath() . 'private.pem') &&
                file_exists($account->getKeyDirectoryPath() . 'public.pem');
    }

    /**
     * @param string $email
     * @return Account
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public static function get($email) {

        $account = new self($email);

        if(!self::exists($email))
            throw new \RuntimeException('Keys not found - does this account exist?');

        $directoryNewAccountResponse = Storage::getInstance()->getDirectoryNewAccountResponse($account);
        if($directoryNewAccountResponse !== NULL) {
            Utilities\Logger::getInstance()->add(
                Utilities\Logger::LEVEL_DEBUG,
                get_class() . '::' . __FUNCTION__ .  ' "' . $email . '" (from cache)'
            );
            return $account;
        }

        $request = new Request\Account\Get($account);
        $response = $request->getResponse();

        Storage::getInstance()->setDirectoryNewAccountResponse($account, $response);

        Utilities\Logger::getInstance()->add(
            Utilities\Logger::LEVEL_INFO,
            get_class() . '::' . __FUNCTION__ .  ' "' . $email . '"'
        );
        return $account;
    }

    /**
     * @return Response\AbstractResponse|Response\Account\GetData
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function getData() {

        $request = new Request\Account\GetData($this);
        return $request->getResponse();
    }

    /**
     * @param string $email
     * @return bool
     * @throws Exception\RateLimitReached
     */
    public function update($email) {

        $request = new Request\Account\Update($this, $email);

        try {
            /* $response = */ $request->getResponse();

            $previousKeyDirectoryPath = $this->getKeyDirectoryPath();

            $this->setEmail($email);

            if($previousKeyDirectoryPath != $this->getKeyDirectoryPath())
                rename($previousKeyDirectoryPath, $this->getKeyDirectoryPath());

            return true;

        } catch(Exception\InvalidResponse $e) {
            return false;
        }
    }

    /**
     * @return bool
     * @throws Exception\RateLimitReached
     */
    public function changeKeys() {

        Utilities\KeyGenerator::RSA($this->getKeyDirectoryPath(), 'private-replacement.pem', 'public-replacement.pem');

        $request = new Request\Account\ChangeKeys($this);
        try {
            /* $response = */ $request->getResponse();

            unlink($this->getKeyDirectoryPath() . 'private.pem');
            unlink($this->getKeyDirectoryPath() . 'public.pem');
            rename($this->getKeyDirectoryPath() . 'private-replacement.pem', $this->getKeyDirectoryPath() . 'private.pem');
            rename($this->getKeyDirectoryPath() . 'private-replacement.pem', $this->getKeyDirectoryPath() . 'public.pem');
            return true;

        } catch(Exception\InvalidResponse $e) {

            return false;
        }
    }

    /**
     * @return bool
     * @throws Exception\RateLimitReached
     */
    public function deactivate() {

        $request = new Request\Account\Deactivate($this);

        try {
            /* $response = */ $request->getResponse();

            return true;

        } catch(Exception\InvalidResponse $e) {
            return false;
        }
    }
}