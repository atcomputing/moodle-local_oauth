<?php
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

require_login();

require_capability('local/oauth:manageclients', context_system::instance());

admin_externalpage_setup('local_oauth_settings');

$id = optional_param('id',null, PARAM_TEXT);
$client;
if (isset($id)) {
    $client = local_oauth\client::get_client_by_id($id);
    if (!$client){
        echo $OUTPUT->notification(get_string('client_not_exists', 'local_oauth'));
    }
} else{
    $client = new local_oauth\client( "", "", ["authorization_code"], ["openid", "profile"], "0", 0, 0);
}
$form = new \local_oauth\form\edit_client();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/oauth/view.php'));
} else if ($fromform=$form->get_data() and confirm_sesskey()) {
    if (isset($fromform->client_id)){
        $client->client_id = $fromform->client_id;
    }
    echo 'before:';
    var_export($fromform->user_id);
    echo ':after';
    $client->redirect_uri = $fromform->redirect_uri;
    $client->grant_types = $fromform->grant_types;
    $client->scope = $fromform->scope;
    $client->user_id = !empty($fromform->user_id) ? $fromform->user_id: 0;
    $client->no_confirmation = isset($fromform->no_confirmation) ? 1 : 0;
    $client->store();
    redirect(new moodle_url('/local/oauth/view.php'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_oauth'));

$form->set_data($client);
$form->display();

echo $OUTPUT->footer();
