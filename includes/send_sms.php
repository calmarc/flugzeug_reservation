<?php

require_once 'sms/Aspsms.php';
require_once 'sms/Request.php';
require_once 'sms/Exception.php';
use Aspsms\Aspsms;

function sendsms($mysqli, $natel, $txt)
{
  $res = $mysqli->query("SELECT * FROM `diverses` WHERE `funktion` = 'sms_login' LIMIT 1;");
  $obj = $res->fetch_object();
  $user_key = $obj->data1;
  $user_pass = $obj->data2;

  // set optional attributes
  $options = array(
      //"AffiliateId" => "205567",
      "Originator" => "MFGC.ch"
  );

  $natel = str_replace("+", "00", $natel);
  $natel = preg_replace("/[^0-9]/", "", $natel);

  $tracking_number = "4565-".uniqid(microtime());

  $recipients = array(
      $tracking_number => $natel
  );

  if (!is_numeric($natel) or strlen($natel) < 5)
    return array("", "", "Natel-Nummer fehlerhaft");

  $ret_val = ""; // kommen nur fehler rein...

  // create the aspsms object with they user_key, user_pass and options
  $aspsms = new Aspsms($user_key, $user_pass, $options);
  if (!$aspsms->sendTextSms($txt, $recipients))
      $ret_val =  $aspsms->getSendStatus();

  $credits = $aspsms->credits();
  return array($credits, $tracking_number, $ret_val);
}

function sms_delivery_status($mysqli, $tracking_number)
{
  $res = $mysqli->query("SELECT * FROM `diverses` WHERE `funktion` = 'sms_login' LIMIT 1;");
  $obj = $res->fetch_object();
  $user_key = $obj->data1;
  $user_pass = $obj->data2;

  // set optional attributes
  $options = array(
      "Originator" => "MFGC.ch"
  );

  // create the aspsms object with they user_key, user_pass and options
  $aspsms = new Aspsms($user_key, $user_pass, $options);
  $delivery_status = $aspsms->deliveryStatus($tracking_number);
  return $delivery_status;
}
