<?php

//============================================================================
// Reservationen aufrauemen/leoschen

if (isset($_POST['reservationen']))
{
  // get the date x days before now and delete before that.
  $time_stamp = time();
  $time_stamp = $time_stamp - 2 * 365 * 60 * 60 * 24; // 2 jahre

  // timestamp in der datenbank ist auch lokal
  date_default_timezone_set("Europe/Zurich");
  $now_string = date("Y-m-d H:i:s", $time_stamp);
  date_default_timezone_set('UTC');

  $query = "DELETE FROM `reservationen` WHERE `reservationen`.`timestamp` < ?";
  mysqli_prepare_execute($mysqli, $query, 's', array($now_string));
}
?>
