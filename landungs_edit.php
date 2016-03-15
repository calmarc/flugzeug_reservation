<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

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

$query = "SELECT * FROM `zaehlereintraege` WHERE `id` = '$zaehler_id' LIMIT 1;";
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

//zaehler_id ist OK

$obj = $res->fetch_object();
$flieger_txt = $obj->flieger;

if (isset($_POST['loeschen']))
{
  if ($stmt = $mysqli->prepare("DELETE FROM `calmarws_test`.`zaehlereintraege` WHERE `zaehlereintraege`.`id` = ?"))
  {
    $stmt->bind_param('i', $zaehler_id);
    if (!$stmt->execute()) 
    {
        header('Location: /reservationen/login/error.php?err=Registration failure: DELETE');
        exit;
    }
  }
  header("Location: landungs_eintrag.php?flieger_id=$flieger_id"); 
  exit;
}
else if (isset($_POST['edit']))
{
  $tag = ""; if (isset($_POST['tag'])) $tag = intval($_POST['tag']);
  $monat = ""; if (isset($_POST['monat'])) $monat = intval($_POST['monat']);
  $jahr = ""; if (isset($_POST['jahr'])) $jahr = intval($_POST['jahr']);
  $zaehlerstand = ""; if (isset($_POST['zaehlerstand'])) $zaehlerstand = $_POST['zaehlerstand'];

  $tag = str_pad($tag, 2, "0", STR_PAD_LEFT);
  $monat = str_pad($monat, 2, "0", STR_PAD_LEFT);

  $zaehler_minute = intval($zaehlerstand) * 60;
  $zaehler_minute += round($zaehlerstand * 100, 0, PHP_ROUND_HALF_UP) % 100;

  $datum = "$jahr-$monat-$tag";

  // UPDATE USER DATA
  if ($stmt = $mysqli->prepare("UPDATE `calmarws_test`.`zaehlereintraege` SET `datum` = ?, `zaehler_minute` = ? WHERE `zaehlereintraege`.`id` = ?;")) 
  {
    $stmt->bind_param('sii', $datum, $zaehler_minute, $zaehler_id);

    if (!$stmt->execute()) 
    {
        header('Location: /reservationen/login/error.php?err=Registration failure: UPDATE');
        exit;
    }
  }
  header("Location: landungs_eintrag.php?flieger_id=$flieger_id"); 
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content=
  "width=device-width, initial-scale=1.0">
  <title>Landungs-Eintrag editieren</title>
  <meta name="title" content="Eintrag editieren">
  <meta name="keywords" content="Eintrag editieren">
  <meta name="description" content="Eintrag editieren">
  <meta name="generator" content="Calmar + Vim + Tidy">
  <meta name="owner" content="calmar.ws">
  <meta name="author" content="candrian.org">
  <meta name="robots" content="all">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="/reservationen/css/reservationen.css">
</head>

<!--[if IE]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<body>

<?php include_once('includes/usermenu.php'); ?>

  <main>
    <div id="formular_innen">

    <h1>Eintrag editieren</h1>

<?php

if (isset($_GET['zaehler_id']) && intval($_GET['zaehler_id']) > 0)
{
  $zaehler_id = $_GET['zaehler_id'];
}
else {
  echo "<h3>Keine gültgie Zahler-ID erhalten. Bitte <a href='user_admin.php'>wiederhohlen</a> oder an mac@calmar.ws melden</h3>";
  exit;
}
$query = "SELECT * FROM `zaehlereintraege` WHERE `id` = '$zaehler_id' LIMIT 1;";
$res = $mysqli->query($query); 

if ($res->num_rows != 1)
{
  header('Location: /reservationen/landungs_eintrag.php?flieger_id='.$flieger_id);
  exit;
}
$obj = $res->fetch_object();

$min = intval($obj->zaehler_minute) % 60;
$std = intval($obj->zaehler_minute / 60);
$zaehler_eintrag = $std.'.'.str_pad($min, 2, "0", STR_PAD_LEFT);

list ($jahr, $monat, $tag) = preg_split('/[- ]/', $obj->datum);

?>
      <form action='landungs_edit.php' method='post'>
        <input type='hidden' name='zaehler_id' value='<?php echo $obj->id; ?>' />
        <input type="hidden" name="flieger_id" value="<?php echo $flieger_id; ?>" />
        <div class='center'>
          <table class='user_admin two_standard'>
            <tr class="trblank">
              <td><b>Pilot</b></td>
              <td><b>[<?php echo str_pad($_SESSION['pilotid'], 3, "0", STR_PAD_LEFT).'] '.$_SESSION['name']; ?></b></td>
            </tr>
            <tr class="trblank">
              <td><b>Flieger</b></td>
              <td><b><?php echo $flieger_txt; ?></b></td>
            </tr>
            <tr>
              <td><b>Datum:</b></td>
              <td><input value="<?php echo $tag; ?>" name="tag" style="width: 46px;;" min="1" max="31" required="required" type='number' /> <b>.</b> 
              <input value="<?php echo $monat; ?>" name="monat" style="width: 46px;;" min="1" max="12" required="required" type='number' /> <b>.</b> 
              <input value="<?php echo $jahr; ?>" name="jahr" style="width: 80px;" min="2016" max="2050" required="required" type='number' /></td>
            </tr>
            <tr>
              <td><b>Zählerstand:</b></td>
              <td><input value="<?php echo $zaehler_eintrag; ?>" name="zaehlerstand" style="width: 80px;" required="required" type="number" step="0.01" /></td>
            </tr>
          </table>
        <input class='submit_button' type='submit' name='edit' value='Änderungen abschicken' />
        </div>
      </form>

      <hr style="margin: 52px 10px 84px 10px;" />

      <form action='landungs_edit.php' method='post' onsubmit="return confirm('Eintrag wirklich löschen?');">
      <input type="hidden" name="zaehler_id" value="<?php echo $zaehler_id; ?>" />
      <input type="hidden" name="flieger_id" value="<?php echo $flieger_id; ?>" />
        <div class="center">
          <p><input class="sub_loeschen" type='submit' name='loeschen' value='EINTRAG LÖSCHEN' /></p>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
