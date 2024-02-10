<?php
namespace LE_ACME2\Response\Authorization\Struct;

interface ChallengeErrorConstructorInterface {

    public function __construct(string $type, string $detail, int $status);

}