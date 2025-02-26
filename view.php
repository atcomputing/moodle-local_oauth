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
 * @copyright   2024 AT Computing
 * @author      Rens Sikma <r.sikma@atcomping.nl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

require_capability('local/oauth:manageclients', context_system::instance());

admin_externalpage_setup('local_oauth_settings');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_oauth'));

$action  = optional_param('action', '', PARAM_ALPHA);
if (!empty($action)) {
    $id = required_param('id', PARAM_TEXT);
    $client = local_oauth\client::get_client_by_id($id);
}
if ($action == 'del') {
    // Ask to delete.
    if (!$client) {
        echo $OUTPUT->notification(get_string('client_not_exists', 'local_oauth'));
    }

    $confirm = new moodle_url(
        $PAGE->url,
        ['action' => "delconfirmed", 'id' => $id, 'sesskey' => sesskey()]
    );
    echo $OUTPUT->confirm(
        get_string('confirmdeletestr', 'local_oauth', $client->clientid),
        $confirm,
        $PAGE->url,
    );
} else if ($action == 'delconfirmed') {
    require_sesskey();
    $client->delete();
    echo $OUTPUT->notification(get_string('delok', 'local_oauth'), 'notifysuccess');
}
$clients = $DB->get_records('local_oauth_clients');

if (!empty($clients)) {
    $table = new html_table();
    $table->class = 'generaltable generalbox';
    $table->head = [
        get_string('client_id', 'local_oauth'),
        get_string('client_secret', 'local_oauth'),
        get_string('grant_types', 'local_oauth'),
        get_string('scope', 'local_oauth'),
        get_string('user_id', 'local_oauth'),
        get_string('actions'),
    ];
    $table->align = ['left', 'left', 'center', 'center', 'center', 'center', 'center'];

    foreach ($clients as $client) {
        $row = [];
        $row[] = $client->client_id;
        $row[] = $client->client_secret;
        $row[] = $client->grant_types;
        $row[] = $client->scope;
        $row[] = $client->user_id;

        $editlink = $OUTPUT->action_icon(
            new \moodle_url('edit.php', ['id' => $client->id]),
            new \pix_icon('t/edit', get_string('edit'))
        );
        $deletelink = $OUTPUT->action_icon(
            new \moodle_url('view.php', ['id' => $client->id, 'action' => 'del']),
            new \pix_icon('i/trash', get_string('delete'))
        );
        $row[] = $editlink . $deletelink;

        $table->data[] = $row;
    }
    echo html_writer::table($table);
}

echo '<div>';
echo '<a href="edit.php" class="btn btn-primary">' . get_string('addclient', 'local_oauth') . '</a>';
echo '</div>';

echo $OUTPUT->footer();
