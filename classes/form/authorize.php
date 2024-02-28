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
 * local_oauth user granted event.
 *
 * @package    local_oauth
 * @copyright
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_oauth\form;

use moodleform;

class authorize extends moodleform {

    public function definition() {
        $mform =& $this->_form;

        $clientid = required_param('client_id', PARAM_RAW);

        $text = get_string('auth_question', 'local_oauth', $clientid).'<br />';
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
