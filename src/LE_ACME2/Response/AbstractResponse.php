<?php

namespace LE_ACME2\Response;

use LE_ACME2\Connector\Struct\RawResponse;
use LE_ACME2\Utilities\Logger;

abstract class AbstractResponse {

    protected $_raw = NULL;

    protected $_pattern_header_location = '/^Location: (\S+)$/i';

    public function __construct(RawResponse $raw) {

        $this->_raw = $raw;
    }

    /**
     * @param $pattern
     * @return null|array
     */
    protected function _preg_match_headerLine($pattern) {

        foreach($this->_raw->header as $line) {

            if(preg_match($pattern, $line, $matches) === 1)
                return $matches;
        }
        return null;
    }

    public function isRateLimitReached() {
        return $this->_preg_match_headerLine('/^HTTP.* 429 .*$/i') !== null;
    }

    final public function isValid() {

        $result = $this->_isValid();
        if(!$result) {
            Logger::getInstance()->add(
                Logger::LEVEL_DEBUG,
                get_called_class() . '::' . __FUNCTION__ . ' "result false"'
            );
        }
        return $result;
    }

    protected function _isValid() {

        if($this->isRateLimitReached()) {
            Logger::getInstance()->add(Logger::LEVEL_INFO, 'Invalid response: Rate limit reached', $this->_raw);
        }

        return $this->_preg_match_headerLine('/^HTTP.* 201 Created$/i') !== null ||
            $this->_preg_match_headerLine('/^HTTP.* 200 OK$/i') !== null;
    }

    public function getRaw() {

        return $this->_raw;
    }
}