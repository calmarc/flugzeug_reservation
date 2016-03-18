<?php

if (isset($_POST['submit']))
{
  $flieger_id = ""; if (isset($_POST['flieger_id'])) $flieger_id = $_POST['flieger_id'];
  $von_tag = ""; if (isset($_POST['von_tag'])) $von_tag = $_POST['von_tag'];
  $von_monat = ""; if (isset($_POST['von_monat'])) $von_monat = $_POST['von_monat'];
  $von_jahr = ""; if (isset($_POST['von_jahr'])) $von_jahr = $_POST['von_jahr'];
  $von_stunde = ""; if (isset($_POST['von_stunde'])) $von_stunde = $_POST['von_stunde'];
  $von_minuten = ""; if (isset($_POST['von_minuten'])) $von_minuten = $_POST['von_minuten'];
  $bis_tag = ""; if (isset($_POST['bis_tag'])) $bis_tag = $_POST['bis_tag'];
  $bis_monat = ""; if (isset($_POST['bis_monat'])) $bis_monat = $_POST['bis_monat'];
  $bis_jahr = ""; if (isset($_POST['bis_jahr'])) $bis_jahr = $_POST['bis_jahr'];
  $bis_stunde = ""; if (isset($_POST['bis_stunde'])) $bis_stunde = $_POST['bis_stunde'];
  $bis_minuten = ""; if (isset($_POST['bis_minuten'])) $bis_minuten = $_POST['bis_minuten'];

  $_SESSION['flieger_id']  = $flieger_id;
  $_SESSION['von_tag']  = $von_tag;
  $_SESSION['von_monat']  = $von_monat;
  $_SESSION['von_jahr']  = $von_jahr;
  $_SESSION['von_stunde']  = $von_stunde;
  $_SESSION['von_minuten']  = $von_minuten;
  $_SESSION['bis_tag']  = $bis_tag;
  $_SESSION['bis_monat']  = $bis_monat;
  $_SESSION['bis_jahr']  = $bis_jahr;
  $_SESSION['bis_stunde']  = $bis_stunde;
  $_SESSION['bis_minuten']  = $bis_minuten;

  $von_tag = str_pad($von_tag, 2, "0", STR_PAD_LEFT);
  $von_monat = str_pad($von_monat, 2, "0", STR_PAD_LEFT);
  $von_stunde = str_pad($von_stunde, 2, "0", STR_PAD_LEFT);
  $von_minuten = str_pad($von_minuten, 2, "0", STR_PAD_LEFT);
  $bis_tag = str_pad($bis_tag, 2, "0", STR_PAD_LEFT);
  $bis_monat = str_pad($bis_monat, 2, "0", STR_PAD_LEFT);
  $bis_stunde = str_pad($bis_stunde, 2, "0", STR_PAD_LEFT);
  $bis_minuten = str_pad($bis_minuten, 2, "0", STR_PAD_LEFT);

  $von_date = "$von_jahr-$von_monat-$von_tag $von_stunde:$von_minuten";
  $bis_date = "$bis_jahr-$bis_monat-$bis_tag $bis_stunde:$bis_minuten";

  $vonstamp = strtotime($von_date);
  $bisstamp = strtotime($bis_date);

  // TODO: check values...
  $error_msg = "";
  if ($bisstamp <= $vonstamp)
    $error_msg .= "'Von' Zeit nicht grösser als 'bis' Zeit.<br />";

  if ($bis_stunde == "21" && $bis_minuten == "30")
    $error_msg .= "21:30 liegt ausserhalb der Grenzen. Es kann nur bis 21:00 reserviert werden.<br />";

  if ($vonstamp <= $curstamp)
    $error_msg .= "Die Reservierung liegt in der Vergangenheit.<br /><br />Es wurde keine Reservierung gebucht!<br />";

  if (intval($bis_stunde) == 7 && intval($bis_minuten) == 0)
    $error_msg .= "Auf 7:00 Uhr kann man nicht reservieren.<br />Bitte stattdessen auf den Vortag 21:00 Uhr buchen!<br />";

  // CHECK LEVEL of standby

  remove_zombies($mysqli);

  $level = check_level($mysqli, $flieger_id, $von_date, $bis_date) - 1;
  if ($level >= 3)
    $error_msg .= "Es hat bereits zuviele Standby's [$level] in diesem Zeitraum.<br /><br />Es wurde keine Reservierung gebucht!<br />";

  if ($error_msg == ""){

    $query = "INSERT INTO `mfgcadmin_reservationen`.`reservationen`
      ( `id` , `timestamp` , `user_id` , `flieger_id` , `von` , `bis`) VALUES
      ( NULL , CURRENT_TIMESTAMP , '{$user_id}', '{$flieger_id}', FROM_UNIXTIME({$vonstamp}), FROM_UNIXTIME({$bisstamp}));";

    $mysqli->query($query);

    if (isset($_SESSION['plan']) && $_SESSION['plan'] == 'monatsplan')
      header("Location: index.php?show=monatsplan&tag={$von_tag}&monat={$von_monat}&jahr={$von_jahr}");
    else
      header("Location: index.php?tag={$von_tag}&monat={$von_monat}&jahr={$von_jahr}");
  }
}
else if (isset($_GET['flieger_id']) && isset($_GET['tag']) && isset($_GET['monat']) && isset($_GET['jahr']))
{

  $_SESSION['von_stunde'] = ""; if (isset($_GET['stunde'])) $_SESSION['von_stunde'] = $_GET['stunde'];
  $_SESSION['von_minuten'] = ""; if (isset($_GET['minute'])) $_SESSION['von_minuten'] = $_GET['minute'];

  $_SESSION['flieger_id']  = $_GET['flieger_id'];
  $flieger_id = $_SESSION['flieger_id'];
  $_SESSION['von_tag']  = $_GET['tag'];
  $_SESSION['von_monat']  = $_GET['monat'];
  $_SESSION['von_jahr']  = $_GET['jahr'];

  $_SESSION['bis_tag']  = $_SESSION['von_tag'];
  $_SESSION['bis_monat']  = $_SESSION['von_monat'];
  $_SESSION['bis_jahr']  = $_SESSION['von_jahr'];
  $_SESSION['bis_stunde']  = $_SESSION['von_stunde'];
  $_SESSION['bis_minuten']  = $_SESSION['von_minuten'];
}
else
{
  header('Location: /reservationen/index.php');
  // else nothing to do so
}

?>
