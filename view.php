<?php
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();

require_capability('local/oauth:manageclients', context_system::instance());

admin_externalpage_setup('local_oauth_settings');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_oauth'));

$action  = optional_param('action', '', PARAM_ALPHA);

if ($action == 'del'){
    // Get values
    $confirm   = optional_param('confirm', 0, PARAM_INT);
    $id = required_param('id', PARAM_TEXT);

    // Do delete
    if (empty($confirm)) {
        if (!$client_edit = $DB->get_record('oauth_clients', ['id' => $id])) {
            echo $OUTPUT->notification(get_string('client_not_exists', 'local_oauth'));
        }
        echo '<p>'.get_string('confirmdeletestr', 'local_oauth', $client_edit->client_id).'</p>
            <form action="view.php" method="GET">
                <input type="hidden" name="action" value="del" />
                <input type="hidden" name="confirm" value="1" />
                <input type="hidden" name="id" value="'.$id.'" />
                <input type="submit" value="'.get_string('confirm').'" /> <input type="button" value="'.get_string('cancel').'" onclick="javascript:history.back();" />
            </form>';
    } else {
        if (!$DB->delete_records('oauth_clients', ['id' => $id])) {
            print_error('delete_error', 'local_oauth');
        }
        echo $OUTPUT->notification(get_string('delok', 'local_oauth'), 'notifysuccess');
    }
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
        $row[] = "<a href=\"edit.php?id=".$client->id."\">".get_string('edit')."</a> | <a href=\"view.php?action=del&id=".$client->id."\">".get_string('delete')."</a>";
        $table->data[] = $row;
    }
    echo html_writer::table($table);
}

echo '<div>';
echo '<a href="edit.php" class="btn btn-primary">'.get_string('addclient', 'local_oauth').'</a>';
echo '</div>';

echo $OUTPUT->footer();
