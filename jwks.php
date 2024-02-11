<?php

// Include the necessary files from the OAuth2 Server library

require('../../config.php');
require_once 'vendor/autoload.php';

use OAuth2\Encryption\Jwt;

// Initialize OAuth2 Server

$storage = new \local_oauth\storage\moodle([]);
$pubkey = openssl_pkey_get_public($storage->getPublicKey("proctor2"));
$keyinfo = openssl_pkey_get_details($pubkey);
$jwt = new \OAuth2\Encryption\Jwt();
$jsonData = [
    'keys' => [
        [
            'kty' => 'RSA',
            'n' => $jwt->urlSafeB64Encode($keyinfo['rsa']['n']),
            'e' => $jwt->urlSafeB64Encode($keyinfo['rsa']['e'])
        ],
    ],
];
// Output JWKS JSON
header('Content-Type: application/json');
echo json_encode($jsonData, JSON_PRETTY_PRINT);
