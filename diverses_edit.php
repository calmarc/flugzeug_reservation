<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

sec_session_start();

//============================================================================
// Berechtigungen checken

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_admin($mysqli) == FALSE) { header("Location: /reservationen/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

//----------------------------------------------------------------------------

//============================================================================
// loeschen form wurde gedrueckt

include_once ('diverses_edit.inc.php');

print_html_to_body('Piloten editieren - Administration', '');
include_once('includes/usermenu.php');

?>

  <main>
    <div id="formular_innen">

    <h1>Piloten editieren</h1>

<?php
if (isset($_GET['id']))
{
  $id = $_GET['id'];
}
else
{
  echo "<h3>Keine gültgie ID erhalten. Bitte <a href='pilot_admin.php'>wiederhohlen</a> oder an mac@calmar.ws melden</h3>";
  exit;
}

$query = "SELECT * FROM `diverses` WHERE `diverses`.`id` = '{$id}'";

$res = $mysqli->query($query);
$obj = $res->fetch_object();

echo "
<form action='diverses_edit.php' method='post'>
  <input type='hidden' name='id' value='{$obj->id}' />
    <div class='center'>
    <table class='vtable'>
      <tr class='trblank'>
        <td><b>Funktion:</b></td><td><b>{$obj->funktion}</b></td>
      </tr>
      <tr>
        <td><b>Daten1:</b></td><td><input style='width: 21em;' type='text' name='data1' value='{$obj->data1}'></td>
      </tr>";
if ($obj->funktion == "sms_login_aspsms_ch")
{
  echo "
      <tr>
        <td><b>Daten2:</b></td><td><input style='width: 21em;' type='text' name='data2' value='{$obj->data2}'></td>
      </tr>";
} ?>

    </table>
    <input class='submit_button' type='submit' name='updaten' value='Aenderungen abschicken' />
  </div>
</form>
    </div>
  </main>
</body>
</html>
