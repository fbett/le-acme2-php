<?php

namespace LE_ACME2\Request\Account;

use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Response;

use LE_ACME2\Connector;
use LE_ACME2\Cache;
use LE_ACME2\Utilities;
use LE_ACME2\Exception;

use LE_ACME2\Account;

class Create extends AbstractRequest {
    
    protected $_account;
    
    public function __construct(Account $account) {
        $this->_account = $account;
    }

    /**
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\ServiceUnavailable
     */
    public function getResponse() : Response\Account\Create {

        $payload = [
            'contact' => $this->_buildContactPayload($this->_account->getEmail()),
            'termsOfServiceAgreed' => true,
        ];
        
        $jwk = Utilities\RequestSigner::JWKString(
            $payload,
            Cache\DirectoryResponse::getInstance()->get()->getNewAccount(),
            Cache\NewNonceResponse::getInstance()->get()->getNonce(),
            $this->_account->getKeyDirectoryPath()
        );
        
        $result = Connector\Connector::getInstance()->request(
            Connector\Connector::METHOD_POST,
            Cache\DirectoryResponse::getInstance()->get()->getNewAccount(),
            $jwk
        );
        
        return new Response\Account\Create($result);
    }
}