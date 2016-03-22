<?php

//============================================================================
// Protokoll sachen aufrauemen/leoschen

if (isset($_GET['loeschen']) && $_GET['loeschen'] != 9876543210)
{
  // get the date x days before now and delete before that.
  $time_stamp = time();
  $time_stamp = $time_stamp - intval($_GET['loeschen']) * 60 * 60 * 24; // tage

  // timestamp in der datenbank ist auch lokal
  date_default_timezone_set("Europe/Zurich");
  $now_string = date("Y-m-d H:i:s", $time_stamp);
  date_default_timezone_set('UTC');

  $query = "DELETE FROM `reser_getrimmt` WHERE `reser_getrimmt`.`timestamp` < ?";
  mysqli_prepare_execute($mysqli, $query, 's', array($now_string));
}
?>
