<?php
namespace LE_ACME2Tests;

/**
 * @covers \LE_ACME2\Order
 */
class OrderTest extends AbstractTest {

    public function testNonExisting() {

        $account = \LE_ACME2\Account::get($this->_accountEmail);

        if(\LE_ACME2\Order::exists($account, $this->_orderSubjects)) {
            $this->markTestSkipped('Skipped: Order does already exist');
        }

        $this->assertTrue(!\LE_ACME2\Order::exists($account, $this->_orderSubjects));

        $this->expectException(\RuntimeException::class);
        \LE_ACME2\Order::get($account, $this->_orderSubjects);
    }

    public function testCreate() {

        $account = \LE_ACME2\Account::get($this->_accountEmail);

        if(\LE_ACME2\Order::exists($account, $this->_orderSubjects)) {
            // Skipping order modification tests, when the order already exists
            // to reduce the LE api usage while developing
            TestHelper::getInstance()->setSkipOrderModificationTests(true);
            $this->markTestSkipped('Order modifications skipped: Order does already exist');
        }

        $this->assertTrue(!\LE_ACME2\Order::exists($account, $this->_orderSubjects));

        $order = \LE_ACME2\Order::create($account, $this->_orderSubjects);
        $this->assertTrue(is_object($order));
        $this->assertTrue(count(array_diff($order->getSubjects(), $this->_orderSubjects)) == 0);

        $order = \LE_ACME2\Order::get($account, $this->_orderSubjects);
        $this->assertTrue(is_object($order));

        // TODO: Order replacement?
        //$result = $order->get();
        //$this->assertTrue($result->getStatus() === \LE_ACME2\Response\Account\AbstractAccount::STATUS_VALID);
    }

    public function testUmlautsCreate() {

        $account = \LE_ACME2\Account::get($this->_accountEmail);

        if(\LE_ACME2\Order::exists($account, $this->_umlautsOrderSubjects)) {
            // Skipping order modification tests, when the order already exists
            // to reduce the LE api usage while developing
            TestHelper::getInstance()->setSkipOrderModificationTests(true);
            $this->markTestSkipped('Order modifications skipped: Order does already exist');
        }

        $this->assertTrue(!\LE_ACME2\Order::exists($account, $this->_umlautsOrderSubjects));

        $order = \LE_ACME2\Order::create($account, $this->_umlautsOrderSubjects);
        $this->assertTrue(is_object($order));
        $this->assertTrue(count(array_diff($order->getSubjects(), $this->_umlautsOrderSubjects)) == 0);

        $order = \LE_ACME2\Order::get($account, $this->_umlautsOrderSubjects);
        $this->assertTrue(is_object($order));

        // TODO: Order replacement?
        //$result = $order->get();
        //$this->assertTrue($result->getStatus() === \LE_ACME2\Response\Account\AbstractAccount::STATUS_VALID);
    }
}