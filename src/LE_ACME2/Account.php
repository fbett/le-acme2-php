<?php

namespace LE_ACME2;

use LE_ACME2\Connector\Storage;
use LE_ACME2\Request as Request;
use LE_ACME2\Response as Response;
use LE_ACME2\Utilities as Utilities;

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
     * @return self|null
     */
    public static function create($email) {

        $account = new self($email);
        $account->_initKeyDirectory();

        $request = new Request\Account\Create($account);
        $response = $request->getResponse();
        if($response->isValid()) {
            Storage::getInstance()->setDirectoryNewAccountResponse($account, $response);
            Utilities\Logger::getInstance()->add(
                Utilities\Logger::LEVEL_INFO,
                get_class() . '::' . __FUNCTION__ .  ' "' . $email . '"'
            );
            return $account;
        }
        Utilities\Logger::getInstance()->add(
            Utilities\Logger::LEVEL_INFO,
            get_class() . '::' . __FUNCTION__ .  ' "' . implode(':', $email) . '" - could not be created. Response: <br/>' . var_export($response->getRaw(), true)
        );
        $account->_clearKeyDirectory();
        return null;
    }

    public static function exists($email) {

        $account = new self($email);

        return  file_exists($account->getKeyDirectoryPath()) &&
                file_exists($account->getKeyDirectoryPath() . 'private.pem') &&
                file_exists($account->getKeyDirectoryPath() . 'public.pem');
    }

    /**
     * @param string $email
     * @return self
     */
    public static function get($email) {

        $account = new self($email);

        if(!self::exists($email))
            throw new \RuntimeException('Keys not found.');

        $directoryNewAccountResponse = Storage::getInstance()->getDirectoryNewAccountResponse($account);
        if($directoryNewAccountResponse !== NULl && $directoryNewAccountResponse->isValid()) {
            Utilities\Logger::getInstance()->add(
                Utilities\Logger::LEVEL_DEBUG,
                get_class() . '::' . __FUNCTION__ .  ' "' . $email . '" (from cache)'
            );
            return $account;
        }

        $request = new Request\Account\Get($account);
        $response = $request->getResponse();
        if($response->isValid()) {
            Storage::getInstance()->setDirectoryNewAccountResponse($account, $response);
            Utilities\Logger::getInstance()->add(
                Utilities\Logger::LEVEL_INFO,
                get_class() . '::' . __FUNCTION__ .  ' "' . $email . '"'
            );
            return $account;
        }
        return null;
    }

    /**
     * @return \LE_ACME2\Response\Account\GetData|null
     */
    public function getData() {

        $request = new Request\Account\GetData($this);
        $response = $request->getResponse();
        if($response->isValid()) {
            return $response;
        }
        return null;
    }

    /**
     * @param string $email
     * @return bool
     */
    public function update($email) {

        $request = new Request\Account\Update($this, $email);
        $response = $request->getResponse();
        if($response->isValid()) {

            $previousKeyDirectoryPath = $this->getKeyDirectoryPath();

            $this->setEmail($email);

            if($previousKeyDirectoryPath != $this->getKeyDirectoryPath())
                rename($previousKeyDirectoryPath, $this->getKeyDirectoryPath());

            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function changeKeys() {

        Utilities\KeyGenerator::RSA($this->getKeyDirectoryPath(), 'private-replacement.pem', 'public-replacement.pem');

        $request = new Request\Account\ChangeKeys($this);
        $response = $request->getResponse();
        if($response->isValid()) {

            unlink($this->getKeyDirectoryPath() . 'private.pem');
            unlink($this->getKeyDirectoryPath() . 'public.pem');
            rename($this->getKeyDirectoryPath() . 'private-replacement.pem', $this->getKeyDirectoryPath() . 'private.pem');
            rename($this->getKeyDirectoryPath() . 'private-replacement.pem', $this->getKeyDirectoryPath() . 'public.pem');
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function deactivate() {

        $request = new Request\Account\Deactivate($this);
        $response = $request->getResponse();
        if($response->isValid()) {
            return true;
        }
        return false;
    }
}