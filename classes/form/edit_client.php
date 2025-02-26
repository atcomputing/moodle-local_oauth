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
 * Plugin form for edit_client
 *
 * @package     local_oauth
 * @copyright   https://github.com/projectestac/moodle-local_oauth
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_oauth\form;

use moodleform;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Form for client options
 */
class edit_client extends moodleform {
    /**
     * Define the fields of the form
     */
    public function definition() {
        $server = new \local_oauth\server();
        $bform =& $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $bform->addElement('header', 'general', get_string('general', 'form'));

        $bform->addElement('text', 'clientid', get_string('client_id', 'local_oauth'), ['maxlength' => 80, 'size' => 45]);
        $bform->addRule('clientid', null, 'required', null, 'client');
        $bform->setType('clientid', PARAM_TEXT);
        $bform->addHelpButton('clientid', 'client_id', 'local_oauth');

        $id = optional_param('id', null, PARAM_TEXT);
        if (isset($id)) {
            $bform->addElement('hidden', 'id', $id);
            $bform->setType('id', PARAM_INT);
            $bform->hardFreeze('clientid');
        }

        $bform->addElement('text', 'redirecturi', get_string('redirect_uri', 'local_oauth'), ['maxlength' => 1333, 'size' => 45]);
        $bform->addRule('redirecturi', null, 'required', null, 'client');
        $bform->setType('redirecturi', PARAM_URL);
        $bform->addHelpButton('redirecturi', 'redirect_uri', 'local_oauth');

        // -------------------------------------------------------------------------------
        // Adding the rest of settings, spreading all them into this fieldset.
        $bform->addElement('header', 'othersettings', get_string('othersettings', 'form'));
        $bform->setExpanded('othersettings', false);
        $grantoptions = $server->getGrantTypes();
        foreach ($grantoptions as $key => $value) {
            $grantoptions[$key] = $key . ": " . get_string($key . "_explanation", "local_oauth");
        }
        // TODO Consider replacing this with checkbox for granttypes.
        $select = $bform->addElement('select', 'granttypes', get_string('grant_types', 'local_oauth'), $grantoptions);
        $bform->addHelpButton('granttypes', 'grant_types', 'local_oauth');
        $select->setMultiple(true);

        $scopes = $server->supported_scopes();
        $scopeoptions = [];
        foreach ($scopes as $scope) {
            $scopeoptions[$scope] = $scope;
            // TODO Add explantions, list included claims via:  get_string($scope."_explanation", "local_oauth").
        }
        $select = $bform->addElement('select', 'scope', get_string('scope', 'local_oauth'), $scopeoptions);
        $select->setMultiple(true);

        // TODO This shouldn't hide userid if client_credentials + other is selected.
        // This does not work $bform->hideIf('userid', 'granttypes', 'in', ['client_credentials']);.

        $options = [
            'ajax' => 'core_search/form-search-user-selector',
            'noselectionstring' => get_string('username'),
        ];
        $bform->addElement('autocomplete', 'userid', get_string('user_id', 'local_oauth'), [], $options);

        $bform->addElement('checkbox', 'noconfirmation', get_string('no_confirmation', 'local_oauth'));
        $bform->addHelpButton('noconfirmation', 'no_confirmation', 'local_oauth');
        $bform->setType('noconfirmation', PARAM_BOOL);

        $this->add_action_buttons();
    }

    /**
     * Validate form data.
     * @param array $data parameter passed by the form
     * @param array $files files passed by the form
     */
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        if (!isset($data['client_id'])) {
            return $errors;
        }

        if ($DB->record_exists('local_oauth_clients', ['client_id' => $data['client_id']])) {
            $errors['client_id'] = get_string('client_id_existing_error', 'local_oauth');
        }

        return $errors;
        // TODO Validate redirect url.
    }
}
