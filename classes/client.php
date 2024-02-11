<?php

namespace local_oauth;

require_once('../../config.php');

class client {

    public static function generate_key($client_id){
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
