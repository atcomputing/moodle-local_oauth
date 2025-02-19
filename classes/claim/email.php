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

/**
 * class that implements oidc email claims
 * @link https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
 */
class email implements claim {

    /**
     * Get email claim.
     * @param array $user core_user user user want the email claim from
     * @return array() Returns email information of user
     */
    public function claim($user) {
        $claims = [
            'email' => $user->email,
            // Not inplemented email_verified.
        ];
        return $claims;
    }
}
