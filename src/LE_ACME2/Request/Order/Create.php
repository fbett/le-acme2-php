<?php

namespace LE_ACME2\Request\Order;

use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Response;

use LE_ACME2\Connector;
use LE_ACME2\Exception;
use LE_ACME2\Utilities;

use LE_ACME2\Account;
use LE_ACME2\Order;

class Create extends AbstractRequest {

    protected $_account;
    protected $_order;

    public function __construct(Account $account, Order $order) {

        $this->_account = $account;
        $this->_order = $order;
    }

    /**
     * @return Response\AbstractResponse|Response\Order\Create
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function getResponse() : Response\AbstractResponse {

        $connector = Connector\Connector::getInstance();
        $storage = Connector\Storage::getInstance();

        $identifiers = [];
        foreach($this->_order->getSubjects() as $subject) {

            $identifiers[] = [
                'type' => 'dns',
                'value' => $subject
            ];
        }

        $payload = [
            'identifiers' => $identifiers,
            'notBefore' => '',
            'notAfter' => '',
        ];

        $kid = Utilities\RequestSigner::KID(
            $payload,
            $storage->getDirectoryNewAccountResponse($this->_account)->getLocation(),
            $storage->getGetDirectoryResponse()->getNewOrder(),
            $storage->getNewNonceResponse()->getNonce(),
            $this->_account->getKeyDirectoryPath()
        );
        $result = $connector->request(
            Connector\Connector::METHOD_POST,
            $storage->getGetDirectoryResponse()->getNewOrder(),
            $kid
        );

        return new Response\Order\Create($result);
    }
}