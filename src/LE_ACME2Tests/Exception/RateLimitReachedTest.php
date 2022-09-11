<?php
namespace LE_ACME2Tests\Exception;

use LE_ACME2;
use LE_ACME2Tests\Connector\RawResponse;
use LE_ACME2Tests\EnhancedTestCase;

class RateLimitReachedTest extends EnhancedTestCase {

    /**
     * @covers LE_ACME2\Exception\RateLimitReached
     * @covers LE_ACME2\Response\AbstractResponse::_isRateLimitReached
     * @covers LE_ACME2\Response\AbstractResponse::__construct
     *
     * @return void
     */
    public function test() {

        $raw = RawResponse::createDummyFrom(
            'HTTP/2 429 Too many requests' . "\r\n",
            '{
    "type": "urn:ietf:params:acme:error:rateLimited",
    "detail": "Service busy; retry later."
}',
        );

        /** @var LE_ACME2\Exception\RateLimitReached $exception */
        $exception = $this->catchExpectedException(LE_ACME2\Exception\RateLimitReached::class, function() use($raw) {
            new LE_ACME2\Response\GetDirectory($raw);
        });
        $this->assertIsObject($exception);
        $this->assertTrue(get_class($exception) == LE_ACME2\Exception\RateLimitReached::class);
    }

    /**
     * @covers LE_ACME2\Exception\RateLimitReached
     * @covers LE_ACME2\Response\AbstractResponse::_isRateLimitReached
     * @covers LE_ACME2\Response\AbstractResponse::__construct
     *
     * @return void
     */
    public function testRetryAfterHeader() {

        $raw = RawResponse::createDummyFrom(
            'HTTP/2 429 Too many requests' . "\r\n" .
            'Retry-After: 120',
            '{
    "type": "urn:ietf:params:acme:error:rateLimited",
    "detail": "Service busy; retry later."
}',
        );

        /** @var LE_ACME2\Exception\RateLimitReached $exception */
        $exception = $this->catchExpectedException(LE_ACME2\Exception\RateLimitReached::class, function() use($raw) {
            new LE_ACME2\Response\GetDirectory($raw);
        });
        $this->assertEquals('120', $exception->getRetryAfter());
    }
}