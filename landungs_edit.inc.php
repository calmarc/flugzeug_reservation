<?php

$flieger = "0";
if (isset($_GET['flieger_id']))
  $flieger_id = intval($_GET['flieger_id']);

if (isset($_POST['flieger_id']))
  $flieger_id = intval($_POST['flieger_id']);

$query = "SELECT `flieger` FROM `flieger` WHERE `id` = '$flieger_id' LIMIT 1;";
$res = $mysqli->query($query);

if ($res->num_rows != 1)
{
  header('Location: /reservationen/index.php');
  exit;
}
//flieger_id ist OK

$zaehler_id = "0";
if (isset($_GET['zaehler_id']))
  $zaehler_id = intval($_GET['zaehler_id']);

if (isset($_POST['zaehler_id']))
  $zaehler_id = intval($_POST['zaehler_id']);

$query = "SELECT * FROM `zaehler_eintraege` WHERE `id` = '$zaehler_id' LIMIT 1;";
$res2 = $mysqli->query($query);
if ($res2->num_rows != 1)
{
  header('Location: /reservationen/index.php');
}
$obj2 = $res2->fetch_object();

//TODO ???????????????????????? wieso nicht oben?
if (!check_admin($mysqli))
{
  if (intval($obj2->user_id) != intval($_SESSION['user_id']))
    {
      header('Location: /reservationen/index.php');
      exit;
    }
}
//zaehler_id ist OK (gehoert Piloten oder Admin)

$obj = $res->fetch_object();
$flieger_txt = $obj->flieger;

if (isset($_POST['loeschen']))
{
  $query = "DELETE FROM `mfgcadmin_reservationen`.`zaehler_eintraege` WHERE `zaehler_eintraege`.`id` = ?";
  mysqli_prepare_execute($mysqli, $query, 'i', array ($zaehler_id));

  header("Location: landungs_eintrag.php?flieger_id=$flieger_id");
  exit;
}
else if (isset($_POST['edit']))
{
  $tag = ""; if (isset($_POST['tag'])) $tag = intval($_POST['tag']);
  $monat = ""; if (isset($_POST['monat'])) $monat = intval($_POST['monat']);
  $jahr = ""; if (isset($_POST['jahr'])) $jahr = intval($_POST['jahr']);
  $zaehlerstand = ""; if (isset($_POST['zaehlerstand'])) $zaehlerstand = $_POST['zaehlerstand'];

  $zaehler_minute = intval($zaehlerstand) * 60;
  $digit_minute = round($zaehlerstand * 100, 0, PHP_ROUND_HALF_UP) % 100;
  $zaehler_minute += $digit_minute;

  $tag = str_pad($tag, 2, "0", STR_PAD_LEFT);
  $monat = str_pad($monat, 2, "0", STR_PAD_LEFT);
  $datum = "$jahr-$monat-$tag";

  $error_msg = check_zaehlerstand($zaehlerstand, $digit_minute);

  if ($error_msg == "")
  {
    $query = "UPDATE `mfgcadmin_reservationen`.`zaehler_eintraege` SET `datum` = ?, `zaehler_minute` = ? WHERE `zaehler_eintraege`.`id` = ?;";
    mysqli_prepare_execute($mysqli, $query, 'sii', array ($datum, $zaehler_minute, $zaehler_id));

    header("Location: landungs_eintrag.php?flieger_id=$flieger_id");
    exit;
  }
}

?>
