<?php

namespace LE_ACME2\Request\Account;

use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Response;

use LE_ACME2\Connector;
use LE_ACME2\Utilities;
use LE_ACME2\Exception;

use LE_ACME2\Account;

class ChangeKeys extends AbstractRequest {

    protected $_account;

    public function __construct(Account $account) {
        $this->_account = $account;
    }

    /**
     * @return Response\AbstractResponse|Response\Account\ChangeKeys
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function getResponse() : Response\AbstractResponse {

        $connector = Connector\Connector::getInstance();
        $storage = Connector\Storage::getInstance();

        $currentPrivateKey = openssl_pkey_get_private(
            file_get_contents($this->_account->getKeyDirectoryPath() . 'private.pem')
        );
        $currentPrivateKeyDetails = openssl_pkey_get_details($currentPrivateKey);

        /**
         *  draft-13 Section 7.3.6
         *  "newKey" is deprecated after August 23rd 2018
         */
        $newPrivateKey = openssl_pkey_get_private(
            file_get_contents($this->_account->getKeyDirectoryPath() . 'private-replacement.pem')
        );
        $newPrivateKeyDetails = openssl_pkey_get_details($newPrivateKey);

        $innerPayload = [
            'account' => $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            'oldKey' => [
                "kty" => "RSA",
                "n" => Utilities\Base64::UrlSafeEncode($currentPrivateKeyDetails["rsa"]["n"]),
                "e" => Utilities\Base64::UrlSafeEncode($currentPrivateKeyDetails["rsa"]["e"])
            ],
            'newKey' => [
                "kty" => "RSA",
                "n" => Utilities\Base64::UrlSafeEncode($newPrivateKeyDetails["rsa"]["n"]),
                "e" => Utilities\Base64::UrlSafeEncode($newPrivateKeyDetails["rsa"]["e"])
            ]
        ];

        $outerPayload = Utilities\RequestSigner::JWK(
            $innerPayload,
            $storage->getGetDirectoryResponse()->getKeyChange(),
            $storage->getNewNonceResponse()->getNonce(),
            $this->_account->getKeyDirectoryPath(),
            'private-replacement.pem'
        );

        $data = Utilities\RequestSigner::KID(
            $outerPayload,
            $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            $storage->getGetDirectoryResponse()->getKeyChange(),
            $storage->getNewNonceResponse()->getNonce(),
            $this->_account->getKeyDirectoryPath(),
            'private-replacement.pem'
        );

        $result = $connector->request(
            Connector\Connector::METHOD_POST,
            $storage->getGetDirectoryResponse()->getKeyChange(),
            $data
        );

        return new Response\Account\ChangeKeys($result);
    }
}