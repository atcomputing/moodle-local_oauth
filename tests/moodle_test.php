<?php

namespace local_oauth\storage;

defined('MOODLE_INTERNAL') || die();


global $CFG;
require_once($CFG->dirroot.'/local/oauth/classes/storage/moodle.php');

// TODO include test from vendor/bshaffer/oauth2-server-php/test for storage

class moodle_test extends \advanced_testcase {

    public function test_user()
    {
        $this->resetAfterTest(true);
        $storage = new moodle([]);
        $user = $this->getDataGenerator()->create_user([]);

        $details = $storage->getUserDetails($user->username);
        $this->assertNotEmpty($details);
    }
}
