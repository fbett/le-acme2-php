<?php

namespace LE_ACME2\Authorizer;

use LE_ACME2\Request;
use LE_ACME2\Response;

use LE_ACME2\Connector;
use LE_ACME2\Utilities;
use LE_ACME2\Exception;

use LE_ACME2\Account;
use LE_ACME2\Order;

abstract class AbstractAuthorizer {

    protected $_account;
    protected $_order;

    /**
     * AbstractAuthorizer constructor.
     *
     * @param Account $account
     * @param Order $order
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\ExpiredAuthorization
     */
    public function __construct(Account $account, Order $order) {

        $this->_account = $account;
        $this->_order = $order;

        $this->_fetchAuthorizationResponses();
    }

    /** @var Response\Authorization\Get[] $_authorizationResponses */
    protected $_authorizationResponses = [];

    /**
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\ExpiredAuthorization
     */
    protected function _fetchAuthorizationResponses() {

        if(!file_exists($this->_order->getKeyDirectoryPath() . 'private.pem')) // Order has finished already
            return;

        $directoryNewOrderResponse = Connector\Storage::getInstance()->getDirectoryNewOrderResponse($this->_account, $this->_order);

        foreach($directoryNewOrderResponse->getAuthorizations() as $authorization) {

            $request = new Request\Authorization\Get($this->_account, $authorization);
            $this->_authorizationResponses[] = $request->getResponse();
        }
    }

    protected function _hasValidAuthorizationResponses() : bool {

        return count($this->_authorizationResponses) > 0;
    }

    abstract public function shouldStartAuthorization() : bool;
    abstract public function progress();

    protected $_finished = false;

    public function hasFinished() : bool {

        Utilities\Logger::getInstance()->add(
            Utilities\Logger::LEVEL_DEBUG,
            get_called_class() . '::' . __FUNCTION__,
            $this->_finished
        );

        return $this->_finished;
    }
}

