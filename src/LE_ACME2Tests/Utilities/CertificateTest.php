<?php
namespace LE_ACME2Tests\Utilities;

use LE_ACME2Tests\AbstractTest;
use LE_ACME2\Utilities;

class CertificateTest extends AbstractTest {

    /**
     * @depends \LE_ACME2Tests\AccountTest::testCreate
     * @depends \LE_ACME2Tests\OrderTest::testCreate
     * @depends \LE_ACME2Tests\OrderTest::testUmlautsCreate
     */
    public function testGenerateCSR() {

        $account = \LE_ACME2\Account::get($this->_accountEmail);

        $this->_testOrderGenerateCSR($account);
        $this->_testOrderUmlautsGenerateCSR($account);
    }

    private function _testOrderGenerateCSR(\LE_ACME2\Account $account) {

        $order = \LE_ACME2\Order::get($account, $this->_orderSubjects);

        // - "ocsp must staple" feature disable
        Utilities\Certificate::disableFeatureOCSPMustStaple();

        $csr = Utilities\Certificate::generateCSR($order);
        $this->assertTrue($csr !== null && is_string($csr));

        // - "ocsp must staple" feature enabled
        Utilities\Certificate::enableFeatureOCSPMustStaple();

        $csr = Utilities\Certificate::generateCSR($order);
        $this->assertTrue($csr !== null && is_string($csr));
    }

    private function _testOrderUmlautsGenerateCSR(\LE_ACME2\Account $account) {

        $order = \LE_ACME2\Order::get($account, $this->_umlautsOrderSubjects);

        // - "ocsp must staple" feature disable
        Utilities\Certificate::disableFeatureOCSPMustStaple();

        $csr = Utilities\Certificate::generateCSR($order);
        $this->assertTrue($csr !== null && is_string($csr));

        // - "ocsp must staple" feature enabled
        Utilities\Certificate::enableFeatureOCSPMustStaple();

        $csr = Utilities\Certificate::generateCSR($order);
        $this->assertTrue($csr !== null && is_string($csr));
    }
}