<?php

function local_oauth_generate_secret() {
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
