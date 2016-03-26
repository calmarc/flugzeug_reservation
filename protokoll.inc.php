<?php

//============================================================================
// Protokoll sachen loeschen / aufrauemen.

if (isset($_POST['cleanup']))
{
  // get the date x days before now and delete before that.
  $time_stamp = time();
  $time_stamp = $time_stamp - 547 * 60 * 60 * 24; // 1.5 jahre

  date_default_timezone_set("Europe/Zurich");
  $now_string = date("Y-m-d H:i:s", $time_stamp);
  date_default_timezone_set('UTC');

  $query = "DELETE FROM `status_meldungen`  WHERE `status_meldungen`.`timestamp` < ?";
  //$query = "DELETE FROM `status_meldungen`  WHERE `status_meldungen`.`timestamp` < {$now_string}";
  //echo $query;
  mysqli_prepare_execute($mysqli, $query, 's', array($now_string));
}

?>
