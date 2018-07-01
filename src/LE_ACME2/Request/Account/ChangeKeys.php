<?php

namespace LE_ACME2\Request\Account;

use LE_ACME2\Connector\Storage;
use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Response as Response;
use LE_ACME2\Utilities as Utilities;

use LE_ACME2\Account;
use LE_ACME2\Connector\Connector;

class ChangeKeys extends AbstractRequest {

    protected $_account;

    public function __construct(Account $account) {

        $this->_account = $account;
    }

    /**
     * @return Response\AbstractResponse|Response\Account\ChangeKeys
     * @throws \LE_ACME2\Exception\InvalidResponse
     * @throws \LE_ACME2\Exception\RateLimitReached
     */
    public function getResponse()
    {
        $connector = Connector::getInstance();
        $storage = Storage::getInstance();

        $privateKey = openssl_pkey_get_private(
            file_get_contents($this->_account->getKeyDirectoryPath() . 'private-replacement.pem')
        );
        $details = openssl_pkey_get_details($privateKey);

        $innerPayload = [
            'account' => $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            'newKey' => [
                "kty" => "RSA",
                "n" => Utilities\Base64::UrlSafeEncode($details["rsa"]["n"]),
                "e" => Utilities\Base64::UrlSafeEncode($details["rsa"]["e"])
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
            Connector::METHOD_POST,
            $storage->getGetDirectoryResponse()->getKeyChange(),
            $data
        );

        return new Response\Account\ChangeKeys($result);
    }
}