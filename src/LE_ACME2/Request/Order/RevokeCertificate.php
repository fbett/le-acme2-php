<?php

namespace LE_ACME2\Request\Order;

use LE_ACME2\Connector\Connector;
use LE_ACME2\Connector\Storage;
use LE_ACME2\Response as Response;

use LE_ACME2\Request\AbstractRequest;
use LE_ACME2\Utilities as Utilities;

class RevokeCertificate extends AbstractRequest {

    protected $_certificateBundle;
    protected $_reason;

    public function __construct(\LE_ACME2\Struct\CertificateBundle $certificateBundle, $reason) {

        $this->_certificateBundle = $certificateBundle;
        $this->_reason = $reason;
    }

    /**
     * @return Response\AbstractResponse|Response\Order\RevokeCertificate
     * @throws \LE_ACME2\Exception\InvalidResponse
     * @throws \LE_ACME2\Exception\RateLimitReached
     */
    public function getResponse()
    {
        $connector = Connector::getInstance();
        $storage = Storage::getInstance();

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
            Connector::METHOD_POST,
            $storage->getGetDirectoryResponse()->getRevokeCert(),
            $jwk
        );

        return new Response\Order\RevokeCertificate($result);
    }

}