<?php

namespace LE_ACME2;

use LE_ACME2\Request as Request;
use LE_ACME2\Response as Response;
use LE_ACME2\Utilities as Utilities;
use LE_ACME2\Exception as Exception;
use \LE_ACME2\Authorizer as Authorizer;

use LE_ACME2\Connector\Storage;

class Order extends AbstractKeyValuable
{

    const CHALLENGE_TYPE_HTTP = 'http-01';
    const CHALLENGE_TYPE_DNS = 'dns-01';

    /**
     * @deprecated
     * @param $directoryPath
     */
    public static function setHTTPAuthorizationDirectoryPath($directoryPath)
    {

        Authorizer\HTTP::setDirectoryPath($directoryPath);
    }

    protected $_account;
    protected $_subjects;

    protected $_existsNotValidChallenges = true;

    /**
     * @var $renewBeforeDays
     * Days before expiration that we allow to renew
     */
    protected $renewBeforeDays = 7;

    public function __construct(Account $account, array $subjects)
    {

        array_map(function ($subject) {

            if (preg_match_all('~(\*\.)~', $subject) > 1) {
                throw new \RuntimeException('Cannot create orders with multiple wildcards in one domain.');
            }
        }, $subjects);

        $this->_account = $account;
        $this->_subjects = $subjects;

        $this->_identifier = $this->_getAccountIdentifier($account) . DIRECTORY_SEPARATOR . 'order_' . md5(implode('|', $subjects));
    }

    public function getSubjects()
    {

        return $this->_subjects;
    }

    public function setRenewBeforeDays(int $days)
    {
        $this->renewBeforeDays = $days;

        return $this;
    }

    /**
     * @param Account $account
     * @param array $subjects
     * @param string $keyType
     * @return Order
     * @throws Exception\AbstractException
     */
    public static function create(Account $account, array $subjects, $keyType = self::KEY_TYPE_RSA)
    {
        $order = new self($account, $subjects);
        return $order->_create($keyType, false);
    }

    public static function createForce(Account $account, array $subjects, $keyType = self::KEY_TYPE_RSA)
    {
        $order = new self($account, $subjects);
        return $order->_create($keyType, true);
    }

    /**
     * @param $keyType
     * @param bool $ignoreIfKeysExist
     * @return Order
     * @throws Exception\AbstractException
     */
    protected function _create($keyType, $ignoreIfKeysExist = false)
    {

        $this->_initKeyDirectory($keyType, $ignoreIfKeysExist);

        $request = new Request\Order\Create($this->_account, $this);

        try {
            $response = $request->getResponse();

            Storage::getInstance()->setDirectoryNewOrderResponse($this->_account, $this, $response);
            Utilities\Logger::getInstance()->add(
                Utilities\Logger::LEVEL_INFO,
                get_class() . '::' . __FUNCTION__ .  ' "' . implode(':', $this->getSubjects()) . '"'
            );
            return $this;
        } catch (Exception\AbstractException $e) {
            $this->_clearKeyDirectory();
            throw $e;
        }
    }

    /**
     * @param Account $account
     * @param array $subjects
     * @return bool
     */
    public static function exists(Account $account, $subjects)
    {

        $order = new self($account, $subjects);
        return file_exists($order->getKeyDirectoryPath() . 'DirectoryNewOrderResponse');
    }

    /**
     * @param Account $account
     * @param array $subjects
     * @return Order
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public static function get(Account $account, array $subjects)
    {

        $order = new self($account, $subjects);

        if (!self::exists($account, $subjects)) {
            throw new \RuntimeException('Order does not exist');
        }

        $directoryNewOrderResponse = Storage::getInstance()->getDirectoryNewOrderResponse($account, $order);
        if ($directoryNewOrderResponse !== null && $directoryNewOrderResponse->getStatus() == Response\Order\AbstractDirectoryNewOrder::STATUS_VALID) {
            Utilities\Logger::getInstance()->add(
                Utilities\Logger::LEVEL_DEBUG,
                get_class() . '::' . __FUNCTION__ .  ' "' . implode(':', $subjects) . '" (from cache)'
            );
            return $order;
        }

        $request = new Request\Order\Get($account, $order);
        $response = $request->getResponse();

        Storage::getInstance()->setDirectoryNewOrderResponse($account, $order, $response);
        Utilities\Logger::getInstance()->add(
            Utilities\Logger::LEVEL_INFO,
            get_class() . '::' . __FUNCTION__ .  ' "' . implode(':', $subjects) . '"'
        );

        return $order;
    }

    /** @var Authorizer\AbstractAuthorizer|Authorizer\HTTP|null $_authorizer  */
    protected $_authorizer = null;

    /**
     * @param $type
     * @return Authorizer\AbstractAuthorizer|Authorizer\HTTP|null
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    protected function _getAuthorizer($type)
    {

        if ($this->_authorizer === null) {
            if ($type == self::CHALLENGE_TYPE_HTTP) {
                $this->_authorizer = new Authorizer\HTTP($this->_account, $this);
            } else {
                throw new \RuntimeException('Challenge type not implemented');
            }
        }
        return $this->_authorizer;
    }

    /**
     * @return bool
     * @param $type
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function shouldStartAuthorization($type)
    {

        return $this->_getAuthorizer($type)->shouldStartAuthorization();
    }

    /**
     * @param $type
     * @return bool
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\HTTPAuthorizationInvalid
     */
    public function authorize($type)
    {

        /** @var Authorizer\HTTP $authorizer */
        $authorizer = $this->_getAuthorizer($type);
        $authorizer->progress();

        return $authorizer->hasFinished();
    }

    /**
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function finalize()
    {

        if (!is_object($this->_authorizer) || !$this->_authorizer->hasFinished()) {
            throw new \RuntimeException('Not all challenges are valid. Please check result of authorize() first!');
        }

        Utilities\Logger::getInstance()->add(
            Utilities\Logger::LEVEL_DEBUG,
            get_class() . '::' . __FUNCTION__ . ' "Will finalize'
        );

        $directoryNewOrderResponse = Storage::getInstance()->getDirectoryNewOrderResponse($this->_account, $this);

        if ($directoryNewOrderResponse->getStatus() == Response\Order\AbstractDirectoryNewOrder::STATUS_PENDING /* DEPRECATED AFTER JULI 5TH 2018 */ ||
            $directoryNewOrderResponse->getStatus() == Response\Order\AbstractDirectoryNewOrder::STATUS_READY   // ACME draft-12 Section 7.1.6
        ) {
            $request = new Request\Order\Finalize($this->_account, $this);
            $directoryNewOrderResponse = $request->getResponse();
            Storage::getInstance()->setDirectoryNewOrderResponse($this->_account, $this, $directoryNewOrderResponse);
        }

        if ($directoryNewOrderResponse->getStatus() == Response\Order\AbstractDirectoryNewOrder::STATUS_VALID) {
            $request = new Request\Order\GetCertificate($directoryNewOrderResponse);
            $response = $request->getResponse();

            $certificate = $response->getCertificate();
            $intermediate = $response->getIntermediate();

            $certificateInfo = openssl_x509_parse($certificate);

            $path = $this->getKeyDirectoryPath() . self::BUNDLE_DIRECTORY_PREFIX . $certificateInfo['validTo_time_t'] . DIRECTORY_SEPARATOR;

            mkdir($path);
            rename($this->getKeyDirectoryPath() . 'private.pem', $path . 'private.pem');
            file_put_contents($path . 'certificate.crt', $certificate);
            file_put_contents($path . 'intermediate.pem', $intermediate);
            file_put_contents($path . 'expire_ts.txt', $certificateInfo['validTo_time_t']);

            Utilities\Logger::getInstance()->add(Utilities\Logger::LEVEL_INFO, 'Certificate received');
        }
    }

    const BUNDLE_DIRECTORY_PREFIX = 'bundle_';

    protected function _getLatestCertificateDirectory()
    {

        $files = scandir($this->getKeyDirectoryPath(), SORT_NUMERIC | SORT_DESC);
        foreach ($files as $file) {
            if (substr($file, 0, strlen(self::BUNDLE_DIRECTORY_PREFIX)) == self::BUNDLE_DIRECTORY_PREFIX && is_dir($this->getKeyDirectoryPath() . $file)) {
                return $file;
            }
        }
        return false;
    }

    public function isCertificateBundleAvailable()
    {

        return $this->_getLatestCertificateDirectory() !== false;
    }

    public function getCertificateBundle()
    {

        if (!$this->isCertificateBundleAvailable()) {
            throw new \RuntimeException('There is no certificate available');
        }

        $directory = $this->_getLatestCertificateDirectory();

        $certificatePath = $this->getKeyDirectoryPath() . $directory . DIRECTORY_SEPARATOR;

        // Changed intermediate file extension.
        $intermediateFile = 'intermediate.' . (file_exists($certificatePath . 'intermediate.crt') ? 'crt' : 'pem');

        $expireTime = substr($directory, strlen(self::BUNDLE_DIRECTORY_PREFIX));

        return new Struct\CertificateBundle(
            $certificatePath,
            'private.pem',
            'certificate.crt',
            $intermediateFile,
            (int) $expireTime
        );
    }

    /**
     * @param string $keyType
     * @throws Exception\AbstractException
     */
    public function enableAutoRenewal($keyType = self::KEY_TYPE_RSA)
    {

        if (!$this->isCertificateBundleAvailable()) {
            throw new \RuntimeException('There is no certificate available');
        }

        $directoryNewOrderResponse = Storage::getInstance()->getDirectoryNewOrderResponse($this->_account, $this);
        if ($directoryNewOrderResponse->getStatus() != Response\Order\AbstractDirectoryNewOrder::STATUS_VALID) {
            return;
        }

        Utilities\Logger::getInstance()->add(Utilities\Logger::LEVEL_DEBUG, 'Auto renewal triggered');

        $directory = $this->_getLatestCertificateDirectory();

        $expireTime = substr($directory, strlen(self::BUNDLE_DIRECTORY_PREFIX));

        if (strtotime('+' . $this->renewBeforeDays . ' days', time()) > $expireTime) {
            Utilities\Logger::getInstance()->add(Utilities\Logger::LEVEL_INFO, 'Auto renewal: Will recreate order');

            $this->_create($keyType, true);
        }
    }

    /**
     * @param int $reason The reason to revoke the LetsEncrypt Order instance certificate. Possible reasons can be found in section 5.3.1 of RFC5280.
     * @return bool
     * @throws Exception\RateLimitReached
     */
    public function revokeCertificate($reason = 0)
    {

        if (!$this->isCertificateBundleAvailable()) {
            throw new \RuntimeException('There is no certificate available to revoke');
        }

        $bundle = $this->getCertificateBundle();

        $request = new Request\Order\RevokeCertificate($bundle, $reason);

        try {
            /* $response = */ $request->getResponse();
            rename($this->getKeyDirectoryPath(), $this->_getKeyDirectoryPath('-revoked-' . microtime(true)));
            return true;
        } catch (Exception\InvalidResponse $e) {
            return false;
        }
    }
}
