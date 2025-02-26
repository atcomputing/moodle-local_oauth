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
 * Plugin privicy provider
 *
 * @package     local_oauth
 * @copyright   2024 AT Computing
 * @author      Rens Sikma <r.sikma@atcomping.nl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_oauth\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Privacy Subsystem implementation for local_oauth.
 *
 */
class provider implements
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Return the fields which contain personal data.
     *
     * @param collection $collection reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $collection): collection {

        $collection->add_database_table(
            'local_oauth_user_auth_scopes',
            [
                'id' => 'privacy:metadata:local_oauth:auth_scopes:id',
                'client_id' => 'privacy:metadata:local_oauth:auth_scopes:id',
                'user_id' => 'privacy:metadata:local_oauth:auth_scopes:id',
                'scope' => 'privacy:metadata:local_oauth:auth_scopes:id',

            ],
            'privacy:metadata:local_oauth:auth_scopes:tableexplanation'
        );

        $collection->add_database_table(
            'local_oauth_refresh_tokens',
            [
                'id' => 'privacy:metadata:local_oauth:refresh_tokens:id',
                'refresh_token' => 'privacy:metadata:local_oauth:refresh_tokens:refresh_token',
                'client_id' => 'privacy:metadata:local_oauth:refresh_tokens:client_id',
                'user_id' => 'privacy:metadata:local_oauth:refresh_tokens:user_id',
                'expires' => 'privacy:metadata:local_oauth:refresh_tokens:expires',
                'scope' => 'privacy:metadata:local_oauth:refresh_tokens:scope',
            ],
            'privacy:metadata:local_oauth:refresh_tokens:tableexplanation'
        );

        $collection->add_database_table(
            'local_oauth_auth_codes',
            [
                'id' => 'privacy:metadata:local_oauth:auth_codes:id',
                'authorization_code' => 'privacy:metadata:local_oauth:auth_codes:authorization_code',
                'client_id' => 'privacy:metadata:local_oauth:auth_codes:client_id',
                'user_id' => 'privacy:metadata:local_oauth:auth_codes:user_id',
                'redirect_uri' => 'privacy:metadata:local_oauth:auth_codes:redirect_uri',
                'expires' => 'privacy:metadata:local_oauth:auth_codes:expires',
                'scope' => 'privacy:metadata:local_oauth:auth_codes:scope',
                'id_token' => 'privacy:metadata:local_oauth:auth_codes:id_token',
            ],
            'privacy:metadata:local_oauth:auth_codes:tableexplanation'
        );

        $collection->add_database_table(
            'local_oauth_access_tokens',
            [
                'id' => 'privacy:metadata:local_oauth:access_tokens:id',
                'access_token' => 'privacy:metadata:local_oauth:access_tokens:access_token',
                'client_id' => 'privacy:metadata:local_oauth:access_tokens:client_id',
                'user_id' => 'privacy:metadata:local_oauth:access_tokens:user_id',
                'expires' => 'privacy:metadata:local_oauth:access_tokens:expires',
                'scope' => 'privacy:metadata:local_oauth:access_tokens:scope',
            ],
            'privacy:metadata:local_oauth:access_tokens:tableexplanation'
        );

        $collection->add_external_location_link(
            'oauth_client',
            [
                'family_name' => 'privacy:metadata:oauth_client:family_name',
                'given_name' => 'privacy:metadata:oauth_client:given_name',
                'middle_name' => 'privacy:metadata:oauth_client:middle_name',
                'nickname' => 'privacy:metadata:oauth_client:nickname',
                'preferred_username' => 'privacy:metadata:oauth_client:preferred_username',
                'profile' => 'privacy:metadata:oauth_client:profile',
                'picture' => 'privacy:metadata:oauth_client:picture',
                'zoneinfo' => 'privacy:metadata:oauth_client:zoneinfo',
                'updated_at' => 'privacy:metadata:oauth_client:updated_at',
                'email' => 'privacy:metadata:oauth_client:email',
                'phone_number' => 'privacy:metadata:oauth_client:phone_number',
                'street_address' => 'privacy:metadata:oauth_client:street_address',
                'locality ' => 'privacy:metadata:oauth_client:locality',
                'country' => 'privacy:metadata:oauth_client:country',
                'enrolments' => 'privacy:metadata:oauth_client:enrolments',
            ],
            'privacy:metadata:oauth_client'
        );

        // Plugin also access user and enrolment data but does not store or modify it.
        // So we dont have to specify that.

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int           $userid       The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {

        $contextlist = new \core_privacy\local\request\contextlist();
        return $contextlist->add_user_context($userid);
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();

        if (!is_a($context, \context_user::class)) {
            return;
        }
        $userid = $context->instanceid;

        $hasdata = false;

        $hasdata = $hasdata || $DB->record_exists('local_oauth_user_auth_scopes', ['user_id' => $userid]);
        $hasdata = $hasdata || $DB->record_exists('local_oauth_refresh_tokens', ['user_id' => $userid]);
        $hasdata = $hasdata || $DB->record_exists('local_oauth_auth_codes', ['user_id' => $userid]);
        $hasdata = $hasdata || $DB->record_exists('local_oauth_access_tokens', ['user_id' => $userid]);

        if ($hasdata) {
            $userlist->add_user($userid);
        }
    }

        /**
         * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
         *
         * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
         */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $user = $contextlist->get_user();
        $context = \context_user::instance($user->id);
        $subcontext = [
            get_string('plugin', 'local_oauth'),
            get_string('pluginname', 'local_oauth'),
        ];
        $notexportedstr = get_string('privacy:request:notexportedsecurity', 'local_oauth');

        $userauthscopes = $DB->get_records('local_oauth_user_auth_scopes', ['user_id' => $user->id]);
        foreach ($userauthscopes as $scope) {
            $scope->user_id = transform::user($scope->user_id);
            writer::with_context($context)->export_data(array_merge($subcontext, [
                $scope->client_id,
                $scope->scope,
                get_string('privacy:metadata:local_oauth:auth_scopes', 'local_oauth'),
            ]), $scope);
        }

        $refreshtokens = $DB->get_records('local_oauth_refresh_tokens', ['user_id' => $user->id]);
        foreach ($refreshtokens as $token) {
            $token->user_id = transform::user($token->user_id);
            $token->expires = transform::datetime($token->expires);
            $token->refresh_token = $notexportedstr;
            writer::with_context($context)->export_data(array_merge($subcontext, [
                $token->client_id,
                $token->scope,
                get_string('privacy:metadata:local_oauth:refresh_tokens', 'local_oauth'),
            ]), $token);
        }

        $oauthcodes = $DB->get_records('local_oauth_auth_codes', ['user_id' => $user->id]);
        foreach ($oauthcodes as $code) {
            $code->user_id = transform::user($code->user_id);
            $code->expires = transform::datetime($code->expires);
            $code->authorization_code = $notexportedstr;
            writer::with_context($context)->export_data(array_merge($subcontext, [
                $code->client_id,
                $code->scope,
                get_string('privacy:metadata:local_oauth:auth_codes', 'local_oauth'),
            ]), $code);
        }

        $accesstokens = $DB->get_records('local_oauth_access_tokens', ['user_id' => $user->id]);
        foreach ($accesstokens as $token) {
            $token->user_id = transform::user($token->user_id);
            $token->expires = transform::datetime($token->expires);
            $token->access_token = $notexportedstr;
            writer::with_context($context)->export_data(array_merge($subcontext, [
                $token->client_id,
                $token->scope,
                get_string('privacy:metadata:local_oauth:access_tokens', 'local_oauth'),
            ]), $token);
        }
    }


    /**
     * Delete all personal data for all users in the specified context.
     *
     * @param context $context Context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {

        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }
        $userid = $context->instanceid;
        self::delete_user(userid);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {

        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }

        foreach ($userlist->get_userids() as $userid) {
            self::delete_user($userid);
        }
    }

    /**
     * Delete user data in the list of given contexts.
     *
     * @param approved_contextlist $contextlist the list of contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {

        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;

        self::delete_user(userid);
    }

    /**
     * Delete user in database
     *
     * @param int $userid The use to delete local_oauth data from
     */
    private static function delete_user(int $userid) {
        global $DB;
        $DB->delete_records('local_oauth_user_auth_scopes', ['user_id' => $userid]);
        $DB->delete_records('local_oauth_refresh_tokens', ['user_id' => $userid]);
        $DB->delete_records('local_oauth_auth_codes', ['user_id' => $userid]);
        $DB->delete_records('local_oauth_access_tokens', ['user_id' => $userid]);
    }
}
