<?php

namespace LE_ACME2\Request\Order;

use LE_ACME2\Connector\Storage;
use LE_ACME2\Order;
use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Response as Response;
use LE_ACME2\Utilities as Utilities;

use LE_ACME2\Account;
use LE_ACME2\Connector\Connector;

class Create extends AbstractRequest {

    protected $_account;
    protected $_order;

    public function __construct(Account $account, Order $order) {

        $this->_account = $account;
        $this->_order = $order;
    }

    /**
     * @return Response\AbstractResponse|Response\Order\Create
     * @throws \LE_ACME2\Exception\InvalidResponse
     * @throws \LE_ACME2\Exception\RateLimitReached
     */
    public function getResponse()
    {
        $connector = Connector::getInstance();
        $storage = Storage::getInstance();

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
            Connector::METHOD_POST,
            $storage->getGetDirectoryResponse()->getNewOrder(),
            $kid
        );

        return new Response\Order\Create($result);
    }
}