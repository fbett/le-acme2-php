<?php

namespace LE_ACME2;

use LE_ACME2\Request as Request;
use LE_ACME2\Response as Response;
use LE_ACME2\Utilities as Utilities;

use LE_ACME2\Connector\Storage;

class Order extends AbstractKeyValuable {

    const CHALLENGE_TYPE_HTTP = 'http-01';
    const CHALLENGE_TYPE_DNS = 'dns-01';

    protected static $_HTTPAuthorizationDirectoryPath = null;

    public static function setHTTPAuthorizationDirectoryPath($directoryPath) {

        if(!file_exists($directoryPath)) {
            throw new \RuntimeException('HTTP authorization directory path does not exist');
        }

        self::$_HTTPAuthorizationDirectoryPath = realpath($directoryPath) . DIRECTORY_SEPARATOR;
    }

    protected $_account;
    protected $_subjects;

    protected $_existsNotValidChallenges = true;

    public function __construct(Account $account, array $subjects) {

        array_map(function($subject) {

            if(preg_match_all('~(\*\.)~', $subject) > 1)
                throw new \RuntimeException('Cannot create orders with multiple wildcards in one domain.');

        }, $subjects);

        $this->_account = $account;
        $this->_subjects = $subjects;

        $this->_identifier = $this->_getAccountIdentifier($account) . DIRECTORY_SEPARATOR . 'order_' . md5(implode('|', $subjects));
    }

    public function getSubjects() {

        return $this->_subjects;
    }

    /**
     * @param Account $account
     * @param array $subjects
     * @param string $keyType
     * @return Order|null
     */
    public static function create(Account $account, array $subjects, $keyType = self::KEY_TYPE_RSA) {

        $order = new self($account, $subjects);
        return $order->_create($keyType, false);
    }

    protected function _create($keyType, $ignoreIfKeysExist = false) {

        $this->_initKeyDirectory($keyType, $ignoreIfKeysExist);

        $request = new Request\Order\Create($this->_account, $this);
        $response = $request->getResponse();
        if($response->isValid()) {
            Storage::getInstance()->setDirectoryNewOrderResponse($this->_account, $this, $response);
            Utilities\Logger::getInstance()->add(
                Utilities\Logger::LEVEL_INFO,
                get_class() . '::' . __FUNCTION__ .  ' "' . implode(':', $this->getSubjects()) . '"'
            );
            return $this;
        }
        Utilities\Logger::getInstance()->add(
            Utilities\Logger::LEVEL_INFO,
            get_class() . '::' . __FUNCTION__ .  ' "' . implode(':', $this->getSubjects()) . '" - could not be created. Response: <br/>' . var_export($response->getRaw(), true)
        );
        $this->_clearKeyDirectory();
        return null;
    }

    /**
     * @param Account $account
     * @param array $subjects
     * @return bool
     */
    public static function exists(Account $account, $subjects) {

        $order = new self($account, $subjects);
        return file_exists($order->getKeyDirectoryPath() . 'DirectoryNewOrderResponse');
    }

    /**
     * @param Account $account
     * @param array $subjects
     * @return self
     */
    public static function get(Account $account, array $subjects) {

        $order = new self($account, $subjects);

        if(!self::exists($account, $subjects))
            throw new \RuntimeException('Order does not exist');

        $directoryNewOrderResponse = Storage::getInstance()->getDirectoryNewOrderResponse($account, $order);
        if($directoryNewOrderResponse->isValid() && $directoryNewOrderResponse->getStatus() == Response\Order\AbstractDirectoryNewOrder::STATUS_VALID) {
            Utilities\Logger::getInstance()->add(
                Utilities\Logger::LEVEL_DEBUG,
                get_class() . '::' . __FUNCTION__ .  ' "' . implode(':', $subjects) . '" (from cache)'
            );
            return $order;
        }

        $request = new Request\Order\Get($account, $order);
        $response = $request->getResponse();
        if($response->isValid()) {
            Storage::getInstance()->setDirectoryNewOrderResponse($account, $order, $response);
            Utilities\Logger::getInstance()->add(
                Utilities\Logger::LEVEL_INFO,
                get_class() . '::' . __FUNCTION__ .  ' "' . implode(':', $subjects) . '"'
            );
            return $order;
        }
        return null;
    }

    public function authorize($type) {

        if(!file_exists($this->getKeyDirectoryPath() . 'private.pem')) // Order has finished already
            return false;

        if($type == self::CHALLENGE_TYPE_HTTP) {

            if($this->_continueHTTPAuthorizations()) {

                $this->_existsNotValidChallenges = false;
                return true;
            }
            Utilities\Logger::getInstance()->add(
                Utilities\Logger::LEVEL_DEBUG,
                get_class() . '::' . __FUNCTION__ . ' "Non valid challenges found."'
            );
            return false;
        }
        throw new \RuntimeException('Challenge type not implemented');
    }

    protected function _continueHTTPAuthorizations() {

        if(self::$_HTTPAuthorizationDirectoryPath === NULL) {

            throw new \RuntimeException('HTTP authorization directory path is not set');
        }

        $directoryNewOrderResponse = Storage::getInstance()->getDirectoryNewOrderResponse($this->_account, $this);
        $existsNotValidChallenges = false;

        foreach($directoryNewOrderResponse->getAuthorizations() as $authorization) {

            $request = new Request\Authorization\Get($authorization);
            $response = $request->getResponse();
            if($response->isValid()) {

                $challenge = $response->getChallenge(self::CHALLENGE_TYPE_HTTP);

                if($challenge->status == Response\Authorization\Struct\Challenge::STATUS_PENDING) {

                    Utilities\Logger::getInstance()->add(
                        Utilities\Logger::LEVEL_DEBUG,
                        get_class() . '::' . __FUNCTION__ . ' "Non valid challenge found',
                        $challenge
                    );

                    $existsNotValidChallenges = true;

                    Utilities\Challenge::writeHTTPAuthorizationFile(self::$_HTTPAuthorizationDirectoryPath, $this->_account, $challenge);
                    if(Utilities\Challenge::validateHTTPAuthorizationFile($response->getIdentifier()->value, $this->_account, $challenge)) {

                        $request = new Request\Authorization\Start($this->_account, $this, $challenge);
                        $response = $request->getResponse();
                        if(!$response->isValid())
                            return false;
                    } else {

                        Utilities\Logger::getInstance()->add(Utilities\Logger::LEVEL_INFO, 'Could not validate HTTP Authorization file');
                    }
                }
                else if($challenge->status == Response\Authorization\Struct\Challenge::STATUS_VALID) {

                }
                else {

                    throw new \RuntimeException('Challenge status "' . $challenge->status . '" is not implemented');
                }
            } else {

                return false;
            }
        }

        return !$existsNotValidChallenges;
    }

    public function finalize() {

        if($this->_existsNotValidChallenges) {

            throw new \RuntimeException('Not all challenges are valid. Please check result of authorize() first!');
        }

        Utilities\Logger::getInstance()->add(
            Utilities\Logger::LEVEL_DEBUG,
            get_class() . '::' . __FUNCTION__ . ' "Will finalize'
        );

        $directoryNewOrderResponse = Storage::getInstance()->getDirectoryNewOrderResponse($this->_account, $this);

        if($directoryNewOrderResponse->getStatus() == Response\Order\AbstractDirectoryNewOrder::STATUS_PENDING) {

            $request = new Request\Order\Finalize($this->_account, $this);
            $directoryNewOrderResponse = $request->getResponse();
            if($directoryNewOrderResponse->isValid()) {

                Storage::getInstance()->setDirectoryNewOrderResponse($this->_account, $this, $directoryNewOrderResponse);
            }
        }

        if($directoryNewOrderResponse->getStatus() == Response\Order\AbstractDirectoryNewOrder::STATUS_VALID) {

            $request = new Request\Order\GetCertificate($directoryNewOrderResponse);
            $response = $request->getResponse();
            if($response->isValid()) {

                $certificate = $response->getCertificate();
                $intermediate = $response->getIntermediate();

                $certificateInfo = openssl_x509_parse($certificate);

                $path = $this->getKeyDirectoryPath() . self::BUNDLE_DIRECTORY_PREFIX . $certificateInfo['validTo_time_t'] . DIRECTORY_SEPARATOR;

                mkdir($path);
                rename($this->getKeyDirectoryPath() . 'private.pem', $path . 'private.pem');
                file_put_contents($path . 'certificate.crt', $certificate);
                file_put_contents($path . 'intermediate.pem', $intermediate);

                Utilities\Logger::getInstance()->add(Utilities\Logger::LEVEL_INFO, 'Certificate received');
            }
        }
    }

    const BUNDLE_DIRECTORY_PREFIX = 'bundle_';

    protected function _getLatestCertificateDirectory() {

        $files = scandir($this->getKeyDirectoryPath(), SORT_NUMERIC | SORT_DESC);
        foreach($files as $file) {
            if(substr($file, 0, strlen(self::BUNDLE_DIRECTORY_PREFIX)) == self::BUNDLE_DIRECTORY_PREFIX && is_dir($this->getKeyDirectoryPath() . $file))
                return $file;
        }
        return false;
    }

    public function isCertificateBundleAvailable() {

        return $this->_getLatestCertificateDirectory() !== FALSE;
    }

    public function getCertificateBundle() {

        if(!$this->isCertificateBundleAvailable()) {
            throw new \RuntimeException('There is no certificate available');
        }

        $certificatePath = $this->getKeyDirectoryPath() . $this->_getLatestCertificateDirectory() . DIRECTORY_SEPARATOR;

        // Changed intermediate file extension.
        $intermediateFile = 'intermediate.' . (file_exists($certificatePath . 'intermediate.crt') ? 'crt' : 'pem');

        return new Struct\CertificateBundle(
            $certificatePath,
            'private.pem',
            'certificate.crt',
            $intermediateFile
        );
    }

    public function enableAutoRenewal($keyType = self::KEY_TYPE_RSA) {

        if(!$this->isCertificateBundleAvailable()) {
            throw new \RuntimeException('There is no certificate available');
        }

        $directoryNewOrderResponse = Storage::getInstance()->getDirectoryNewOrderResponse($this->_account, $this);
        if($directoryNewOrderResponse->getStatus() != Response\Order\AbstractDirectoryNewOrder::STATUS_VALID)
            return;

        Utilities\Logger::getInstance()->add(Utilities\Logger::LEVEL_DEBUG,'Auto renewal triggered');

        $directory = $this->_getLatestCertificateDirectory();

        $expireTime = substr($directory, strlen(self::BUNDLE_DIRECTORY_PREFIX));

        if(strtotime('+7 days', time()) > $expireTime) {

            Utilities\Logger::getInstance()->add(Utilities\Logger::LEVEL_INFO,'Auto renewal: Will recreate order');

            $this->_create($keyType, true);
        }
    }

    /**
     * @param int $reason The reason to revoke the LetsEncrypt Order instance certificate. Possible reasons can be found in section 5.3.1 of RFC5280.
     * @return bool
     */
    public function revokeCertificate($reason = 0) {

        if(!$this->isCertificateBundleAvailable()) {
            throw new \RuntimeException('There is no certificate available');
        }

        $bundle = $this->getCertificateBundle();

        $request = new Request\Order\RevokeCertificate($bundle, $reason);
        $response = $request->getResponse();
        if($response->isValid()) {
            rename($this->getKeyDirectoryPath(), $this->_getKeyDirectoryPath('-revoked-' . microtime(true)));
            return true;
        }
        return false;
    }
}