<?php

namespace LE_ACME2\Request\Account;

use LE_ACME2\Account;
use LE_ACME2\Response as Response;

class Update extends AbstractLocation {

    protected $_newEmail;

    public function __construct(Account $account, $newEmail)
    {
        parent::__construct($account);

        $this->_newEmail = $newEmail;
    }

    protected function _getPayload() {

        return [
            'contact' => $this->_buildContactPayload($this->_newEmail),
        ];
    }

    public function getResponse() {

        return new Response\Account\Update($this->_getRawResponse());
    }
}
