<?php

// von der uebersicht

if (isset($_GET['flugzeug_id']) && $_GET['flugzeug_id'] > 0)
{
  $flugzeug_id = $_GET['flugzeug_id'];

  $query = "SELECT * FROM `flugzeug` WHERE `id` = '$flugzeug_id' LIMIT 1;";
  $res = $mysqli->query($query);

  // flugzeug ID checken
  if ($res->num_rows != 1)
  {
    header('Location: /reservationen/index.php');
    exit;
  }

  if (isset($_GET['action'], $_GET['service_id']) && $_GET['action'] == "del" && $_GET['service_id'] > 0)
  {
    $query = "DELETE FROM `mfgcadmin_reservationen`.`service_eintraege` WHERE `service_eintraege`.`id` = ?;";
    mysqli_prepare_execute($mysqli, $query, 'i', array ($_GET['service_id']));
    write_status_message($mysqli, "[Service-Eintrag]", $_SESSION['user_id'], "Gelöscht");
  }
}
else if (isset($_POST['submit']))
{
  $flugzeug_id = $_POST['flugzeug_id'];
  $tag = $_POST['tag'];
  $monat = $_POST['monat'];
  $jahr = $_POST['jahr'];
  $zaehlerstand = $_POST['zaehlerstand'];
  $verantwortlich = intval($_POST['verantwortlich']);

  $_SESSION['flugzeug_id_se']  = $flugzeug_id;
  $_SESSION['tag_se']  = $tag;
  $_SESSION['monat_se']  = $monat;
  $_SESSION['jahr_se']  = $jahr;

  list($zaehler_minute,$digit_minute) = compute_minute_from_zaehlerstand($zaehlerstand);

  $tag = str_pad($tag, 2, "0", STR_PAD_LEFT);
  $monat = str_pad($monat, 2, "0", STR_PAD_LEFT);
  $datum = "$jahr-$monat-$tag";

  $error_msg = check_zaehlerstand($zaehlerstand, $digit_minute);

  // ueberpruefen ob mit service eintrag mit zaehlereintrag ok..
  $res_x = $mysqli->query("SELECT MAX(`zaehler_minute`) AS `zaehler_minute` FROM `zaehler_eintraege` WHERE `flugzeug_id` = '{$flugzeug_id}';");
  $obj_x = $res_x->fetch_object();
  $min = $obj_x->zaehler_minute;

  if ($error_msg == "")
  {
    if ($min < $zaehler_minute)
      $error_msg = "HINWEIS: Der Service-Eintrag ist grösser als der letzte Landungseintrag!<br />Allenfall korrigieren.";

    $query = "INSERT INTO `mfgcadmin_reservationen`.`service_eintraege` (
        `id` , `user_id` , `flugzeug_id` , `datum` , `zaehler_minute`) VALUES ( NULL , ?, ?, ?, ?)";
    mysqli_prepare_execute($mysqli, $query, 'iisi', array ($verantwortlich, $flugzeug_id, $datum, $zaehler_minute));

    write_status_message($mysqli, "[Service-Eintrag]", $_SESSION['user_id'], "Neu");
  }
}
else
{
  header('Location: /reservationen/index.php');
  exit;
}

?>
