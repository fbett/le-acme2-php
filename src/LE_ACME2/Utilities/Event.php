<?php

namespace LE_ACME2\Utilities;

use LE_ACME2\SingletonTrait;

class Event {

    const EVENT_CONNECTOR_WILL_REQUEST = 'EVENT_CONNECTOR_WILL_REQUEST';

    use SingletonTrait;

    private $_subscriber = [];

    /**
     * @param string $event
     * @param callable $callable function(string $event, array $payload = null)
     */
    public function subscribe(string $event, callable $callable) : void {

        if(!isset($this->_subscriber[$event])) {
            $this->_subscriber[$event] = [];
        }

        $this->_subscriber[$event][] = $callable;
    }
    
    public function trigger(string $event, array $payload = null) : void {

        Logger::getInstance()->add(Logger::LEVEL_DEBUG, 'Event triggered: ' . $event);

        if(!$this->_subscriber[$event]) {
            return;
        }

        foreach($this->_subscriber[$event] as $callable) {
            $callable($event, $payload);
        }
    }
}