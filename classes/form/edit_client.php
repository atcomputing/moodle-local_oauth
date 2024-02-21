<?php

namespace local_oauth\form;

use moodleform;

require_once("$CFG->libdir/formslib.php");

class edit_client extends moodleform {

    function definition() {
        global $CFG;
        $server = new \local_oauth\server();
        $bform =& $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed
        $bform->addElement('header', 'general', get_string('general', 'form'));

        $bform->addElement('text', 'client_id', get_string('client_id', 'local_oauth'), ['maxlength' => 80, 'size' => 45]);
        $bform->addRule('client_id', null, 'required', null, 'client');
        $bform->setType('client_id', PARAM_TEXT);
        $bform->addHelpButton('client_id', 'client_id', 'local_oauth');

        $id = optional_param('id',null, PARAM_TEXT);
        if (isset($id)){
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
        // $bform->addElement('text', 'grant_types', get_string('grant_types', 'local_oauth'), ['maxlength' => 80, 'size' => 45]);
        // $bform->setType('grant_types', PARAM_TEXT);
        $grant_options = $server->getGrantTypes();
        foreach($grant_options as $key=>$value) {
            $grant_options[$key] = $key . ": " . get_string($key."_explanation", "local_oauth");
        }
        $select = $bform->addElement('select', 'grant_types', get_string('grant_types', 'local_oauth'), $grant_options);
        $bform->addHelpButton('grant_types', 'grant_types', 'local_oauth');
        $select->setMultiple(true);

        $scopes = $server->supported_scopes();
        $scope_options = [];
        foreach($scopes as $scope) {
            $scope_options[$scope] = $scope;
            // TODO add explantions, list included claims via:  get_string($scope."_explanation", "local_oauth");
        }
        $select = $bform->addElement('select', 'scope', get_string('scope', 'local_oauth'), $scope_options);
        $select->setMultiple(true);
        // $bform->addElement('text', 'scope', get_string('scope', 'local_oauth'), ['maxlength' => 1333, 'size' => 45]);
        // $bform->setType('scope', PARAM_TEXT);

        // TODO this shouldn't hide user_id if client_credentials + other is selected
        $bform->hideIf('user_id', 'grant_types[]', 'not in', 'client_credentials');
        $options = [
            // 'multiple' => true,
            'ajax' => 'core_search/form-search-user-selector',
            'noselectionstring' => get_string('username')
        ];
        $bform->addElement('autocomplete', 'user_id', get_string('user_id', 'local_oauth'), [], $options);
        // $bform->addElement('text', 'user_id', get_string('user_id', 'local_oauth'), ['maxlength' => 80, 'size' => 45]);
        // $bform->setType('user_id', PARAM_INT);

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
        # TODO valide redirect url
    }
}
