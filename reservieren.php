<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('login/includes/db_connect.php');
include_once ('login/includes/functions.php');

sec_session_start();

$curstamp = time(); // wird einige male gebraucht


if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }

  // braucht man auch unten
  $userid = $_SESSION['user_id'];

  if (isset($_GET['action'], $_GET['reservierung']) && $_GET['action'] == 'del' && intval($_GET['reservierung']) > 0 ){

    // entry must be owned by this logged-in user. must be in the future still..
    $query = "SELECT `von` FROM `reservationen` WHERE `id` = ".$_GET['reservierung']." AND `userid` = '$userid' AND `von` > FROM_UNIXTIME($curstamp) LIMIT 1;";
    $res = $mysqli->query($query);
    if ($res->num_rows < 1){
      header("Location: reservieren.php");
      exit;
    }

	$query = "DELETE FROM `calmarws_test`.`reservationen` WHERE `reservationen`.`id` = ".intval($_GET['reservierung']).";";
    $mysqli->query($query); 
	// TODO: check is everything went well
  }
  else if (isset($_POST['submit']))
  {
	
    $message = "";

	//echo "<br >";
	//echo "<br >";
	//echo "<br >";
	//echo "<br >";
	//echo "<br >";
	//echo "2016-02-24 05:14:24";
    
    $flieger = ""; if (isset($_POST['flieger'])) $flieger = $_POST['flieger'];
    $vontag = ""; if (isset($_POST['vontag'])) $vontag = $_POST['vontag'];
    $bistag = ""; if (isset($_POST['bistag'])) $bistag = $_POST['bistag'];
    $vontag_orig = $vontag;
    $bistag = ""; if (isset($_POST['bistag'])) $bistag = $_POST['bistag'];
    $bistag_orig = $bistag;
    $vonzeit = ""; if (isset($_POST['vonzeit'])) $vonzeit = $_POST['vonzeit'];
    $biszeit = ""; if (isset($_POST['biszeit'])) $biszeit = $_POST['biszeit'];

    $vonstamp = strtotime ($vontag.' '.$vonzeit);
    $bisstamp = strtotime ($bistag.' '.$biszeit);
    
    // TODO: check values...
    $error_msg = "";
    if ($bisstamp <= $vonstamp){
      $error_msg = "'Von' [$vontag_orig $vonzeit] nicht grösser als 'bis' [$bistag_orig $biszeit].<br /><br />Es wurde keine Reservierung gebucht!";
    }
    if ($vonstamp <= $curstamp){
      $error_msg = "Die Reservierung [$vontag_orig $vonzeit] liegt in der Vergangenheit.<br /><br />Es wurde keine Reservierung gebucht!<br />";
    }
     

    if ($error_msg == ""){
      $tmp = explode(".", $vontag);
      $vontag = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];
      $tmp = explode(".", $bistag);
      $bistag = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];

      $von =  $vontag.' '.$vonzeit.':00';
      $bis =  $bistag.' '.$biszeit.':00';

      $query = "INSERT INTO `calmarws_test`.`reservationen` ( `id` , `timestamp` , `userid` , `fliegerid` , `von` , `bis`) VALUES ( NULL , CURRENT_TIMESTAMP , '$userid', '$flieger', '$von', '$bis');";

      $mysqli->query($query); 

      $res = $mysqli->query("SELECT `flieger` from `flieger` WHERE `id` = $flieger;");
      $obj = $res->fetch_object();

      $dauer_m = (($bisstamp - $vonstamp) / 60);
      $dauer_h = intval($dauer_m / 60);
      $dauer_m = $dauer_m % 60;

      $message = "<p><b style='color: green;'>Die Reservierung wurde eingetragen!</b></p>";
      $message .= "<div class='center'>";
      $message .= "<table>";
      $message .= "<tr><td style='text-align: right;'>Von:</td><td><b>$vontag_orig</b> / <b>$vonzeit Uhr</b></td></tr>";
      $message .= "<tr><td style='text-align: right;'>Bis:</td><td><b>$bistag_orig</b> / <b>$biszeit Uhr</b></td></tr>";
      $message .= "<tr><td style='text-align: right;'>Flieger:</td><td><b>".$obj->flieger."</b></td></tr>";
      $message .= "<tr><td style='text-align: right;'>Dauer:</td><td><b>".$dauer_h."h ".$dauer_m."m</b></td></tr>";
      $message .= "</table>";
      $message .= "</div>";
      $message .= "<p style='margin-top: 40px;'>Zurück zu den <a href='reservieren.php'>Reservationen</a></p>";
    }
  }

  ?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content=
    "width=device-width, initial-scale=1.0">
    <title>Benutzer Editieren - Administration</title>
    <meta name="title" content="Benutzer Administration">
    <meta name="keywords" content="Benutzer,Administration">
    <meta name="description" content="Benutzer Administration">
    <meta name="generator" content="Calmar + Vim + Tidy">
    <meta name="owner" content="calmar.ws">
    <meta name="author" content="candrian.org">
    <meta name="robots" content="all">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="/reservationen/reservationen.css">
	<link rel="stylesheet" type="text/css" href="datetime/jquery.datetimepicker.css"/>

    <script type="text/JavaScript" src="js/forms.js"></script> 
	<style type="text/css">
	.custom-date-style { background-color: red !important; }
	.input{	}
	.input-wide{ width: 500px; }
	</style>


  </head>

  <!--[if IE]>
  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->

  <body>

  <?php include_once('includes/usermenu.php'); ?>
  <main>

  <div id="formular_innen">

  <h1>Flieger reservieren</h1>

  <?php

  if (isset($message) && $message != ""){
    echo $message;
    echo '</div></main></body></html>';
    exit;
  }
  if (isset($error_msg) && $error_msg != ""){
    echo "<p><b style='color: red;'>$error_msg</b></p>";
  }

  $query = "SELECT * FROM `flieger`;";
  $res = $mysqli->query($query); 
  $flieger_option = "";
  while($obj = $res->fetch_object())
    $flieger_option .= "<option value='".$obj->id."'>".$obj->flieger." (".$obj->id.")</option>";
    
  ?>
  <form action='reservieren.php' method='post'>
  <div class='center'>
  <table class='user_admin'>

  <tr><td><b>Flieger</b></td><td><select size='1' name='flieger'><?php echo $flieger_option; ?></select></td></tr>

  <tr>
	<td style="text-align: right;"><b>Von (Tag/Zeit):</b></td>
    <td style="text-align: left;"><input style="width: 120px;"  name="vontag" class="fixbreite" required="required" type="text" id="vontag" /> / <input style="width: 70px;" name="vonzeit" class="fixbreite" required="required"  type="text" id="vonzeit" /></td>
  </tr>
  <tr>
	<td style="text-align: right;"><b>Bis (Tag/Zeit):</b></td>
    <td style="text-align: left;"><input style="width: 120px;"  name="bistag" class="fixbreite" required="required" type="text" id="bistag" /> / <input style="width: 70px;"  name="biszeit" class="fixbreite" required="required"  type="text" id="biszeit" /></td>
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

  $query = "SELECT `reservationen`.`id`, `reservationen`.`von`, `reservationen`.`bis`, `flieger`.`flieger`  FROM `reservationen` JOIN `flieger` ON `flieger`.`id` = `reservationen`.`fliegerid` WHERE `userid` = $userid AND `von` >= '$date' ORDER BY `von` DESC;";
  $res = $mysqli->query($query); 

  while ($obj = $res->fetch_object()){

      list( $tag, $zeit) = explode(" ", $obj->von);
      $tmp = explode(":", $zeit);
      $vonzeit = $tmp[0].':'.$tmp[1];
      $tmp = explode("-", $tag);
      $vontag = $tmp[2].'.'.$tmp[1].'.'.preg_replace('/../',"",$tmp[0], 1);
      list( $tag, $zeit)  = explode(" ", $obj->bis);
      $tmp = explode(":", $zeit);
      $biszeit = $tmp[0].':'.$tmp[1];
      $tmp = explode("-", $tag);
      $bistag = $tmp[2].'.'.$tmp[1].'.'.preg_replace('/../',"",$tmp[0], 1);

      if ($vontag == $bistag)
        $datum = "$vontag: <i>$vonzeit - $biszeit Uhr</i>";
      else
        $datum = "$vontag <i>$vonzeit</i> - $bistag <i>$biszeit</i>";

      echo '<tr><td><a onclick="return confirm(\'Wirklich löschen?\')" href="reservieren.php?action=del&amp;reservierung='.$obj->id.'">[löschen]</a></td><td>'.$datum.'</td><td>'.$obj->flieger.'</td></tr>';
  }

  $query = "SELECT * FROM `reservationen` JOIN `flieger` ON `flieger`.`id` = `reservationen`.`fliegerid` WHERE `userid` = $userid AND `von` < '$date' ORDER BY `von` DESC;";
  $res = $mysqli->query($query); 

  while ($obj = $res->fetch_object()){
      list( $tag, $zeit) = explode(" ", $obj->von);
      $tmp = explode(":", $zeit);
      $vonzeit = $tmp[0].':'.$tmp[1];
      $tmp = explode("-", $tag);
      $vontag = $tmp[2].'.'.$tmp[1].'.'.preg_replace('/../',"",$tmp[0], 1);
      list( $tag, $zeit)  = explode(" ", $obj->bis);
      $tmp = explode(":", $zeit);
      $biszeit = $tmp[0].':'.$tmp[1];
      $tmp = explode("-", $tag);
      $bistag = $tmp[2].'.'.$tmp[1].'.'.preg_replace('/../',"",$tmp[0], 1);

      if ($vontag == $bistag)
        $datum = "$vontag: <i>$vonzeit - $biszeit Uhr</i>";
      else
        $datum = "$vontag <i>$vonzeit</i> - $bistag <i>$biszeit</i>";
      echo '<tr><td></td><td style="color: grey;">'.$datum.'</td><td style="color: grey;">'.$obj->flieger.'</td></tr>';
  }

?>
      </table>
    </div>

  </div>
  </main>
  </body>
<?php include ('datetime/include-date-time.js'); ?>
  </html>
