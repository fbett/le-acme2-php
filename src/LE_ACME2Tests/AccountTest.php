<?php
namespace LE_ACME2Tests;

use LE_ACME2\Exception\InvalidResponse;

/**
 * @covers \LE_ACME2\Account
 */
class AccountTest extends AbstractTest {
    
    private $_commonKeyDirectoryPath;

    public function __construct() {
        parent::__construct();

        $this->_commonKeyDirectoryPath = TestHelper::getInstance()->getTempPath() . 'le-storage/';
    }

    public function testNonExistingCommonKeyDirectoryPath() {

        $this->assertTrue(\LE_ACME2\Account::getCommonKeyDirectoryPath() === null);

        $notExistingPath = TestHelper::getInstance()->getTempPath() . 'should-not-exist/';

        $this->catchExpectedException(
            \RuntimeException::class,
            function() use($notExistingPath) {
                \LE_ACME2\Account::setCommonKeyDirectoryPath($notExistingPath);
            }
        );
    }

    public function testCommonKeyDirectoryPath() {

        if(!file_exists($this->_commonKeyDirectoryPath)) {
            mkdir($this->_commonKeyDirectoryPath);
        }

        \LE_ACME2\Account::setCommonKeyDirectoryPath($this->_commonKeyDirectoryPath);

        $this->assertTrue(
            \LE_ACME2\Account::getCommonKeyDirectoryPath() === $this->_commonKeyDirectoryPath
        );
    }

    public function testNonExisting() {

        if(\LE_ACME2\Account::exists($this->_accountEmail)) {
            $this->markTestSkipped('Skipped: Account does already exist');
        }

        $this->assertTrue(!\LE_ACME2\Account::exists($this->_accountEmail));

        $this->catchExpectedException(
            \RuntimeException::class,
            function() {
                \LE_ACME2\Account::get($this->_accountEmail);
            }
        );

    }

    public function testCreate() {

        if(\LE_ACME2\Account::exists($this->_accountEmail)) {
            // Skipping account modification tests, when the account already exists
            // to reduce the LE api usage while developing
            TestHelper::getInstance()->setSkipAccountModificationTests(true);
            $this->markTestSkipped('Account modifications skipped: Account does already exist');
        }

        $this->assertTrue(!\LE_ACME2\Account::exists($this->_accountEmail));

        $account = \LE_ACME2\Account::create($this->_accountEmail);
        $this->assertTrue(is_object($account));
        $this->assertTrue($account->getEmail() === $this->_accountEmail);

        $account = \LE_ACME2\Account::get($this->_accountEmail);
        $this->assertTrue(is_object($account));

        $result = $account->getData();
        $this->assertTrue($result->getStatus() === \LE_ACME2\Response\Account\AbstractAccount::STATUS_VALID);
    }

    public function testInvalidCreate() {

        if(TestHelper::getInstance()->shouldSkipAccountModificationTests()) {
            $this->expectNotToPerformAssertions();
            return;
        }

        $e = $this->catchExpectedException(
            InvalidResponse::class,
            function() {
                \LE_ACME2\Account::create('test@example.org');
            }
        );
        $this->assertEquals(
            'Invalid response received: ' .
            'urn:ietf:params:acme:error:invalidEmail' .
            ' - ' .
            'Error creating new account :: invalid contact domain. Contact emails @example.org are forbidden',
            $e->getMessage(),
        );
    }

    public function testModification() {

        if(TestHelper::getInstance()->shouldSkipAccountModificationTests()) {
            $this->expectNotToPerformAssertions();
            return;
        }

        $account = \LE_ACME2\Account::get($this->_accountEmail);
        $this->assertTrue(is_object($account));

        $keyDirectoryPath = $account->getKeyDirectoryPath();
        $newEmail = 'new-' . $this->_accountEmail;

        // An email from example.org is not allowed
        $result = $account->update('test@example.org');
        $this->assertTrue($result === false);

        $result = $account->update($newEmail);
        $this->assertTrue($result === true);

        $this->assertTrue($account->getKeyDirectoryPath() !== $keyDirectoryPath);
        $this->assertTrue(file_exists($account->getKeyDirectoryPath()));

        $result = $account->update($this->_accountEmail);
        $this->assertTrue($result === true);

        $result = $account->changeKeys();
        $this->assertTrue($result === true);

        // 11. August 2022
        // Quickfix: The LE server will not recognize fast enough, that the account key has already changed
        sleep(5);
    }

    public function testDeactivation() {

        if(TestHelper::getInstance()->shouldSkipAccountModificationTests()) {
            $this->expectNotToPerformAssertions();
            return;
        }

        $account = \LE_ACME2\Account::get($this->_accountEmail);
        $this->assertTrue(is_object($account));

        $result = $account->deactivate();
        $this->assertTrue($result === true);

        // 11. August 2022
        // Quickfix: The LE server will not recognize fast enough, that the account is already deactivated
        sleep(5);

        // The account is already deactivated
        $result = $account->deactivate();
        $this->assertTrue($result === false);

        // The account is already deactivated
        $result = $account->changeKeys();
        $this->assertTrue($result === false);

        // The account is already deactivated
        $this->catchExpectedException(
            \LE_ACME2\Exception\InvalidResponse::class,
            function() use($account) {
                $account->getData();
            }
        );
    }

    public function testCreationAfterDeactivation() {

        if(TestHelper::getInstance()->shouldSkipAccountModificationTests()) {
            $this->expectNotToPerformAssertions();
            return;
        }

        $account = \LE_ACME2\Account::get($this->_accountEmail);
        $this->assertTrue(is_object($account));

        system('rm -R ' . $account->getKeyDirectoryPath());
        $this->assertTrue(!\LE_ACME2\Account::exists($this->_accountEmail));

        $account = \LE_ACME2\Account::create($this->_accountEmail);
        $this->assertTrue(is_object($account));
    }

    public function test() {

        $account = \LE_ACME2\Account::get($this->_accountEmail);
        $this->assertTrue(is_object($account));
    }
}