<?php
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();

require_capability('local/oauth:manageclients', context_system::instance());

admin_externalpage_setup('local_oauth_settings');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_oauth'));

// Get values
$action  = optional_param('action', '', PARAM_ALPHA);
if (!empty($action)){
    $id = required_param('id', PARAM_TEXT);
    $client = local_oauth\client::get_client_by_id($id);
}
if ($action == 'del'){

    // ask to delete
    if (!$client){
        echo $OUTPUT->notification(get_string('client_not_exists', 'local_oauth'));
    }

    $confirm  = new moodle_url($PAGE->url, ['action'=>"delconfirmed", 'id' => $id]);
    echo $OUTPUT->confirm(
        get_string('confirmdeletestr', 'local_oauth', $client->client_id),
        $confirm,
        $PAGE->url,
    );
}else if ($action == 'delconfirmed') {
    $client->delete();
    echo $OUTPUT->notification(get_string('delok', 'local_oauth'), 'notifysuccess');
}
$clients = $DB->get_records('oauth_clients');

if (!empty($clients)) {
    $table = new html_table();
    $table->class = 'generaltable generalbox';
    $table->head = [
        get_string('client_id', 'local_oauth'),
        get_string('client_secret', 'local_oauth'),
        get_string('grant_types', 'local_oauth'),
        get_string('scope', 'local_oauth'),
        get_string('user_id', 'local_oauth'),
        get_string('actions')
    ];
    $table->align = ['left', 'left', 'center', 'center', 'center', 'center', 'center'];

    foreach ($clients as $client) {
        $row = [];
        $row[] = $client->client_id;
        $row[] = $client->client_secret;
        $row[] = $client->grant_types;
        $row[] = $client->scope;
        $row[] = $client->user_id;

        $edit_link = $OUTPUT->action_icon(
            new \moodle_url('edit.php', ['id'=>$client->id]),
            new \pix_icon('t/edit', get_string('edit'))) ;
        $delete_link= $OUTPUT->action_icon(
            new \moodle_url('view.php', ['id'=>$client->id, 'action' => 'del']),
            new \pix_icon('i/trash', get_string('delete'))) ;
        $row[] = $edit_link . $delete_link;

        $table->data[] = $row;
    }
    echo html_writer::table($table);
}

echo '<div>';
echo '<a href="edit.php" class="btn btn-primary">'.get_string('addclient', 'local_oauth').'</a>';
echo '</div>';

echo $OUTPUT->footer();
