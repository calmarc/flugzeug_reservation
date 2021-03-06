<?php

// braucht man auch ganz unten HTML
$user_id = $_SESSION['user_id'];

// falls admin fuer jemanden Eintraege macht.
if (isset($_POST['user_id']))
  $user_id = $_POST['user_id'];

// wird auch unten gebraucht bei der Reservations-Ausgabe
date_default_timezone_set("Europe/Zurich");
$local_datetime = date("Y-m-d H:i:s", time());
date_default_timezone_set("UTC");

if (isset($_POST['submit']))
{
  $flugzeug_id = $_POST['flugzeug_id'];
  $von_tag = $_POST['von_tag'];
  $von_monat = $_POST['von_monat'];
  $von_jahr = $_POST['von_jahr'];
  $von_stunde = $_POST['von_stunde'];
  $von_minute = $_POST['von_minute'];
  $bis_tag = $_POST['bis_tag'];
  $bis_monat = $_POST['bis_monat'];
  $bis_jahr = $_POST['bis_jahr'];
  $bis_stunde = $_POST['bis_stunde'];
  $bis_minute = $_POST['bis_minute'];

  $_SESSION['flugzeug_id']  = $flugzeug_id;
  $_SESSION['von_tag']  = $von_tag;
  $_SESSION['von_monat']  = $von_monat;
  $_SESSION['von_jahr']  = $von_jahr;
  $_SESSION['von_stunde']  = $von_stunde;
  $_SESSION['von_minute']  = $von_minute;
  $_SESSION['bis_tag']  = $bis_tag;
  $_SESSION['bis_monat']  = $bis_monat;
  $_SESSION['bis_jahr']  = $bis_jahr;
  $_SESSION['bis_stunde']  = $bis_stunde;
  $_SESSION['bis_minute']  = $bis_minute;

  $von_tag = str_pad($von_tag, 2, "0", STR_PAD_LEFT);
  $von_monat = str_pad($von_monat, 2, "0", STR_PAD_LEFT);
  $von_stunde = str_pad($von_stunde, 2, "0", STR_PAD_LEFT);
  $von_minute = str_pad($von_minute, 2, "0", STR_PAD_LEFT);
  $bis_tag = str_pad($bis_tag, 2, "0", STR_PAD_LEFT);
  $bis_monat = str_pad($bis_monat, 2, "0", STR_PAD_LEFT);
  $bis_stunde = str_pad($bis_stunde, 2, "0", STR_PAD_LEFT);
  $bis_minute = str_pad($bis_minute, 2, "0", STR_PAD_LEFT);

  $von_date = "{$von_jahr}-{$von_monat}-{$von_tag} {$von_stunde}:{$von_minute}:00";
  $bis_date = "{$bis_jahr}-{$bis_monat}-{$bis_tag} {$bis_stunde}:{$bis_minute}:00";

  $error_msg = "";
  if ($bis_date <= $von_date)
    $error_msg .= "'Von' Zeit nicht grösser als 'bis' Zeit.<br />";

  if ($von_stunde == "21" && $von_minute == "30" || $bis_stunde == "21" && $bis_minute == "30")
    $error_msg .= "21:30 liegt ausserhalb der Grenzen.<br />";

  if ($von_stunde == "21")
    $error_msg .= "Ab 21:{$von_minute} Uhr kann man nicht reservieren.<br />Bitte stattdessen den nächsten Tag verwenden!<br />";

  if ($bis_stunde == "07" && $bis_minute == "00")
    $error_msg .= "Auf 7:00 Uhr kann man nicht reservieren.<br />Bitte stattdessen auf den Vortag 21:00 Uhr buchen!<br />";

  if (strtotime($bis_date) - strtotime($von_date) > 60 * 60 * 24 * 31)
    $error_msg .= "Eine Reservation darf nicht länger als 31 Tage dauern.<br />";

  if ($von_date <= $local_datetime)
    $error_msg .= "Die Reservierung liegt in der Vergangenheit.<br />";

  // check if it goes into a flugverbot (998)
  $flugverbot_id = get_user_id_from_pilot_nr($mysqli, "998");

  if (check_inside_flugverbot($mysqli, $von_date, $bis_date, $flugzeug_id))
    $error_msg .= "Die Reservation geht in ein Flugverbot rein. Bitte ändern.";

  // CHECK LEVEL of standby
  remove_zombies($mysqli);

  $level = check_level($mysqli, $flugzeug_id, $von_date, $bis_date) - 1;
  if ($level >= 3)
    $error_msg .= "Es hat bereits zuviele Standby's [$level] in diesem Zeitraum.<br /><br />Es wurde keine Reservierung gebucht!<br />";


  if ($error_msg == "")
  {
    // CURRENT_TIMESTAMP =  Zurich = server-local seems to
    $query = "INSERT INTO `mfgcadmin_reservationen`.`reservationen`
      ( `id` , `timestamp` , `user_id` , `flugzeug_id` , `von` , `bis`) VALUES
      ( NULL , CURRENT_TIMESTAMP , ?, ?, ?, ?);";
    mysqli_prepare_execute ($mysqli, $query, 'iiss', array ($user_id, $flugzeug_id,$von_date, $bis_date));

    // wenn flugverbot, timestamp = + 10 jahre = neu, kleine prioritaet
    if ($user_id == $flugverbot_id)
    {
      $last_id = $mysqli->insert_id;
      $query = "UPDATE `reservationen` SET `timestamp` = DATE_ADD(`timestamp`, INTERVAL 10 YEAR) WHERE `reservationen`.`id` = {$last_id};";
      $mysqli->query($query);
    }


    $datum = mysql2chtimef ($von_date, $bis_date, FALSE);
    write_status_message($mysqli, "[Reservation]", $_SESSION['user_id'], "Neu: $datum ");

    if (isset($_SESSION['plan']) && $_SESSION['plan'] == 'monatsplan')
      header("Location: index.php?show=monatsplan&tag={$von_tag}&monat={$von_monat}&jahr={$von_jahr}");
    else
      header("Location: index.php?tag={$von_tag}&monat={$von_monat}&jahr={$von_jahr}");
  }
}
// vom Chart.. die werte mal in die sesseion eintragen.. damit die comboes damit
// gefuellt werden koennen.

else if (isset($_GET['flugzeug_id']) && isset($_GET['tag']) && isset($_GET['monat']) && isset($_GET['jahr']))
{

  $_SESSION['von_stunde'] = ""; if (isset($_GET['stunde'])) $_SESSION['von_stunde'] = $_GET['stunde'];
  $_SESSION['von_minute'] = ""; if (isset($_GET['minute'])) $_SESSION['von_minute'] = $_GET['minute'];

  $_SESSION['flugzeug_id']  = $_GET['flugzeug_id'];
  $flugzeug_id = $_SESSION['flugzeug_id'];
  $_SESSION['von_tag']  = $_GET['tag'];
  $_SESSION['von_monat']  = $_GET['monat'];
  $_SESSION['von_jahr']  = $_GET['jahr'];

  $_SESSION['bis_tag']  = $_SESSION['von_tag'];
  $_SESSION['bis_monat']  = $_SESSION['von_monat'];
  $_SESSION['bis_jahr']  = $_SESSION['von_jahr'];
  $_SESSION['bis_stunde']  = $_SESSION['von_stunde'];
  $_SESSION['bis_minute']  = $_SESSION['von_minute'];
}
else
{
  header('Location: /reservationen/index.php');
  // else nothing to do here
}

?>
