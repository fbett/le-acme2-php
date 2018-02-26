# le-acme2-php
LetsEncrypt client library for ACME v2 in PHP.

This library is a fork of [yourivw/LEClient](https://github.com/yourivw/LEClient), but the code is completely restructured and enhanced with some new features:
- Support for Composer autoload (including separated Namespaces)
- Automatic renewal process
- Managed HTTP authentication process
- Response caching mechanism

The aim of this client is to make an easy-to-use and integrated solution to create a LetsEncrypt-issued SSL/TLS certificate with PHP.

Currently the authentication via HTTP is integrated. For that it is necessary, that you are able to place a redirect on the web server of the domain.

## Current version

This client was developed by using the LetsEncrypt staging server.
Please come back on February 29, 2018 to see, if there are any required changes when using the live servers.

### Prerequisites

The minimum required PHP version is 7.1.0 due to the implementation of ECDSA.

This client also depends on cURL and OpenSSL.

## Getting Started

Install via composer:

```php
composer require fbett/le-acme2-php
```

Also have a look at the [LetsEncrypt documentation](https://letsencrypt.org/docs/) for more information and documentation on LetsEncrypt and ACME.

## Example Integration

1. Create a working directory. 
Warning: This directory will also include private keys, so i suggest to place this directory somewhere not in the root document path of the web server. 
Additionally this directory should be protected to be read from other web server users.

```php
mkdir /etc/ssl/le-storage/
chown root:root /etc/ssl/le-storage
chmod 0600 /etc/ssl/le-storage
```

1. Create a directory for the acme challenges. It must be reachable by http/https.

```php
mkdir /var/www/acme-challenges
```

1. Redirect specific requests to your acme-challenges directory

Example apache virtual host configuration:

```xml
<VirtualHost ...>
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteCond %{HTTPS} off
        RewriteRule \.well-known/acme-challenge/(.*)$ https://your-domain.com/path/to/acme-challenges/$1 [R=302;L]
    </IfModule>
</VirtualHost>
```

1. Use the certificate bundle, if the certificate is issued:

```php
if($order->isCertificateBundleAvailable()) {

    $bundle = $order->getCertificateBundle();
    
    $pathToPrivateKey = $bundle->path . $bundle->private;
    $pathToCertificate = $bundle->path . $bundle->certificate;
    $pathToIntermediate = $bundle->path . $bundle->intermediate;
    
    $order->enableAutoRenewal(); // If the date of expiration is closer than seven days, the order will automatically start the renewal process.
}
```

If a certificate is renewed, the path will also change. 

My integrated workflow is the following:
- User enables SSL to a specific domain in my control panel
- The cronjob of this control panel will detect these changes and tries to create or get an order like in der HTTP-Sample.
- The cronjob will fetch the certificate bundle, if the certificate bundle is ready (mostly on the second run)
- The cronjob builds the Apache2 virtual host file and detects, that the virtual host file now differs. So the cronjob will restart the Apache2 service.


Please take a look on Samples\HTTP.php for a full sample workflow.

## Known Issues

- The DNS based authentication is not currently not implemented, so wildcard certificates can not be validated. All PR's are welcome.


## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.