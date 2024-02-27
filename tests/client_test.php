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
 * @copyright   2024 Rens Sikma <r.sikma@atcomping.nl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_oauth;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/oauth/vendor/autoload.php');
require_once($CFG->dirroot.'/local/oauth/classes/storage/moodle.php');
/*
 * @coversDefaultClass \local_oauth\client
 */
class client_test extends \advanced_testcase {
    /*
     * TeST
     * @covers ::execute
     */
    public function test_constructor() {
        global $DB;
        $storage = new \local_oauth\storage\moodle([]);

        $this->resetAfterTest(true);

        // Test if return null if client does not exist.
        $this->assertNull(client::get_client_by_id(0));

        $client1 = new client('client', 'http://localhost', ['authorization_code'], ['openid'], "0", 0);
        // Test has no id until stored.
        $this->assertNull($client1->id());
        $client1->store();

        $record1 = $DB->get_record('oauth_clients', ['client_id' => 'client']);
        $client2 = client::get_client_by_id($record1->id);

        // Test stored client has id.
        $this->assertNotNull($client2->id());

        $props1 = get_object_vars($client1);
        $props2 = get_object_vars($client2);
        unset($props2['id']);
        // Test stored client from db is same as created except id(which is private).
        $this->assertEquals($props1, $props2);
        // Test key creation.
        $this->assertNotNull($storage->getPrivateKey("client"));
        $this->assertNotNull($storage->getPublicKey("client"));

        $client2->delete();

        $record2 = $DB->get_record('oauth_clients', ['client_id' => 'client']);

        // Test client deleted.
        $this->assertFalse($record2);

        // Test key deletion.
        $this->assertFalse($storage->getPrivateKey("client"));
        $this->assertFalse($storage->getPublicKey("client"));
    }
}
