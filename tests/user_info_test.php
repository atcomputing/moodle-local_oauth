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
 * @copyright   2024 Rens Sikma <r.sikma@atcomputing.nl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
namespace local_oauth;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/oauth/vendor/autoload.php');

/**
 * test user infor response
 */
final class user_info_test extends \advanced_testcase {

    /**
     * test userinfor response after authentication with all scopes
     * @covers \local\oauth::
     */
    public function test_userinfo(): void {

        $this->resetAfterTest(true);

        $client = new client('client',
            'http://localhost',
            ['authorization_code'],
            ['openid', 'profile', 'email', 'address', 'phone', 'enrolments'],
            "0",
            0);
        $client->store();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $storage = new \local_oauth\storage_moodle([]);

        $accesstoken = new \OAuth2\ResponseType\AccessToken($storage);
        $token = $accesstoken->createAccessToken("client", $user->id, "openid profile email address phone enrolments");
        $request = new \OAuth2\Request([], ['access_token' => $token['access_token']], [], [], [], ['REQUEST_METHOD' => 'post']);

        $server = new \local_oauth\server();
        $response = new \OAuth2\Response();
        $server->handleUserInfoRequest($request, $response);

        $userinfo = $response->getParameters();
        $this->assertEquals($user->username, $userinfo['preferred_username']);
        $this->assertEquals($user->phone1, $userinfo['phone_number']);
        $this->assertEquals($user->country, $userinfo['address']['country']);
        $this->assertEquals($user->email, $userinfo['email']);
        $this->assertEquals([$course->shortname], $userinfo['enrolments']);
    }
}
