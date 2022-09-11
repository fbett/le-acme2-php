<?php

namespace LE_ACME2\Exception;

class RateLimitReached extends AbstractException {

    /** @var string|null */
    private $retryAfter;

    public function __construct(string $request, string $detail, string $retryAfter = null) {
        parent::__construct(
            "Invalid response received for request (" . $request . "): " .
            "rate limit reached - " . $detail
        );

        $this->retryAfter = $retryAfter;
    }

    /**
     * Returns the value of the given Retry-After header
     *
     * Retry-After: <http-date>
     * Retry-After: <delay-seconds>
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Retry-After
     *
     * @return string|null <http-date> or <delay-seconds> or null (when not given)
     */
    public function getRetryAfter() : ?string {
        return $this->retryAfter;
    }
}