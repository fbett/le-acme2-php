<?php
namespace LE_ACME2Tests\Response\Order;

use LE_ACME2;
use LE_ACME2Tests\Connector;
use LE_ACME2Tests\EnhancedTestCase;

class GetTest extends EnhancedTestCase {

    /**
     * @covers \LE_ACME2\Exception\OrderStatusInvalid
     * @covers \LE_ACME2\Response\AbstractResponse::_isValid
     * @return void
     */
    public function testGetChallengeError() {

        $rawResponse = Connector\RawResponse::createDummyFrom(
            Connector\RawResponse::HEADER_200,
            file_get_contents(dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . '_JSONSamples' . DIRECTORY_SEPARATOR . 'OrderStatusInvalid.json')
        );

        $this->catchExpectedException(
            LE_ACME2\Exception\OrderStatusInvalid::class,
            function() use($rawResponse) {
                new LE_ACME2\Response\Order\Get($rawResponse, 'http://dummy.org');
            }
        );
    }
}