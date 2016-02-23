<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('login/includes/db_connect.php');
include_once ('login/includes/functions.php');

sec_session_start();

if (login_check($mysqli) == true):


  if (isset($_POST['submit']))
  {
	
    $message = "";
	//echo "<br >";
	//echo "<br >";
	//echo "<br >";
	//echo "<br >";
	//echo "<br >";
	//echo "2016-02-24 05:14:24";
	$userid = $_SESSION['user_id'];
    $flieger = ""; if (isset($_POST['flieger'])) $flieger = $_POST['flieger'];
    $vontag = ""; if (isset($_POST['vontag'])) $vontag = $_POST['vontag'];
    $bistag = ""; if (isset($_POST['bistag'])) $bistag = $_POST['bistag'];
    $vontag_orig = $vontag;
    $bistag = ""; if (isset($_POST['bistag'])) $bistag = $_POST['bistag'];
    $bistag_orig = $bistag;
    $vonzeit = ""; if (isset($_POST['vonzeit'])) $vonzeit = $_POST['vonzeit'];
    $biszeit = ""; if (isset($_POST['biszeit'])) $biszeit = $_POST['biszeit'];

    $tmp = explode(".", $vontag);
    $vontag = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];
    $tmp = explode(".", $bistag);
    $bistag = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];

    $von =  $vontag.' '.$vonzeit.':00';
    $bis =  $bistag.' '.$biszeit.':00';
   
    // TODO: check values...

	$query = "INSERT INTO `calmarws_test`.`reservationen` ( `id` , `timestamp` , `userid` , `fliegerid` , `von` , `bis`) VALUES ( NULL , CURRENT_TIMESTAMP , '$userid', '$flieger', '$von', '$bis');";

  	echo $query;

    $mysqli->query($query); 

    $res = $mysqli->query("SELECT `flieger` from `flieger` WHERE `id` = $flieger;");
    $obj = $res->fetch_object();

	$message = "<p><b style='color: green;'>Die Reservierung wurde eingetragen!</b></p>";
    $message .= "<div class='center'>";
    $message .= "<table>";
    $message .= "<tr><td style='text-align: right;'>Von:</td><td><b>$vontag_orig</b> / <b>$vonzeit Uhr</b></td></tr>";
    $message .= "<tr><td style='text-align: right;'>Bis:</td><td><b>$bistag_orig</b> / <b>$biszeit Uhr</b></td></tr>";
    $message .= "<tr><td style='text-align: right;'>Flieger:</td><td><b>".$obj->flieger."</b></td></tr>";
    $message .= "</table>";
    $message .= "</div>";
	$message .= "<p style='margin-top: 40px;'>Zum <a href='index.php'>Überblick</a></p>";
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

  $query = "SELECT * FROM `flieger`;";
  $res = $mysqli->query($query); 
  $flieger_option = "";
  while($obj = $res->fetch_object())
    $flieger_option .= "<option value='".$obj->id."'>".$obj->flieger." (".$obj->id.")</option>";
    
  ?>
  <form action='reservieren.php' method='post'>
  <div class='center'>
  <table id='user_admin'>

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

  </div>
  </main>
  </body>
<?php include ('datetime/include-date-time.js'); ?>
  </html>

<?php else :
header("Location: /reservationen/login/index.php");
exit;
endif; 
?>
