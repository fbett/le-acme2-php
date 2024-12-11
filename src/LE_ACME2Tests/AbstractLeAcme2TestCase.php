<?php
namespace LE_ACME2Tests;

use PHPUnit\Framework\TestCase;
use LE_ACME2;

abstract class AbstractLeAcme2TestCase extends EnhancedTestCase {

    protected $_accountEmail;
    protected $_orderSubjects = [];
    protected $_umlautsOrderSubjects = [];

    public function __construct(string $name) {

        $this->_accountEmail = 'le_acme2_php' . phpversion() . '_client@test.com';

        parent::__construct($name);

        $this->_orderSubjects[] = 'test.de';

        $this->_umlautsOrderSubjects[] = 'xn--test--kra0kxb.de'; // test-üäö.de

        LE_ACME2\Connector\Connector::getInstance()->useStagingServer(true);
    }
}