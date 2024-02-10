<?php
namespace LE_ACME2;

trait SingletonTrait {

    private static $_instance = NULL;

    final public static function getInstance(): self {

        if( self::$_instance === NULL ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}