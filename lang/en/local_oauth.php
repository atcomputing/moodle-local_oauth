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
 * Plugin en translations
 *
 * @package     local_oauth
 * @copyright   https://github.com/examus/moodle-local_oauth
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['addclient'] = 'Add new client';
$string['auth_question'] = 'Do you want to authorize <strong>{$a}</strong>?';
$string['auth_question_desc'] = 'This application is asking to have access this information over your account:';
$string['auth_question_login'] = 'This application is to access your login information';
$string['authorization_code_explanation'] = 'Lets user login via redirect to Moodle';
$string['client_credentials_explanation'] = 'Lets application login as a user';
$string['client_id'] = 'Client identifier';
$string['client_id_existing_error'] = 'The Client identifier specified already exists, please choose another one';
$string['client_id_help'] = 'Identifier to be used from the client form in order to reference this provider.' .
   'It has to be unique. For instance, a valid identifier could be "blog1" or "nodes".';
$string['client_not_exists'] = 'Client does not exist';
$string['client_secret'] = 'Client secret Key';
$string['confirmdeletestr'] = 'Are you sure you want to delete client {$a}?';
$string['delok'] = 'Client successfully deleted';
$string['event_user_granted'] = 'User granted';
$string['event_user_info_request'] = 'User info requested';
$string['event_user_info_request_failed'] = 'User info request failed';
$string['event_user_not_granted'] = 'User not granted';
$string['grant_types'] = 'Grant Types';
$string['grant_types_help'] = 'Choose how the user is allowed to login';
$string['no_confirmation'] = 'Do not prompt users to confirm authorized scope';
$string['no_confirmation_help'] = 'The user will not be prompted to confirm authorized scopes, after the authentication was successfull';
$string['oauth:manageclients'] = 'Manage OAuth provider Clients';

$string['plugin'] = 'Local Plugin';
$string['pluginname'] = 'OAuth provider';

$string['privacy:metadata:local_oauth:auth_scopes'] = 'Authorized scopes.';
$string['privacy:metadata:local_oauth:auth_scopes:tableexplanation'] = 'Scopes a user hase aproved for a OAuht/Openid Connect client.';
$string['privacy:metadata:local_oauth:auth_scopes:id'] = 'Id of authorization.';
$string['privacy:metadata:local_oauth:auth_scopes:client_id'] = 'Id of the client that request the scope.';
$string['privacy:metadata:local_oauth:auth_scopes:user_id'] = 'User that has approved the client to get access scopes.';
$string['privacy:metadata:local_oauth:auth_scopes:scopes'] = 'Scopes that were approved, Indicating which information can be shared.';

$string['privacy:metadata:local_oauth:refresh_tokens'] = 'Oauth refresh tokens';
$string['privacy:metadata:local_oauth:refresh_tokens:tableexplanation'] = 'Stores which refresh tokens can be used to refresh a access token.';
$string['privacy:metadata:local_oauth:refresh_tokens:id'] = 'Id of refresh_token.';
$string['privacy:metadata:local_oauth:refresh_tokens:refresh_token'] = 'The refresh token itself.';
$string['privacy:metadata:local_oauth:refresh_tokens:client_id'] = 'Id of client that can use refresh_token.';
$string['privacy:metadata:local_oauth:refresh_tokens:user_id'] = 'Id of User that can use the refresh_token.';
$string['privacy:metadata:local_oauth:refresh_tokens:expires'] = 'how long refresh_token will be valid.';
$string['privacy:metadata:local_oauth:refresh_tokens:scope'] = 'scopes of access toke that when refreshed.';

$string['privacy:metadata:local_oauth:auth_codes'] = 'authroization codes';
$string['privacy:metadata:local_oauth:auth_codes:tableexplanation'] = 'Stores which authorization_codes are used.which aure used to get you first access code.';
$string['privacy:metadata:local_oauth:auth_codes:id'] = 'Id of authorization code.';
$string['privacy:metadata:local_oauth:auth_codes:authorization_code'] = 'the authorization code itself'.
$string['privacy:metadata:local_oauth:auth_codes:client_id'] = 'Id of client that is allowed to use authorization code';
$string['privacy:metadata:local_oauth:auth_codes:user_id'] = 'Id of user by which the authroization was request';
$string['privacy:metadata:local_oauth:auth_codes:redirect_uri'] = 'Url of the website that the use was redirect to whith the authorization code.';
$string['privacy:metadata:local_oauth:auth_codes:expires'] = 'How long authorization code is valid.';
$string['privacy:metadata:local_oauth:auth_codes:scope'] = 'Scope use for the authorization';
$string['privacy:metadata:local_oauth:auth_codes:id_token'] = 'toke used with authorization code, to identitfy the authorized that was authenicated';

$string['privacy:metadata:local_oauth:access_tokens'] = 'Oauth access_tokens';
$string['privacy:metadata:local_oauth:access_tokens:tableexplanation'] = 'Stores valid access tokens.';
$string['privacy:metadata:local_oauth:access_tokens:id'] = 'Id of access_token';
$string['privacy:metadata:local_oauth:access_tokens:access_token'] = 'access token itself';
$string['privacy:metadata:local_oauth:access_tokens:client_id'] = 'Id of client for which access token was generated';
$string['privacy:metadata:local_oauth:access_tokens:user_id'] = 'Id of user attached to this access token';
$string['privacy:metadata:local_oauth:access_tokens:expires'] = 'How long access token is valid';
$string['privacy:metadata:local_oauth:access_tokens:scope'] = 'Scopes in which the access token can be used';

$string['privacy:metadata:oauth_client'] = 'OAuth2/Openid-Connect Client.';
$string['privacy:metadata:oauth_client:family_name'] = 'If scope contains profile, family_name is send to oauth_client';
$string['privacy:metadata:oauth_client:given_name'] = 'If scope contains profile, given_name is send to oauth_client';
$string['privacy:metadata:oauth_client:middle_name'] = 'If scope contains profile, middle_name is send to oauth_client';
$string['privacy:metadata:oauth_client:nickname'] = 'If scope contains profile, nickname is send to oauth_client';
$string['privacy:metadata:oauth_client:preferred_username'] = 'If scope contains profile, preferred_username is send to oauth_client';
$string['privacy:metadata:oauth_client:profile'] = 'If scope contains profile, link to profile page is send to oauth_client';
$string['privacy:metadata:oauth_client:picture'] = 'If scope contains profile, link to profile picture is send to oauth_client';
$string['privacy:metadata:oauth_client:zoneinfo'] = 'If scope contains profile, timezone of the user is send to oauth_client';
$string['privacy:metadata:oauth_client:updated_at'] = 'If scope contains profile, when profile was last update is send to oauth_client';
$string['privacy:metadata:oauth_client:email'] = 'If scope contains email, users email is send to oauth_client';
$string['privacy:metadata:oauth_client:phone_number'] = 'If scope contains phone_number , users phone_number is send to oauth_client';
$string['privacy:metadata:oauth_client:street_address'] = 'If scope contains address, users street_address is send to oauth_client';
$string['privacy:metadata:oauth_client:locality'] = 'If scope contains address, users locality/city is send to oauth_client';
$string['privacy:metadata:oauth_client:country'] = 'If scope contains address, users country is send to oauth_client';
$string['privacy:metadata:oauth_client:enrolments'] = 'If scope contains enrolments, users enrolment as a list of course short names is send to oauth_client';

$string['redirect_uri'] = 'Redirect URL';
$string['redirect_uri_help'] = 'URI where to redirect after login.';
$string['refresh_token_explanation'] = 'let you refresh access token';
$string['saveok'] = 'Client successfully saved';
$string['scope'] = 'Scope';
$string['scope_address'] = "address";
$string['scope_email'] = "email";
$string['scope_enrolments'] = "enrolments";
$string['scope_openid'] = "openid";
$string['scope_phone'] = "phne";
$string['scope_profile'] = "profile";
$string['settings'] = 'OAuth provider settings';


$string['user_credentials_explanation'] = 'Lets application login via sending Users password ';
$string['user_id'] = 'User ID';







