# le-acme2-php [![CircleCI](https://circleci.com/gh/fbett/le-acme2-php/tree/master.svg?style=svg)](https://app.circleci.com/pipelines/github/fbett/le-acme2-php?branch=master)
[![Scrutinizer Build Status](https://scrutinizer-ci.com/g/fbett/le-acme2-php/badges/build.png?b=master)](https://scrutinizer-ci.com/g/fbett/le-acme2-php/build-status/master)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/fbett/le-acme2-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fbett/le-acme2-php/)
[![Latest Stable Version](http://poser.pugx.org/fbett/le_acme2/v)](https://packagist.org/packages/fbett/le_acme2)
[![License](http://poser.pugx.org/fbett/le_acme2/license)](https://packagist.org/packages/fbett/le_acme2)
[![PHP Version Require](http://poser.pugx.org/fbett/le_acme2/require/php)](https://packagist.org/packages/fbett/le_acme2)

LetsEncrypt client library for ACME v2 written in PHP.

This library is inspired by [yourivw/LEClient](https://github.com/yourivw/LEClient), completely rewritten and enhanced with some new features:
- Support for Composer autoload (including separated Namespaces)
- Automatic renewal process
- Managed HTTP authentication process
- Response caching mechanism
- Prevents blocking while waiting for server results
- Optional set a preferred chain

The aim of this client is to make an easy-to-use and integrated solution to create a LetsEncrypt-issued SSL/TLS certificate with PHP.

You have the possibility to use the HTTP authentication:
You need to be able to redirect specific requests (see below)

You have also the possibility to use DNS authentication:
You need to be able to set dynamic DNS configurations.

Wildcard certificates can only be requested by using the dns authentication.

## Current version

Tested with LetsEncrypt staging and production servers.

## Prerequisites

The minimum required PHP version is 8.2.

This client also depends on cURL and OpenSSL.

## Getting Started

Install via composer:

```
composer require fbett/le_acme2
```

Also have a look at the [LetsEncrypt documentation](https://letsencrypt.org/docs/) for more information and documentation on LetsEncrypt and ACME.

## Example Integration

- Create a working directory. 
Warning: This directory will also include private keys, so i suggest to place this directory somewhere not in the root document path of the web server. 
Additionally this directory should be protected to be read from other web server users.

```
mkdir /etc/ssl/le-storage/
chown root:root /etc/ssl/le-storage
chmod 0600 /etc/ssl/le-storage
```

- (HTTP authorization only) Create a directory for the acme challenges. It must be reachable by http/https.

```
mkdir /var/www/acme-challenges
```

- (HTTP authorization only) Redirect specific requests to your acme-challenges directory

Example apache virtual host configuration:

```
<VirtualHost ...>
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteCond %{HTTPS} off
        RewriteRule \.well-known/acme-challenge/(.*)$ https://your-domain.com/path/to/acme-challenges/$1 [R=302,L]
    </IfModule>
</VirtualHost>
```

- (DNS authorization only) Set the DNS configuration

If `DNSWriter::write(...)` is called, set the DNS configuration like described in:

[https://letsencrypt.org/docs/challenge-types/#dns-01-challenge](https://letsencrypt.org/docs/challenge-types/#dns-01-challenge)

(By adding the digest as a TXT record for the subdomain '_acme-challenge'.)


- Use the certificate bundle, if the certificate is issued:

```
if($order->isCertificateBundleAvailable()) {

    $bundle = $order->getCertificateBundle();
    
    $pathToPrivateKey = $bundle->path . $bundle->private;
    $pathToCertificate = $bundle->path . $bundle->certificate;
    $pathToIntermediate = $bundle->path . $bundle->intermediate;
    
    $order->enableAutoRenewal(); // If the date of expiration is closer than thirty days, the order will automatically start the renewal process.
}
```

If a certificate is renewed, the path will also change. 

My integrated workflow is the following:
- User enables SSL to a specific domain in my control panel
- The cronjob of this control panel will detect these changes and tries to create or get an order like in the sample.
- The cronjob will fetch the information within the certificate bundle, if the certificate bundle is ready (mostly on the second run for challenge type HTTP and on the third run for challenge type DNS)
- The cronjob will also build the Apache virtual host files and will restart the Apache2 service, if the new config file is different.

Please take a look on the Samples for a full sample workflow.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.
