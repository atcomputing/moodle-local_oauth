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
 * oauth2/openidconnect login page
 *
 * @package    local_oauth
 * @subpackage oauth
 * @copyright  2014 onwards Pau Ferrer Ocaña
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.Files.RequireLogin.Missing

require('../../config.php');
require_once(__DIR__ . '/lib.php');
require_once('vendor/autoload.php');

$clientid = required_param('client_id', PARAM_RAW);
$responsetype = required_param('response_type', PARAM_RAW);
$scope = optional_param('scope', false, PARAM_TEXT);
$state = optional_param('state', false, PARAM_TEXT);
$nonce = optional_param('nonce', false, PARAM_TEXT);
$url = $CFG->wwwroot . '/local/oauth/login.php?client_id=' . $clientidi . '&response_type=' . $responsetype;

if ($scope) {
    $url .= '&scope=' . $scope;
}

if ($state) {
    $url .= '&state=' . $state;
}

if ($nonce) {
    $url .= '&nonce=' . $nonce;
}

$PAGE->set_url($CFG->wwwroot . '/local/oauth/login.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');

if (isloggedin() && !isguestuser()) {
    // Include our OAuth2 Server object.
    $server = new \local_oauth\server();

    $request = OAuth2\Request::createFromGlobals();
    $response = new OAuth2\Response();

    if (!$server->validateAuthorizeRequest($request, $response)) {
        $logparams = ['objectid' => $USER->id, 'other' => ['clientid' => $clientid, 'scope' => $scope]];
        $event = \local_oauth\event\user_not_granted::create($logparams);
        $event->trigger();

        $response->send();
        die();
    }

    $isauthorized = get_authorization_from_form($url, $clientid, $scope);

    $logparams = ['objectid' => $USER->id, 'other' => ['clientid' => $clientid, 'scope' => $scope]];
    if ($isauthorized) {
        $event = \local_oauth\event\user_granted::create($logparams);
    } else {
        $event = \local_oauth\event\user_not_granted::create($logparams);
    }
    $event->trigger();

    // Print the authorization code if the user has authorized your client.
    $server->handleAuthorizeRequest($request, $response, $isauthorized, $USER->id);
    $response->send();
} else {
    $SESSION->wantsurl = $url;
    redirect(new moodle_url('/login/index.php'));
}
