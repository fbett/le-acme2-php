<?php

namespace LE_ACME2\Response\Authorization\Struct;

class ChallengeError {

    const TYPE_ERROR_DNS = 'urn:ietf:params:acme:error:dns';

    /** @var string $type */
    public $type;

    /** @var string $detail */
    public $detail;

    /** @var int $status */
    public $status;

    public function __construct(string $type, string $detail, int $status) {

        $this->type = $type;
        $this->detail = $detail;
        $this->status = $status;
    }
}