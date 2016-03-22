<?php

//============================================================================
// falls vom 'flug eintragen' edit link
// damit man beim gleichen flieger bleibt (evt in session tun lieber?)

if (isset($_GET['flieger_id']))
  $flieger_id = intval($_GET['flieger_id']);

// edit oder loeschen wurde gedrueckt
if (isset($_POST['flieger_id']))
  $flieger_id = intval($_POST['flieger_id']);

$query = "SELECT `flieger` FROM `flieger` WHERE `id` = '{$flieger_id}' LIMIT 1;";
$res = $mysqli->query($query);
$obj = $res->fetch_object();
$flieger_txt = $obj->flieger;

if ($res->num_rows != 1)
{
  header('Location: /reservationen/index.php');
  exit;
}
//flieger_id ist OK

if (isset($_GET['zaehler_id']))
  $zaehler_id = intval($_GET['zaehler_id']);

if (isset($_POST['zaehler_id']))
  $zaehler_id = intval($_POST['zaehler_id']);

$query = "SELECT * FROM `zaehler_eintraege` WHERE `id` = '{$zaehler_id}' LIMIT 1;";
$res2 = $mysqli->query($query);
if ($res2->num_rows != 1)
{
  header('Location: /reservationen/index.php');
}
//zaehler_id ist auch ok. (eigentlich mit flieger kombinieren? Egal)
$obj2 = $res2->fetch_object();
$eintrag_user_id = $obj2->user_id;

// admin_bol setzen und zusaetzlich das oder user_id muss mit eintrag entsprechen

$admin_bol = TRUE;
if (!check_admin($mysqli))
{
  if (intval($eintrag_user_id) != intval($_SESSION['user_id']))
  {
    header('Location: /reservationen/index.php');
    exit;
  }
  $admin_bol = FALSE;
}
//zaehler_id ist OK (gehoert Piloten oder Admin)

//============================================================================
// Loeschen wurde gedrueckt

if (isset($_POST['loeschen']))
{
  $query = "DELETE FROM `mfgcadmin_reservationen`.`zaehler_eintraege` WHERE `zaehler_eintraege`.`id` = ?";
  mysqli_prepare_execute($mysqli, $query, 'i', array ($zaehler_id));

  list($pilot_id_pad, $pilot_name) = get_pilot_from_user_id($mysqli, $_SESSION['user_id']);
  list($pilot_id_pad2, $pilot_name2) = get_pilot_from_user_id($mysqli, $eintrag_user_id);
  write_status_message($mysqli, "[Landungs-Eintrag]", "Gelöscht: durch [{$pilot_id_pad}] {$pilot_name}: von {$pilot_id_pad2}");

  header("Location: landungs_eintrag.php?flieger_id=$flieger_id");
  exit;
}

//============================================================================
// Neue Daten aktualieren und auf landungs_eintrag gehen.

else if (isset($_POST['edit']))
{
  $tag = intval($_POST['tag']);
  $monat = intval($_POST['monat']);
  $jahr = intval($_POST['jahr']);
  $zaehlerstand = $_POST['zaehlerstand'];
  // IST nicht immer der Fall! nur bei der tecnam
  $zaehler_umdrehungen = 0; if (isset($_POST['zaehler_umdrehungen'])) $zaehler_umdrehungen = intval($_POST['zaehler_umdrehungen']);

  $zaehler_minute = intval($zaehlerstand) * 60;
  $digit_minute = round($zaehlerstand * 100, 0, PHP_ROUND_HALF_UP) % 100;
  $zaehler_minute += $digit_minute;

  $tag = str_pad($tag, 2, "0", STR_PAD_LEFT);
  $monat = str_pad($monat, 2, "0", STR_PAD_LEFT);
  $datum = "$jahr-$monat-$tag";

  $error_msg = check_zaehlerstand($zaehlerstand, $digit_minute);

  if ($error_msg == "")
  {
    $query = "UPDATE `mfgcadmin_reservationen`.`zaehler_eintraege` SET `datum` = ?, `zaehler_minute` = ?, `zaehler_umdrehungen` = ? WHERE `zaehler_eintraege`.`id` = ?;";
    mysqli_prepare_execute($mysqli, $query, 'siii', array ($datum, $zaehler_minute, $zaehler_umdrehungen, $zaehler_id));

    list($pilot_id_pad, $pilot_name) = get_pilot_from_user_id($mysqli, $_SESSION['user_id']);
    list($pilot_id_pad2, $pilot_name2) = get_pilot_from_user_id($mysqli, $eintrag_user_id);
    write_status_message($mysqli, "[Landungs-Eintrag]", "Editiert: durch [{$pilot_id_pad}] {$pilot_name}: von [{$pilot_id_pad2}] $pilot_name2");

    header("Location: landungs_eintrag.php?flieger_id={$flieger_id}");
    exit;
  }
}

?>
