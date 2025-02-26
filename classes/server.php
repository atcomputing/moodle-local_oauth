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
 * Server class
 *
 * @package     local_oauth
 * @copyright   2024 AT Computing
 * @author      Rens Sikma <r.sikma@atcomping.nl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_oauth;

/**
 * Server class reprenset the server settings used for this OIDC server
 */
class server extends \OAuth2\Server {
    // TODO Modify AuthorizeController so it add all claims in id_token.
    // TODO Make singelton.
    // TODO Do we want to support implicit flow.
    /**
     * Create server object
     */
    public function __construct() {
        global $CFG;

        $storage = new \local_oauth\storage_moodle([]);

        $config = [
            // TODO Does this work behind revers proxy.
            'issuer' => $CFG->wwwroot,
            'use_openid_connect'       => true,
            'enforce_state'            => true,
            // phpcs:disable Squiz.PHP.CommentedOutCode.Found
            // phpcs:disable moodle.Commenting.InlineComment.NotCapital
            // 'use_jwt_access_tokens'        => false,
            // 'jwt_extra_payload_callable' => null,
            // 'store_encrypted_token_string' => true,
            // 'id_lifetime'              => 3600,
            // 'access_lifetime'          => 3600,
            // 'www_realm'                => 'Service',
            // 'token_param_name'         => 'access_token',
            // 'token_bearer_header_name' => 'Bearer',
            // 'require_exact_redirect_uri' => true,
            // 'allow_implicit'           => false,
            // 'enforce_pkce'             => false,
            // 'allow_credentials_in_request_body' => true,
            // 'allow_public_clients'     => true,
            // 'always_issue_new_refresh_token' => false,
            // 'unset_refresh_token_after_use' => true,

        ];
        $granttypes = [
            'authorization_code' => new \OAuth2\OpenID\GrantType\AuthorizationCode($storage),
            'client_credentials' => new \OAuth2\GrantType\ClientCredentials($storage),
            'user_credentials'   => new \OAuth2\GrantType\UserCredentials($storage),
            'refresh_token'      => new \OAuth2\GrantType\RefreshToken($storage),
        ];
        parent::__construct($storage, $config, $granttypes);
    }

    /**
     * Getter for scopes this serer supports.
     */
    public function supported_scopes() {
        $claims = $this->getStorage('user_claims')->valid_claims();
        $reserved = ['openid']; // TODO should offline_access be included.
        return array_merge($reserved, $claims);
    }

    /**
     * Generete well-known openid configuration json for this server
     * Not used yet
     */
    public function write_well_known_openid_configuration() {
        global $CFG;

        $dir = "/local/oauth/.well-known/";
        $path = $dir . "openid-configuration";

        // TODO Does this work behind revers proxy.
        $base = $CFG->wwwroot . '/local_auth';
        $jsondata = [
            'issuer' => $CFG->wwwroot,
            'authorization_endpoint' => $base . '/login.php',
            'token_endpoint' => $base . '/token_endpoint.php',
            'userinfo_endpoin' => $base . '/user_info.php',
            'jwks_uri' => $base . '/jwks.php',
        ];
        $content = json_encode($jsondata, JSON_PRETTY_PRINT);
        echo $content;
        if (!file_exists($CFG->dirroot . $dir)) {
            mkdir($CFG->dirroot . $dir, 0777, true);
        }
        file_put_contents(
            $CFG->dirroot . $path,
            $content
        );
    }
}
