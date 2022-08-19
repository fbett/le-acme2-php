<?php
namespace LE_ACME2Tests;

use PHPUnit\Framework\TestCase;
use LE_ACME2;

abstract class AbstractTest extends EnhancedTestCase {

    protected $_accountEmail = 'le_acme2_php_client@test.com';
    protected $_orderSubjects = [];
    protected $_umlautsOrderSubjects = [];

    public function __construct() {
        parent::__construct();

        $this->_orderSubjects[] = 'test.de';

        $this->_umlautsOrderSubjects[] = 'xn--test--kra0kxb.de'; // test-üäö.de

        LE_ACME2\Connector\Connector::getInstance()->useStagingServer(true);
    }
}