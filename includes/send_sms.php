<?php

require_once 'sms/Aspsms.php';
require_once 'sms/Request.php';
require_once 'sms/Exception.php';
use Aspsms\Aspsms;

function sendsms($natel, $txt)
{
  $res = $mysqli->query("SELECT * FROM `diverses` WHERE `funktion` = 'sms_login' LIMIT 1;");
  $obj = $res->fetch_object();
  $user_key = $obj->data1;
  $user_pass = $obj->data2;

  // set optional attributes
  $options = array(
      "AffiliateId" => "205567",
      "Originator" => "MFGC.ch",
  );
  // array with numbers and the generated unique tracking code. You should store this informations
  // to a database to request tracking informations later on.
  //
  $natel = str_replace(" ", "", $natel);
  $natel = str_replace("'", "", $natel);
  $natel = str_replace("+", "00", $natel);

  $recipients = array(
      "4565-".uniqid(microtime()) => $natel
  );


  if (!is_numeric($natel) or strlen($natel) < 5) 
    return FALSE;

  // create the aspsms object with they user_key, user_pass and options
  $aspsms = new Aspsms($user_key, $user_pass, $options);
  if (!$aspsms->sendTextSms($txt, $recipients)) 
      return $aspsms->getSendStatus();
  return TRUE;
}
