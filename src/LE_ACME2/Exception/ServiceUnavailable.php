<?php

namespace LE_ACME2\Exception;

class ServiceUnavailable extends AbstractException {

    private ?string $retryAfter;

    public function __construct(string $request, string $detail, string $retryAfter = null) {

        $message = "Invalid response received for request (" . $request . "): service unavailable - " . $detail;

        if($retryAfter !== null) {
            $message .= ' - may retry after: ' . $retryAfter;
        }

        parent::__construct($message);

        $this->retryAfter = $retryAfter;
    }

    /**
     * Returns the value of the given Retry-After header
     *
     * Retry-After: <http-date>
     * Retry-After: <delay-seconds>
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Retry-After
     *
     * @return ?string  "http-date" or "delay-seconds" or null (when not given)
     */
    public function getRetryAfter() : ?string {
        return $this->retryAfter;
    }
}