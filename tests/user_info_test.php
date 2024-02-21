<?php
namespace local_oauth;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/oauth/vendor/autoload.php');
require_once($CFG->dirroot.'/local/oauth/classes/storage/moodle.php');
require_once($CFG->dirroot.'/local/oauth/user_info.php');

class user_info_test extends \advanced_testcase {

    public function test_userinfo(){

        $this->resetAfterTest(true);

        $client = new client('client', 'http://localhost', ['authorization_code'], ['openid', 'profile', 'email', 'address', 'phone', 'enrolments'], "0", 0, 0);
        $client->store();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $storage = new \local_oauth\storage\moodle([]);

        $access_token = new \OAuth2\ResponseType\AccessToken($storage);
        $token = $access_token->createAccessToken("client", $user->id, "openid profile email address phone enrolments");
        $request = new \OAuth2\Request([],['access_token' => $token['access_token']],[],[],[], ['REQUEST_METHOD' => 'post']);

        $server = new \local_oauth\server();
        $response = new \OAuth2\Response();
        $server->handleUserInfoRequest($request,$response);

        $user_info = $response->getParameters();
        $this->assertEquals($user->username, $user_info['preferred_username']);
        $this->assertEquals($user->phone1, $user_info['phone_number']);
        $this->assertEquals($user->country, $user_info['address']['country']);
        $this->assertEquals($user->email, $user_info['email']);
        $this->assertEquals([$course->shortname], $user_info['enrolments']);
        // var_export($user_info);
    }
}
