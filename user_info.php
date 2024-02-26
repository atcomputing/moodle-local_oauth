<?php

require_once('../../config.php');

$server = new \local_oauth\server();

// attributes still missing from old project 'id,auth,description'
// Handle a user_info request for an OAuth2.0 Access Token and send the response to the client
$server->handleUserInfoRequest(OAuth2\Request::createFromGlobals())->send();
