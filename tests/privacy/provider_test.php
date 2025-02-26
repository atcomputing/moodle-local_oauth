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
 * Privacy provider tests.
 *
 * @package    local_oauth
 * @copyright   2025 AT Computing
 * @author      Rens Sikma <r.sikma@atcomping.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_oauth\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use local_oauth\privacy\provider;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/oauth/lib.php');

/**
 * Privacy provider tests class.
 */
final class provider_test extends \core_privacy\tests\provider_testcase {
    /**
     * Test for provider::get_metadata().
     *
     * @covers ::get_metadata
     */
    public function test_get_metadata(): void {
        $collection = new collection('local_oauth');
        $newcollection = provider::get_metadata($collection);
        $itemcollection = $newcollection->get_collection();
        $this->assertCount(5, $itemcollection);
        $itemnames = array_map(fn($item) =>$item->get_name(), $itemcollection);
        $this->assertContains("local_oauth_user_auth_scopes", $itemnames);
        $this->assertContains("local_oauth_refresh_tokens", $itemnames);
        $this->assertContains("local_oauth_auth_codes", $itemnames);
        $this->assertContains("local_oauth_access_tokens", $itemnames);
        $this->assertContains("oauth_client", $itemnames);

        // No test for all attributes, I believe those will work.
    }

    /**
     * Test for provider::get_contexts_for_userid().
     *
     * @covers ::get_contexts_for_userid
     */
    public function test_get_contexts_for_userid(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();

        // Check the contexts supplied are correct.
        $contextlist = provider::get_contexts_for_userid($user->id);
        $this->assertCount(1, $contextlist);
    }

    /**
     * Test for provider::test_get_users_in_context()
     *
     * @covers ::get_users_in_context
     */
    public function test_get_users_in_context_empty(): void {
        $this->resetAfterTest();
        $component = 'local_oauth';
        $context = \context_system::instance();

        $userlist = new \core_privacy\local\request\userlist($context, $component);
        provider::get_users_in_context($userlist);
    }

    /**
     * Test for provider::test_get_users_in_context()
     *
     * @covers ::get_users_in_context
     */
    public function test_get_users_in_context(): void {
        $this->resetAfterTest();
        $component = 'local_oauth';

        $user = $this->getDataGenerator()->create_user();
        $context = \context_user::instance($user->id);

        $userlist = new \core_privacy\local\request\userlist($context, $component);

        $storage = new \local_oauth\storage_moodle([]);
        $storage->setAccessToken("dummy_access", "dummy_client", $user->id, 1000);
        provider::get_users_in_context($userlist);
        $this->assertEquals([$user->id], $userlist->get_userIds());
    }

    /**
     * Test that user data is exported correctly.
     *
     * @covers ::export_user_data
     */
    public function test_export_user_data(): void {
        $this->resetAfterTest();

        $component = 'local_oauth';

        $client = "dummy_client";
        $scope = "dummy_scopes";

        // Create users which will make submissions.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $context = \context_user::instance($user->id);

        // Fill local_oauth user data in database.
        $this->setup_test_scenario_data($user->id);

        // Export data.
        $writer = \core_privacy\local\request\writer::with_context($context);

        // Assert correct output.
        $this->assertFalse($writer->has_any_data());
        $this->export_context_data_for_user($user->id, $context, $component);
        $this->assertTrue($writer->has_any_data());

        $subcontext = [
            get_string('plugin', 'local_oauth'),
            get_string('pluginname', 'local_oauth'),
            $client,
            $scope,
        ];
        $newsubcontext = array_merge(
            $subcontext,
            [get_string('privacy:metadata:local_oauth:auth_scopes', 'local_oauth')]
        );
        $data = $writer->get_data($newsubcontext);
        $this->assertObjectHasProperty('id', $data);
        $this->assertObjectHasProperty('client_id', $data);
        $this->assertEquals($data->user_id, $user->id);
        $this->assertObjectHasProperty('scope', $data);

        $newsubcontext = array_merge(
            $subcontext,
            [get_string('privacy:metadata:local_oauth:refresh_tokens', 'local_oauth', 'local_oauth')]
        );
        $data = $writer->get_data($newsubcontext);

        $this->assertObjectHasProperty('id', $data);
        $this->assertObjectHasProperty('refresh_token', $data);
        $this->assertObjectHasProperty('client_id', $data);
        $this->assertEquals($data->user_id, $user->id);
        $this->assertObjectHasProperty('expires', $data);
        $this->assertObjectHasProperty('scope', $data);

        $newsubcontext = array_merge(
            $subcontext,
            [get_string('privacy:metadata:local_oauth:auth_codes', 'local_oauth', 'local_oauth')]
        );
        $data = $writer->get_data($newsubcontext);
        $this->assertObjectHasProperty('id', $data);
        $this->assertObjectHasProperty('authorization_code', $data);
        $this->assertObjectHasProperty('client_id', $data);
        $this->assertEquals($data->user_id, $user->id);
        $this->assertObjectHasProperty('redirect_uri', $data);
        $this->assertObjectHasProperty('expires', $data);
        $this->assertObjectHasProperty('scope', $data);
        $this->assertObjectHasProperty('id_token', $data);

        $newsubcontext = array_merge(
            $subcontext,
            [get_string('privacy:metadata:local_oauth:access_tokens', 'local_oauth', 'local_oauth')]
        );
        $data = $writer->get_data($newsubcontext);
        $this->assertObjectHasProperty('id', $data);
        $this->assertObjectHasProperty('access_token', $data);
        $this->assertObjectHasProperty('client_id', $data);
        $this->assertEquals($data->user_id, $user->id);
        $this->assertObjectHasProperty('expires', $data);
        $this->assertObjectHasProperty('scope', $data);
    }

    /**
     * Test that data for users in approved userlist is deleted.
     *
     * @covers ::delete_data_for_users
     */
    public function test_delete_data_for_users(): void {
        $this->resetAfterTest();
        $component = 'local_oauth';

        // Create users1.
        $user1 = $this->getDataGenerator()->create_user();
        $this->setUser($user1);
        $usercontext1 = \context_user::instance($user1->id);
        // Create list of users with a related user data in usercontext1.
        $userlist1 = new \core_privacy\local\request\userlist($usercontext1, $component);

        // Create a user2.
        $user2 = $this->getDataGenerator()->create_user();
        $this->setUser($user2);
        $usercontext2 = \context_user::instance($user2->id);
        // Create list of users with a related user data in usercontext2.
        $userlist2 = new \core_privacy\local\request\userlist($usercontext2, $component);

        // Create repository_instances record for user1.
        $this->setup_test_scenario_data($user1->id);
        // Create repository_instances record for user2.
        $this->setup_test_scenario_data($user2->id);

        // Ensure the user list for usercontext1 contains user1.
        provider::get_users_in_context($userlist1);
        $this->assertCount(1, $userlist1);
        // Ensure the user list for usercontext2 contains user2.
        provider::get_users_in_context($userlist2);
        $this->assertCount(1, $userlist2);

        // Convert $userlist1 into an approved_contextlist.
        $approvedlist = new approved_userlist($usercontext1, $component, $userlist1->get_userids());

        // Delete using delete_data_for_user.
        provider::delete_data_for_users($approvedlist);

        // Re-fetch users in the usercontext1 - The user list should now be empty.
        $userlist1 = new \core_privacy\local\request\userlist($usercontext1, $component);
        provider::get_users_in_context($userlist1);
        $this->assertCount(0, $userlist1);
        // Re-fetch users in the usercontext2 - The user list should not be empty.
        $userlist2 = new \core_privacy\local\request\userlist($usercontext2, $component);
        provider::get_users_in_context($userlist2);
        $this->assertCount(1, $userlist2);

        // User data should be only removed in the user context.
        $systemcontext = \context_system::instance();
        // Add userlist2 to the approved user list in the system context.
        $approvedlist = new approved_userlist($systemcontext, $component, $userlist2->get_userids());
        // Delete user1 data using delete_data_for_user.
        provider::delete_data_for_users($approvedlist);
        // Re-fetch users in usercontext2 - The user list should not be empty (user2).
        $userlist2 = new \core_privacy\local\request\userlist($usercontext2, $component);
        provider::get_users_in_context($userlist2);
        $this->assertCount(1, $userlist2);
    }

    /**
     * Helper function to setup repository_instances records for testing a specific user.
     *
     * @param int $userid       The Id of the User used for testing.
     */
    private function setup_test_scenario_data($userid) {

        $client = "dummy_client";
        $scope = "dummy_scopes";

        // Test will fail if not all refreshtoken are unique.
        $refreshtoken = bin2hex(openssl_random_pseudo_bytes(10));

        // Fill local_oauth_access_tokens table.
        $storage = new \local_oauth\storage_moodle([]);
        $storage->setRefreshToken($refreshtoken, $client, $userid, 1000, $scope);
        $storage->setAuthorizationCode("dummy_code", $client, $userid, "dummy_redirect", 1000, $scope, "dummy_id");
        $storage->setAccessToken("dummy_access", $client, $userid, 1000, $scope);

        // Fill local_oauth_user_auth_scopes table.
        authorize_user_scope($userid, $client, $scope);
    }
}
