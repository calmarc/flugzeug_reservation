<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');
include_once ('includes/graphic.php');

sec_session_start();

$curstamp = time(); // wird einige male gebraucht

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

// braucht man auch ganz unten
$userid = $_SESSION['user_id'];

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

  $vonstamp = strtotime ($von_date);
  $bisstamp = strtotime ($bis_date);

  // TODO: check values...
  $error_msg = "";
  if ($bisstamp <= $vonstamp)
    $error_msg = "'Von' Zeit nicht grösser als 'bis' Zeit.<br /><br />Es wurde keine Reservierung gebucht!";

  if ($vonstamp <= $curstamp)
    $error_msg = "Die Reservierung liegt in der Vergangenheit.<br /><br />Es wurde keine Reservierung gebucht!<br />";

  // CHECK LEVEL of standby
  remove_zombies($mysqli);
  $level = check_level($mysqli, $flieger_id, $von_date, $bis_date) - 1;
  if ($level >= 3)
    $error_msg = "Es hat bereits zuviele Standby's [$level] in diesem Zeitraum.<br /><br />Es wurde keine Reservierung gebucht!<br />";
   
  if ($error_msg == ""){

    $query = "INSERT INTO `calmarws_test`.`reservationen` 
      ( `id` , `timestamp` , `userid` , `fliegerid` , `von` , `bis`) VALUES 
      ( NULL , CURRENT_TIMESTAMP , '$userid', '$flieger_id', FROM_UNIXTIME($vonstamp), FROM_UNIXTIME($bisstamp));";

    $mysqli->query($query);
    header("Location: index.php?tag=$von_tag&monat=$von_monat&jahr=$von_jahr");
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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content=
  "width=device-width, initial-scale=1.0">
  <title>Flugzeug reservieren</title>
  <meta name="title" content="Flugzeug reservieren">
  <meta name="keywords" content="Flugzeug reservieren">
  <meta name="description" content="Flugzeug reservieren">
  <meta name="generator" content="Calmar + Vim + Tidy">
  <meta name="owner" content="calmar.ws">
  <meta name="author" content="candrian.org">
  <meta name="robots" content="all">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" type="text/css" href="/reservationen/css/reservationen.css">
  <link rel="stylesheet" type="text/css" href="datetime/jquery.datetimepicker.css"/>
</head>
<!--[if IE]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<body>

<?php include_once('includes/usermenu.php'); ?>
<main>
  <div id="formular_innen">

  <h1>Flugzeug reservieren</h1>

<?php
if (isset($msg) && $msg != "")
{
  echo "$msg</div></main></body></html>";
  exit;
}

if (isset($error_msg) && $error_msg != "")
  echo "<p><b style='color: red;'>$error_msg</b></p>";

if (isset($flieger_id))
{
  $query = "SELECT * FROM `flieger` WHERE `id` = '$flieger_id' LIMIT 1;";
  $res = $mysqli->query($query); 
  $obj = $res->fetch_object();
  $fliegertxt = $obj->flieger;
  $hidden = '<input type="hidden" name="flieger_id" value="'.$flieger_id.'" />';
}
else
{
  $flieger_id = "";
  $query = "SELECT * FROM `flieger`;";
  $res = $mysqli->query($query); 
  $fliegertxt = "";
  while($obj = $res->fetch_object())
     $fliegertxt .= "<option value='".$obj->id."'>".$obj->flieger." (".$obj->id.")</option>";
   $fliegertxt = "<select size='1' name='flieger_id'>$fliegertxt<select>";
  $hidden = "";
}

$von_stunde = $_SESSION['von_stunde'];
if ($von_stunde == "")
  $von_stunde = "7";

$von_minute = $_SESSION['von_minuten'];
if ($von_minute == "")
  $von_minute = "0";

$bis_stunde = $_SESSION['bis_stunde'];
if ($bis_stunde == "")
  $bis_stunde = "7";

$bis_minute = $_SESSION['bis_minuten'];
if ($bis_minute == "")
  $bis_minute = "0";

?>
  <form action='reservieren.php' method='post'>
<?php echo $hidden; ?>
    <div class='center'>
      <table class='user_admin reservierung'>
        <tr class="trblank">
          <td><b>Pilot:</b></td>
          <td><b>[<?php echo str_pad($_SESSION['pilotid'], 3, "0", STR_PAD_LEFT).'] '.$_SESSION['name']; ?></b></td>
        </tr>
        <tr class="trblank">
          <td><b>Flugzeug:</b></td>
          <td><b><?php echo $fliegertxt; ?></b></td>
        </tr>
        <tr class="raser2">
          <td><b>Datum von:</b></td>
          <td><input value="<?php echo $_SESSION['von_tag']; ?>" name="von_tag" style="width: 46px;;" min="1" max="31" required="required" type='number' /> <b>.</b> 
          <input value="<?php echo $_SESSION['von_monat'] ?>" name="von_monat" style="width: 46px;;" min="1" max="12" required="required" type='number' /> <b>.</b> 
          <input value="<?php echo $_SESSION['von_jahr'] ?>" name="von_jahr" style="width: 80px;" min="2016" max="2050" required="required" type='number' /></td>
        </tr>
        <tr class="raser1">
          <td><b>Zeit von:</b></td>
          <td><input value="<?php echo $von_stunde; ?>" name="von_stunde" style="width: 46px;;" min="7" max="20" required="required" type='number' /> <b>:</b>
          <input value="<?php echo $von_minute; ?>" name="von_minuten" style="width: 46px;;" min="0" max="30" step="30" required="required" type='number' /> <b>Uhr</b></td>
        </tr>
        <tr class="raser2">
          <td><b>Datum bis:</b></td>
          <td><input value="<?php echo $_SESSION['bis_tag'] ?>" name="bis_tag" style="width: 46px;;" min="1" max="31" required="required" type='number' /> <b>.</b> 
          <input value="<?php echo $_SESSION['bis_monat'] ?>" name="bis_monat" style="width: 46px;;" min="1" max="12" required="required" type='number' /> <b>.</b> 
          <input value="<?php echo $_SESSION['bis_jahr'] ?>" name="bis_jahr" style="width: 80px;" min="2016" max="2050" required="required" type='number' /></td>
        </tr>
        <tr class="raser1">
          <td><b>Zeit bis:</b></td>
          <td><input value="<?php echo $bis_stunde; ?>" name="bis_stunde" style="width: 46px;;" min="7" max="21" required="required" type='number' /> <b>:</b>
          <input value="<?php echo $bis_minute; ?>" name="bis_minuten" style="width: 46px;" min="0" max="30" step="30" required="required" type='number' /> <b>Uhr</b></td>
        </tr>
      </table>
    <input class='submit_button' type='submit' name='submit' value='Reservierung abschicken' />
    </div>
  </form>
  <div class='center'>
    <br />
    <h1>Reservationen</h1>
    <table class='user_admin'>
  <?php

// jetzt Zeit
$date = date("Y-m-d H:i:s", time());

$query = "SELECT `reservationen`.`id`, `reservationen`.`von`, `reservationen`.`bis`, `flieger`.`flieger`, `reservationen`.`fliegerid` FROM `reservationen` JOIN `flieger` ON `flieger`.`id` = `reservationen`.`fliegerid` WHERE `userid` = $userid AND `von` >= '$date' ORDER BY `von` DESC;";
$res = $mysqli->query($query); 

while ($obj = $res->fetch_object())
{
  $datum = mysql2chtimef($obj);
  echo ' <tr>
          <td><a onclick="return confirm(\'Reservation wirklich löschen?\')" 
          href="res_loeschen.php?backto=reservieren.php&amp;tag='.$_SESSION['von_tag'].'&monat='.$_SESSION['von_monat'].'&amp;jahr='.$_SESSION['von_jahr'].'&amp;flieger_id='.$flieger_id.'&amp;action=del&amp;reservierung='.$obj->id.'">[löschen]</a></td>
          <td>'.$datum.'</td><td>'.$obj->flieger.'</td>
        </tr>';
}

$query = "SELECT * FROM `reservationen` JOIN `flieger` ON `flieger`.`id` = `reservationen`.`fliegerid` WHERE `userid` = $userid AND `von` < '$date' ORDER BY `von` DESC LIMIT 5;";
$res = $mysqli->query($query); 

while ($obj = $res->fetch_object())
{
  $datum = mysql2chtimef($obj);
  echo ' <tr>
          <td></td>
          <td style="color: grey;">'.$datum.'</td>
          <td style="color: grey;">'.$obj->flieger.'</td>
        </tr>';
}

?>
      </table>
    </div>
  </div>
</main>
</body>
</html>
