<?php

if (isset($_POST['loeschen']))
{
  $pilot_id = $_POST['pilot_id'];

  if ($stmt = $mysqli->prepare("DELETE FROM `calmarws_test`.`piloten` WHERE `piloten`.`pilot_id` = ?;"))
  {
    $stmt->bind_param('i', $pilot_id);
    if (!$stmt->execute()) 
    {
        header('Location: /reservationen/login/error.php?err=Registration failure: DELETE');
        exit;
    }
  }

  // man hat sich selber geloesch.. delete $_SESSION (ausloggen)
  
  if (intval($_SESSION['pilot_id']) ==  intval($pilot_id)) {
    header("Location: /reservationen/login/logout.php");
    exit;
  }

  header("Location: pilot_admin.php");
  exit;
}
//----------------------------------------------------------------------------
//============================================================================
// Daten der Piloten updaten

if (isset($_POST['updaten']))
{
  $id = intval($_POST['id']);
  $pilot_id = intval($_POST['pilot_id']);
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

  $res = $mysqli->query("SELECT * FROM `piloten` WHERE `piloten`.`id` = $id LIMIT 1;");
  $obj = $res->fetch_object();

  date_default_timezone_set("Europe/Zurich");
  $date_t = date("Y-m-d", time()); 
  date_default_timezone_set('UTC');

  // email scharf wenn noetig (weil wieder gut ist jetzt)
  if ($obj->email_gesch == TRUE && ($checkflug > $date_t || $checkflug == "0000-00-00"))
    mysqli_prepare_execute($mysqli, "UPDATE `calmarws_test`.`piloten` SET `email_gesch` = '0' WHERE `piloten`.`id` = ?;", 'i', array ($id));

  // passwort mit salt.. generieren.. und eintragen
  if ($password != "")
  {
    $query= "SELECT `salt` FROM `piloten` WHERE `id` = $id LIMIT 1;";
    $res = $mysqli->query($query); 
    $obj = $res->fetch_object();

    $password = hash('sha512', $password);
    $password = hash('sha512', $password . $obj->salt);

    $query = "UPDATE `calmarws_test`.`piloten` SET `password` = ? WHERE `piloten`.`id` = ?; ";
    mysqli_prepare_execute($mysqli, $query, 'si', array ($password, $id));
  }

  // UPDATE USER DATA
  $query = "UPDATE `calmarws_test`.`piloten` SET `pilot_id` = ?, `email` = ?, `admin` = ?, `name` = ?, `telefon` = ?, `natel` = ?, `checkflug` = ?, `gesperrt` = ? WHERE `piloten`.`id` = ?; ";
  mysqli_prepare_execute($mysqli, $query, 'isissssii', array ($pilot_id, $email, $admin_nr, $name, $telefon, $natel, $checkflug, $gesperrt_bol, $id));

  header("Location: pilot_admin.php");
  exit;
}
//----------------------------------------------------------------------------
?>
