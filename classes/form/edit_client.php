<?php

namespace local_oauth\form;

use moodleform;

require_once("$CFG->libdir/formslib.php");

class edit_client extends moodleform {

    function definition() {
        global $CFG;
        $bform =& $this->_form;
        $bform->addElement('hidden', 'action', 'add');
        $bform->setType('action', PARAM_ACTION);

        // Adding the "general" fieldset, where all the common settings are showed
        $bform->addElement('header', 'general', get_string('general', 'form'));

        $bform->addElement('text', 'client_id', get_string('client_id', 'local_oauth'), ['maxlength' => 80, 'size' => 45]);
        $bform->addRule('client_id', null, 'required', null, 'client');
        $bform->setType('client_id', PARAM_TEXT);
        $bform->addHelpButton('client_id', 'client_id', 'local_oauth');

        $action = optional_param('action', false, PARAM_TEXT);

        if ($action == 'edit') {
            $id = required_param('id', PARAM_TEXT);
            $bform->addElement('hidden', 'id', $id);
            $bform->setType('id', PARAM_INT);
            $bform->hardFreeze('client_id');
        }

        $bform->addElement('text', 'redirect_uri', get_string('redirect_uri', 'local_oauth'), ['maxlength' => 1333, 'size' => 45]);
        $bform->addRule('redirect_uri', null, 'required', null, 'client');
        $bform->setType('redirect_uri', PARAM_URL);
        $bform->addHelpButton('redirect_uri', 'redirect_uri', 'local_oauth');

        //-------------------------------------------------------------------------------
        // Adding the rest of settings, spreading all them into this fieldset
        $bform->addElement('header', 'othersettings', get_string('othersettings', 'form'));
        $bform->setExpanded('othersettings', false);
        $bform->addElement('text', 'grant_types', get_string('grant_types', 'local_oauth'), ['maxlength' => 80, 'size' => 45]);
        $bform->setType('grant_types', PARAM_TEXT);

        $bform->addElement('text', 'scope', get_string('scope', 'local_oauth'), ['maxlength' => 1333, 'size' => 45]);
        $bform->setType('scope', PARAM_TEXT);

        $bform->addElement('text', 'user_id', get_string('user_id', 'local_oauth'), ['maxlength' => 80, 'size' => 45]);
        $bform->setType('user_id', PARAM_INT);

        $bform->addElement('checkbox', 'use_email_aliases', get_string('use_email_aliases', 'local_oauth'));
        $bform->addHelpButton('use_email_aliases', 'use_email_aliases', 'local_oauth');
        $bform->setType('use_email_aliases', PARAM_BOOL);

        $bform->addElement('checkbox', 'no_confirmation', get_string('no_confirmation', 'local_oauth'));
        $bform->addHelpButton('no_confirmation', 'no_confirmation', 'local_oauth');
        $bform->setType('no_confirmation', PARAM_BOOL);

        $this->add_action_buttons();

    }

    function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        if(!isset($data['client_id'])){
            return $errors;
        }

        if ($DB->record_exists('oauth_clients', ['client_id' => $data['client_id']])) {
            $errors['client_id'] = get_string('client_id_existing_error', 'local_oauth');
        }

        return $errors;
    }
}
