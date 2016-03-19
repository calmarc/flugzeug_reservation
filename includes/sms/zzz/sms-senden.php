<?php

require_once 'Aspsms.php';
require_once 'Request.php';
require_once 'Exception.php';

use Aspsms\Aspsms;

// define your
define('USER_KEY', 'armin.beck@dsl.li');
define('USER_PASS', 'mfg#ch:a_b');
// set optional attributes
$options = array(
    "AffiliateId" => "205567",
    "Originator" => "MFGC.ch",
);
// array with numbers and the generated unique tracking code. You should store this informations
// to a database to request tracking informations later on.
$recipients = array(
    "4565-".uniqid(microtime()) => "0041 79 433 66 77"
);

// create the aspsms object with they user_key, user_pass and options
$aspsms = new Aspsms(USER_KEY, USER_PASS, $options);

// send the message to the network

if (!$aspsms->sendTextSms("Deine Reservation wurde aktiviert!", $recipients)) {
    echo "<p>Something went wrong while sending your Message to ASPSMS.net!</p>";
    echo $aspsms->getSendStatus();
}
?>
