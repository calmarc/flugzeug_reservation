<?php

//============================================================================
// Loeschen gedruckt

if (isset($_POST['loeschen']))
{
  $user_id = $_POST['user_id'];

  $query = "DELETE FROM `mfgcadmin_reservationen`.`piloten` WHERE `piloten`.`id` = ?;";
  mysqli_prepare_execute($mysqli, $query, 'i', array ($user_id));
  $query = "DELETE FROM `mfgcadmin_reservationen`.`reservationen` WHERE `reservationen`.`user_id` = ?;";
  mysqli_prepare_execute ($mysqli, $query, 'i', array ($user_id));

  list($pilot_nr_pad2, $name2) = get_pilot_from_user_id($mysqli, $user_id);
  write_status_message ($mysqli, "[Pilot gelöscht]", $_SESSION['user_id'], "[{$pilot_nr_pad2}] $name2");

  // man hat sich selber geloesch.. delete $_SESSION (ausloggen)

  if (intval($_SESSION['user_id']) ==  intval($user_id)) {
    header("Location: /reservationen/login/logout.php");
    exit;
  }

  header("Location: pilot_admin.php");
  exit;
}

//============================================================================
// Daten der Piloten updaten

if (isset($_POST['updaten']))
{
  $user_id = intval($_POST['user_id']);
  $pilot_nr = intval($_POST['pilot_nr']);
  $name = trim($_POST['name']);
  $natel = trim($_POST['natel']);
  $telefon = trim($_POST['telefon']);
  $email = trim($_POST['email']);
  $admin = $_POST['admin'];
  $checkflug = trim($_POST['checkflug']);
  $gesperrt = trim($_POST['gesperrt']);
  $password = trim($_POST['password']);

  if ($admin == "ja")
    $admin_nr = 1;
  else
    $admin_nr = 0;

  if ($gesperrt == "ja")
    $gesperrt_bol = TRUE;
  else
    $gesperrt_bol = FALSE;

  //=====
  //  checkflug checken.. und mail scharf machen.

  // convert into mysql-date
  if ($checkflug != "")
  {
    list ($t, $m, $y) =  explode(".", $checkflug);
    $t = str_pad($t, 2, "0", STR_PAD_LEFT);
    $m = str_pad($m, 2, "0", STR_PAD_LEFT);
    $checkflug = "$y-$m-$t";
  }
  else
    $checkflug = "0000-00-00";

  $res = $mysqli->query("SELECT * FROM `piloten` WHERE `piloten`.`id` = {$user_id} LIMIT 1;");
  $obj = $res->fetch_object();

  date_default_timezone_set("Europe/Zurich");
  $date_t = date("Y-m-d", time());
  date_default_timezone_set('UTC');

  // email scharf wenn noetig (weil wieder gut ist jetzt)
  if ($obj->email_gesch == TRUE && ($checkflug > $date_t || $checkflug == "0000-00-00"))
    mysqli_prepare_execute($mysqli, "UPDATE `mfgcadmin_reservationen`.`piloten` SET `email_gesch` = '0' WHERE `piloten`.`id` = ?;", 'i', array ($user_id));
    // TODO status message ausgeben - pilot nicht mehr gesperrt oder so

  // passwort mit salt.. generieren.. und seperat eintragen
  // TODO: evt function schreiben fuer das passwort gezinkel.
  if ($password != "")
  {
    $query= "SELECT `salt` FROM `piloten` WHERE `id` = {$user_id} LIMIT 1;";
    $res = $mysqli->query($query);
    $obj = $res->fetch_object();

    $password = hash('sha512', $password);
    $password = hash('sha512', $password . $obj->salt);

    $query = "UPDATE `mfgcadmin_reservationen`.`piloten` SET `password` = ? WHERE `piloten`.`id` = ?; ";
    mysqli_prepare_execute($mysqli, $query, 'si', array ($password, $user_id));
  }

  // UPDATE user data
  $query = "UPDATE `mfgcadmin_reservationen`.`piloten` SET `pilot_nr` = ?, `email` = ?, `admin` = ?, `name` = ?, `telefon` = ?, `natel` = ?, `checkflug` = ?, `gesperrt` = ? WHERE `piloten`.`id` = ?; ";
  $_SESSION['pilot_nr'] = $pilot_nr;
  mysqli_prepare_execute($mysqli, $query, 'isissssii', array ($pilot_nr, $email, $admin_nr, $name, $telefon, $natel, $checkflug, $gesperrt_bol, $user_id));

  header("Location: pilot_admin.php");
  exit;
}
//----------------------------------------------------------------------------
?>
