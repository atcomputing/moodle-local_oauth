<?php

namespace local_oauth;

require_once($CFG->dirroot.'/local/oauth/vendor/autoload.php');
\OAuth2\Autoloader::register();

class server  extends \OAuth2\Server {

    # TODO modify AuthorizeController so it add all claims in id_token;
    # TODO make singelton
    # TODO do we want to support implicit flow

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

            // TODO Does this work behind revers proxy
            'issuer' => $CFG->wwwroot,
        ];
        $grantTypes= [
            'authorization_code' => new \OAuth2\OpenID\GrantType\AuthorizationCode($storage),
            'client_credentials' => new \OAuth2\GrantType\ClientCredentials($storage),
            'user_credentials'   => new \OAuth2\GrantType\UserCredentials($storage),
            'refresh_token'      => new \OAuth2\GrantType\RefreshToken($storage )
        ];
        parent::__construct($storage, $config, $grantTypes);
    }

    public function supported_scopes(){
        $claims = $this->getStorage('user_claims')->valid_claims();
        $reserved = ['openid']; // TODO should offline_access be included
        return array_merge( $reserved,$claims);
    }

    public function write_well_known_openid_configuration(){
        global $CFG;

        $dir ="/local/oauth/.well-known/";
        $path =$dir."openid-configuration";

        // TODO Does this work behind revers proxy
        $base = $CFG->wwwroot . '/local_auth';
        $jsonData =  [
            'issuer' => $CFG->wwwroot,
            'authorization_endpoint' => $base.'/login.php',
            'token_endpoint' => $base.'/token_endpoint.php',
            'userinfo_endpoin' => $base.'/user_info.php',
            'jwks_uri' => $base.'/jwks.php'
        ];
        $content =json_encode($jsonData, JSON_PRETTY_PRINT);
        echo $content;
        if (!file_exists($CFG->dirroot . $dir)){
            mkdir($CFG->dirroot . $dir, 0777, true);
        }
        file_put_contents(
            $CFG->dirroot . $path,
            $content
        );
    }
}
