<?php

\core\session\manager::write_close();

// attributes still missing from old project 'id,auth,description'

$server = new \local_oauth\server();

// Handle a user_info request for an OAuth2.0 Access Token and send the response to the client
$server->handleUserInfoRequest(OAuth2\Request::createFromGlobals())->send();
