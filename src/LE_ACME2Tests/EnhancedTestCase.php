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

    protected function catchExpectedException(string $exception, \Closure $callback) : \Exception {

        try {
            $callback();
        } catch (\Exception $e) {
            $this->assertEquals(
                $exception,
                get_class($e),
                'Exception message: ' . $e->getMessage(),
            );
            return $e;
        }

        throw new \RuntimeException('Expected exception not thrown: ' . $exception);
    }
}