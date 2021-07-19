<?php
namespace LE_ACME2\Exception;

use LE_ACME2\Response;

class OrderStatusInvalid extends StatusInvalid {

    /** @var Response\Order\AbstractOrder $response */
    public $response;

    public function __construct(string $message, Response\Order\AbstractOrder $response) {
        parent::__construct($message);

        $this->response = $response;
    }
}