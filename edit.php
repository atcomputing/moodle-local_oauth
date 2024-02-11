<?php
require_once('../../config.php');
// require_once("{$CFG->libdir}/formslib.php");
require_once($CFG->libdir.'/adminlib.php');

require_login();

require_capability('local/oauth:manageclients', context_system::instance());

admin_externalpage_setup('local_oauth_settings');

$id = optional_param('id', '', PARAM_ALPHA);

$bform = new \local_oauth\form\edit_client();

if ($bform->is_cancelled()) {
    redirect(new moodle_url('/local/oauth/view.php'));
} else if ($fromform=$bform->get_data() and confirm_sesskey()) {
    //get values
    $client = new stdClass();
    $client->redirect_uri = $fromform->redirect_uri;
    $client->grant_types = $fromform->grant_types;
    $client->scope = $fromform->scope;
    $client->user_id = $fromform->user_id ? $fromform->user_id :'';
    $client->use_email_aliases = isset($fromform->use_email_aliases) ? 1 : 0;
    $client->no_confirmation = isset($fromform->no_confirmation) ? 1 : 0;

    //do save
    if (!isset($client_edit)) {
        $client->client_id = $fromform->client_id;
        $client->client_secret = local_oauth\client::generate_secret();
        if (!$DB->insert_record('oauth_clients', $client)) {
            print_error('insert_error', 'local_oauth');
        }
        local_oauth\client::generate_key($fromform->client_id);
    } else {
        $client->id = $client_edit->id;
        if (!$DB->update_record('oauth_clients', $client)) {
            print_error('update_error', 'local_oauth');
        }
    }
    redirect(new moodle_url('/local/oauth/view.php'));
}

$form = new stdClass();
//set values
if (isset($client_edit)) {
    $form->client_id           = $client_edit->client_id;
    $form->redirect_uri        = $client_edit->redirect_uri;
    $form->grant_types         = $client_edit->grant_types;
    $form->scope               = $client_edit->scope;
    $form->user_id             = $client_edit->user_id;
    $form->use_email_aliases   = $client_edit->use_email_aliases;
    $form->no_confirmation     = $client_edit->no_confirmation;
} else {
    $form->client_id           = "";
    $form->redirect_uri        = "";
    $form->grant_types         = "authorization_code";
    $form->scope               = "openid";
    $form->user_id             = "0";
    $form->no_confirmation     = 1;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_oauth'));
$bform->set_data($form);
$bform->display();

echo $OUTPUT->footer();
