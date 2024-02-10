<?php

namespace LE_ACME2\Response\Authorization\Struct;

class ChallengeError implements ChallengeErrorConstructorInterface {

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

    public static function createFrom(array $array) : static {
        return new static(
            $array['type'],
            $array['detail'],
            $array['status'],
        );
    }

    public function hasStatusServerError() : bool {
        return
            $this->status >= 500
            && $this->status < 600
        ;
    }
}