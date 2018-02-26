<?php

namespace LE_ACME2\Utilities;

use LE_ACME2\Order;

class Certificate {

    public static function generateCSR(Order $order)
    {
        $dn = [
            "commonName" => $order->getSubjects()[0]
        ];

        $san = implode(",", array_map(function ($dns) {

                return "DNS:" . $dns;
            }, $order->getSubjects())
        );

        $config_file = $order->getKeyDirectoryPath() . 'csr_config';

        file_put_contents($config_file,
            'HOME = .
			RANDFILE = ' . $order->getKeyDirectoryPath() . '.rnd
			[ req ]
			default_bits = 4096
			default_keyfile = privkey.pem
			distinguished_name = req_distinguished_name
			req_extensions = v3_req
			[ req_distinguished_name ]
			countryName = Country Name (2 letter code)
			[ v3_req ]
			basicConstraints = CA:FALSE
			subjectAltName = ' . $san . '
			keyUsage = nonRepudiation, digitalSignature, keyEncipherment');

        $privateKey = openssl_pkey_get_private(file_get_contents($order->getKeyDirectoryPath() . 'private.pem'));
        $csr = openssl_csr_new($dn, $privateKey, array('config' => $config_file, 'digest_alg' => 'sha256'));
        openssl_csr_export ($csr, $csr);

        unlink($config_file);

        return $csr;
    }

}