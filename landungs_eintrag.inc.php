<?php

//============================================================================
// von der Chart-Uebersicht. flugzeug id checken etc.
// SESSION tag, monat und jahr von heute speicher (default spaeter)

if (isset($_GET['flugzeug_id']) && $_GET['flugzeug_id'] > 0)
{
  $flugzeug_id = $_GET['flugzeug_id'];

  $query = "SELECT * FROM `flugzeug` WHERE `id` = '{$flugzeug_id}' LIMIT 1;";
  $res = $mysqli->query($query);

  if ($res->num_rows != 1)
  {
    header('Location: /reservationen/index.php');
    exit;
  }
  date_default_timezone_set("Europe/Zurich");
  $_SESSION['tag'] = date('d', time());
  $_SESSION['monat'] = date('m', time());
  $_SESSION['jahr'] = date('Y', time());
  date_default_timezone_set('UTC');
}

//============================================================================
// Ein neuer Eintrag wurde durchgegeben

else if (isset($_POST['submit']))
{
  $flugzeug_id = $_POST['flugzeug_id'];
  $tag = $_POST['tag'];
  $monat = $_POST['monat'];
  $jahr = $_POST['jahr'];
  $zaehlerstand = $_POST['zaehlerstand'];

  // Neu setzen (neues Default)

  $_SESSION['flugzeug_id']  = $flugzeug_id;
  $_SESSION['tag']  = $tag;
  $_SESSION['monat']  = $monat;
  $_SESSION['jahr']  = $jahr;

  list($zaehler_minute,$digit_minute) = computer_minute_from_zaehlerstand($zaehlerstand);

  $tag = str_pad($tag, 2, "0", STR_PAD_LEFT);
  $monat = str_pad($monat, 2, "0", STR_PAD_LEFT);
  $datum = "$jahr-$monat-$tag";

  $error_msg = check_zaehlerstand($zaehlerstand, $digit_minute);

  if ($error_msg == "")
  {
    $query = "INSERT INTO `mfgcadmin_reservationen`.`zaehler_eintraege` (
              `id` , `user_id` , `flugzeug_id` , `datum` , `zaehler_minute`, `zaehler_umdrehungen`) VALUES ( NULL , ?, ?, ?, ?, ?)";
    mysqli_prepare_execute($mysqli, $query, 'iisii', array ($user_id, $flugzeug_id, $datum, $zaehler_minute, 0));

    list($pilot_nr_pad, $pilot_name) = get_pilot_from_user_id($mysqli, $_SESSION['user_id']);
    write_status_message($mysqli, "[Landungs-Eintrag]", "Neu: durch [{$pilot_nr_pad}] {$pilot_name}");
  }
}
else
{
  header('Location: /reservationen/index.php');
  exit;
}
?>
