<?php

namespace LE_ACME2Tests;

use PHPUnit;

class EnhancedTestCase extends PHPUnit\Framework\TestCase {

    /**
     * @deprecated Exception is not caught. Additional assertions in the same TestCase method will not be executed. Use catchExpectedException
     *
     * @param string $exception
     * @return void
     */
    public function expectException(string $exception): void
    {
        parent::expectException($exception);
    }

    protected function catchExpectedException(string $exception, \Closure $callback) {

        try {
            $callback();
        } catch (\Exception $e) {
            $this->assertEquals($exception, get_class($e));
            return;
        }

        throw new \RuntimeException('Expected exception not thrown: ' . $exception);
    }
}