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
 * Plugin index file
 *
 * @package     local_oauth
 * @copyright   2024 Rens Sikma <r.sikma@atcomping.nl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_oauth\claim;

class profile implements claim {

    public function claim($user) {
        global $CFG, $PAGE;
        $userpicture = new \user_picture($user);
        $claims = [
            // Not implemented 'website' 'gender' 'birthdate' 'locale' 'name'.
            'family_name' => $user->lastname,
            'given_name' => $user->firstname,
            'middle_name' => $user->middlename,
            'nickname' => $user->alternatename,
            'preferred_username' => $user->username,
            'profile' => $CFG->wwwroot."/user/profile.php?id=".$user->id,
            'picture' => $user->picture ? $userpicture->get_url($PAGE)->raw_out() : null,
            // NOTE moodle timezone is 99 if you set timezone is same as server.
            'zoneinfo' => $user->timezone == '99' ? date_default_timezone_get() : $user->timezone,
            'updated_at' => $user->timemodified,
        ];
        return $claims;
    }
}
