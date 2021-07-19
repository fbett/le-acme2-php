<?php

namespace LE_ACME2\Response\Authorization\Struct;

class Challenge {

    // Status from RFC 8555 (7.1.6), version: March 2019

    const STATUS_PENDING = 'pending';
    const STATUS_PROGRESSING = 'processing';
    const STATUS_VALID = 'valid';
    const STATUS_INVALID = 'invalid';

    public $type;
    public $status;
    public $url;
    public $token;
    public $error;

    public function __construct(string $type, string $status, string $url, string $token, ChallengeError $error = null) {

        $this->type = $type;
        $this->status = $status;
        $this->url = $url;
        $this->token = $token;
        $this->error = $error;
    }
}