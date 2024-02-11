<?php
/**
 * NO_DEBUG_DISPLAY - disable moodle specific debug messages and any errors in output
 */
define('NO_DEBUG_DISPLAY', true);
require_once '../../config.php';

\core\session\manager::write_close();

$server = new \local_oauth\server();
$request = OAuth2\Request::createFromGlobals();

if (!$server->verifyResourceRequest($request)) {
    $logparams = ['other' => ['cause' => 'invalid_approval']];
    $event = \local_oauth\event\user_info_request_failed::create($logparams);
    $event->trigger();

    $server->getResponse()->send();
    die();
}

$token = $server->getAccessTokenData($request);
if (isset($token['user_id']) && !empty($token['user_id'])) {

    $user = $DB->get_record('user', ['id' => $token['user_id']], 'id,auth,username,idnumber,firstname,lastname,middlename,email,lang,country,phone1,address,description');
    if (!$user) {
        $logparams = ['other' => ['cause' => 'user_not_found']];
        $event = \local_oauth\event\user_info_request_failed::create($logparams);
        $event->trigger();

        // FIXME: there is no response at this stage!
        $response->send();
    }

    $client = $DB->get_record('oauth_clients', ['client_id' => $token['client_id']]);
    if (!$client) {
        // FIXME: handle error
        return;
    }

    $response = new OAuth2\Response();
    $scopeRequired = 'openid';
    if (!$server->verifyResourceRequest($request, $response, $scopeRequired)) {
        $logparams = ['relateduserid' => $user->id, 'other' => ['cause' => 'insufficient_scope']];
        $event = \local_oauth\event\user_info_request_failed::create($logparams);
        $event->trigger();

        // if the scope required is different from what the token allows, this will send a "401 insufficient_scope" error
        $response->send();
    }

    $logparams = ['userid' => $user->id];
    $event = \local_oauth\event\user_info_request::create($logparams);
    $event->trigger();

    if($client->use_email_aliases){
        list($local, $domain) = explode('@', $user->email);
        $user->email = $local . '+' . $user->username . '@' . $domain;
    }
    $enrolments = [];
    $courses = enrol_get_users_courses($user->id,true,'shortname', null);
    foreach ($courses as $course) {
        $enrolments[] = $course->shortname;
    }
    $user->enrolments = $enrolments;
    echo json_encode($user);
} else {
    $logparams = ['other' => ['cause' => 'invalid_token']];
    $event = \local_oauth\event\user_info_request_failed::create($logparams);
    $event->trigger();

    $server->getResponse()->send();
}
