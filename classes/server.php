<?php

namespace local_oauth;

require_once($CFG->dirroot.'/local/oauth/vendor/autoload.php');
\OAuth2\Autoloader::register();

class server  extends \OAuth2\Server {

    public function __construct() {
        global $CFG;

        $storage = new \local_oauth\storage\moodle([]);

        $config =[
            // 'use_jwt_access_tokens'        => false,
            // 'jwt_extra_payload_callable' => null,
            // 'store_encrypted_token_string' => true,
            'use_openid_connect'       => true,
            // 'id_lifetime'              => 3600,
            // 'access_lifetime'          => 3600,
            // 'www_realm'                => 'Service',
            // 'token_param_name'         => 'access_token',
            // 'token_bearer_header_name' => 'Bearer',
            'enforce_state'            => true,
            // 'require_exact_redirect_uri' => true,
            // 'allow_implicit'           => false,
            // 'enforce_pkce'             => false,
            // 'allow_credentials_in_request_body' => true,
            // 'allow_public_clients'     => true,
            // 'always_issue_new_refresh_token' => false,
            // 'unset_refresh_token_after_use' => true,
            'issuer' => $CFG->wwwroot,
        ];
        $grandTypes= [
            'authorization_code' => new \OAuth2\GrantType\AuthorizationCode($storage),
            'client_credentials' => new \OAuth2\GrantType\ClientCredentials($storage)
            // 'user_credentials'   => new UserCredentials($storage),
            // 'refresh_token'      => new RefreshToken($storage, array(
            //     'always_issue_new_refresh_token' => true,
            // )),
        ];
        parent::__construct($storage, $config,);
    }
}
