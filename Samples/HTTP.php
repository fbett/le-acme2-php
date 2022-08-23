<?php

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'autoload.php'; //Path to composer autoload

/**
 * CONFIG
 */

// Config the desired paths
\LE_ACME2\Account::setCommonKeyDirectoryPath('/etc/ssl/le-storage/');
\LE_ACME2\Authorizer\HTTP::setDirectoryPath('/var/www/acme-challenges/');

// General configs
\LE_ACME2\Connector\Connector::getInstance()->useStagingServer(true);
\LE_ACME2\Utilities\Logger::getInstance()->setDesiredLevel(\LE_ACME2\Utilities\Logger::LEVEL_INFO);

/**
 * OPTIONAL CONFIG: Delay connection response to prevent bleaching rate limits
 *
 * \LE_ACME2\Connector\Connector::getInstance()->delayResponse(400);
 */

/**
 * OPTIONAL CONFIG: Enable feature: OCSP Must Staple
 *
 * \LE_ACME2\Utilities\Certificate::enableFeatureOCSPMustStaple();
 */

/**
 * OPTIONAL CONFIG: Set preferred chain
 * It is not reasonable usable in/after 2022, but we keep the method for future situations
 *
 * \LE_ACME2\Order::setPreferredChain(\LE_ACME2\Order::IDENTRUST_ISSUER_CN);
 */

/**
 * OPTIONAL CONFIG: Event subscriber
 * 
 * \LE_ACME2\Utilities\Event::getInstance()->subscribe(
       \LE_ACME2\Utilities\Event::EVENT_CONNECTOR_WILL_REQUEST,
       function(string $event, array $payload = null) {
           // Do something, f.e. force to save the logs
       }
   );
 */

/**
 * WORKFLOW START
 */

$account_email = 'test@example.org';

$account = !\LE_ACME2\Account::exists($account_email) ?
    \LE_ACME2\Account::create($account_email) :
    \LE_ACME2\Account::get($account_email);

/**
 * OPTIONAL: Update email address
 *
 * $account->update('new-test@example.org');
 */

/**
 * OPTIONAL: Deactivate account
 * Warning: It is not possible to reactivate an account.
 *
 * $account->deactivate();
 */

$subjects = [
    'example.org', // First item will be set as common name on the certificate
    'www.example.org'
];

if(!\LE_ACME2\Order::exists($account, $subjects)) {

    // Do some pre-checks, f.e. external dns checks - not required

    $order = \LE_ACME2\Order::create($account, $subjects);
} else {
    $order = \LE_ACME2\Order::get($account, $subjects);
}

/**
 * OPTIONAL: Clear current order (in case to restart on status "invalid")
 * Already received certificate bundles will not be affected
 *
 * $order->clear();
 */

if($order->shouldStartAuthorization(\LE_ACME2\Order::CHALLENGE_TYPE_HTTP)) {
    // Do some pre-checks, f.e. external dns checks - not required

    // Example test:
    foreach($subjects as $subject) {
        try {
            $response = \LE_ACME2\Utilities\ChallengeHTTP::fetch($subject, \LE_ACME2\Authorizer\HTTP::TEST_TOKEN);
            if($response != \LE_ACME2\Authorizer\HTTP::TEST_CHALLENGE) {
                die('Invalid response: ' . var_export([
                    'Expected:' => \LE_ACME2\Authorizer\HTTP::TEST_CHALLENGE,
                    'Response:' => $response,
                ]));
            }
        } catch(\LE_ACME2\Exception\HTTPAuthorizationInvalid $e) {
            die('Exception thrown while validating HTTP authorization: ' . $e->getMessage());
        }
    }

}

if($order->authorize(\LE_ACME2\Order::CHALLENGE_TYPE_HTTP)) {
    $order->finalize();
}

if($order->isCertificateBundleAvailable()) {

    $bundle = $order->getCertificateBundle();
    $order->enableAutoRenewal();

    /**
     * OPTIONAL: Revoke certificate
     *
     * $order->revokeCertificate($reason = 0);
     */
}