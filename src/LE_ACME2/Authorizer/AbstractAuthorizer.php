<?php

namespace LE_ACME2\Authorizer;

use LE_ACME2\Request as Request;
use LE_ACME2\Response as Response;
use LE_ACME2\Utilities as Utilities;

use LE_ACME2\Account;
use LE_ACME2\Connector\Storage;
use LE_ACME2\Order;

abstract class AbstractAuthorizer {

    protected $_account;
    protected $_order;

    /**
     * AbstractAuthorizer constructor.
     *
     * @param Account $account
     * @param Order $order
     * @throws \LE_ACME2\Exception\InvalidResponse
     * @throws \LE_ACME2\Exception\RateLimitReached
     */
    public function __construct(Account $account, Order $order) {

        $this->_account = $account;
        $this->_order = $order;

        $this->_fetchAuthorizationResponses();
    }

    /** @var Response\Authorization\Get[] $_authorizationResponses */
    protected $_authorizationResponses = [];

    /**
     * @throws \LE_ACME2\Exception\InvalidResponse
     * @throws \LE_ACME2\Exception\RateLimitReached
     */
    protected function _fetchAuthorizationResponses() {

        if(!file_exists($this->_order->getKeyDirectoryPath() . 'private.pem')) // Order has finished already
            return;

        $directoryNewOrderResponse = Storage::getInstance()->getDirectoryNewOrderResponse($this->_account, $this->_order);

        foreach($directoryNewOrderResponse->getAuthorizations() as $authorization) {

            $request = new Request\Authorization\Get($this->_account, $authorization);
            $this->_authorizationResponses[] = $request->getResponse();
        }
    }

    protected function _hasValidAuthorizationResponses() {

        return count($this->_authorizationResponses) > 0;
    }

    abstract public function shouldStartAuthorization();
    abstract public function progress();

    protected $_finished = false;

    public function hasFinished() {

        Utilities\Logger::getInstance()->add(
            Utilities\Logger::LEVEL_DEBUG,
            get_called_class() . '::' . __FUNCTION__,
            $this->_finished
        );

        return $this->_finished;
    }
}

