<?php

namespace local_oauth\form;

use moodleform;

require_once("$CFG->libdir/formslib.php");

class authorize extends moodleform {

    function definition() {
        global $CFG;
        $mform =& $this->_form;

        $client_id = required_param('client_id', PARAM_RAW);

        $text = get_string('auth_question', 'local_oauth', $client_id).'<br />';
        $mform->addElement('html', $text);
        $scope = optional_param('scope', false, PARAM_TEXT);
        if (!empty($scope)) {
            $scopes = explode(' ', $scope);
            $text = get_string('auth_question_desc', 'local_oauth').'<ul>';
            foreach ($scopes as $scope) {
                $text .= '<li>'.get_string('scope_'.$scope, 'local_oauth').'</li>';
            }
            $text .= '</ul>';
        } else {
            $text = get_string('auth_question_login', 'local_oauth');
        }
        $mform->addElement('html', $text);

        $this->add_action_buttons(true, get_string('confirm'));
    }
}
