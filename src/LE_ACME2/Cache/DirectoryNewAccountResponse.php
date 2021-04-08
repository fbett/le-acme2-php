<?php
namespace LE_ACME2\Cache;

use LE_ACME2\Account;
use LE_ACME2\Connector;
use LE_ACME2\Response;
use LE_ACME2\Exception;
use LE_ACME2\SingletonTrait;

class DirectoryNewAccountResponse extends AbstractKeyValuableCache {

    use SingletonTrait;

    private const _FILE = 'DirectoryNewAccountResponse';

    private $_responses = [];

    public function get(Account $account): ?Response\Account\AbstractDirectoryNewAccount {

        $accountIdentifier = $this->_getObjectIdentifier($account);

        if(isset($this->_responses[$accountIdentifier]))
            return $this->_responses[$accountIdentifier];

        $cacheFile = $account->getKeyDirectoryPath() . self::_FILE;

        if(file_exists($cacheFile) && filemtime($cacheFile) > strtotime('-7 days')) {

            $rawResponse = Connector\RawResponse::getFromString(file_get_contents($cacheFile));

            try {
                $directoryNewAccountResponse = new Response\Account\Create($rawResponse);
                $this->set($account, $directoryNewAccountResponse);

                return $directoryNewAccountResponse;

            } catch(Exception\AbstractException $e) {

                $this->set($account, null);
            }
        }
        return null;
    }

    public function set(Account $account, Response\Account\AbstractDirectoryNewAccount $response = null) : void {

        $accountIdentifier = $this->_getObjectIdentifier($account);

        $filePath = $account->getKeyDirectoryPath() . self::_FILE;

        if($response === null) {

            unset($this->_responses[$accountIdentifier]);

            if(file_exists($filePath)) {
                unlink($filePath);
            }

            return;
        }

        $this->_responses[$accountIdentifier] = $response;
        file_put_contents($filePath, $response->getRaw()->toString());
    }
}