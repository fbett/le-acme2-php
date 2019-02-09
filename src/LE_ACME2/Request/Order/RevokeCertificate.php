<?php

namespace LE_ACME2\Request\Order;

use LE_ACME2\Response;
use LE_ACME2\Request\AbstractRequest;

use LE_ACME2\Connector;
use LE_ACME2\Exception;
use LE_ACME2\Struct;
use LE_ACME2\Utilities;

class RevokeCertificate extends AbstractRequest {

    protected $_certificateBundle;
    protected $_reason;

    public function __construct(Struct\CertificateBundle $certificateBundle, $reason) {

        $this->_certificateBundle = $certificateBundle;
        $this->_reason = $reason;
    }

    /**
     * @return Response\AbstractResponse|Response\Order\RevokeCertificate
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     */
    public function getResponse() {

        $connector = Connector\Connector::getInstance();
        $storage = Connector\Storage::getInstance();

        $certificate = file_get_contents($this->_certificateBundle->path . $this->_certificateBundle->certificate);
        preg_match('~-----BEGIN\sCERTIFICATE-----(.*)-----END\sCERTIFICATE-----~s', $certificate, $matches);
        $certificate = trim(Utilities\Base64::UrlSafeEncode(base64_decode(trim($matches[1]))));

        $payload = [
            'certificate' => $certificate,
            'reason' => $this->_reason
        ];

        $jwk = Utilities\RequestSigner::JWKString(
            $payload,
            $storage->getGetDirectoryResponse()->getRevokeCert(),
            $storage->getNewNonceResponse()->getNonce(),
            $this->_certificateBundle->path,
            $this->_certificateBundle->private
        );

        $result = $connector->request(
            Connector\Connector::METHOD_POST,
            $storage->getGetDirectoryResponse()->getRevokeCert(),
            $jwk
        );

        return new Response\Order\RevokeCertificate($result);
    }

}