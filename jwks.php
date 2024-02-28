<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin index file
 *
 * @package     local_oauth
 * @copyright   2024 Rens Sikma <r.sikam@atcomputing.nl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.Files.RequireLogin.Missing

require('../../config.php');
require_once('vendor/autoload.php');

use OAuth2\Encryption\Jwt;

$storage = new \local_oauth\storage_moodle([]);

$jsondata = ['keys' => []];
$keys = $DB->get_records('oauth_public_keys', null, '', 'public_key');
foreach ($keys as $key) {
    $pubkey = openssl_pkey_get_public($key->public_key);
    $keyinfo = openssl_pkey_get_details($pubkey);
    $jwt = new \OAuth2\Encryption\Jwt();
    $jsondata['keys'][] =
        [
            'kty' => 'RSA',
            "use" => "sig",
            // TODO add kid, x5c, 'alg' => 'RS256'.
            'n' => $jwt->urlSafeB64Encode($keyinfo['rsa']['n']),
            'e' => $jwt->urlSafeB64Encode($keyinfo['rsa']['e']),
        ];
}

// Output JWKS JSON.
header('Content-Type: application/json');
echo json_encode($jsondata, JSON_PRETTY_PRINT);
