<?php

# TODO move to controler
function get_authorization_from_form($url, $clientid, $scope = false) {
    global $CFG, $OUTPUT, $USER, $DB;
    require_once("{$CFG->libdir}/formslib.php");

    if (is_scope_authorized_by_user($USER->id, $clientid, $scope)) {
        return true;
    }

    $client = $DB->get_record('oauth_clients', ['client_id' => $clientid]);

    if ($client && $client->no_confirmation) {
        authorize_user_scope($USER->id, $clientid, $scope);
        return true;
    }

    $mform = new \local_oauth\form\authorize($url);
    if ($mform->is_cancelled()) {
        return false;
    } else if ($fromform = $mform->get_data() and confirm_sesskey()) {
        authorize_user_scope($USER->id, $clientid, $scope);
        return true;
    }

    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
    die();
}

# TODO move to user
function is_scope_authorized_by_user($userid, $clientid, $scope = false) {
    global $DB;
    if (!$scope) {
        $scope = 'login';
    }
    return $DB->record_exists('oauth_user_auth_scopes', ['client_id' => $clientid, 'scope' => $scope, 'user_id' =>  $userid]);
}

# TODO move to user
function authorize_user_scope($userid, $clientid, $scope = false) {
    global $DB;
    if (!$scope) {
        $scope = 'login';
    }
    $record = new StdClass();
    $record->client_id = $clientid;
    $record->user_id = $userid;
    $record->scope = $scope;

    $DB->insert_record('oauth_user_auth_scopes', $record);
}
