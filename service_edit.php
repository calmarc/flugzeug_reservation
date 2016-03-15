<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

sec_session_start();

date_default_timezone_set("Europe/Zurich");
if (!isset($_SESSION['tag'])) $_SESSION['tag'] = date('d', time());
if (!isset($_SESSION['monat'])) $_SESSION['monat'] = date('m', time());
if (!isset($_SESSION['jahr'])) $_SESSION['jahr'] = date('Y', time());
date_default_timezone_set('UTC');

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_admin($mysqli) == FALSE) { header("Location: /reservationen/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

// von der uebersicht
if (isset($_GET['flieger_id']) && $_GET['flieger_id'] > 0)
{
  $flieger_id = $_GET['flieger_id'];

  $query = "SELECT * FROM `flieger` WHERE `id` = '$flieger_id' LIMIT 1;";
  $res = $mysqli->query($query); 

  if ($res->num_rows != 1)
  {
    header('Location: /reservationen/index.php');
    exit;
  }
  $flieger_id = $_GET['flieger_id'];
  $flieger_name = $res->fetch_object()->flieger;

  if (isset($_GET['action'], $_GET['service_id']) && $_GET['action'] == "del" && $_GET['service_id'] > 0)
  {
	if ($stmt = $mysqli->prepare("DELETE FROM `calmarws_test`.`service_eintraege` WHERE `service_eintraege`.`id` = ?;"))
	{
	  $stmt->bind_param('i', $_GET['service_id']);
	  if (!$stmt->execute()) 
	  {
		header('Location: /reservationen/login/error.php?err=Registration failure: DELETE');
		exit;
	  }
	}
  }
}
else if (isset($_POST['submit']))
{
  // TODO ziemlich double maessig mit landungs-eintrag.. 
  $flieger_id = ""; if (isset($_POST['flieger_id'])) $flieger_id = $_POST['flieger_id'];
  $tag = ""; if (isset($_POST['tag'])) $tag = $_POST['tag'];
  $monat = ""; if (isset($_POST['monat'])) $monat = $_POST['monat'];
  $jahr = ""; if (isset($_POST['jahr'])) $jahr = $_POST['jahr'];
  $zaehlerstand = ""; if (isset($_POST['zaehlerstand'])) $zaehlerstand = $_POST['zaehlerstand'];
  $verantwortlich = ""; if (isset($_POST['verantwortlich'])) $verantwortlich = $_POST['verantwortlich'];
  $verantwortlich = intval($verantwortlich);

  $zaehler_minute = intval($zaehlerstand) * 60;
  $zaehler_minute += round($zaehlerstand * 100, 0, PHP_ROUND_HALF_UP) % 100;

  $_SESSION['flieger_id']  = $flieger_id;
  $_SESSION['tag']  = $tag;
  $_SESSION['monat']  = $monat;
  $_SESSION['jahr']  = $jahr;

  $tag = str_pad($tag, 2, "0", STR_PAD_LEFT);
  $monat = str_pad($monat, 2, "0", STR_PAD_LEFT);
  $date = "$jahr-$monat-$tag";

  $query = "SELECT * FROM `flieger` WHERE `id` = '$flieger_id' LIMIT 1;";
  $res = $mysqli->query($query); 
  $flieger_name = $res->fetch_object()->flieger;

  $error_msg = "";

  $z_max = -1;

  if ($stmt = $mysqli->prepare("INSERT INTO `calmarws_test`.`service_eintraege` (
	  `id` ,
	  `user_id` ,
	  `flieger_id` ,
	  `datum` ,
	  `zaehler_minute`
	  )
	  VALUES (
	  NULL , ?, ?, ?, ?
	  )"))
  {
	$stmt->bind_param('iisi', $verantwortlich, $flieger_id, $date, $zaehler_minute);
	if (!$stmt->execute()) 
	{
		header('Location: /reservationen/login/error.php?err=Registration failure: INSERT');
		exit;
	}
  }
}
else
{
  header('Location: /reservationen/index.php');
  exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content=
  "width=device-width, initial-scale=1.0">
  <title>Service Eintrag</title>
  <meta name="title" content="Service Eintrag">
  <meta name="keywords" content="Service,Eintrag">
  <meta name="description" content="Service Eintrag">
  <meta name="generator" content="Calmar + Vim + Tidy">
  <meta name="owner" content="calmar.ws">
  <meta name="author" content="candrian.org">
  <meta name="robots" content="all">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" type="text/css" href="/reservationen/css/reservationen.css">

</head>
<!--[if IE]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<body>

<?php include_once('includes/usermenu.php'); ?>
<main>
  <div id="formular_innen">

  <h1>Service Liste - <span style="color: #cc3300;"><?php echo $flieger_name; ?></span></h1>

<?php
$hidden = '<input type="hidden" name="flieger_id" value="'.$flieger_id.'" />';
?>
  <form action='/reservationen/service_edit.php' method='post'>
<?php echo $hidden; ?>
    <div class='center'>
      <table class='user_admin'>
        <tr class="trblank">
          <td><b>Datum:</b></td>
          <td>
            <select size="1" name="tag" style="width: 46px;">
              <?php combobox_tag($_SESSION['tag']); ?>
            </select> <b>.</b> 
            <select size="1" name="monat" style="width: 46px;">
              <?php combobox_monat($_SESSION['monat']); ?>
            </select> <b>.</b> 
            <select size="1" name="jahr" style="width: 86px;">
              <?php combobox_jahr($_SESSION['jahr']); ?>
            </select>
          </td>
        </tr>
        <tr class="trblank">
          <td><b>Verantwortlich:</b></td>
          <td>
<?php

$res = $mysqli->query("SELECT * FROM `members` WHERE `admin` > 0 ORDER BY `pilotid` ASC;");
echo '<select size="1" style="width: 15em;" name="verantwortlich">';
while ($obj = $res->fetch_object())
{
  $selected = "";
  if ($_SESSION['user_id'] == $obj->pilotid)
    $selected = "selected='selected'";
  echo "<option $selected value='".$obj->pilotid."'>".$obj->name."</option>";
}
echo '</select>';

?>
          </td>
        </tr>
        <tr class="trblank">
          <td><b>Bei Zählerstand:</b></td>
          <td><input name="zaehlerstand" style="width: 80px;" required="required" type="number" step="0.01" /></td>
        </tr>
      </table>
    <input class='submit_button' type='submit' name='submit' value='Neuen Service eintragen' />
    </div>
  </form>
    <br />
    <br />
    <hr />
    <br />
    <h3><span style="color: #cc0000;"><?php echo $flieger_name; ?></span> Service-Einträge</h3>

  <div class='center'>
    <table class='vertical_table'>
    <tr>
      <th style="background-color: #99ff99;"></th>
      <th>Datum</th>
      <th>Zählerstand</th>
      <th>Verantwortlich</th>
    </tr>
  <?php

$query = "SELECT `service_eintraege`.`id`,
                 `service_eintraege`.`user_id`,
                 `members`.`name`,
                 `service_eintraege`.`zaehler_minute`,
                 `service_eintraege`.`datum`
         FROM `service_eintraege` LEFT OUTER JOIN `members` ON `members`.`pilotid` = `service_eintraege`.`user_id` 
         WHERE `flieger_id` = '".$flieger_id."'  ORDER BY `zaehler_minute` DESC LIMIT 50;";

$res = $mysqli->query($query);

while ($obj = $res->fetch_object())
{
  list ($jahr, $monat, $tag) = preg_split('/[- ]/', $obj->datum);

  $z_min = $obj->zaehler_minute;

  $zaehlerstand = intval($z_min / 60).".";
  $zaehlerstand .= str_pad(intval($z_min % 60), 2, "0", STR_PAD_LEFT)."h";

  $name = $obj->name;
  $zaehler_min = $obj->zaehler_minute;
  $service_id = $obj->id;
  $user_id = $obj->user_id;

  $edit_link = '<a onclick="return confirm(\'Service-Eintrag wirklich löschen?\')" href="service_edit.php?action=del&amp;service_id='.$service_id.'&amp;flieger_id='.$flieger_id.'"><img src="/reservationen/bilder/delete.png" alt="loeschen" /></a>';

  echo ' <tr>
          <td>'.$edit_link.'</td>
          <td>'.$tag.'.'.$monat.'.'.$jahr.'</td><td style="text-align: right;">'.$zaehlerstand.'</td><td>'.$obj->name.'</td>
        </tr>';
}

?>
      </table>
    </div>
  </div>
</main>
</body>
</html>
