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
 * @copyright
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// TODO can we decouple confirmation page/form from logic.
/**
 * If user is authorized with request scopes.
 * If request for first time, and confirmation is required. render confirmation pagekkk
 * @return Boolean if user is autrhoized by with these scopes
 */
function get_authorization_from_form($url, $clientid, $scope = false) {
    global $CFG, $OUTPUT, $USER, $DB;
    require_once("{$CFG->libdir}/formslib.php");

    if (is_scope_authorized_by_user($USER->id, $clientid, $scope)) {
        return true;
    }

    $client = $DB->get_record('local_oauth_clients', ['client_id' => $clientid]);

    if ($client && $client->no_confirmation) {
        authorize_user_scope($USER->id, $clientid, $scope);
        return true;
    }

    $mform = new \local_oauth\form\authorize($url);
    if ($mform->is_cancelled()) {
        return false;
    } else if ($fromform = $mform->get_data() && confirm_sesskey()) {
        authorize_user_scope($USER->id, $clientid, $scope);
        return true;
    }

    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
    die();
}
/**
 * Test if user_id is already authorized for this scope.
 **/
function is_scope_authorized_by_user($userid, $clientid, $scope = false) {
    global $DB;
    if (!$scope) {
        $scope = 'login';
    }
    return $DB->record_exists('local_oauth_user_auth_scopes', ['client_id' => $clientid, 'scope' => $scope, 'user_id' => $userid]);
}

/**
 * Store used has/is authorize with these scopes.
 **/
function authorize_user_scope($userid, $clientid, $scope = false) {
    global $DB;
    if (!$scope) {
        $scope = 'login';
    }
    $record = new StdClass();
    $record->client_id = $clientid;
    $record->user_id = $userid;
    $record->scope = $scope;

    $DB->insert_record('local_oauth_user_auth_scopes', $record);
}
