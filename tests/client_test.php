<?php

namespace local_oauth;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/oauth/vendor/autoload.php');
require_once($CFG->dirroot.'/local/oauth/classes/storage/moodle.php');

class client_test extends \advanced_testcase {

    public function test_constructor()
    {
        global $DB;
        $storage = new \local_oauth\storage\moodle([]);

        $this->resetAfterTest(true);

        // test if return null if client does not exist
        $this->assertNull(client::get_client_by_id(0));

        $client1 = new client('client', 'http://localhost', 'authorization_code', 'openid', "0", 0, 0);
        # test has no id until stored
        $this->assertNull($client1->id());
        $client1->store();

        $record1 = $DB->get_record('oauth_clients',['client_id'=> 'client']);
        $client2 = client::get_client_by_id($record1->id);

        # test stored client has id
        $this->assertNotNull($client2->id());

        $props1 = get_object_vars($client1);
        $props2 = get_object_vars($client2);
        # Test stored client from db is same as created except id(which is private)
        $this->assertEquals($props1,$props2);
        // test key creation
        $this->assertNotNull($storage->getPrivateKey("client"));
        $this->assertNotNull($storage->getPublicKey("client"));

        $client2->delete();

        $record2 = $DB->get_record('oauth_clients',['client_id'=> 'client']);

        // test client deleted
        $this->assertFalse($record2);

        // test key deletion
        $this->assertFalse($storage->getPrivateKey("client"));
        $this->assertFalse($storage->getPublicKey("client"));
    }
}
