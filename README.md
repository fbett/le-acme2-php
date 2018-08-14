# le-acme2-php
LetsEncrypt client library for ACME v2 written in PHP.

This library is inspired by [yourivw/LEClient](https://github.com/yourivw/LEClient) and enhanced with some new features:
- Support for Composer autoload (including separated Namespaces)
- Automatic renewal process
- Managed HTTP authentication process
- Response caching mechanism
- Prevents blocking while waiting for server results

The aim of this client is to make an easy-to-use and integrated solution to create a LetsEncrypt-issued SSL/TLS certificate with PHP.

Currently the authentication via HTTP is integrated. For that it is necessary, that you are able to place a redirect on the web server of the domain.

## Current version

Tested with LetsEncrypt staging and production servers.

## Prerequisites

The minimum required PHP version is 5.6.0.
To use ECDSA keys, PHP version from 7.1.0 is required.

This client also depends on cURL and OpenSSL.

## Getting Started

Install via composer:

```
composer require fbett/le_acme2
```

Also have a look at the [LetsEncrypt documentation](https://letsencrypt.org/docs/) for more information and documentation on LetsEncrypt and ACME.

## Example Integration

1. Create a working directory. 
Warning: This directory will also include private keys, so i suggest to place this directory somewhere not in the root document path of the web server. 
Additionally this directory should be protected to be read from other web server users.

```
mkdir /etc/ssl/le-storage/
chown root:root /etc/ssl/le-storage
chmod 0600 /etc/ssl/le-storage
```

2. Create a directory for the acme challenges. It must be reachable by http/https.

```
mkdir /var/www/acme-challenges
```

3. Redirect specific requests to your acme-challenges directory

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

4. Use the certificate bundle, if the certificate is issued:

```
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
- The cronjob will fetch the information within the certificate bundle, if the certificate bundle is ready (mostly on the second run)
- The cronjob will also build the Apache virtual host files and will restart the Apache2 service, if the new config file is different.


Please take a look on Samples\HTTP.php for a full sample workflow.

## Known Issues

- The DNS based authentication is not currently not implemented, so wildcard certificates can not be validated. All PR's are welcome.


## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.
