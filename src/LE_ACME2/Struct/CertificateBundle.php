<?php

namespace LE_ACME2\Struct;

class CertificateBundle
{

    public $path;
    public $private;
    public $certificate;
    public $intermediate;

    public function __construct($path, $private, $certificate, $intermediate)
    {

        $this->path = $path;
        $this->private = $private;
        $this->certificate = $certificate;
        $this->intermediate = $intermediate;
    }
}
