<?php
namespace LE_ACME2\Cache;

use LE_ACME2\Account;
use LE_ACME2\Connector;
use LE_ACME2\Request;
use LE_ACME2\Response;
use LE_ACME2\Exception;
use LE_ACME2\Utilities;
use LE_ACME2\SingletonTrait;

class DirectoryNewAccountResponse extends AbstractKeyValuableCache {

    use SingletonTrait;

    private const _FILE = 'DirectoryNewAccountResponse';

    private $_responses = [];

    /**
     * @param Account $account
     * @return Response\Account\AbstractDirectoryNewAccount
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function get(Account $account): Response\Account\AbstractDirectoryNewAccount {

        $accountIdentifier = $this->_getObjectIdentifier($account);

        if(array_key_exists($accountIdentifier, $this->_responses)) {
            return $this->_responses[ $accountIdentifier ];
        }
        $this->_responses[ $accountIdentifier ] = null;

        $cacheFile = $account->getKeyDirectoryPath() . self::_FILE;

        if(file_exists($cacheFile) && filemtime($cacheFile) > strtotime('-7 days')) {

            $rawResponse = Connector\RawResponse::getFromString(file_get_contents($cacheFile));

            $directoryNewAccountResponse = null;

            try {
                $directoryNewAccountResponse = new Response\Account\Create($rawResponse);
            } catch(Exception\AbstractException $e) {
                $this->set($account, null);
            }

            if($directoryNewAccountResponse !== null) {

                $this->_responses[ $accountIdentifier ] = $directoryNewAccountResponse;

                Utilities\Logger::getInstance()->add(
                    Utilities\Logger::LEVEL_DEBUG,
                    get_class() . '::' . __FUNCTION__ . ' response from cache'
                );

                return $directoryNewAccountResponse;
            }
        }

        $request = new Request\Account\Get($account);
        $response = $request->getResponse();

        $this->set($account, $response);

        return $response;
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