<?php
namespace LE_ACME2Tests\Response\Order;

use LE_ACME2\Exception\OrderStatusInvalid;
use LE_ACME2Tests\Connector;
use PHPUnit\Framework\TestCase;

class GetTest extends TestCase {

    public function testGetChallengeError() {

        $rawResponse = Connector\RawResponse::createDummyFrom(
            Connector\RawResponse::HEADER_200,
            file_get_contents(dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . '_JSONSamples' . DIRECTORY_SEPARATOR . 'OrderStatusInvalid.json')
        );

        $this->expectException(OrderStatusInvalid::class);
        $response = new \LE_ACME2\Response\Order\Get($rawResponse, 'http://dummy.org');
    }
}