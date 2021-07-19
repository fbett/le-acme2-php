<?php
namespace LE_ACME2Tests\Response\Authorization;

use LE_ACME2Tests\Connector;
use PHPUnit\Framework\TestCase;

class GetTest extends TestCase {

    /**
     * @covers \LE_ACME2\Response\Authorization\Get::getChallenges
     */
    public function testGetChallengeError() {

        $rawResponse = Connector\RawResponse::createDummyFrom(
            Connector\RawResponse::HEADER_200,
            file_get_contents(dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . '_JSONSamples' . DIRECTORY_SEPARATOR . 'ChallengeError.json')
        );

        $response = new \LE_ACME2\Response\Authorization\Get($rawResponse);
        $challenge = $response->getChallenge('http-01');

        $error = $challenge->error;
        $this->assertTrue(is_object($error) === true);
        $this->assertTrue($error->type === 'urn:ietf:params:acme:error:dns');
        $this->assertTrue($error->detail === "DNS problem: SERVFAIL looking up CAA for domain1.tld - the domain's nameservers may be malfunctioning");
        $this->assertTrue($error->status === 400);
    }
}