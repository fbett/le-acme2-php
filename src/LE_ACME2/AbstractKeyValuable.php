<?php

namespace LE_ACME2;

use LE_ACME2\Connector\Connector;

abstract class AbstractKeyValuable {

    const KEY_TYPE_RSA = "RSA";
    const KEY_TYPE_EC = "EC";

    protected $_identifier;

    protected static $_directoryPath = null;

    public static function setCommonKeyDirectoryPath($directoryPath) {

        if(!file_exists($directoryPath)) {
            throw new \RuntimeException('Common Key Directory Path does not exist');
        }

        self::$_directoryPath = realpath($directoryPath) . DIRECTORY_SEPARATOR;
    }

    protected function _getKeyDirectoryPath($appendix = '') {

        return self::$_directoryPath . $this->_identifier . $appendix . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    public function getKeyDirectoryPath() {

        return $this->_getKeyDirectoryPath('');
    }

    protected function _initKeyDirectory($keyType = self::KEY_TYPE_RSA, $ignoreIfKeysExist = false) {

        if(!file_exists($this->getKeyDirectoryPath())) {

            mkdir($this->getKeyDirectoryPath());
        }

        if(!$ignoreIfKeysExist && (file_exists($this->getKeyDirectoryPath() . 'private.pem') ||
            file_exists($this->getKeyDirectoryPath() . 'public.pem')) ) {

            throw new \RuntimeException('Keys exist already. Exists the ' . get_class($this) . ' already?');
        }

        if($keyType == self::KEY_TYPE_RSA) {

            Utilities\KeyGenerator::RSA(
                $this->getKeyDirectoryPath(),
                'private.pem',
                'public.pem'
            );
        } else if($keyType == self::KEY_TYPE_EC) {

            Utilities\KeyGenerator::EC(
                $this->getKeyDirectoryPath(),
                'private.pem',
                'public.pem'
            );
        } else {

            throw new \RuntimeException('Key type "' . $keyType . '" not supported.');
        }
    }

    protected function _clearKeyDirectory() {

        unlink($this->getKeyDirectoryPath() . 'private.pem');
        unlink($this->getKeyDirectoryPath() . 'public.pem');
    }

    protected function _getAccountIdentifier(Account $account) {

        $staging = Connector::getInstance()->isUsingStagingServer();

        return 'account_' . ($staging ? 'staging_' : 'live_') . $account->getEmail();
    }
}