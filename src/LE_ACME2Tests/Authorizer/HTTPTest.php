<?php
namespace LE_ACME2Tests\Authorizer;

use LE_ACME2Tests\AbstractLeAcme2TestCase;
use LE_ACME2Tests\TestHelper;

/**
 * @covers \LE_ACME2\Authorizer\HTTP
 */
class HTTPTest extends AbstractLeAcme2TestCase {

    private $_directoryPath;

    public function __construct(string $name) {
        parent::__construct($name);

        $this->_directoryPath = TestHelper::getInstance()->getTempPath() . 'acme-challenges/';
    }

    public function testNonExistingDirectoryPath() {

        $this->assertTrue(\LE_ACME2\Authorizer\HTTP::getDirectoryPath() === null);

        $this->catchExpectedException(
            \RuntimeException::class,
            function() {
                \LE_ACME2\Authorizer\HTTP::setDirectoryPath(TestHelper::getInstance()->getNonExistingPath());
            }
        );
    }

    public function testDirectoryPath() {

        if(!file_exists($this->_directoryPath)) {
            mkdir($this->_directoryPath);
        }

        \LE_ACME2\Authorizer\HTTP::setDirectoryPath($this->_directoryPath);

        $this->assertTrue(
            \LE_ACME2\Authorizer\HTTP::getDirectoryPath() === $this->_directoryPath
        );
    }
}