<?php

namespace local_oauth;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/oauth/vendor/autoload.php');
require_once($CFG->dirroot.'/local/oauth/classes/storage/moodle.php');

class server_test extends \advanced_testcase {

    public function test_authorization_code_openid()
    {
        $this->resetAfterTest(true);

        $server = new server();
        $client = new client('client', 'http://localhost', ['authorization_code'], 'openid email address', "0", 0, 0);
        $client->store();
        $user = $this->getDataGenerator()->create_user([
            'address' => 'home',
            'email'=>'user1@example.com',
            'username'=>'user1'
        ]);

        // from: https://bshaffer.github.io/oauth2-server-php-docs/overview/openid-connect/
        // create a request object to mimic an authorization code request
        $request = new \OAuth2\Request(array(
            'client_id'     => 'client',
            'redirect_uri'  => 'http://localhost',
            'response_type' => 'code',
            'scope'         => 'openid email address',
            'state'         => 'xyz',
        ));

        $response = new \OAuth2\Response();
        $server->handleAuthorizeRequest($request, $response, true, $user->id);

        $redirect_url = $response->getHttpHeader('Location');
        $this->assertEmpty($response->getParameters('error'));
        $this->assertNotNull($redirect_url);

        // parse the returned URL to get the authorization code
        $parts = parse_url($redirect_url);
        parse_str($parts['query'], $query);

        // pull the code from storage and verify an "id_token" was added
        $code = $server->getStorage('authorization_code')
                       ->getAuthorizationCode($query['code']);
        $this->assertNotNull($code);
        $this->assertNotNull($code['authorization_code']);
        $this->assertEquals( $user->id, $code['user_id']);
        $this->assertEquals('http://localhost', $code['redirect_uri']);
        $this->assertNotNull($code['expires']);
        $this->assertEquals('openid email address', $code['scope']);
        $this->assertEquals('client', $code['client_id']);
        $this->assertNotNull($code['id_token']); # TODO decode
        $id_token = $code['id_token'];

        $jwt = new \OAuth2\Encryption\Jwt();
        $key = $server->getStorage('public_key')->getPublicKey("client");
        $user_info = $jwt->decode($id_token,$key);
        $this->assertEquals($user->id, $user_info['sub']);

        var_export($user_info);
        // $info = $server->getStorage('user_claims')->getUserClaims($user->id,"address");
        // var_export($info);
    }

    public function test_client_credentials_openid(){

        $this->resetAfterTest(true);

        $server = new server();
        $user = $this->getDataGenerator()->create_user([]);
        $client = new client('client', 'http://localhost', ['client_credentials'], 'openid', $user->id, 0, 0);
        $client->store();

        // from: https://bshaffer.github.io/oauth2-server-php-docs/overview/openid-connect/
        // create a request object to mimic an authorization code request
        $request = new \OAuth2\Request(
            [], # query
            [ 'grant_type'    => 'client_credentials', # request
              // 'response_type'  => 'code',
              # this only works because: allow_credentials_in_request_body=true
              'client_id'     => 'client',
              'client_secret' => $client->client_secret
            ],
            [], # attributes
            [], # cookies
            [], # files
            ['REQUEST_METHOD' => 'post'] # server
            // [], # content
            // ['AUTHORIZATION' => 'Basic '.base64_encode('client:'.$client->client_secret)], # headers
        );

        $response = new \OAuth2\Response();
        $server->handleTokenRequest($request, $response, true);

        $token = $response->getParameters('acces_token');
        $this->assertNotNull($token);
        $this->assertEquals('openid', $token['scope']);
        $authorization = $server->getStorage('access_token')->getAccessToken($token['access_token']);
        $this->assertEquals($user->id, $authorization['user_id']);
    }

    public function test_refresh_token(){

        $this->resetAfterTest(true);
        $server = new server();
        // $access_token = new \OAuth2\ResponseType\AccessToken($storage);
        // $token = $access_token->createAccessToken("client", $user->id, "openid profile email address phone enrolments");
    }

    public function test_grantTypes(){

        $server = new server();
        $grants = $server->getGrantTypes();
        $this->assertEquals(['authorization_code','client_credentials','user_credentials', 'refresh_token'], array_keys($grants));
    }
}
