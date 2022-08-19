<?php
namespace LE_ACME2Tests\Response\Order;

use LE_ACME2\Exception\OrderStatusInvalid;
use LE_ACME2Tests\Connector;
use LE_ACME2Tests\EnhancedTestCase;
use PHPUnit\Framework\TestCase;

class GetTest extends EnhancedTestCase {

    public function testGetChallengeError() {

        $rawResponse = Connector\RawResponse::createDummyFrom(
            Connector\RawResponse::HEADER_200,
            file_get_contents(dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . '_JSONSamples' . DIRECTORY_SEPARATOR . 'OrderStatusInvalid.json')
        );

        $this->catchExpectedException(
            OrderStatusInvalid::class,
            function() use($rawResponse) {
                new \LE_ACME2\Response\Order\Get($rawResponse, 'http://dummy.org');
            }
        );
    }
}