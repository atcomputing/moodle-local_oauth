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
 * @copyright   2024 Rens Sikma <r.sikma@atcomping.nl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_oauth;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/oauth/vendor/autoload.php');
require_once($CFG->dirroot.'/local/oauth/classes/storage/moodle.php');

class server_test extends \advanced_testcase {

    public function test_authorization_code_openid() {
        $this->resetAfterTest(true);

        $server = new server();
        $client = new client('client', 'http://localhost', ['authorization_code'], ['openid', 'email', 'address'], "0", 0);
        $client->store();
        $user = $this->getDataGenerator()->create_user([
            'address' => 'home',
            'email' => 'user1@example.com',
            'username' => 'user1',
        ]);

        // From: https://bshaffer.github.io/oauth2-server-php-docs/overview/openid-connect/ .
        // Create a request object to mimic an authorization code request.
        $request = new \OAuth2\Request([
            'client_id'     => 'client',
            'redirect_uri'  => 'http://localhost',
            'response_type' => 'code',
            'scope'         => 'openid email address',
            'state'         => 'xyz',
        ]);

        $response = new \OAuth2\Response();
        $server->handleAuthorizeRequest($request, $response, true, $user->id);

        $redirecturl = $response->getHttpHeader('Location');
        $this->assertEmpty($response->getParameters('error'));
        $this->assertNotNull($redirecturl);

        // Parse the returned URL to get the authorization code.
        $parts = parse_url($redirecturl);
        parse_str($parts['query'], $query);

        // Pull the code from storage and verify an "id_token" was added.
        $code = $server->getStorage('authorization_code')
            ->getAuthorizationCode($query['code']);
        $this->assertNotNull($code);
        $this->assertNotNull($code['authorization_code']);
        $this->assertEquals( $user->id, $code['user_id']);
        $this->assertEquals('http://localhost', $code['redirect_uri']);
        $this->assertNotNull($code['expires']);
        $this->assertEquals('openid email address', $code['scope']);
        $this->assertEquals('client', $code['client_id']);
        $this->assertNotNull($code['id_token']);
        $idtoken = $code['id_token'];

        $jwt = new \OAuth2\Encryption\Jwt();
        $key = $server->getStorage('public_key')->getPublicKey("client");
        $userinfo = $jwt->decode($idtoken, $key);
        $this->assertEquals($user->id, $userinfo['sub']);

        // TODO test more userinfo attributes.
    }

    public function test_client_credentials_openid() {

        $this->resetAfterTest(true);

        $server = new server();
        $user = $this->getDataGenerator()->create_user([]);
        $client = new client('client', 'http://localhost', ['client_credentials'], ['openid'], $user->id, 0);
        $client->store();

        // From: https://bshaffer.github.io/oauth2-server-php-docs/overview/openid-connect/.
        // Create a request object to mimic an authorization code request.
        $request = new \OAuth2\Request(
            [], // Query.
            [ 'grant_type'    => 'client_credentials', // Request.
              // This only works because: allow_credentials_in_request_body=true.
              'client_id'     => 'client',
              'client_secret' => $client->client_secret,
            ],
            [], // Attributes.
            [], // Cookies.
            [], // Files.
            ['REQUEST_METHOD' => 'post'] // Server.
        );

        $response = new \OAuth2\Response();
        $server->handleTokenRequest($request, $response, true);

        $token = $response->getParameters('acces_token');
        $this->assertNotNull($token);
        $this->assertEquals('openid', $token['scope']);
        $authorization = $server->getStorage('access_token')->getAccessToken($token['access_token']);
        $this->assertEquals($user->id, $authorization['user_id']);
    }

    // TODO add test_refresh_token.

    public function test_granttypes() {

        $server = new server();
        $grants = $server->getGrantTypes();
        $this->assertEquals(['authorization_code', 'client_credentials', 'user_credentials', 'refresh_token'], array_keys($grants));
    }
}
