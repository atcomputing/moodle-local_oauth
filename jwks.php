<?php

/**
 * NO_DEBUG_DISPLAY - disable moodle specific debug messages and any errors in output
 */
define('NO_DEBUG_DISPLAY', true);
require('../../config.php');
require_once 'vendor/autoload.php';

use OAuth2\Encryption\Jwt;

// Initialize OAuth2 Server

$storage = new \local_oauth\storage\moodle([]);

$JsonData=['keys'=>[]];
$keys= $DB->get_records('oauth_public_keys',null,'','public_key');
foreach($keys as $key){
    $pubkey = openssl_pkey_get_public($key->public_key);
    $keyinfo = openssl_pkey_get_details($pubkey);
    $jwt = new \OAuth2\Encryption\Jwt();
    $jsonData['keys'][] =
        [
            'kty' => 'RSA',
            "use" => "sig",
            // 'kid' TODO
            // x5c
            // 'alg' => 'RS256',
            'n' => $jwt->urlSafeB64Encode($keyinfo['rsa']['n']),
            'e' => $jwt->urlSafeB64Encode($keyinfo['rsa']['e'])
        ];
}
// var_export($records);

// // Output JWKS JSON
header('Content-Type: application/json');
echo json_encode($jsonData, JSON_PRETTY_PRINT);
