<?php

// von der uebersicht

if (isset($_GET['flieger_id']) && $_GET['flieger_id'] > 0)
{
  $flieger_id = $_GET['flieger_id'];

  // todo: eventuell fliegercheck. function
  $query = "SELECT * FROM `flieger` WHERE `id` = '$flieger_id' LIMIT 1;";
  $res = $mysqli->query($query);

  // flieger ID checken
  if ($res->num_rows != 1)
  {
    header('Location: /reservationen/index.php');
    exit;
  }

  if (isset($_GET['action'], $_GET['service_id']) && $_GET['action'] == "del" && $_GET['service_id'] > 0)
  {
    $query = "DELETE FROM `mfgcadmin_reservationen`.`service_eintraege` WHERE `service_eintraege`.`id` = ?;";
    mysqli_prepare_execute($mysqli, $query, 'i', array ($_GET['service_id']));
    write_status_message($mysqli, "[Service: Eintrag gelöscht]", "[{$_SESSION['pilot_id']}] {$_SESSION['name']}");
  }
}
else if (isset($_POST['submit']))
{
  $flieger_id = $_POST['flieger_id'];
  $tag = $_POST['tag'];
  $monat = $_POST['monat'];
  $jahr = $_POST['jahr'];
  $zaehlerstand = $_POST['zaehlerstand'];
  $verantwortlich = intval($_POST['verantwortlich']);

  // TODO werden fuer die defautls gebraucht. Besser andere woerter nehmen da
  // das eher nach return values fuer chart toenen
  $_SESSION['flieger_id']  = $flieger_id;
  $_SESSION['tag']  = $tag;
  $_SESSION['monat']  = $monat;
  $_SESSION['jahr']  = $jahr;

  list($zaehler_minute,$digit_minute) = computer_minute_from_zaehlerstand($zaehlerstand);

  $tag = str_pad($tag, 2, "0", STR_PAD_LEFT);
  $monat = str_pad($monat, 2, "0", STR_PAD_LEFT);
  $datum = "$jahr-$monat-$tag";

  $error_msg = check_zaehlerstand($zaehlerstand, $digit_minute);

  // ueberpruefen ob mit service eintrag mit zaehlereintrag ok..
  $res_x = $mysqli->query("SELECT MAX(`zaehler_minute`) AS `zaehler_minute` FROM `zaehler_eintraege` WHERE `flieger_id` = '{$flieger_id}';");
  $obj_x = $res_x->fetch_object();
  $min = $obj_x->zaehler_minute;

  if ($error_msg == "")
  {
    if ($min < $zaehler_minute)
      $error_msg = "HINWEIS: Der Service-Eintrag ist grösser als der letzte Landungseintrag!<br />Allenfall korrigieren.";

    $query = "INSERT INTO `mfgcadmin_reservationen`.`service_eintraege` (
        `id` , `user_id` , `flieger_id` , `datum` , `zaehler_minute`) VALUES ( NULL , ?, ?, ?, ?)";
    mysqli_prepare_execute($mysqli, $query, 'iisi', array ($verantwortlich, $flieger_id, $datum, $zaehler_minute));

    $pilot_id_pad = str_pad($_SESSION['pilot_id'], 3, "0", STR_PAD_LEFT);
    write_status_message($mysqli, "[Service: Neuer Eintrag]", "[{$pilot_id_pad}] {$_SESSION['name']}");
  }
}
else
{
  header('Location: /reservationen/index.php');
  exit;
}

?>
