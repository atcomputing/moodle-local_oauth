<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    local
 * @subpackage oauth
 * @copyright
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_oauth;

use OAuth2\OpenID\Storage\UserClaimsInterface;
use OAuth2\OpenID\Storage\AuthorizationCodeInterface as OpenIDAuthorizationCodeInterface;

class storage_moodle implements
    \OAuth2\Storage\AuthorizationCodeInterface,
    \OAuth2\Storage\AccessTokenInterface,
    \OAuth2\Storage\ClientCredentialsInterface,
    \OAuth2\Storage\UserCredentialsInterface,
    \OAuth2\Storage\RefreshTokenInterface,
    \OAuth2\Storage\JwtBearerInterface,
    \OAuth2\Storage\ScopeInterface,
    \OAuth2\Storage\PublicKeyInterface,
    \OAuth2\OpenID\Storage\UserClaimsInterface,
    OpenIDAuthorizationCodeInterface {
    // TODO have only 1 public private key pair for all clients?
    protected $config;

    public array $claimfunctions;

    public function __construct($connection, $config = []) {
        $this->config = $config;
        $this->claimfunctions = [
            'profile' => new \local_oauth\claim\profile,
            'email' => new \local_oauth\claim\email,
            'address' => new \local_oauth\claim\address,
            'phone' => new \local_oauth\claim\phone,
            'enrolments' => new \local_oauth\claim\enrolments,
        ];
    }
    /* \OAuth2\Storage\ClientCredentialsInterface
     * phpcs:disable moodle.NamingConventions.ValidFunctionName.LowercaseMethod
     * phpcs:disable moodle.NamingConventions.ValidVariableName.VariableNameUnderscore
    /**
     * @param string $client_id
     * @param null|string $client_secret
     * @return bool
     */
    public function checkClientCredentials($client_id, $client_secret = null) {
        global $DB;
        $client_secret_db = $DB->get_field('local_oauth_clients', 'client_secret', ['client_id' => $client_id]);
        return $client_secret == $client_secret_db;
    }

    /* \OAuth2\Storage\ClientCredentialsInterface
     * phpcs:disable moodle.NamingConventions.ValidFunctionName.LowercaseMethod
     * phpcs:disable moodle.NamingConventions.ValidVariableName.VariableNameUnderscore
    /**
     * @param string $client_id
     * @return bool
     */
    public function isPublicClient($client_id) {
        global $DB;
        $client = $DB->get_record('local_oauth_clients', ['client_id' => $client_id]);
        if (!$client) {
            return false;
        }
        return empty($client->client_secret);
    }

    /* \OAuth2\Storage\ClientInterface
     * phpcs:disable moodle.NamingConventions.ValidFunctionName.LowercaseMethod
     * phpcs:disable moodle.NamingConventions.ValidVariableName.VariableNameUnderscore
    /**
     * @param string $client_id
     * @return array|mixed
     */
    public function getClientDetails($client_id) {
        global $DB;
        $client = $DB->get_record('local_oauth_clients', ['client_id' => $client_id]);
        if (!$client) {
            return false;
        }
        unset($client->id);
        return (array) $client;
    }

    /**
     * @param string $client_id
     * @param null|string $client_secret
     * @param null|string $redirect_uri
     * @param null|array  $grant_types
     * @param null|string $scope
     * @param null|string $user_id
     * @return bool
     */
    public function setClientDetails(
        $client_id,
        $client_secret = null,
        $redirect_uri = null,
        $grant_types = null,
        $scope = null,
        $user_id = null) {
        global $DB;
        if ($client = $DB->get_record('local_oauth_clients', ['client_id' => $client_id])) {
            $client->client_secret = $client_secret;
            $client->redirect_uri = $redirect_uri;
            $client->grant_types = $grant_types;
            $client->scope = $scope;
            $client->user_id = $user_id;
            $DB->update_record('local_oauth_clients', $client);
        } else {
            $client = new \StdClass();
            $client->client_secret = $client_secret;
            $client->redirect_uri = $redirect_uri;
            $client->grant_types = $grant_types;
            $client->scope = $scope;
            $client->user_id = $user_id;
            $client->client_id = $client_id;
            $DB->insert_record('local_oauth_clients', $client);
        }

        return true;
    }

    /**
     * @param $client_id
     * @param $grant_type
     * @return bool
     */
    public function checkRestrictedGrantType($client_id, $grant_type) {
        $details = $this->getClientDetails($client_id);
        if (isset($details['grant_types'])) {
            $grant_types = explode(' ', $details['grant_types']);

            return in_array($grant_type, (array) $grant_types);
        }

        // If grant_types are not defined, then none are restricted.
        return true;
    }

    /**
     * @param string $access_token
     * @return array|bool|mixed|null
     */
    public function getAccessToken($access_token) {
        global $DB;
        $token = $DB->get_record('local_oauth_access_tokens', ['access_token' => $access_token]);
        if (!$token) {
            return false;
        }
        unset($token->id);
        return (array) $token;
    }

    /**
     * @param string $access_token
     * @param mixed  $client_id
     * @param mixed  $user_id
     * @param int    $expires
     * @param string $scope
     * @return bool
     */
    public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope = null) {
        global $DB;

        // If it exists, update it.
        if ($token = $DB->get_record('local_oauth_access_tokens', ['access_token' => $access_token])) {
            $token->client_id = $client_id;
            $token->expires = $expires;
            $token->user_id = $user_id;
            $token->scope = $scope;
            $DB->update_record('local_oauth_access_tokens', $token);
        } else {
            $token = new \StdClass();
            $token->client_id = $client_id;
            $token->expires = $expires;
            $token->user_id = $user_id;
            $token->scope = $scope;
            $token->access_token = $access_token;
            $DB->insert_record('local_oauth_access_tokens', $token);
        }
        return true;
    }


    /**
     * @param $access_token
     * @return bool
     */
    public function unsetAccessToken($access_token) {
        global $DB;
        $DB->delete_records('local_oauth_access_tokens', ['access_token' => $access_token]);

    }

    /* OAuth2\Storage\AuthorizationCodeInterface */
    /**
     * @param string $code
     * @return mixed
     */
    public function getAuthorizationCode($code) {
        global $DB;
        $code = $DB->get_record('local_oauth_authorization_codes', ['authorization_code' => $code]);
        if (!$code) {
            return false;
        }
        unset($code->id);
        return (array) $code;
    }

    /**
     * @param string $code
     * @param mixed  $client_id
     * @param mixed  $user_id
     * @param string $redirect_uri
     * @param int    $expires
     * @param string $scope
     * @param string $id_token
     * @return bool|mixed
     */
    public function setAuthorizationCode(
        $code,
        $client_id,
        $user_id,
        $redirect_uri,
        $expires,
        $scope = null,
        $id_token = null,
        $code_challenge = null,
        $code_challenge_method = null) {

        // TODO Implement Proof Key for Code Exchange (code_challenge), by storing in in db?
        global $DB;
        if (func_num_args() > 6) {
            // We are calling with an id token.
            return call_user_func_array([$this, 'setAuthorizationCodeWithIdToken'], func_get_args());
        }

        // If it exists, update it.
        if ($auth_code = $DB->get_record('local_oauth_authorization_codes', ['authorization_code' => $code])) {
            $auth_code->client_id = $client_id;
            $auth_code->user_id = $user_id;
            $auth_code->redirect_uri = $redirect_uri;
            $auth_code->expires = $expires;
            $auth_code->scope = $scope;
            $DB->update_record('local_oauth_authorization_codes', $auth_code);
        } else {
            $auth_code = new \StdClass();
            $auth_code->client_id = $client_id;
            $auth_code->user_id = $user_id;
            $auth_code->redirect_uri = $redirect_uri;
            $auth_code->expires = $expires;
            $auth_code->scope = $scope;
            $auth_code->authorization_code = $code;
            $DB->insert_record('local_oauth_authorization_codes', $auth_code);
        }
        return true;
    }

    /**
     * @param string $code
     * @param mixed  $client_id
     * @param mixed  $user_id
     * @param string $redirect_uri
     * @param string $expires
     * @param string $scope
     * @param string $id_token
     * @return bool
     */
    private function setAuthorizationCodeWithIdToken(
        $code,
        $client_id,
        $user_id,
        $redirect_uri,
        $expires,
        $scope = null,
        $id_token = null) {
        global $DB;

        // If it exists, update it.
        if ($auth_code = $DB->get_record('local_oauth_authorization_codes', ['authorization_code' => $code])) {
            $auth_code->client_id = $client_id;
            $auth_code->user_id = $user_id;
            $auth_code->redirect_uri = $redirect_uri;
            $auth_code->expires = $expires;
            $auth_code->scope = $scope;
            $auth_code->id_token = $id_token;
            $DB->update_record('local_oauth_authorization_codes', $auth_code);
        } else {
            $auth_code = new \StdClass();
            $auth_code->client_id = $client_id;
            $auth_code->user_id = $user_id;
            $auth_code->redirect_uri = $redirect_uri;
            $auth_code->expires = $expires;
            $auth_code->scope = $scope;
            $auth_code->id_token = $id_token;
            $auth_code->authorization_code = $code;
            $DB->insert_record('local_oauth_authorization_codes', $auth_code);
        }
        return true;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function expireAuthorizationCode($code) {
        global $DB;
        return $DB->delete_records('local_oauth_authorization_codes', ['authorization_code' => $code]);
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function checkUserCredentials($username, $password) {
        if ($user = $this->getUser($username)) {
            return $this->checkPassword($user, $password);
        }

        return false;
    }

    /* \OAuth2\Storage\UserCredentialsInterface
    /**
     * @param string $username
     * @return array|bool
     */
    public function getUserDetails($username) {
        return $this->getUser($username);
    }

    public function valid_claims() {
        return array_keys($this->claimfunctions);
    }

    /**
     * @param mixed  $user_id
     * @param string $claims
     * @return array|bool
     */
    public function getUserClaims($user_id, $claims) {
        if (!$user = \core_user::get_user($user_id)) {
            return false;
        }
        $claims = explode(' ', trim($claims));
        $userclaims = [];

        // For each requested claim, if the user has the claim, set it in the response.
        $validclaims = $this->valid_claims();
        foreach ($validclaims as $validclaim) {
            if (in_array($validclaim, $claims)) {
                if (isset($this->claimfunctions[$validclaim])) {
                    $userclaims = array_merge($userclaims, $this->claimfunctions[$validclaim]->claim($user));
                }
            }
        }

        return $userclaims;
    }

    /**
     * @param string $refresh_token
     * @return bool|mixed
     */
    public function getRefreshToken($refresh_token) {
        global $DB;
        $token = $DB->get_record('local_oauth_refresh_tokens', ['refresh_token' => $refresh_token]);
        if (!$token) {
            return false;
        }
        unset($token->id);
        return (array) $token;
    }

    /**
     * @param string $refresh_token
     * @param mixed  $client_id
     * @param mixed  $user_id
     * @param string $expires
     * @param string $scope
     * @return bool
     */
    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null) {
        global $DB;

        $token = new \StdClass();
        $token->refresh_token = $refresh_token;
        $token->client_id = $client_id;
        $token->user_id = $user_id;
        $token->expires = $expires;
        $token->scope = $scope;
        $DB->insert_record('local_oauth_refresh_tokens', $token);

        return true;
    }

    /**
     * @param string $refresh_token
     * @return bool
     */
    public function unsetRefreshToken($refresh_token) {
        global $DB;
        return $DB->delete_records('local_oauth_refresh_tokens', ['refresh_token' => $refresh_token]);
    }

    /**
     * @param array $user
     * @param string $password
     * @return bool
     */
    protected function checkPassword($user, $password) {
        $user = (object)$user;
        return validate_internal_user_password($user, $password);
    }

    /**
     * @param string $username
     * @return array|bool
     */
    public function getUser($username) {
        global $DB;
        // TODO Don't user use DB direcly for accesing user.
        $userinfo = $DB->get_record('user', ['username' => $username]);
        if (!$userinfo) {
            return false;
        }
        $userinfo = (array) $userinfo;
        $userinfo['user_id'] = $userinfo['id'];

        return $userinfo;
    }

    /* \OAuth2\Storage\ScopeInterface
    /**
     * @param string $scope
     * @return bool
     */
    public function scopeExists($scope) {
        return isset($claimfunctions[$scope]);
    }

    /* \OAuth2\Storage\ScopeInterface
    /**
     * @param mixed $client_id
     * @return null|string
     */
    public function getDefaultScope($client_id = null) {
        global $DB;
        $scope = $DB->get_fieldset_select('local_oauth_scopes', 'scope', 'is_default = :is_default', ['is_default' => true]);

        if ($scope) {
            return implode(' ', $scope);
        }

        return null;
    }

    /* \OAuth2\Storage\JwtBearerInterface
    /**
     * @param mixed $client_id
     * @param $subject
     * @return string
     */
    public function getClientKey($client_id, $subject) {
        global $DB;
        return $DB->get_field('local_oauth_jwt', 'public_key' , ['client_id' => $client_id, 'subject' => $subject]);
    }

    /* \OAuth2\Storage\ClientInterface::getClientScope
     * phpcs:disable moodle.NamingConventions.ValidFunctionName.LowercaseMethod
     * phpcs:disable moodle.NamingConventions.ValidVariableName.VariableNameUnderscore
    /**
     * @param mixed $client_id
     * @return bool|null
     */
    public function getClientScope($client_id) {
        if (!$clientdetails = $this->getClientDetails($client_id)) {
            return false;
        }

        if (isset($clientdetails['scope'])) {
            return $clientdetails['scope'];
        }

        return null;
    }

    /* \OAuth2\Storage\JwtBearerInterface
     * phpcs:disable moodle.NamingConventions.ValidFunctionName.LowercaseMethod
     * phpcs:disable moodle.NamingConventions.ValidVariableName.VariableNameUnderscore
    /**
     * @param mixed $client_id
     * @param $subject
     * @param $audience
     * @param $expires
     * @param $jti
     * @return array|null
     */
    public function getJti($client_id, $subject, $audience, $expiration, $jti) {
        // TODO: Needs cassandra implementation.
        throw new \Exception('getJti() for the Moodle driver is currently unimplemented.');
    }

    /* \OAuth2\Storage\JwtBearerInterface
     * phpcs:disable moodle.NamingConventions.ValidFunctionName.LowercaseMethod
     * phpcs:disable moodle.NamingConventions.ValidVariableName.VariableNameUnderscore
    /**
     * (@inheritdoc)
     * @param mixed $client_id
     * @param $subject
     * @param $audience
     * @param $expires
     * @param $jti
     * @return bool
     */
    public function setJti($client_id, $subject, $audience, $expiration, $jti) {
        // TODO: Needs cassandra implementation.
        throw new \Exception('setJti() for the Moodle driver is currently unimplemented.');
    }

    /* \OAuth2\Storage\PublicKeyInterface
     * phpcs:disable moodle.NamingConventions.ValidFunctionName.LowercaseMethod
     * phpcs:disable moodle.NamingConventions.ValidVariableName.VariableNameUnderscore
    /**
     * (@inheritdoc)
     * @param mixed $client_id
     * @return mixed
     */
    public function getPublicKey($client_id = null) {
        global $DB;
        return $DB->get_field_select(
            'local_oauth_public_keys',
            'public_key' ,
            'client_id=:client_id OR client_id IS NULL',
            ['client_id' => $client_id],
            'client_id IS NOT NULL DESC');
    }

    /* \OAuth2\Storage\PublicKeyInterface
     * phpcs:disable moodle.NamingConventions.ValidFunctionName.LowercaseMethod
     * phpcs:disable moodle.NamingConventions.ValidVariableName.VariableNameUnderscore
    /**
     * (@inheritdoc)
     * @param mixed $client_id
     * @return mixed
     */
    public function getPrivateKey($client_id = null) {
        global $DB;
        return $DB->get_field_select(
            'local_oauth_public_keys',
            'private_key',
            'client_id=:client_id OR client_id IS NULL',
            ['client_id' => $client_id],
            'client_id IS NOT NULL DESC'
        );
    }

    /* \OAuth2\Storage\PublicKeyInterface
     * phpcs:disable moodle.NamingConventions.ValidFunctionName.LowercaseMethod
     * phpcs:disable moodle.NamingConventions.ValidVariableName.VariableNameUnderscore
    /**
     * (@inheritdoc)
     * @param mixed $client_id
     * @return string
     */
    public function getEncryptionAlgorithm($client_id = null) {
        global $DB;
        $alg = $DB->get_field_select(
            'local_oauth_public_keys',
            'encryption_algorithm' ,
            'client_id=:client_id OR client_id IS NULL',
            ['client_id' => $client_id],
            'client_id IS NOT NULL DESC');
        if ($alg) {
            return $alg;
        }
        return 'RS256';
    }

    // phpcs:disable Squiz.PHP.CommentedOutCode.Found
    // Could be used if you want to use test from vendor/bshaffer/test, But not used now.
    // phpcs:disable moodle.Commenting.InlineComment.SpacingBefore
    // phpcs:disable moodle.Commenting.InlineComment.NotCapital
    //
    // /**
    //  * @param string $username
    //  * @param string $password
    //  * @param string $firstName
    //  * @param string $lastName
    //  * @return bool
    //  */
    // public function setUser($username, $password, $firstName = null, $lastName = null) {
    //     global $DB;
    //     $user = $DB->get_record('user', ['username' => $username]);
    //     if ($user) {
    //         if ($firstName) {
    //             $DB->set_field('user', 'firstname', $firstName, ['id' => $user->id]);
    //         }
    //         if ($lastName) {
    //             $DB->set_field('user', 'lastname', $lastName, ['id' => $user->id]);
    //         }
    //         update_internal_user_password($userInfo, $password);
    //     } else {
    //         $user = create_user_record($username, $password);
    //         if ($user) {
    //             if ($firstName) {
    //                 $DB->set_field('user', 'firstname', $firstName, ['id' => $user->id]);
    //             }
    //             if ($lastName) {
    //                 $DB->set_field('user', 'lastname', $lastName, ['id' => $user->id]);
    //             }
    //         }
    //     }
    //     return true;
    // }
    //
    // /**
    //  * DDL to create OAuth2 database and tables for PDO storage
    //  *
    //  * @see https://github.com/dsquier/oauth2-server-php-mysql
    //  *
    //  * @param string $dbName
    //  * @return string
    //  */
    // public function getBuildSql($notused = false) {
    //     $sql = "
    //     CREATE TABLE mdl_oauth_clients (
    //       client_id             VARCHAR(80)   NOT NULL,
    //       client_secret         VARCHAR(80)   NOT NULL,
    //       redirect_uri          VARCHAR(2000),
    //       grant_types           VARCHAR(80),
    //       scope                 VARCHAR(4000),
    //       user_id               VARCHAR(80),
    //       PRIMARY KEY (client_id)
    //     );
    //
    //     CREATE TABLE mdl_oauth_access_tokens (
    //       access_token         VARCHAR(40)    NOT NULL,
    //       client_id            VARCHAR(80)    NOT NULL,
    //       user_id              VARCHAR(80),
    //       expires              TIMESTAMP      NOT NULL,
    //       scope                VARCHAR(4000),
    //       PRIMARY KEY (access_token)
    //     );
    //
    //     CREATE TABLE mdl_oauth_authorization_codes (
    //       authorization_code  VARCHAR(40)    NOT NULL,
    //       client_id           VARCHAR(80)    NOT NULL,
    //       user_id             VARCHAR(80),
    //       redirect_uri        VARCHAR(2000),
    //       expires             TIMESTAMP      NOT NULL,
    //       scope               VARCHAR(4000),
    //       id_token            VARCHAR(1000),
    //       PRIMARY KEY (authorization_code)
    //     );
    //
    //     CREATE TABLE mdl_oauth_refresh_tokens (
    //       refresh_token       VARCHAR(40)    NOT NULL,
    //       client_id           VARCHAR(80)    NOT NULL,
    //       user_id             VARCHAR(80),
    //       expires             TIMESTAMP      NOT NULL,
    //       scope               VARCHAR(4000),
    //       PRIMARY KEY (refresh_token)
    //     );
    //
    //     CREATE TABLE {user} (
    //       username            VARCHAR(80),
    //       password            VARCHAR(80),
    //       first_name          VARCHAR(80),
    //       last_name           VARCHAR(80),
    //       email               VARCHAR(80),
    //       email_verified      BOOLEAN,
    //       scope               VARCHAR(4000)
    //     );
    //
    //     CREATE TABLE mdl_oauth_scopes (
    //       scope               VARCHAR(80)  NOT NULL,
    //       is_default          BOOLEAN,
    //       PRIMARY KEY (scope)
    //     );
    //
    //     CREATE TABLE mdl_oauth_jwt (
    //       client_id           VARCHAR(80)   NOT NULL,
    //       subject             VARCHAR(80),
    //       public_key          VARCHAR(2000) NOT NULL
    //     );
    //
    //     CREATE TABLE mdl_oauth_public_keys (
    //       client_id            VARCHAR(80),
    //       public_key           VARCHAR(2000),
    //       private_key          VARCHAR(2000),
    //       encryption_algorithm VARCHAR(100) DEFAULT 'RS256'
    //     );
    //
    // ";
    //
    //     return $sql;
    // }
}
