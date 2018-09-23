<?php

namespace LE_ACME2\Struct;

class CertificateBundle
{

    public $path;
    public $private;
    public $certificate;
    public $intermediate;
    public $expireTime;

    public function __construct($path, $private, $certificate, $intermediate, int $expireTime)
    {
        $this->path = $path;
        $this->private = $private;
        $this->certificate = $certificate;
        $this->intermediate = $intermediate;
        $this->expireTime = $expireTime;
    }

    public function getPrivateKey()
    {
        return file_get_contents($this->path . $this->private);
    }

    public function getCertificate()
    {
        return file_get_contents($this->path . $this->certificate);
    }

    public function getIntermediateCertificate()
    {
        return file_get_contents($this->path . $this->intermediate);
    }
}
