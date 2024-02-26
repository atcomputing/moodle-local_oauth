<?php

namespace local_oauth;

require_once($CFG->dirroot.'/config.php');

class client {
    public int $id; // has to be pulic now because the way its send to database in update
    public string $client_id;
    public string $client_secret;
    public string $redirect_uri;
    public array $grant_types;
    public array $scope;
    public int $user_id;
    public int $no_confirmation;

    public function __construct($client_id, $redirect_uri, $grant_types, $scope, $user_id, $no_confirmation){
        $this->client_id = $client_id;
        $this->redirect_uri = $redirect_uri;
        $this->grant_types = $grant_types;
        $this->scope = $scope;
        $this->user_id = $user_id;
        $this->no_confirmation = $no_confirmation;
        $this->client_secret = self::generate_secret();
    }

    public function id(){
        if (isset($this->id)){
            return $this->id;
        }
        return null;
    }

    // TODO use $storage->getClientDetails
    public static function get_client_by_id($id){
        global $DB;

        $row = $DB->get_record('oauth_clients', array('id' => $id));
        if (!$row) {
            return null  ;
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
        $client->client_secret= $row->client_secret;
        return $client;
    }

    public function store(){
        // TODO use $storatge->setClientDetails?
        global $DB;
        $row = (array) $this;
        $row['grant_types'] = implode(" ", $this->grant_types);
        $row['scope'] = implode(" ", $this->scope);
        if (isset($this->id)){
            unset($this->clien_id);
            if (!$DB->update_record('oauth_clients', $row)) {
                print_error('update_error', 'local_oauth');
            }
        }else{
            if (!$DB->insert_record('oauth_clients', $row)) {
                print_error('insert_error', 'local_oauth');
            }
            $this->generate_key_pair($this->client_id);
        }
    }

    public function delete(){

        global $DB;
        if (!$DB->delete_records('oauth_clients', ['id' => $this->id])) {
            print_error('delete_error', 'local_oauth');
        }
        if (!$DB->delete_records('oauth_public_keys',['client_id'=>$this->client_id])){
            print_error('delete_error', 'local_oauth');
        }
    }

    public static function generate_key_pair($client_id){
        global $DB;
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 1028,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        // Create the private and public key
        $res = openssl_pkey_new($config);

        // Extract the private key from $res to $privKey
        openssl_pkey_export($res, $privKey);

        // Extract the public key from $res to $pubKey
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

        $record = new \stdClass();
        $record->client_id =  $client_id;
        $record->public_key = $pubKey;
        $record->private_key  = $privKey;

        if (!$DB->insert_record('oauth_public_keys', $record)) {
            print_error('insert_error', 'local_oauth');
        }
    }

    public static function generate_secret() {
        // Get a whole bunch of random characters from the OS
        $fp = fopen('/dev/urandom', 'rb');
        $entropy = fread($fp, 32);
        fclose($fp);

        // Takes our binary entropy, and concatenates a string which represents the current time to the microsecond
        $entropy .= uniqid(mt_rand(), true);

        // Hash the binary entropy
        $hash = hash('sha512', $entropy);

        // Chop and send the first 80 characters back to the client
        return substr($hash, 0, 48);
    }
}
