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
 * Plugin token endpoint
 *
 * @package     local_oauth
 * @copyright   https://github.com/projectestac/moodle-local_oauth
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.Files.RequireLogin.Missing

require_once('../../config.php');
require_once('vendor/autoload.php');

\core\session\manager::write_close();

$server = new \local_oauth\server();

// Handle a request for an OAuth2.0 Access Token and send the response to the client.
$server->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
