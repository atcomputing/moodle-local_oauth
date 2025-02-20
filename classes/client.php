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
 * @copyright   2024 AT Computing
 * @author      Rens Sikma <r.sikma@atcomping.nl>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_oauth;

/**
 * class that manges settings for client for this IdP.
 */
class client {
    /** @var int $id primary for this client in the database */
    public int $id; // Has to be pulic now because the way its send to database in update.

    /** @var string $clientid name of the client */
    public string $clientid;

    /** @var string $clientsecret password of this client*/
    public string $clientsecret;

    /** @var string $redirecturi addres of the clinet to send user to after identification*/
    public string $redirecturi;

    /** @var array $granttypes types of oauth flows server will accept for this client*/
    public array $granttypes;

    /** @var array $scope the client is allowed to request, which in our case are the claims*/
    public array $scope;

    /** @var int $userid if client grantype used is client_authenticat. which user the client inperonates */
    public int $userid;

    /** @var int $noconfirmation if user is asked to confirm that client is allowed to get this information of the user*/
    public int $noconfirmation;

    /**
     * Initialze the client class.
     * @param int $clientid
     * @param string $redirecturi
     * @param array $granttypes
     * @param array $scope
     * @param int $userid
     * @param bool $noconfirmation
     */
    public function __construct($clientid, $redirecturi, $granttypes, $scope, $userid, $noconfirmation) {
        $this->clientid = $clientid;
        $this->redirecturi = $redirecturi;
        $this->granttypes = $granttypes;
        $this->scope = $scope;
        $this->userid = $userid;
        $this->noconfirmation = $noconfirmation;
        $this->clientsecret = self::generate_secret();
    }

    /**
     * Getter for id.
     */
    public function id() {
        if (isset($this->id)) {
            return $this->id;
        }
        return null;
    }

    // TODO Use $storage->getClientDetails instead .
    /**
     * Get client by client id from database.
     *
     * @param int $id clientid
     */
    public static function get_client_by_id($id) {
        global $DB;

        $row = $DB->get_record('local_oauth_clients', ['id' => $id]);
        if (!$row) {
            return null;
        }

        $client = new Client(
            $row->client_id,
            $row->redirect_uri,
            explode(" ", $row->grant_types),
            explode(" ", $row->scope),
            $row->user_id,
            $row->no_confirmation
        );
        $client->id = $row->id;
        $client->clientsecret = $row->client_secret;
        return $client;
    }

    /**
     * Create or Update row for this client in the database
     */
    public function store() {
        // Can't use $storatge->setClientDetails because that is using no_confirmation?
        global $DB;
        $row = [
            'client_id' => $this->clientid,
            'client_secret' => $this->clientsecret,
            'redirect_uri' => $this->redirecturi,
            'grant_types' => implode(" ", $this->granttypes),
            'scope' => implode(" ", $this->scope),
            'user_id' => $this->userid,
            'no_confirmation' => $this->noconfirmation,
        ];
        if (isset($this->id)) {
            $row['id'] = $this->id;
            $DB->update_record('local_oauth_clients', $row);
        } else {
            $DB->insert_record('local_oauth_clients', $row);
            $this->generate_key_pair($this->clientid);
        }
    }

    /**
     * Delete client from database.
     * This also deletes the client keys from the database
     */
    public function delete() {

        global $DB;
        $DB->delete_records('local_oauth_clients', ['id' => $this->id]);
        $DB->delete_records('local_oauth_public_keys', ['client_id' => $this->clientid]);
    }

    /**
     * Generate public private key pair
     *
     * @param int $clientid
     */
    public static function generate_key_pair($clientid) {
        global $DB;
        $config = [
            "digest_alg" => "sha512",
            "private_key_bits" => 1028,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        // Create the private and public key.
        $res = openssl_pkey_new($config);

        // Extract the private key from $res to $privkey.
        openssl_pkey_export($res, $privkey);

        // Extract the public key from $res to $pubkey.
        $pubkey = openssl_pkey_get_details($res);
        $pubkey = $pubkey["key"];

        $record = new \stdClass();
        $record->client_id = $clientid;
        $record->public_key = $pubkey;
        $record->private_key  = $privkey;

        $DB->insert_record('local_oauth_public_keys', $record);
    }

    /**
     * Generate a random password.
     */
    public static function generate_secret() {
        // Get a whole bunch of random characters from the OS.
        $fp = fopen('/dev/urandom', 'rb');
        $entropy = fread($fp, 32);
        fclose($fp);

        // Takes our binary entropy, and concatenates a string which represents the current time to the microsecond.
        $entropy .= uniqid(mt_rand(), true);

        // Hash the binary entropy.
        $hash = hash('sha512', $entropy);

        // Chop and send the first 80 characters back to the client.
        return substr($hash, 0, 48);
    }
}
