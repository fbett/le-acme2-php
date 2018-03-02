<?php

namespace LE_ACME2\Connector\Struct;

class RawResponse {

    /** @var string */
    public $request;

    /** @var array */
    public $header;

    /** @var array */
    public $body;

    public function init($method, $url, $response, $headerSize) {

        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        $body_json = json_decode($body, true);

        $this->request = $method . ' ' . $url;

        $this->header = array_map(function($line) {
            return trim($line);
        }, explode("\n", $header));

        $this->body = $body_json === null ? $body : $body_json;
    }

    public function toString() {

        return serialize([
            'request' => $this->request,
            'header' => $this->header,
            'body' => $this->body,
        ]);
    }

    public static function getFromString($string) {

        $array = unserialize($string);

        $rawResponse = new self();

        $rawResponse->request = $array['request'];
        $rawResponse->header = $array['header'];
        $rawResponse->body = $array['body'];

        return $rawResponse;
    }
}