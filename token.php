<?php

require_once '../../config.php';

\core\session\manager::write_close();

$server = new \local_oauth\server();

// Handle a request for an OAuth2.0 Access Token and send the response to the client
$server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();