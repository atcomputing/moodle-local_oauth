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


// TODO include test from vendor/bshaffer/oauth2-server-php/test for storage.

class storage_moodle_test extends \advanced_testcase {

    /**
     * @covers \local\oauth\storage_moodle::get_userDetails
     */
    public function test_user() {
        $this->resetAfterTest(true);
        $storage = new storage_moodle([]);
        $user = $this->getDataGenerator()->create_user([]);

        $details = $storage->getUserDetails($user->username);
        $this->assertNotEmpty($details);
    }
}
