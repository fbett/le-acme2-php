<?php

namespace LE_ACME2\Response;

use LE_ACME2\Exception;

use LE_ACME2\Connector\RawResponse;

abstract class AbstractResponse {

    /** @var RawResponse $_raw */
    protected $_raw;

    protected $_pattern_header_location = '/^Location: (\S+)$/i';

    /**
     * AbstractResponse constructor.
     *
     * @param RawResponse $raw
     * @throws Exception\InvalidResponse
     * @throws Exception\RateLimitReached
     * @throws Exception\ServiceUnavailable
     */
    public function __construct(RawResponse $raw) {

        $this->_raw = $raw;

        if($this->_isServiceUnavailable()) {

            $detail = "";
            if(isset($raw->body['detail'])) {
                $detail = $raw->body['detail'];
            }

            $retryAfterMatches = $this->_preg_match_headerLine('/^Retry-After: (.+)$/i');

            throw new Exception\ServiceUnavailable(
                $raw->request,
                $detail,
                $retryAfterMatches !== null ? $retryAfterMatches[1] : null
            );
        }

        if($this->_isRateLimitReached()) {

            $detail = "";
            if(isset($raw->body['type']) && $raw->body['type'] == 'urn:ietf:params:acme:error:rateLimited') {
                $detail = $raw->body['detail'];
            }

            throw new Exception\RateLimitReached(
                $raw->request,
                $detail,
            );
        }

        $result = $this->_isValid();
        if(!$result) {

            $responseStatus = $this->_preg_match_headerLine('/^HTTP\/.* [0-9]{3,} /i');
            throw new Exception\InvalidResponse(
                $raw,
                isset($responseStatus[1]) ? $responseStatus[1] : null,
            );
        }
    }

    protected function _preg_match_headerLine(string $pattern) : ?array {

        foreach($this->_raw->header as $line) {

            $matches = [];
            if(preg_match($pattern, $line, $matches) === 1) {
                return $matches;
            }
        }
        return null;
    }

    protected function _isRateLimitReached() : bool {
        return $this->_preg_match_headerLine('/^HTTP\/.* 429/i') !== null;
    }

    protected function _isServiceUnavailable() : bool {
        return $this->_preg_match_headerLine('/^HTTP\/.* 503/i') !== null;
    }

    protected function _isValid() : bool {

        return $this->_preg_match_headerLine('/^HTTP\/.* 201/i') !== null || //Created
            $this->_preg_match_headerLine('/^HTTP\/.* 200/i') !== null ||
            $this->_preg_match_headerLine('/^HTTP\/.* 204/i') !== null;
    }

    public function getRaw() : RawResponse {
        return $this->_raw;
    }
}